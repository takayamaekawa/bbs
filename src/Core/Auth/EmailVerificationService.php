<?php

namespace Root\Composer\Core\Auth;

use PDO;
use Root\Composer\Core\Config\Settings;
use Root\Composer\Lib\Helpers\Random;

class EmailVerificationService
{
  private PDO $db;
  private Settings $settings;

  public function __construct(PDO $db, Settings $settings)
  {
    $this->db = $db;
    $this->settings = $settings;
  }

  /**
   * メール認証トークンを生成して送信
   */
  public function sendVerificationEmail(int $userId, string $email): bool
  {
    try {
      // 32文字のランダムトークンを生成
      $token = Random::generateSecureToken(32);
      $hashedToken = password_hash($token, PASSWORD_DEFAULT);

      // 24時間後の有効期限を設定
      $expiresAt = date('Y-m-d H:i:s', time() + 86400);

      // データベースにトークンを保存
      $sql = 'UPDATE members SET
                email_verification_token = :token,
                email_verification_expires = :expires
              WHERE id = :id';

      $stmt = $this->db->prepare($sql);
      $stmt->bindValue(':token', $hashedToken, PDO::PARAM_STR);
      $stmt->bindValue(':expires', $expiresAt, PDO::PARAM_STR);
      $stmt->bindValue(':id', $userId, PDO::PARAM_INT);

      if (!$stmt->execute()) {
        error_log('Failed to save verification token for user ID: ' . $userId);
        return false;
      }

      // 認証URLを生成
      $encodedUserId = urlencode($userId);
      $encodedToken = urlencode($token);
      $verificationUrl = "http://" . $_SERVER['HTTP_HOST'] . "/profile/verify.php?u={$encodedUserId}&t={$encodedToken}";

      // メール送信
      return $this->sendEmail($email, $verificationUrl);
    } catch (\Exception $e) {
      error_log('Email verification error: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * 認証トークンを検証
   */
  public function verifyToken(int $userId, string $token): bool
  {
    try {
      $sql = 'SELECT email_verification_token, email_verification_expires
              FROM members
              WHERE id = :id AND email_verified = 0';

      $stmt = $this->db->prepare($sql);
      $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
      $stmt->execute();

      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!$user) {
        return false; // ユーザーが存在しないか、既に認証済み
      }

      // トークンの有効期限をチェック
      if (strtotime($user['email_verification_expires']) < time()) {
        return false; // 期限切れ
      }

      // トークンを検証
      if (!password_verify($token, $user['email_verification_token'])) {
        return false; // トークンが無効
      }

      // メール認証を完了
      $sql = 'UPDATE members SET
                email_verified = 1,
                email_verification_token = NULL,
                email_verification_expires = NULL
              WHERE id = :id';

      $stmt = $this->db->prepare($sql);
      $stmt->bindValue(':id', $userId, PDO::PARAM_INT);

      return $stmt->execute();
    } catch (\Exception $e) {
      error_log('Token verification error: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * ユーザーがメール認証済みかどうかを確認
   */
  public function isEmailVerified(int $userId): bool
  {
    try {
      $sql = 'SELECT email_verified FROM members WHERE id = :id';
      $stmt = $this->db->prepare($sql);
      $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
      $stmt->execute();

      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result && (bool)$result['email_verified'];
    } catch (\Exception $e) {
      error_log('Email verification check error: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * 実際のメール送信処理（PHPMailerを使用）
   */
  private function sendEmail(string $toEmail, string $verificationUrl): bool
  {
    try {
      // PHPMailerがインストールされているかチェック
      if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        error_log('PHPMailer is not installed. Please run: composer require phpmailer/phpmailer');
        return false;
      }

      $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

      // SMTP設定
      $mail->isSMTP();
      $mail->Host = $this->settings->get('mail.host');
      $mail->SMTPAuth = true;
      $mail->Username = $this->settings->get('mail.username');
      $mail->Password = $this->settings->get('mail.password');
      $mail->SMTPSecure = $this->settings->get('mail.encryption', 'tls');
      $mail->Port = $this->settings->get('mail.port', 587);

      // 文字コード設定
      $mail->CharSet = 'UTF-8';
      $mail->isHTML(true);

      // 送信者設定
      $mail->setFrom(
        $this->settings->get('mail.from_email'),
        $this->settings->get('mail.from_name', $this->settings->get('app_settings.site_name', '絶・掲示板'))
      );
      
      // 返信先設定
      $replyToEmail = $this->settings->get('mail.reply_to_email');
      $replyToName = $this->settings->get('mail.reply_to_name');
      if ($replyToEmail) {
        $mail->addReplyTo($replyToEmail, $replyToName);
      }

      // 受信者設定
      $mail->addAddress($toEmail);

      // メール内容
      $mail->Subject = '【' . $this->settings->get('app_settings.site_name', '絶・掲示板') . '】メールアドレス認証のお願い';
      $mail->Body = $this->getEmailTemplate($verificationUrl);

      return $mail->send();
    } catch (\Exception $e) {
      error_log('Mail send error: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * メールテンプレートを生成
   */
  private function getEmailTemplate(string $verificationUrl): string
  {
    $siteName = $this->settings->get('app_settings.site_name', '絶・掲示板');

    return <<<HTML
    <html>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
      <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4CAF50;">{$siteName} メールアドレス認証</h2>

        <p>この度は{$siteName}にご登録いただき、ありがとうございます。</p>

        <p>メールアドレスの認証を完了するため、以下のリンクをクリックしてください：</p>

        <div style="text-align: center; margin: 30px 0;">
          <a href="{$verificationUrl}"
             style="background-color: #4CAF50; color: white; padding: 12px 24px;
                    text-decoration: none; border-radius: 4px; display: inline-block;">
            メールアドレスを認証する
          </a>
        </div>

        <p>リンクが機能しない場合は、以下のURLを直接ブラウザにコピー&ペーストしてください：</p>
        <p style="word-break: break-all; background-color: #f4f4f4; padding: 10px; border-radius: 4px;">
          {$verificationUrl}
        </p>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">

        <p style="font-size: 12px; color: #666;">
          このメールに覚えがない場合は、このメールを無視してください。<br>
          認証リンクは24時間で無効になります。
        </p>

        <p style="font-size: 12px; color: #666;">
          © 2025 {$siteName}
        </p>
      </div>
    </body>
    </html>
    HTML;
  }
}

