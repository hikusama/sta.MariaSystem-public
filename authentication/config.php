<?php

function db_connect()
{
    $host = 'db';              
    $username = 'stuser';
    $password = 'stpass';
    $database = 'stamaraiadb';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}

$pdo = db_connect();