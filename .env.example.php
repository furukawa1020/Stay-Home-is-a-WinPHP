<?php
/**
 * 環境設定ファイル（サンプル）
 * 
 * このファイルを `.env.php` にコピーして使用してください。
 * `.env.php` は .gitignore に含まれており、Gitにコミットされません。
 * 
 * コピーコマンド:
 * cp .env.example.php .env.php
 * 
 * または Windows:
 * copy .env.example.php .env.php
 */

// ==========================================
// アプリケーション設定
// ==========================================

// 環境: development, staging, production
define('APP_ENV', 'development');

// デバッグモード（本番環境では false に設定）
define('APP_DEBUG', true);

// ==========================================
// API設定
// ==========================================

// 人流API エンドポイント
// 例: https://api.example.com/people-flow/current
define('PEOPLE_FLOW_API_URL', 'https://api.example.com/people-flow');

// API キー（必要な場合）
define('PEOPLE_FLOW_API_KEY', 'your_api_key_here');

// APIタイムアウト（秒）
define('API_TIMEOUT', 5);

// APIリトライ回数
define('API_RETRY_COUNT', 3);

// ==========================================
// ログ設定
// ==========================================

// ログレベル: DEBUG, INFO, WARNING, ERROR, CRITICAL
define('LOG_LEVEL', 'INFO');

// ログ有効化
define('LOG_ENABLED', true);

// ログファイル最大サイズ（バイト）
define('LOG_MAX_SIZE', 10 * 1024 * 1024); // 10MB

// ログファイル保持日数
define('LOG_RETENTION_DAYS', 30);

// ==========================================
// キャッシュ設定
// ==========================================

// キャッシュ有効化
define('CACHE_ENABLED', true);

// OPIキャッシュTTL（秒）
define('CACHE_OPI_TTL', 3600); // 1時間

// ==========================================
// セキュリティ設定
// ==========================================

// CSRFトークンの有効期限（秒）
define('CSRF_TOKEN_EXPIRY', 3600); // 1時間

// セッションの有効期限（秒）
define('SESSION_LIFETIME', 86400); // 24時間

// セキュアクッキー（HTTPS環境では true に設定）
define('SESSION_COOKIE_SECURE', false);

// ==========================================
// データベース設定（将来用）
// ==========================================

// データベース有効化
// define('DB_ENABLED', false);

// データベースホスト
// define('DB_HOST', 'localhost');

// データベース名
// define('DB_NAME', 'kodoku_quest');

// データベースユーザー
// define('DB_USER', 'root');

// データベースパスワード
// define('DB_PASSWORD', 'password');

// データベース文字セット
// define('DB_CHARSET', 'utf8mb4');

// ==========================================
// メール設定（将来用）
// ==========================================

// メール送信有効化
// define('MAIL_ENABLED', false);

// SMTPホスト
// define('MAIL_SMTP_HOST', 'smtp.example.com');

// SMTPポート
// define('MAIL_SMTP_PORT', 587);

// SMTPユーザー
// define('MAIL_SMTP_USER', 'no-reply@example.com');

// SMTPパスワード
// define('MAIL_SMTP_PASSWORD', 'password');

// 送信元メールアドレス
// define('MAIL_FROM_ADDRESS', 'no-reply@example.com');

// 送信元名
// define('MAIL_FROM_NAME', '孤独優勝クエスト');

// ==========================================
// ソーシャルメディア設定（将来用）
// ==========================================

// Twitter/X API キー
// define('TWITTER_API_KEY', 'your_api_key');
// define('TWITTER_API_SECRET', 'your_api_secret');

// ==========================================
// Google Analytics（将来用）
// ==========================================

// Google Analytics 測定ID
// define('GA_MEASUREMENT_ID', 'G-XXXXXXXXXX');

// ==========================================
// カスタム設定
// ==========================================

// サイトURL（本番環境では実際のURLに変更）
define('SITE_URL', 'http://localhost/PHPdenanika');

// 管理者メールアドレス
define('ADMIN_EMAIL', 'admin@example.com');

// タイムゾーン
define('TIMEZONE', 'Asia/Tokyo');

// ==========================================
// 設定の読み込み
// ==========================================

// タイムゾーン設定
date_default_timezone_set(TIMEZONE);

// エラー表示設定
if (APP_ENV === 'development' && APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

// ==========================================
// 使用方法
// ==========================================
/*

1. このファイルを `.env.php` にコピー:
   cp .env.example.php .env.php

2. `.env.php` を編集して、各設定値を環境に合わせて変更

3. `lib/config.php` の先頭で `.env.php` を読み込む:
   if (file_exists(__DIR__ . '/../.env.php')) {
       require_once __DIR__ . '/../.env.php';
   }

4. 設定値を使用:
   $apiUrl = PEOPLE_FLOW_API_URL;
   $logLevel = LOG_LEVEL;

*/

// ==========================================
// セキュリティ注意事項
// ==========================================
/*

⚠️ 重要:
- `.env.php` は絶対にGitにコミットしないでください
- `.gitignore` に `.env.php` が含まれていることを確認してください
- 本番環境では、APIキーやパスワードを環境変数で管理することを推奨します
- `APP_ENV` を 'production' に設定し、`APP_DEBUG` を false にしてください

*/
