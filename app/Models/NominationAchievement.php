<?php

namespace App\Models;

use App\Core\Model;

class NominationAchievement extends Model
{
    public function findByNominationId(int $nominationId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM nomination_achievements WHERE nomination_id = :id ORDER BY sort_order ASC'
        );
        $stmt->execute(['id' => $nominationId]);
        return $stmt->fetchAll();
    }

    public function create(int $nominationId, string $text, ?string $docFilename, int $sortOrder): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO nomination_achievements (nomination_id, achievement_text, document_filename, sort_order)
             VALUES (:nid, :text, :doc, :sort)'
        );
        $stmt->execute([
            'nid' => $nominationId,
            'text' => $text,
            'doc' => $docFilename,
            'sort' => $sortOrder,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function deleteById(int $id, int $nominationId): bool
    {
        // nomination_id bhi check karo taaki user sirf apne hi rows delete kar sake
        $stmt = $this->db->prepare(
            'DELETE FROM nomination_achievements WHERE id = :id AND nomination_id = :nid'
        );
        return $stmt->execute(['id' => $id, 'nid' => $nominationId]);
    }

    public function findByIdAndNominationId(int $id, int $nominationId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM nomination_achievements WHERE id = :id AND nomination_id = :nid LIMIT 1'
        );
        $stmt->execute(['id' => $id, 'nid' => $nominationId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateById(int $id, int $nominationId, string $text, ?string $docFilename, int $sortOrder): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE nomination_achievements
         SET achievement_text = :text, document_filename = :doc, sort_order = :sort
         WHERE id = :id AND nomination_id = :nid'
        );
        return $stmt->execute([
            'text' => $text,
            'doc' => $docFilename,
            'sort' => $sortOrder,
            'id' => $id,
            'nid' => $nominationId,
        ]);
    }
}