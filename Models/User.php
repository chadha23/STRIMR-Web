<?php
class User {
    public static function usernameExists($username) {
        $pdo = Database::get();
        // Validation sensible à la casse - Jean différent de jean
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetchColumn() > 0;
    }
    
    public static function register($username, $password) {
        try {
            $pdo = Database::get();
            
            // Vérifier l'unicité du nom d'utilisateur (sensible à la casse)
            if (self::usernameExists($username)) {
                throw new Exception("Ce nom d'utilisateur est déjà utilisé");
            }
            
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $result = $stmt->execute([$username, $hash]);
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Erreur d'insertion: " . $errorInfo[2]);
            }
            
            $userId = $pdo->lastInsertId();
            if (!$userId) {
                throw new Exception("Erreur: ID utilisateur non récupéré");
            }
            
            return $userId;
            
        } catch (PDOException $e) {
            throw new Exception("Erreur base de données: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e; // Re-lancer l'exception
        }
    }

    public static function login($username, $password) {
        $pdo = Database::get();
        // Connexion sensible à la casse - Jean différent de jean
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
        return false;
    }
}