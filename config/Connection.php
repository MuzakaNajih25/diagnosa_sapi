<?php
// ============================================================
//  FILE      : Connection.php
//  LETAK     : app/Config/Connection.php  (atau taruh di root)
//  DESKRIPSI : Koneksi PDO ke database db_sipakar
// ============================================================

class Connection
{
    // ── Konfigurasi — sesuaikan jika beda ──────────────────
    private static string $host   = '127.0.0.1';
    private static int    $port   = 3306;
    private static string $dbname = 'db_sipakar';
    private static string $user   = 'root';
    private static string $pass   = '';            // isi password MySQL-mu
    // ────────────────────────────────────────────────────────

    private static ?PDO $instance = null;

    /**
     * Kembalikan satu instance PDO (singleton).
     * Panggil: $pdo = Connection::getInstance();
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                self::$host,
                self::$port,
                self::$dbname
            );

            try {
                self::$instance = new PDO($dsn, self::$user, self::$pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // Di production, jangan tampilkan pesan error ke user
                die('Koneksi database gagal: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }

    // Cegah clone & instansiasi langsung
    private function __construct() {}
    private function __clone()     {}
}