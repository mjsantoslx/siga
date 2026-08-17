<?php
$password = $argv[1] ?? '';
if ($password === '') {
    fwrite(STDERR, "Uso: php password_hash.php 'password'\n");
    exit(1);
}
echo password_hash($password, PASSWORD_DEFAULT), PHP_EOL;
