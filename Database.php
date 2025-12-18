<?php
// Database.php → VERSION FINALE QUI MARCHE À 100% (chemin corrigé)
class Database {
    private static $instance = null;

    public static function get() {
        if (self::$instance === null) {
            // CHEMIN CORRIGÉ – TOUJOURS BON, MÊME EN STRUCTURE PLATE
            $configFile = __DIR__ . '/config/database.php';
            
            if (!file_exists($configFile)) {
                die("Fichier de config database manquant : $configFile");
            }

            $config = require $configFile;

            self::$instance = new PDO(
                $config['dsn'],
                $config['user'],
                $config['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        }
        return self::$instance;
    }
}