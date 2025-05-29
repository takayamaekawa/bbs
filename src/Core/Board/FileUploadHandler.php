<?php

namespace Root\Composer\Core\Board;

use RuntimeException;

class FileUploadHandler
{
  private string $uploadDir;
  private int $maxFileSize;
  private array $allowedMimeTypes;

  public function __construct(string $uploadDir = null, int $maxFileSize = 1000000000)
  {
    if ($uploadDir === null) {
      // config.jsonからアップロードディレクトリを取得
      $settings = \Root\Composer\Core\Config\Settings::getInstance();
      $uploadPath = $settings->get('paths.upload_directory', 'public/assets/img/upload/');
      $uploadDir = PROJECT_ROOT . '/' . ltrim($uploadPath, '/');
    }
    $this->uploadDir = rtrim($uploadDir, '/') . '/';
    $this->maxFileSize = $maxFileSize;
    $this->allowedMimeTypes = [
      'jpeg' => 'image/jpeg',
      'png' => 'image/png',
      'gif' => 'image/gif',
      'mp4' => 'video/mp4',
      'mkv' => 'video/x-matroska',
      'mp3' => 'audio/mpeg',
      'wav' => 'audio/wav',
    ];

    // ディレクトリが存在しない場合は作成
    if (!is_dir($this->uploadDir)) {
      mkdir($this->uploadDir, 0775, true);
    }
  }

  public function handleUpload(array $file): ?string
  {
    if (!isset($file['error']) || !is_int($file['error']) || $file['name'] === '') {
      return null;
    }

    $this->validateUpload($file);
    $filename = $this->generateFileName($file['tmp_name']);
    $fullPath = $this->uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
      throw new RuntimeException('ファイルの保存に失敗しました', 500);
    }

    return $filename;
  }

  private function validateUpload(array $file): void
  {
    switch ($file['error']) {
      case UPLOAD_ERR_OK:
        break;
      case UPLOAD_ERR_NO_FILE:
        throw new RuntimeException('ファイルが選択されていません', 400);
      case UPLOAD_ERR_INI_SIZE:
        throw new RuntimeException('ファイルサイズが大きすぎます', 400);
      default:
        throw new RuntimeException('その他のエラーが発生しました', 500);
    }

    if ($file['size'] > $this->maxFileSize) {
      throw new RuntimeException('ファイルサイズが大きすぎます', 400);
    }

    $this->validateMimeType($file);
  }

  private function validateMimeType(array $file): string
  {
    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $ext = array_search($mimeType, $this->allowedMimeTypes, true);

    if ($ext === false) {
      throw new RuntimeException('無効なファイル形式です', 400);
    }

    $pathInfo = pathinfo($file['name']);
    $fileExtension = strtolower($pathInfo['extension']);

    if ($ext !== $fileExtension && !($ext === 'jpeg' && in_array($fileExtension, ['jpg', 'jpeg']))) {
      throw new RuntimeException('無効なファイル形式です', 400);
    }

    return $ext;
  }

  private function generateFileName(string $tmpName): string
  {
    $date = getdate();
    $hash = hash('sha256', $tmpName . $date['year'] . $date['mon'] . $date['mday'] . $date['hours'] . $date['minutes'] . $date['seconds']);

    $finfo = new \finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpName);
    $ext = array_search($mimeType, $this->allowedMimeTypes, true);

    return $hash . '.' . $ext;
  }

  public function getFileExtension(string $filename): ?string
  {
    $pathInfo = pathinfo($filename);
    return $pathInfo['extension'] ?? null;
  }

  public function isImageFile(string $filename): bool
  {
    $ext = $this->getFileExtension($filename);
    return in_array($ext, ['jpeg', 'png', 'gif', 'jpg']);
  }

  public function isVideoFile(string $filename): bool
  {
    $ext = $this->getFileExtension($filename);
    return in_array($ext, ['mp4', 'mkv']);
  }

  public function isAudioFile(string $filename): bool
  {
    $ext = $this->getFileExtension($filename);
    return in_array($ext, ['mp3', 'wav']);
  }
}
