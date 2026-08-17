<?php

namespace App\Core;

/**
 * Model: clase base para todos los modelos.
 * Provee acceso a la conexión y operaciones genéricas.
 */
abstract class Model
{
    protected \PDO $db;
    protected string $table = '';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /** Devuelve todos los registros */
    public function all(): array
    {
        return $this->db->query("SELECT * FROM `{$this->table}`")->fetchAll();
    }

    /** Busca un registro por id */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Devuelve registros donde la columna es igual a un valor */
    public function where(string $column, mixed $value): array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$column}` = ?");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }

    /** Devuelve el primer registro donde la columna es igual a un valor */
    public function firstWhere(string $column, mixed $value): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$column}` = ? LIMIT 1");
        $stmt->execute([$value]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Inserta un registro y devuelve su id */
    public function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql = "INSERT INTO `{$this->table}` (`" . implode('`, `', $columns) . "`) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        return (int) $this->db->lastInsertId();
    }

    /** Actualiza un registro por id */
    public function update(int $id, array $data): bool
    {
        $sets = implode(', ', array_map(fn ($c) => "`{$c}` = ?", array_keys($data)));
        $sql = "UPDATE `{$this->table}` SET $sets WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([...array_values($data), $id]);
    }

    /** Elimina un registro por id */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /** Ejecuta una consulta libre y devuelve registros */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Ejecuta una consulta libre sin devolver registros */
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /** Devuelve el último id insertado */
    public function lastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }
}
