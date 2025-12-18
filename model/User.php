<?php

require_once __DIR__ . '/db.php';

class User
{
    /**
     * @var PDO
     */
    private $conn;

    /**
     * Cache table column lookups to avoid repeated SHOW queries.
     *
     * @var array<string, bool>
     */
    private static $columnCache = [];

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    private function columnExists(string $column): bool
    {
        if (array_key_exists($column, self::$columnCache)) {
            return self::$columnCache[$column];
        }

        $quoted = $this->conn->quote($column);
        $stmt   = $this->conn->query("SHOW COLUMNS FROM users LIKE $quoted");
        $exists = $stmt && $stmt->fetch();

        self::$columnCache[$column] = (bool) $exists;

        return self::$columnCache[$column];
    }

    private function ensureFullNameColumn(): void
    {
        if ($this->columnExists('full_name')) {
            return;
        }

        $this->conn->exec("ALTER TABLE users ADD COLUMN full_name VARCHAR(100) NULL AFTER username");
        self::$columnCache['full_name'] = true;
    }

    public function findByEmailOrUsername(string $input): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, username, password, email, is_verified, role
             FROM users
             WHERE email = :input OR username = :input
             LIMIT 1"
        );
        $stmt->execute([':input' => $input]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function emailExistsOrUsernameExists(string $email, string $username): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, is_verified
             FROM users
             WHERE email = :email OR username = :username
             LIMIT 1"
        );
        $stmt->execute([
            ':email'    => $email,
            ':username' => $username,
        ]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function createUser(
        string $username,
        string $fullName,
        string $email,
        string $passwordHash,
        ?string $token,
        ?string $expiresAt,
        int $isVerified = 0
    ): int {
        $this->ensureFullNameColumn();

        $columns      = ['username', 'email', 'password', 'is_verified', 'verification_token'];
        $placeholders = [':username', ':email', ':password', ':is_verified', ':verification_token'];
        $params       = [
            ':username'          => $username,
            ':email'             => $email,
            ':password'          => $passwordHash,
            ':is_verified'       => $isVerified,
            ':verification_token'=> $token,
        ];

        if ($this->columnExists('full_name')) {
            array_splice($columns, 1, 0, 'full_name');
            array_splice($placeholders, 1, 0, ':full_name');
            $params[':full_name'] = $fullName;
        }

        if ($this->columnExists('verification_expires_at')) {
            $columns[]      = 'verification_expires_at';
            $placeholders[] = ':verification_expires_at';
            $params[':verification_expires_at'] = $expiresAt;
        }

        $columns[]      = 'created_at';
        $placeholders[] = 'NOW()';

        $sql = sprintf(
            'INSERT INTO users (%s) VALUES (%s)',
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return (int) $this->conn->lastInsertId();
    }

    public function verifyEmailByToken(string $token): bool
    {
        $selectSql = "SELECT id, verification_expires_at
                      FROM users
                      WHERE verification_token = :token
                      LIMIT 1";
        $stmt = $this->conn->prepare($selectSql);
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        if (
            $this->columnExists('verification_expires_at')
            && !empty($user['verification_expires_at'])
            && strtotime($user['verification_expires_at']) < time()
        ) {
            return false;
        }

        $updateSql = 'UPDATE users SET is_verified = 1, verification_token = NULL';
        if ($this->columnExists('verification_expires_at')) {
            $updateSql .= ', verification_expires_at = NULL';
        }
        $updateSql .= ' WHERE id = :id';

        $update = $this->conn->prepare($updateSql);
        $update->execute([':id' => $user['id']]);

        return true;
    }

    public function getProfileById(int $id): ?array
    {
        $columns = ['id', 'username', 'email'];
        if ($this->columnExists('full_name')) {
            $columns[] = 'full_name';
        }

        $sql = sprintf(
            'SELECT %s FROM users WHERE id = :id LIMIT 1',
            implode(', ', $columns)
        );

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function updateProfile(int $id, string $username, string $fullName, string $email): array
    {
        $checkSql = "SELECT id
                     FROM users
                     WHERE (email = :email OR username = :username)
                       AND id <> :id
                     LIMIT 1";
        $check = $this->conn->prepare($checkSql);
        $check->execute([
            ':email'    => $email,
            ':username' => $username,
            ':id'       => $id,
        ]);

        if ($check->fetch()) {
            return ['success' => false, 'error' => 'duplicate'];
        }

        $this->ensureFullNameColumn();

        $fields = ['username = :username', 'email = :email'];
        $params = [
            ':username' => $username,
            ':email'    => $email,
            ':id'       => $id,
        ];

        if ($this->columnExists('full_name')) {
            $fields[]            = 'full_name = :full_name';
            $params[':full_name'] = $fullName;
        }

        $sql = sprintf('UPDATE users SET %s WHERE id = :id', implode(', ', $fields));
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return ['success' => true];
    }

    public function countUsers(): int
    {
        $stmt = $this->conn->query('SELECT COUNT(*) AS total FROM users');
        $row  = $stmt ? $stmt->fetch() : null;

        return $row ? (int) $row['total'] : 0;
    }

    public function lastRegisteredUsers($limit = 3): array
    {
        $baseSql = 'SELECT id, username, email, created_at FROM users ORDER BY id DESC';

        if ($limit !== null && (int) $limit > 0) {
            $stmt = $this->conn->prepare($baseSql . ' LIMIT :limit');
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $this->conn->query($baseSql);
        }

        if (!$stmt) {
            return [];
        }

        return $stmt->fetchAll() ?: [];
    }
}
