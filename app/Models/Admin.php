<?php

namespace App\Models;

use App\Core\Model;

class Admin extends Model
{
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM admins WHERE email = :email AND is_active = 1 LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $admin = $stmt->fetch();
        return $admin ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM admins WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $admin = $stmt->fetch();
        return $admin ?: null;
    }
}