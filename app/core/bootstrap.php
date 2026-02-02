<?php
declare(strict_types=1);

// =======================
// Sessão segura
// =======================
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// =======================
// Classe de conexão PDO
// =======================
if (!class_exists('Database')) {
    class Database
    {
        private static ?PDO $instance = null;

        private function __construct() {} // previne instância externa

        public static function getConnection(): PDO
        {
            if (self::$instance === null) {
                $host = 'db.fkfkhzfcyuuwvwufhrrj.supabase.co';
                $name = 'postgres';
                $user = 'postgres';
                $pass = 'IyweOVEcT0S1ZOVL';
                $port = '5432';

                try {
                    self::$instance = new PDO(
                    "pgsql:host={$host};port={$port};dbname={$name};sslmode=require",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );;
                } catch (PDOException $e) {
                    error_log('DB CONNECT ERROR: ' . $e->getMessage());
                    http_response_code(500);
                    exit('Erro ao conectar ao banco de dados: ' . $e->getMessage());
                }
            }

            return self::$instance;
        }
    }
}
