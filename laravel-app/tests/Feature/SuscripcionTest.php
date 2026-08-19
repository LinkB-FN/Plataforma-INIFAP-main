<?php

namespace Tests\Feature;

use App\Mail\NuevaPublicacionDeInteres;
use App\Models\Publicacion;
use App\Services\NotificadorSuscripciones;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SuscripcionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('suscripciones');
        Schema::create('suscripciones', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('palabra_clave', 30);
            $table->timestamps();
            $table->unique(['email', 'palabra_clave']);
        });
    }

    public function test_guarda_los_temas_seleccionados_sin_duplicarlos(): void
    {
        $respuesta = $this->from('/')->post(route('suscripciones.store'), [
            'email' => 'PERSONA@EJEMPLO.COM',
            'palabras_clave' => ['frijol', 'garbanzo', 'frijol'],
        ]);

        $respuesta->assertRedirect('/')->assertSessionHas('suscripcion_status');
        $this->assertDatabaseCount('suscripciones', 2);
        $this->assertDatabaseHas('suscripciones', [
            'email' => 'persona@ejemplo.com',
            'palabra_clave' => 'frijol',
        ]);
    }

    public function test_rechaza_palabras_no_permitidas(): void
    {
        $this->from('/')->post(route('suscripciones.store'), [
            'email' => 'persona@ejemplo.com',
            'palabras_clave' => ['tema-inventado'],
        ])->assertRedirect('/')->assertSessionHasErrors('palabras_clave.0');

        $this->assertDatabaseCount('suscripciones', 0);
    }

    public function test_acepta_temas_adicionales_del_catalogo(): void
    {
        $this->from('/')->post(route('suscripciones.store'), [
            'email' => 'persona@ejemplo.com',
            'palabras_clave' => ['tomate', 'maiz', 'nopal'],
        ])->assertRedirect('/')->assertSessionHasNoErrors();

        $this->assertDatabaseCount('suscripciones', 3);
    }

    public function test_envia_un_solo_correo_con_las_coincidencias_del_usuario(): void
    {
        Mail::fake();
        $this->post(route('suscripciones.store'), [
            'email' => 'persona@ejemplo.com',
            'palabras_clave' => ['frijol', 'garbanzo'],
        ]);

        $publicacion = new Publicacion([
            'titulo' => 'Nuevas variedades de frijol y garbanzo',
        ]);
        $publicacion->id = 25;

        app(NotificadorSuscripciones::class)->notificar($publicacion);

        Mail::assertSent(NuevaPublicacionDeInteres::class, 1);
        Mail::assertSent(NuevaPublicacionDeInteres::class, function ($correo): bool {
            return $correo->hasTo('persona@ejemplo.com')
                && $correo->palabrasCoincidentes === ['frijol', 'garbanzo'];
        });
    }

    public function test_no_envia_correo_si_el_contenido_no_coincide(): void
    {
        Mail::fake();
        $this->post(route('suscripciones.store'), [
            'email' => 'persona@ejemplo.com',
            'palabras_clave' => ['frijol'],
        ]);

        app(NotificadorSuscripciones::class)->notificar(new Publicacion([
            'titulo' => 'Manejo sustentable de pastizales',
        ]));

        Mail::assertNothingSent();
    }

    public function test_detecta_temas_aunque_el_titulo_tenga_acentos(): void
    {
        Mail::fake();
        $this->post(route('suscripciones.store'), [
            'email' => 'persona@ejemplo.com',
            'palabras_clave' => ['maiz'],
        ]);

        app(NotificadorSuscripciones::class)->notificar(new Publicacion([
            'titulo' => 'Producción sustentable de maíz',
        ]));

        Mail::assertSent(NuevaPublicacionDeInteres::class, 1);
    }
}
