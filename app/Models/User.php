<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

final class User
{
    public function __construct(private PDO $db) {}

    public function findByName(string $nome): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT Id, Nome, Email, Password, Administrador, Activo
             FROM utilizadores
             WHERE Nome = :nome
             LIMIT 1'
        );
        $stmt->execute(['nome' => trim($nome)]);
        return $stmt->fetch() ?: null;
    }
}
