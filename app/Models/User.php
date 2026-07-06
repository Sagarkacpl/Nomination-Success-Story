<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return (bool) $stmt->fetch();
    }

    /**
     * @param array $data keys: full_name, email, mobile, password (plain), profile_pic (nullable)
     * @return int newly inserted user id
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO users
                    (full_name, email, mobile, password_hash, plain_password, profile_pic,
                     verification_token, is_verified, created_at)
                VALUES
                    (:full_name, :email, :mobile, :password_hash, :plain_password, :profile_pic,
                     :verification_token, 0, NOW())';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT), // bcrypt/argon2id
            'plain_password' => $data['password'],
            'profile_pic' => $data['profile_pic'] ?? null,
            'verification_token' => $data['verification_token'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function verifyEmailByToken(string $token): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE verification_token = :token LIMIT 1');
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        $update = $this->db->prepare(
            'UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = :id'
        );
        $update->execute(['id' => $user['id']]);

        return true;
    }

    public function updatePassword(int $id, string $plainPassword): void
    {
        $stmt = $this->db->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $stmt->execute([
            'hash' => password_hash($plainPassword, PASSWORD_DEFAULT),
            'id' => $id,
        ]);
    }

    // ---------------- Brute-force protection ----------------

    public function recordLoginAttempt(string $email, bool $success): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO login_attempts (email, ip_address, success, attempted_at)
             VALUES (:email, :ip, :success, NOW())'
        );
        $stmt->execute([
            'email' => $email,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'success' => $success ? 1 : 0,
        ]);
    }

    /**
     * Count failed attempts for this email within the lockout window.
     */
    public function recentFailedAttempts(string $email): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS cnt FROM login_attempts
             WHERE email = :email
               AND success = 0
               AND attempted_at > (NOW() - INTERVAL :minutes MINUTE)'
        );
        $stmt->bindValue('email', $email);
        $stmt->bindValue('minutes', LOGIN_LOCKOUT_MINUTES, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetch()['cnt'];
    }

    /**
     * Recent password hashes for this user (most recent first).
     * Used only to check reuse — never exposed or logged.
     */
    public function getRecentPasswordHashes(int $userId, int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            'SELECT password_hash FROM password_history
            WHERE user_id = :uid
            ORDER BY created_at DESC
            LIMIT :lim'
        );
        $stmt->bindValue('uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue('lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_column($stmt->fetchAll(), 'password_hash');
    }

    /**
     * True if the given plain-text password matches the user's CURRENT
     * password or any of their last $historyLimit passwords.
     * Uses password_verify() against each stored hash (hashes are salted,
     * so we can't compare them directly — each must be checked individually).
     */
    public function isPasswordReused(int $userId, string $newPlainPassword, int $historyLimit = 5): bool
    {
        $current = $this->findById($userId);
        $hashesToCheck = $this->getRecentPasswordHashes($userId, $historyLimit);

        if ($current && !empty($current['password_hash'])) {
            $hashesToCheck[] = $current['password_hash'];
        }

        foreach ($hashesToCheck as $hash) {
            if (password_verify($newPlainPassword, $hash)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Changes the user's password:
     *  1. Archives the CURRENT hash into password_history
     *  2. Hashes + saves the new password
     *  3. Trims history to the last $historyLimit rows for this user
     * All in one transaction so a failure never leaves history/user out of sync.
     */
    public function changePasswordWithHistory(int $userId, string $newPlainPassword, int $historyLimit = 5): void
    {
        $current = $this->findById($userId);
        if (!$current) {
            throw new \RuntimeException('User not found.');
        }

        $this->db->beginTransaction();
        try {
            // 1. Archive current hash
            $insert = $this->db->prepare(
                'INSERT INTO password_history (user_id, password_hash, created_at)
                VALUES (:uid, :hash, NOW())'
            );
            $insert->execute([
                'uid' => $userId,
                'hash' => $current['password_hash'],
            ]);

            // 2. Save new hash
            $newHash = password_hash($newPlainPassword, PASSWORD_DEFAULT);
            $update = $this->db->prepare('UPDATE users SET password_hash = :hash WHERE id = :uid');
            $update->execute(['hash' => $newHash, 'uid' => $userId]);

            // 3. Trim history — keep only the most recent $historyLimit rows
            $trim = $this->db->prepare(
                'DELETE FROM password_history
                WHERE user_id = :uid
                AND id NOT IN (
                    SELECT id FROM (
                        SELECT id FROM password_history
                        WHERE user_id = :uid2
                        ORDER BY created_at DESC
                        LIMIT :lim
                    ) keep_ids
                )'
            );
            $trim->bindValue('uid', $userId, PDO::PARAM_INT);
            $trim->bindValue('uid2', $userId, PDO::PARAM_INT);
            $trim->bindValue('lim', $historyLimit, PDO::PARAM_INT);
            $trim->execute();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Verify a plain-text password against the user's current hash.
     * Small convenience wrapper used by the change-password action.
     */
    public function verifyPassword(int $userId, string $plainPassword): bool
    {
        $user = $this->findById($userId);
        if (!$user) {
            return false;
        }
        return password_verify($plainPassword, $user['password_hash']);
    }

    /**
     * ADDITION to App\Models\User
     * Paste inside the existing User class, anywhere after emailExists().
     *
     * NOTE: This deliberately selects only safe columns — it does NOT
     * into an admin listing, export, or API response through this method.
     */
    public function getAll(): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, full_name, email, mobile, profile_pic, is_verified, created_at, plain_password
         FROM users
         ORDER BY created_at DESC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function setResetToken(int $userId, string $token, string $expiry): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET reset_token = :token, reset_token_expiry = :expiry WHERE id = :id'
        );
        $stmt->execute(['token' => $token, 'expiry' => $expiry, 'id' => $userId]);
    }

    public function findByValidResetToken(string $token): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE reset_token = :token AND reset_token_expiry > NOW() LIMIT 1'
        );
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function resetPasswordByToken(string $token, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare(
            'UPDATE users SET password_hash = :hash, plain_password = :plain_password, reset_token = NULL, reset_token_expiry = NULL
         WHERE reset_token = :token AND reset_token_expiry > NOW()'
        );
        return $stmt->execute(['hash' => $hash, 'plain_password' => $newPassword, 'token' => $token]);
    }
}
