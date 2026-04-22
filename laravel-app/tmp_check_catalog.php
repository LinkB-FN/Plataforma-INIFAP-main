<?php
$path = 'C:/Users/gatit/Tec De Software/4to cuatri/Plataforma-INIFAP-main/publicaciones-data.js';
$contents = @file_get_contents($path);
if ($contents === false) {
    echo "no-file\n";
    exit(1);
}
$pattern = '/const\s+publicacionesTecnicas\s*=\s*\[(.*?)\]\s*;/s';
if (preg_match($pattern, $contents, $matches)) {
    echo "match-len=" . strlen($matches[1]) . "\n";
    $raw = '[' . $matches[1] . ']';
    $json = preg_replace('/([\{\s,])([a-zA-Z_][a-zA-Z0-9_]*)\s*:/', '$1"$2":', $raw);
    $json = preg_replace('/,(\s*[}\]])/', '$1', $json);
    $data = json_decode($json, true);
    if (!is_array($data)) {
        echo "json-error=" . json_last_error_msg() . "\n";
    } else {
        echo "count=" . count($data) . "\n";
    }
} else {
    echo "no-match\n";
}
