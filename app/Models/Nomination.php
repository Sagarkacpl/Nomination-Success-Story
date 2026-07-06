<?php

namespace App\Models;

use App\Core\Model;

class Nomination extends Model
{
    public function findByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM nominations WHERE user_id = :uid LIMIT 1');
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Ek field ko save/update karta hai (upsert).
     * Agar row exist nahi karti to registration_number generate karke pehli baar create karta hai.
     * Agar final_submission ho chuki hai to koi bhi field update reject kar deta hai.
     */
    public function saveField(int $userId, string $field, $value): array
    {
        $allowedFields = [
            'full_name',
            'membership_no',
            'designation',
            'organization',
            'engagement',
            'engagement_other_text',
            'linkedin',
            'experience',
            'email',
            'mobile',
            'state',
            'city',
            // 'achievements',
            'story_title',
            'declaration_true',
            'declaration_original',
            'declaration_no_guarantee',
        ];

        if (!in_array($field, $allowedFields, true)) {
            throw new \InvalidArgumentException('Invalid field name.');
        }

        $existing = $this->findByUserId($userId);

        // ── Final submission ho chuki ho to koi bhi update block karo ──
        if ($existing && !empty($existing['final_submission'])) {
            throw new \RuntimeException('This nomination has already been finally submitted and cannot be edited.');
        }

        if (!$existing) {
            $regNo = $this->generateRegistrationNumber();

            $stmt = $this->db->prepare(
                "INSERT INTO nominations (user_id, registration_number, {$field}, created_at)
                 VALUES (:uid, :reg, :val, NOW())"
            );
            $stmt->execute(['uid' => $userId, 'reg' => $regNo, 'val' => $value]);

            return ['registration_number' => $regNo, 'created' => true];
        }

        $stmt = $this->db->prepare("UPDATE nominations SET {$field} = :val WHERE user_id = :uid");
        $stmt->execute(['val' => $value, 'uid' => $userId]);

        return ['registration_number' => $existing['registration_number'], 'created' => false];
    }

    /**
     * Final submit se pehle check karne ke liye — already submitted hai ya nahi.
     */
    public function isFinalSubmitted(int $userId): bool
    {
        $nomination = $this->findByUserId($userId);
        return !empty($nomination['final_submission']);
    }

    public function markFinalSubmitted(int $userId): void
    {
        // ── Double-submit guard ──
        if ($this->isFinalSubmitted($userId)) {
            throw new \RuntimeException('This nomination has already been finally submitted.');
        }

        $stmt = $this->db->prepare('UPDATE nominations SET final_submission = 1 WHERE user_id = :uid');
        $stmt->execute(['uid' => $userId]);
    }

    /**
     * SS20260001, SS20260002, ... format.
     * registration_counters table se atomic increment (race-condition safe).
     */
    private function generateRegistrationNumber(): string
    {
        $year = (int) date('Y');

        $this->db->beginTransaction();
        try {
            $this->db->prepare(
                'INSERT INTO registration_counters (year_val, last_seq) VALUES (:y, 0)
                 ON DUPLICATE KEY UPDATE last_seq = last_seq' // no-op if exists, ensures row present
            )->execute(['y' => $year]);

            $this->db->prepare(
                'UPDATE registration_counters SET last_seq = last_seq + 1 WHERE year_val = :y'
            )->execute(['y' => $year]);

            $stmt = $this->db->prepare('SELECT last_seq FROM registration_counters WHERE year_val = :y');
            $stmt->execute(['y' => $year]);
            $seq = (int) $stmt->fetch()['last_seq'];

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return 'SS' . $year . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * All nominations for the admin listing table.
     * Joins users table to show the registrant's account email too (optional —
     * remove the JOIN if you only want the nomination's own `email` field).
     */
    public function getAll(): array
    {
        $stmt = $this->db->prepare(
            'SELECT n.*
            FROM nominations n
            ORDER BY n.created_at DESC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * ANOTHER ADDITION to App\Models\Nomination
     * Paste alongside getAll() (from the earlier file).
     */

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM nominations WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }


}