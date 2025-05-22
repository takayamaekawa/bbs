<?php

namespace Root\Composer\Core\Config;

class Settings {
  private static ?self $instance = null;

  private array $config = [];

  private string $configFilePath;

  /**
   * Settings constructor.
   * プライベートにして、getInstance()経由でのみインスタンス化されるようにします。
   *
   * @param string $configFilePath JSON設定ファイルの絶対パス
   * @throws \RuntimeException 設定ファイルの読み込みやパースに失敗した場合
   */
  private function __construct(string $configFilePath) {
    $this->configFilePath = $configFilePath;
    $this->load();
  }

  /**
   * シングルトンインスタンスを取得します。
   * 初回呼び出し時に設定ファイルをロードします。
   *
   * @param string|null $configFilePath JSON設定ファイルの絶対パス (初回初期化時のみ有効)
   * @return self
   * @throws \InvalidArgumentException 初回初期化時にconfigFilePathが指定されない場合
   * @throws \RuntimeException 設定ファイルの読み込みやパースに失敗した場合
   */
  public static function getInstance(?string $configFilePath = null): self {
    if (self::$instance === null) {
      if ($configFilePath === null) {
        // 実際には、デフォルトパスを設定するか、より厳密なエラー処理を行う
        throw new \InvalidArgumentException('Configuration file path must be provided for the first initialization.');
      }
      self::$instance = new self($configFilePath);
    }
    // 2回目以降の呼び出しで $configFilePath が渡されても、既存のインスタンスのパスは変更しない
    // もし変更を許可したい場合は、別途ロジックが必要
    return self::$instance;
  }

  /**
   * 設定ファイルをロードします。
   *
   * @throws \RuntimeException 設定ファイルの読み込みやパースに失敗した場合
   */
  private function load(): void {
    if (!file_exists($this->configFilePath)) {
      throw new \RuntimeException("設定ファイルが見つかりません: {$this->configFilePath}");
    }

    $jsonString = file_get_contents($this->configFilePath);
    if ($jsonString === false) {
      throw new \RuntimeException("設定ファイルの読み込みに失敗しました: {$this->configFilePath}");
    }

    $configData = json_decode($jsonString, true); // 連想配列としてデコード

    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new \RuntimeException('設定ファイルのJSONデコードに失敗しました: ' . json_last_error_msg());
    }
    $this->config = $configData;
  }

  /**
   * 指定されたキーの設定値を取得します。
   * ドット記法（例: 'database.host'）でネストした値にアクセスできます。
   *
   * @param string $key 設定キー (例: 'app_name', 'database.host')
   * @param mixed|null $default キーが存在しない場合に返すデフォルト値
   * @return mixed 設定値、またはデフォルト値
   */
  public function get(string $key, $default = null) {
    if (empty($key)) {
      return $default;
    }

    // キーにドットが含まれていない場合、トップレベルの値を取得
    if (strpos($key, '.') === false) {
      return $this->config[$key] ?? $default;
    }

    // ドット記法でネストした値を取得
    $keys = explode('.', $key);
    $value = $this->config;

    foreach ($keys as $segment) {
      if (is_array($value) && array_key_exists($segment, $value)) {
        $value = $value[$segment];
      } else {
        return $default; //途中でキーが見つからなければデフォルト値を返す
      }
    }
    return $value;
  }

  /**
   * 全ての設定値を連想配列として取得します。
   *
   * @return array
   */
  public function getAll(): array {
    return $this->config;
  }

  /**
   * (デバッグ用など) 設定を強制的にリロードします。
   * 通常の運用では不要かもしれません。
   * @throws \RuntimeException
   */
  public function reload(): void {
    $this->load();
  }
}
