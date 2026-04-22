<?php

$pdo = new PDO('mysql:host=192.168.1.51;port=3306', 'adrian', 'secret123');
$pdo->exec('DROP DATABASE IF EXISTS paco_test_db');
$pdo->exec('CREATE DATABASE paco_test_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
echo "BD paco_test_db recreada.\n";
