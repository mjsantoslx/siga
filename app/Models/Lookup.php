<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class Lookup
{
    private const TABLES = [
        'generos' => ['label' => 'Géneros', 'column' => 'Designacao'],
        'tipos_contacto' => ['label' => 'Tipos de contacto', 'column' => 'Designacao'],
        'tipos_evento' => ['label' => 'Tipos de evento', 'column' => 'Designacao'],
        'tipos_parentesco' => ['label' => 'Tipos de parentesco', 'column' => 'TipoParentesco'],
        'nacionalidades' => ['label' => 'Nacionalidades', 'column' => 'Nacionalidade'],
    ];

    public function __construct(private PDO $db) {}

    public function tables(): array
    {
        return self::TABLES;
    }

    public function meta(string $table): array
    {
        if (!isset(self::TABLES[$table])) {
            throw new \InvalidArgumentException('Tabela de apoio inválida.');
        }
        return self::TABLES[$table];
    }

    public function all(string $table): array
    {
        $meta = $this->meta($table);
        $column = $meta['column'];
        return $this->db->query(
            "SELECT Id, `{$column}` AS Designacao FROM `{$table}` ORDER BY `{$column}`"
        )->fetchAll();
    }

    public function find(string $table, int $id): ?array
    {
        $meta = $this->meta($table);
        $column = $meta['column'];
        $stmt = $this->db->prepare(
            "SELECT Id, `{$column}` AS Designacao FROM `{$table}` WHERE Id = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $table, string $value): int
    {
        $meta = $this->meta($table);
        $column = $meta['column'];
        $value = trim($value);
        if ($value === '') {
            throw new \InvalidArgumentException('A designação é obrigatória.');
        }
        $stmt = $this->db->prepare(
            "INSERT INTO `{$table}` (`{$column}`) VALUES (:value)"
        );
        $stmt->execute(['value' => $value]);
        return (int)$this->db->lastInsertId();
    }

    public function update(string $table, int $id, string $value): void
    {
        $meta = $this->meta($table);
        $column = $meta['column'];
        $value = trim($value);
        if ($value === '') {
            throw new \InvalidArgumentException('A designação é obrigatória.');
        }
        $stmt = $this->db->prepare(
            "UPDATE `{$table}` SET `{$column}` = :value WHERE Id = :id"
        );
        $stmt->execute(['value' => $value, 'id' => $id]);
    }

    public function delete(string $table, int $id): void
    {
        $meta = $this->meta($table);
        $column = $meta['column'];
        $stmt = $this->db->prepare(
            "DELETE FROM `{$table}` WHERE Id = :id"
        );
        try {
            $stmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            if ((int)($e->errorInfo[1] ?? 0) === 1451) {
                throw new \RuntimeException(
                    'Não é possível eliminar este registo porque já está a ser utilizado na aplicação.'
                );
            }
            throw $e;
        }
    }
}
