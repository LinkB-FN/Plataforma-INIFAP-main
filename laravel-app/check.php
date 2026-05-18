<?php
$sql1 = file_get_contents('1_biblioteca_inifap.sql');
echo "1_biblioteca_inifap.sql is valid UTF-8: " . (mb_check_encoding($sql1, "UTF-8") ? "yes" : "no") . PHP_EOL;

$sql2 = file_get_contents('2_biblioteca_seguridad.sql');
echo "2_biblioteca_seguridad.sql is valid UTF-8: " . (mb_check_encoding($sql2, "UTF-8") ? "yes" : "no") . PHP_EOL;

// Let's also find the first invalid character if any
if (!mb_check_encoding($sql1, "UTF-8")) {
    $sql1 = utf8_encode($sql1); // deprecated but works for latin1
    file_put_contents('1_biblioteca_inifap_utf8.sql', $sql1);
    echo "Fixed 1_biblioteca_inifap.sql\n";
}
if (!mb_check_encoding($sql2, "UTF-8")) {
    $sql2 = utf8_encode($sql2);
    file_put_contents('2_biblioteca_seguridad_utf8.sql', $sql2);
    echo "Fixed 2_biblioteca_seguridad.sql\n";
}
