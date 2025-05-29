<?php

namespace Root\Composer\Core\Auth;

use PDO;

class User
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function findByEmail(string $email): ?array
  {
    $sql = 'SELECT * FROM members WHERE email = :email';
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function findById(int $id): ?array
  {
    $sql = 'SELECT * FROM members WHERE id = :id';
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function findByName(string $name): ?array
  {
    $sql = 'SELECT * FROM members WHERE name = :name';
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function create(array $userData): bool
  {
    $sql = 'INSERT INTO members (username, password, name, email, created_at, updated_at)
                VALUES (:username, :password, :name, :email, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)';

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':username', $userData['username'] ?? $userData['name'], PDO::PARAM_STR);
    $stmt->bindValue(':password', password_hash($userData['password'], PASSWORD_DEFAULT), PDO::PARAM_STR);
    $stmt->bindValue(':name', $userData['name'], PDO::PARAM_STR);
    $stmt->bindValue(':email', $userData['email'], PDO::PARAM_STR);

    return $stmt->execute();
  }

  public function updateIcon(int $userId, string $iconFilename): bool
  {
    $sql = 'UPDATE members SET icon = :icon, updated_at = CURRENT_TIMESTAMP WHERE id = :id';
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':icon', $iconFilename, PDO::PARAM_STR);
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
    return $stmt->execute();
  }

  public function verifyPassword(string $password, string $hashedPassword): bool
  {
    return password_verify($password, $hashedPassword);
  }

  public function isNameTaken(string $name): bool
  {
    $sql = 'SELECT COUNT(*) FROM members WHERE name = :name';
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    return (int) $stmt->fetchColumn() > 0;
  }

  public function isEmailTaken(string $email): bool
  {
    $sql = 'SELECT COUNT(*) FROM members WHERE email = :email';
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    return (int) $stmt->fetchColumn() > 0;
  }

  public function getLastInsertId(): int
  {
    return (int) $this->db->lastInsertId();
  }
}

