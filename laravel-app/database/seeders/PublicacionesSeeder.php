<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PublicacionesSeeder extends Seeder
{
    private function parseObjectList(string $raw): array
    {
        $items = [];

        if (!preg_match_all('/\{([^{}]*)\}/s', $raw, $objectMatches)) {
            return $items;
        }

        foreach ($objectMatches[1] as $objectBody) {
            $item = [];
            if (preg_match_all('/([a-zA-Z_][a-zA-Z0-9_]*)\s*:\s*("(?:\\\\.|[^"])*"|\d+|true|false|null)/s', $objectBody, $kvMatches, PREG_SET_ORDER)) {
                foreach ($kvMatches as $kv) {
                    $key = $kv[1];
                    $value = $kv[2];

                    if ($value !== '' && $value[0] === '"') {
                        $value = substr($value, 1, -1);
                        $value = stripcslashes($value);
                    } elseif ($value === 'true' || $value === 'false') {
                        $value = $value === 'true';
                    } elseif ($value === 'null') {
                        $value = null;
                    } else {
                        $value = (int) $value;
                    }

                    $item[$key] = $value;
                }
            }

            if (!empty($item)) {
                $items[] = $item;
            }
        }

        return $items;
    }

    private function loadCatalogo(string $path, string $constName): array
    {
        $contents = @file_get_contents($path);
        if ($contents === false) {
            return [];
        }

        $pattern = '/const\s+' . preg_quote($constName, '/') . '\s*=\s*\[(.*?)\]\s*;/s';
        if (!preg_match($pattern, $contents, $matches)) {
            return [];
        }

        return $this->parseObjectList($matches[1]);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $catalogPath = base_path('../publicaciones-data.js');

        // Datos para publicaciones tecnicas
        $publicacionesTecnicasRaw = $this->loadCatalogo($catalogPath, 'publicacionesTecnicas');
        if (empty($publicacionesTecnicasRaw)) {
            $publicacionesTecnicasRaw = [
            [
                'titulo' => 'La siembra en surcos y corrugaciones con pileteo',
                'year' => 2004,
                'tipo' => 'pdf',
                'portada' => 'imagenes/portadita_siembra_en_surcos.jpg',
                'url' => 'http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=3&t=1',
            ],
            [
                'titulo' => 'Estadísticas Climatológicas Básicas del Estado de Zacatecas. (Período 1961-2003)',
                'year' => 2004,
                'tipo' => 'pdf',
                'portada' => 'imagenes/Portadita_2.jpg',
                'url' => 'http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=4&t=1',
            ],
            // ...otros datos omitidos para brevedad...
            ];
        }

        $now = now();
        $publicacionesTecnicas = array_map(function (array $p) use ($now) {
            $url = $p['url'] ?? null;

            return [
                'titulo' => $p['titulo'] ?? '',
                'titulo_en' => $p['titulo_en'] ?? $p['titulo_ingles'] ?? null,
                'year' => $p['year'] ?? null,
                'tipo' => $p['tipo'] ?? 'pdf',
                'portada_path' => $p['portada_path'] ?? $p['portada'] ?? null,
                'file_path' => null,
                'external_url' => $url,
                'created_by' => null,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $publicacionesTecnicasRaw);

        DB::table('publicaciones_tecnicas')->delete();
        if (!empty($publicacionesTecnicas)) {
            DB::table('publicaciones_tecnicas')->insert($publicacionesTecnicas);
        }

        // Datos para publicaciones cientificas
        $publicacionesCientificasRaw = $this->loadCatalogo($catalogPath, 'publicacionesCientificas');
        if (empty($publicacionesCientificasRaw)) {
            $publicacionesCientificasRaw = [
            [
                'titulo' => 'Efecto de la fertilización en maíz bajo condiciones de temporal',
                'titulo_ingles' => 'Effect of fertilization on maize under rainfed conditions',
                'year' => 2023,
                'tipo' => 'pdf',
                'portada' => 'imagenes/portadas/tec01.jpg',
                'url' => 'pdfs/tec01.pdf',
            ],
            [
                'titulo' => 'Manejo de plagas en cultivos de frijol en el Altiplano',
                'titulo_ingles' => 'Pest management in bean crops in the Altiplano',
                'year' => 2023,
                'tipo' => 'pdf',
                'portada' => 'imagenes/portadas/tec02.jpg',
                'url' => 'pdfs/tec02.pdf',
            ],
            // ...otros datos omitidos para brevedad...
            ];
        }

        $publicacionesCientificas = array_map(function (array $p) use ($now) {
            $url = $p['url'] ?? null;
            $isHttp = is_string($url) && preg_match('/^https?:\/\//i', $url);

            return [
                'titulo' => $p['titulo'] ?? '',
                'titulo_en' => $p['titulo_en'] ?? $p['titulo_ingles'] ?? null,
                'year' => $p['year'] ?? null,
                'tipo' => $p['tipo'] ?? 'pdf',
                'portada_path' => $p['portada_path'] ?? $p['portada'] ?? null,
                'file_path' => $isHttp ? null : $url,
                'external_url' => $isHttp ? $url : null,
                'created_by' => null,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $publicacionesCientificasRaw);

        DB::table('publicaciones_cientificas')->delete();
        if (!empty($publicacionesCientificas)) {
            DB::table('publicaciones_cientificas')->insert($publicacionesCientificas);
        }

        // Datos para publicaciones ilustraciones
        $publicacionesIlustracionesRaw = $this->loadCatalogo($catalogPath, 'publicacionesIlustraciones');
        $publicacionesIlustraciones = array_map(function (array $p) use ($now) {
            $url = $p['url'] ?? null;
            $isHttp = is_string($url) && preg_match('/^https?:\/\//i', $url);

            return [
                'titulo' => $p['titulo'] ?? '',
                'titulo_en' => $p['titulo_en'] ?? $p['titulo_ingles'] ?? null,
                'year' => $p['year'] ?? null,
                'tipo' => $p['tipo'] ?? 'ilustraciones',
                'portada_path' => $p['portada_path'] ?? $p['portada'] ?? null,
                'file_path' => $isHttp ? null : $url,
                'external_url' => $isHttp ? $url : null,
                'created_by' => null,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $publicacionesIlustracionesRaw);

        DB::table('publicaciones_ilustraciones')->delete();
        if (!empty($publicacionesIlustraciones)) {
            DB::table('publicaciones_ilustraciones')->insert($publicacionesIlustraciones);
        }
    }
}