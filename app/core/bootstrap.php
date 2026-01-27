<?php
session_start();

echo 'chegando';
exit;

$pdo = new PDO(
    'pgsql:host=db.fkfkhzfcyuuwvwufhrrj.supabase.co;dbname=postgres',
    'postgres',
    'IyweOVEcT0S1ZOVL',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);
