<?php

namespace App\Config;

use PDO;
use PDOException;

/**
 * Database
 * Singleton wrapper around a single PDO connection.
 * Using PDO + prepared statements everywhere protects against SQL injection.
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
        // prevent direct instantiation
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // real prepared statements
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '" . DB_CHARSET . "'",
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Never leak DB credentials/errors to the user
                error_log('DB Connection Error: ' . $e->getMessage());
                die('Database connection failed. Please try again later.');
            }
        }

        return self::$instance;
    }

    // Prevent cloning / unserialization of the singleton
    private function __clone() {}
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton Database');
    }
}
