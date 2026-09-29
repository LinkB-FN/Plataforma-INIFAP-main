<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PermisosPublicacionesTest extends TestCase
{
    protected array $permisos = [];
    protected string $panel = '/biblioteca/administrador/publicaciones';

    protected function setUp(): void
    {
        parent::setUp();

        // Sustituto de la función PostgreSQL: prueba la autorización HTTP en SQLite.
        DB::connection()->getPdo()->sqliteCreateFunction('fn_tiene_permiso',
            fn ($usuario, $modulo, $accion) => (int) ($this->permisos[$modulo][$accion] ?? false), 3);
        DB::statement('CREATE TABLE tipos_publicacion (id INTEGER PRIMARY KEY, clave TEXT, nombre TEXT)');
        DB::statement('CREATE TABLE publicaciones (id INTEGER PRIMARY KEY, id_tipo INTEGER, titulo TEXT, titulo_en TEXT, ano INTEGER, url_imagen TEXT, id_autor INTEGER, muestra BOOLEAN, activo BOOLEAN, creado_en TEXT, actualizado_en TEXT)');
        DB::statement('CREATE TABLE archivos (id INTEGER PRIMARY KEY, id_publicacion INTEGER, es_principal BOOLEAN, formato TEXT, url_archivo TEXT, categoria_medio TEXT, creado_en TEXT, actualizado_en TEXT)');
        DB::table('tipos_publicacion')->insert([
            ['id' => 1, 'clave' => 'tecnica', 'nombre' => 'Técnica'],
            ['id' => 2, 'clave' => 'cientifica', 'nombre' => 'Científica'],
            ['id' => 3, 'clave' => 'ilustracion', 'nombre' => 'Ilustración'],
        ]);
        DB::table('publicaciones')->insert([
            ['id' => 1, 'id_tipo' => 1, 'titulo' => 'Documento técnico'],
            ['id' => 2, 'id_tipo' => 2, 'titulo' => 'Documento científico'],
        ]);
        $usuario = new Usuario(['nombre' => 'Editor', 'activo' => true]);
        $usuario->id = 10;
        $this->actingAs($usuario);
    }

    public function test_sin_permisos_bloquea_todas_las_acciones_y_rutas_alternativas(): void
    {
        foreach ([$this->panel, "$this->panel/crear", "$this->panel/1/editar",
            '/publicaciones/create', '/publicaciones/subir', '/publicaciones/formulario'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->post($this->panel, ['tipo_publicacion' => 'tecnica'])->assertForbidden();
        $this->put("$this->panel/1", ['tipo_publicacion' => 'tecnica'])->assertForbidden();
        $this->delete("$this->panel/1")->assertForbidden();
        $this->post('/publicaciones')->assertForbidden();
        $this->post('/files')->assertForbidden();
        $this->delete('/files/1')->assertForbidden();
        $this->assertDatabaseCount('publicaciones', 2);
    }

    public function test_solo_lectura_filtra_el_listado_y_oculta_acciones(): void
    {
        $this->permisos = ['tecnicas' => ['ver' => true]];
        $this->get($this->panel)->assertOk()->assertSee('Documento técnico')
            ->assertDontSee('Documento científico')->assertDontSee('Subir Documento')
            ->assertDontSee('>Editar</a>', false)->assertDontSee('>Borrar</button>', false);
        $this->post($this->panel, ['tipo_publicacion' => 'tecnica'])->assertForbidden();
        $this->put("$this->panel/1", ['tipo_publicacion' => 'tecnica'])->assertForbidden();
        $this->delete("$this->panel/1")->assertForbidden();
    }

    public function test_crear_solo_ofrece_tipos_autorizados_y_rechaza_otros_por_post(): void
    {
        $this->permisos = ['tecnicas' => ['crear' => true]];
        $this->get("$this->panel/crear")->assertOk()
            ->assertSee('value="tecnica"', false)->assertDontSee('value="cientifica"', false);
        $this->postJson($this->panel, ['tipo_publicacion' => 'tecnica'])->assertUnprocessable()
            ->assertJsonValidationErrors('titulo');
        $this->postJson($this->panel, ['tipo_publicacion' => 'cientifica'])->assertForbidden();
        $this->postJson($this->panel, ['tipo_publicacion' => 'ilustracion'])->assertForbidden();
        $this->postJson('/publicaciones', ['tipo_publicacion' => 'tecnica'])->assertUnprocessable();
    }

    public function test_editar_comprueba_el_tipo_original_y_el_destino(): void
    {
        $this->permisos = ['tecnicas' => ['editar' => true]];
        $this->get("$this->panel/1/editar")->assertOk();
        $this->get("$this->panel/2/editar")->assertForbidden();
        $this->putJson("$this->panel/1", ['tipo_publicacion' => 'tecnica'])
            ->assertUnprocessable()->assertJsonValidationErrors('titulo');
        $this->putJson("$this->panel/1", ['tipo_publicacion' => 'cientifica'])->assertForbidden();
        $this->putJson("$this->panel/2", ['tipo_publicacion' => 'tecnica'])->assertForbidden();
        $this->assertDatabaseHas('publicaciones', ['id' => 1, 'id_tipo' => 1]);
    }

    public function test_borrar_requiere_permiso_del_modulo_del_registro(): void
    {
        $this->permisos = ['tecnicas' => ['borrar' => true]];
        $this->delete("$this->panel/2")->assertForbidden();
        $this->delete("$this->panel/1")->assertRedirect();
        $this->assertDatabaseMissing('publicaciones', ['id' => 1]);
        $this->assertDatabaseHas('publicaciones', ['id' => 2]);
    }

    public function test_invitados_se_redirigen_al_login(): void
    {
        auth()->logout();
        $this->post($this->panel)->assertRedirect('/biblioteca/administrador/login');
        $this->post('/files')->assertRedirect('/biblioteca/administrador/login');
        $this->delete('/files/1')->assertRedirect('/biblioteca/administrador/login');
    }

    public function test_permisos_autorizados_permiten_guardar_y_actualizar(): void
    {
        $this->permisos = ['tecnicas' => ['crear' => true, 'editar' => true]];
        $this->mock(\App\Services\NotificadorSuscripciones::class)
            ->shouldReceive('notificar')->once();
        $this->post($this->panel, [
            'titulo' => 'Nueva técnica', 'tipo_publicacion' => 'tecnica', 'year' => 2026,
            'origen' => 'enlace', 'external_url' => 'https://example.com/documento.pdf',
        ])->assertRedirect(route('admin.dashboard'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('publicaciones', ['titulo' => 'Nueva técnica', 'id_tipo' => 1]);
        $this->put("$this->panel/1", [
            'titulo' => 'Técnica actualizada', 'tipo_publicacion' => 'tecnica', 'year' => 2026,
            'origen' => 'enlace', 'external_url' => 'https://example.com/documento.pdf',
        ])->assertRedirect(route('admin.dashboard'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('publicaciones', ['id' => 1, 'titulo' => 'Técnica actualizada']);
    }

    private function datosSubida(array $cambios = []): array
    {
        return array_replace(['titulo' => 'Documento de prueba', 'year' => 2026,
            'tipo_publicacion' => 'tecnica', 'origen' => 'archivo'], $cambios);
    }

    public function test_las_rutas_antiguas_usan_el_formulario_y_guardado_unicos(): void
    {
        $this->permisos = ['cientificas' => ['crear' => true]];
        foreach (['/publicaciones/create', '/publicaciones/subir', '/publicaciones/formulario'] as $url) {
            $this->get($url)->assertRedirect(route('admin.publicaciones.create'));
        }
        foreach (['/files', '/publicaciones'] as $url) {
            $this->post($url, $this->datosSubida(['tipo_publicacion' => 'cientifica',
                'origen' => 'enlace', 'external_url' => 'https://example.com/paper.pdf']))
                ->assertSessionHasNoErrors()->assertRedirect(route('admin.dashboard'));
        }
        $this->assertDatabaseCount('publicaciones', 4);
        $this->assertDatabaseCount('archivos', 2);
    }

    public function test_rechaza_contenido_vacio_ambiguo_o_no_permitido_al_crear_y_editar(): void
    {
        $this->permisos = ['tecnicas' => ['crear' => true, 'editar' => true]];
        \Illuminate\Support\Facades\Storage::fake('public');
        foreach (['POST' => $this->panel, 'PUT' => "$this->panel/1"] as $method => $url) {
            $this->json($method, $url, $this->datosSubida())->assertUnprocessable()->assertJsonValidationErrors('file');
            $this->json($method, $url, $this->datosSubida(['origen' => 'conservar']))
                ->assertUnprocessable()->assertJsonValidationErrors('origen');
            $this->json($method, $url, $this->datosSubida([
                'file' => \Illuminate\Http\UploadedFile::fake()->create('archivo.exe', 1, 'application/octet-stream'),
            ]))->assertUnprocessable()->assertJsonValidationErrors('file');
            $this->json($method, $url, $this->datosSubida([
                'file' => \Illuminate\Http\UploadedFile::fake()->create('archivo.jpg', 1, 'application/pdf'),
            ]))->assertUnprocessable()->assertJsonValidationErrors('file');
            $this->json($method, $url, $this->datosSubida([
                'file' => \Illuminate\Http\UploadedFile::fake()->create('archivo.pdf', 21000, 'application/pdf'),
            ]))->assertUnprocessable()->assertJsonValidationErrors('file');
            $this->json($method, $url, $this->datosSubida([
                'file' => \Illuminate\Http\UploadedFile::fake()->create('archivo.pdf', 1, 'application/pdf'),
                'external_url' => 'https://example.com/documento.pdf',
            ]))->assertUnprocessable()->assertJsonValidationErrors('external_url');
            $this->json($method, $url, $this->datosSubida(['origen' => 'enlace', 'external_url' => 'ftp://example.com/a.pdf']))
                ->assertUnprocessable()->assertJsonValidationErrors('external_url');
        }
        $this->assertDatabaseCount('publicaciones', 2);
        $this->assertSame([], \Illuminate\Support\Facades\Storage::disk('public')->allFiles());
    }

    public function test_subida_reemplazo_conservacion_y_borrado_limpian_solo_los_archivos_obsoletos(): void
    {
        $this->permisos = ['tecnicas' => ['crear' => true, 'editar' => true, 'borrar' => true]];
        \Illuminate\Support\Facades\Storage::fake('public');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $this->post($this->panel, $this->datosSubida([
            'file' => \Illuminate\Http\UploadedFile::fake()->create('archivo.pdf', 1, 'application/pdf'),
        ]))->assertSessionHasNoErrors();
        $id = DB::table('publicaciones')->max('id');
        $ruta = DB::table('archivos')->where('id_publicacion', $id)->value('url_archivo');
        $disk->assertExists($ruta);
        $this->put("$this->panel/$id", $this->datosSubida(['origen' => 'conservar', 'titulo' => 'Solo cambia el título']))
            ->assertSessionHasNoErrors();
        $disk->assertExists($ruta);
        $this->assertDatabaseHas('archivos', ['id_publicacion' => $id, 'url_archivo' => $ruta]);
        $this->put("$this->panel/$id", $this->datosSubida([
            'file' => \Illuminate\Http\UploadedFile::fake()->create('nuevo.pdf', 1, 'application/pdf'),
        ]))->assertSessionHasNoErrors();
        $nuevaRuta = DB::table('archivos')->where('id_publicacion', $id)->value('url_archivo');
        $this->assertNotSame($ruta, $nuevaRuta);
        $disk->assertMissing($ruta);
        $disk->assertExists($nuevaRuta);
        $this->delete("$this->panel/$id")->assertSessionHasNoErrors();
        $disk->assertMissing($nuevaRuta);
    }

    public function test_un_fallo_de_base_de_datos_elimina_la_subida_nueva(): void
    {
        $this->permisos = ['tecnicas' => ['crear' => true]];
        \Illuminate\Support\Facades\Storage::fake('public');
        \App\Models\Publicacion::creating(function () { throw new \RuntimeException('Fallo de prueba'); });
        try {
            $this->post($this->panel, $this->datosSubida([
                'file' => \Illuminate\Http\UploadedFile::fake()->create('archivo.pdf', 1, 'application/pdf'),
            ]))->assertSessionHasErrors('error');
            $this->assertSame([], \Illuminate\Support\Facades\Storage::disk('public')->allFiles());
            $this->assertDatabaseCount('publicaciones', 2);
        } finally {
            \App\Models\Publicacion::flushEventListeners();
        }
    }

    public function test_la_limpieza_respeta_archivos_compartidos_y_rutas_ajenas(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $disk->put('uploads/files/compartido.pdf', 'contenido');
        $disk->put('imagenes/conservar.png', 'contenido');
        DB::table('archivos')->insert(['id_publicacion' => 1, 'url_archivo' => 'uploads/files/compartido.pdf']);
        app(\App\Services\ArchivosPublicacion::class)->limpiar([
            'uploads/files/compartido.pdf', 'imagenes/conservar.png',
            'uploads/files/../../imagenes/conservar.png', 'https://example.com/documento.pdf',
        ]);
        $disk->assertExists('uploads/files/compartido.pdf');
        $disk->assertExists('imagenes/conservar.png');
    }
}
