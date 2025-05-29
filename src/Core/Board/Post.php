<?php

namespace Root\Composer\Core\Board;

use PDO;

class Post
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function getAllPosts(): array
  {
    $sql = 'SELECT m.*, p.* FROM members m JOIN posts p ON m.id=p.created_by ORDER BY p.created DESC';
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getPostsPaginated(int $page = 1, int $limit = 10): array
  {
    $offset = ($page - 1) * $limit;

    $sql = 'SELECT created FROM posts ORDER BY created DESC LIMIT :limit OFFSET :offset';
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $times = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($times)) {
      return [];
    }

    $placeholders = str_repeat('?,', count($times) - 1) . '?';
    $sql = "SELECT m.*, p.* FROM members m JOIN posts p ON m.id=p.created_by
                WHERE p.created IN ($placeholders) ORDER BY p.created DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute($times);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getPostById(int $id): ?array
  {
    $sql = 'SELECT m.*, p.* FROM members m JOIN posts p ON m.id=p.created_by WHERE p.id = :id';
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
  }

  public function getTotalPostCount(): int
  {
    $sql = 'SELECT COUNT(*) FROM posts';
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
  }

  public function getMaxPostId(): int
  {
    $sql = 'SELECT MAX(id) FROM posts';
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
  }

  public function getMinPostId(): int
  {
    $sql = 'SELECT MIN(id) FROM posts';
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return (int) $stmt->fetchColumn();
  }

  public function createPost(array $data): bool
  {
    $sql = 'INSERT INTO posts (name, created_by, post, created, fname, reply_to, reply_post, reply_id, reply_created, reply_from_id)
                VALUES (:name, :created_by, :post, CURRENT_TIMESTAMP, :fname, :reply_to, :reply_post, :reply_id, :reply_created, :reply_from_id)';

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
    $stmt->bindValue(':created_by', $data['created_by'], PDO::PARAM_INT);
    $stmt->bindValue(':post', $data['post'], PDO::PARAM_STR);
    $stmt->bindValue(':fname', $data['fname'] ?? null, PDO::PARAM_STR);
    $stmt->bindValue(':reply_to', $data['reply_to'] ?? null, PDO::PARAM_STR);
    $stmt->bindValue(':reply_post', $data['reply_post'] ?? null, PDO::PARAM_STR);
    $stmt->bindValue(':reply_id', $data['reply_id'] ?? null, PDO::PARAM_INT);
    $stmt->bindValue(':reply_created', $data['reply_created'] ?? null, PDO::PARAM_STR);
    $stmt->bindValue(':reply_from_id', $data['reply_from_id'] ?? null, PDO::PARAM_INT);

    return $stmt->execute();
  }

  public function updatePost(int $id, array $data): bool
  {
    $sql = 'UPDATE posts
                SET name=:name, post=:post, fname=:fname, extension=:extension, edit=:edit, modified=CURRENT_TIMESTAMP
                WHERE id=:id';

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':name', $data['name'], PDO::PARAM_STR);
    $stmt->bindValue(':post', $data['post'], PDO::PARAM_STR);
    $stmt->bindValue(':fname', $data['fname'] ?? null, PDO::PARAM_STR);
    $stmt->bindValue(':extension', $data['extension'] ?? null, PDO::PARAM_STR);
    $stmt->bindValue(':edit', $data['edit'] ?? 'DONE', PDO::PARAM_STR);

    return $stmt->execute();
  }

  public function deletePost(int $id, int $userId): bool
  {
    $sql = 'DELETE FROM posts WHERE id=:id AND created_by=:created_by';
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':created_by', $userId, PDO::PARAM_INT);
    return $stmt->execute();
  }

  public function postBelongsToUser(int $postId, int $userId): bool
  {
    $sql = 'SELECT created_by FROM posts WHERE id=:id';
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':id', $postId, PDO::PARAM_INT);
    $stmt->execute();
    $createdBy = $stmt->fetchColumn();
    return (int) $createdBy === $userId;
  }

  public function getAllCreatedTimes(): array
  {
    $sql = 'SELECT created FROM posts ORDER BY created DESC';
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }

  public function getUserIcon(int $userId): ?string
  {
    $sql = 'SELECT icon FROM members WHERE id=:id';
    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchColumn() ?: null;
  }
}
