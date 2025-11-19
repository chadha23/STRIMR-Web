<?php
class Database {
    private static $instance = null;
    public static function get() {
        if (!self::$instance) {
            $config = require __DIR__ . '/../../config/database.php';
            self::$instance = new PDO($config['dsn'], $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        }
        return self::$instance;
    }
}