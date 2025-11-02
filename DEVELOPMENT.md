# 📋 開発ドキュメント - 孤独優勝クエスト

## アーキテクチャ概要

### ディレクトリ構成

```
PHPdenanika/
├── index.php                   # エントリーポイント
├── result.php                  # 結果ページ
├── style.css                   # スタイルシート（グラスモーフィズム）
├── script.js                   # クライアントサイドJS
├── lib/                        # ライブラリ
│   ├── config.php              # 全体設定・定数
│   ├── api.php                 # API通信（リトライ・タイムアウト）
│   ├── logger.php              # ロガー（PSR-3互換）
│   ├── cache.php               # キャッシュマネージャー
│   ├── validator.php           # バリデーター
│   └── scene_generator.php     # シーン生成クラス
├── data/                       # データ
│   ├── dialogue.php            # セリフ辞書
│   └── meta.php                # メタデータ・定数
├── logs/                       # ログファイル（自動生成）
│   ├── app.log                 # アプリケーションログ
│   └── error.log               # エラーログ
├── cache/                      # キャッシュファイル（自動生成）
│   ├── opi/                    # OPIキャッシュ
│   ├── scenes/                 # シーンキャッシュ
│   └── statistics.json         # 統計データ
├── .htaccess                   # Apache設定
├── .gitignore                  # Git除外設定
└── README.md                   # プロジェクト説明
```

---

## クラス・関数一覧

### **1. config.php**

#### 定数
- `APP_NAME`, `APP_VERSION`, `APP_ENV`
- `API_TIMEOUT`, `API_RETRY_COUNT`, `API_CACHE_TTL`
- `OPI_*` - OPI閾値設定
- `SESSION_*` - セッション設定
- `CSRF_*` - セキュリティ設定
- `LOG_*` - ログ設定
- `EXP_*` - 経験値設定
- `COMBO_*` - コンボ設定

#### 関数
- `getBaseUrl()` - ベースURL取得
- `getCurrentUrl()` - 現在URL取得
- `generateUserId()` - ユーザーID生成（16桁hex）
- `generateCsrfToken()` - CSRFトークン生成
- `verifyCsrfToken($token)` - CSRFトークン検証
- `getDifficultyLabel($difficulty)` - 難易度ラベル取得
- `getOpiTip($opi)` - OPIヒント取得
- `getTimeOfDay()` - 時間帯取得（morning/noon/evening/night）
- `generateSmartFallbackOpi()` - スマートフォールバックOPI生成
- `isHoliday()` - 祝日判定
- `recordStatistics($opi, $difficulty, $userId)` - 統計記録

---

### **2. Logger クラス**

#### メソッド
- `__construct($logFile, $logLevel = 'INFO')` - コンストラクタ
- `debug($message, array $context = [])` - DEBUGログ
- `info($message, array $context = [])` - INFOログ
- `warning($message, array $context = [])` - WARNINGログ
- `error($message, array $context = [])` - ERRORログ
- `critical($message, array $context = [])` - CRITICALログ

#### 機能
- PSR-3互換ログレベル
- 自動ログローテーション（10MB超過時）
- 古いログファイル削除（30日保持）
- コンテキスト情報JSON出力

---

### **3. CacheManager クラス**

#### メソッド
- `__construct($cacheDir)` - コンストラクタ
- `get($key)` - キャッシュ取得
- `set($key, $value, $ttl = null)` - キャッシュ保存
- `delete($key)` - キャッシュ削除
- `clear()` - 全キャッシュクリア
- `gc()` - ガベージコレクション（期限切れ削除）

#### 機能
- ファイルベースキャッシュ
- TTL（Time To Live）管理
- MD5ハッシュベースのファイル名
- サブディレクトリ分割（パフォーマンス向上）

---

### **4. Validator クラス**

#### メソッド
- `isValidOpi($opi)` - OPI値検証（0-100）
- `isValidDifficulty($difficulty)` - 難易度検証
- `isValidChoice($choice)` - 選択肢検証（stay_*|out_*）
- `isValidSessionId($sessionId)` - セッションID検証
- `isValidTimestamp($timestamp, $maxAge = 3600)` - タイムスタンプ検証
- `sanitizeString($str, $maxLength = 255)` - 文字列サニタイズ
- `sanitizeInt($value, $min = null, $max = null)` - 整数サニタイズ
- `sanitizeArray(array $array, $maxItems = 100)` - 配列サニタイズ

#### 機能
- 入力値検証
- XSS対策（htmlspecialchars）
- 長さ制限
- 型変換

---

### **5. API関数群（api.php）**

#### 主要関数
- `fetchOpiWithRetry($maxRetries = 3)` - OPI取得（リトライ機能付き）
- `fetchOpiFromApi($apiUrl)` - API直接リクエスト
- `getApiUrl()` - APIエンドポイント取得
- `buildApiHeaders()` - HTTPヘッダー構築
- `getHttpResponseCode(array $headers)` - HTTPステータスコード抽出
- `convertToOpi($data)` - APIデータ→OPI変換（7パターン対応）
- `clamp($value, $min, $max)` - 値の範囲制限
- `generateMockApiResponse($opi = null)` - モックデータ生成
- `testApiConnection()` - API接続テスト

#### 機能
- **リトライ機能** - 最大3回、指数バックオフ
- **タイムアウト** - 5秒（設定可能）
- **複数フォーマット対応** - 7種類のJSONフォーマット
- **エラーハンドリング** - 詳細なエラー情報返却
- **レスポンスタイム計測**

#### サポートするAPIフォーマット
1. `people_percent` - 人流パーセンテージ
2. `congestion_rate` - 混雑率
3. `crowd_level` - 混雑レベル（5段階）
4. `density` - 密度（0-1）
5. `traffic_index` - トラフィック指数
6. `data.congestion` - ネストされたデータ
7. `results[0]` - 配列形式

---

### **6. SceneGenerator クラス**

#### プロパティ
- `$opi` - 外出圧力指数
- `$isOffline` - オフライン状態
- `$session` - ユーザーセッション
- `$difficulty` - 難易度
- `$timeOfDay` - 時間帯

#### メソッド
- `__construct($opi, $isOffline, $session)` - コンストラクタ
- `generate()` - シーン生成
- `calculateDifficulty()` - 難易度計算（private）
- `generateMessage()` - メインメッセージ生成（private）
- `getMessagesForDifficulty()` - 難易度別メッセージ取得（private）
- `generateOfflineMessage()` - オフラインメッセージ生成（private）
- `generateStayChoices()` - 在宅行動選択肢生成（private）
- `generateOutChoices()` - 微外出選択肢生成（private）
- `generateSpecialEvent()` - 特別イベント生成（private）
- `filterChoicesByTag($choices, $tags)` - タグフィルタリング（private）

#### 機能
- **時間帯考慮** - morning/noon/evening/night
- **コンボボーナス** - 連続3回以上で特別メッセージ
- **選択肢バリエーション** - 12種類の在宅行動
- **タグベースフィルタリング** - OPIに応じた選択肢最適化
- **履歴除外** - 直近3回の行動を除外
- **ランダムボーナス** - 33%確率で+10~30XP
- **特別イベント** - 10%確率で発生
- **OPI連動外出制限** - OPI>49で微外出なし

#### 在宅行動タグ
- `relax` - リラックス系
- `warm` - 温かい系
- `mood` - 気分転換系
- `body` - 身体系
- `mind` - メンタル系
- `refresh` - リフレッシュ系
- `calm` - 静寂系
- `nostalgic` - ノスタルジック系
- `fun` - 楽しい系
- `creative` - クリエイティブ系
- `expression` - 表現系

---

## データフロー

```
User Request
    ↓
index.php
    ↓
┌─────────────────────────────────┐
│ 1. セッション初期化              │
│    - user_id生成                 │
│    - visit_count++               │
│    - action_history管理          │
└─────────────────────────────────┘
    ↓
┌─────────────────────────────────┐
│ 2. OPI取得（キャッシュ優先）     │
│    ↓                             │
│    Cache::get('opi_YmdH')       │
│    ↓ (miss)                      │
│    fetchOpiWithRetry(3)          │
│    ├─ API Request (timeout 5s)  │
│    ├─ Retry × 3                  │
│    └─ Fallback (smart)           │
│    ↓                             │
│    Cache::set('opi_YmdH', 3600) │
└─────────────────────────────────┘
    ↓
┌─────────────────────────────────┐
│ 3. シーン生成                    │
│    ↓                             │
│    new SceneGenerator()          │
│    ├─ difficulty計算             │
│    ├─ メッセージ生成             │
│    │   └─ 時間帯・コンボ考慮     │
│    ├─ 在宅行動選択肢（12→3）     │
│    │   └─ タグフィルタ・履歴除外 │
│    ├─ 微外出選択肢（OPI≤49）     │
│    └─ 特別イベント（10%）        │
└─────────────────────────────────┘
    ↓
┌─────────────────────────────────┐
│ 4. HTML生成・出力                │
│    ├─ OGP・メタタグ              │
│    ├─ CSRFトークン               │
│    ├─ ユーザー統計表示           │
│    └─ JavaScript初期データ       │
└─────────────────────────────────┘
    ↓
┌─────────────────────────────────┐
│ 5. ログ・統計記録                │
│    ├─ Logger::info()             │
│    └─ recordStatistics()         │
└─────────────────────────────────┘
```

---

## セキュリティ機能

### 1. XSS対策
- `htmlspecialchars()` で全出力エスケープ
- `strip_tags()` で入力サニタイズ
- Content Security Policy準拠

### 2. CSRF対策
- トークン生成・検証（32バイト）
- 1時間有効期限
- `hash_equals()` でタイミング攻撃対策

### 3. セッション管理
- セキュアなユーザーID生成
- セッションハイジャック対策
- 24時間有効期限

### 4. 入力検証
- `Validator`クラスで厳密な検証
- 型チェック・範囲チェック
- 正規表現ベース検証

### 5. HTTPヘッダー
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: no-referrer-when-downgrade`

---

## パフォーマンス最適化

### 1. キャッシング
- **OPIキャッシュ**: 1時間（時刻ベース）
- **シーンキャッシュ**: 必要に応じて実装可能
- **ファイルベース**: 高速・依存なし

### 2. ログローテーション
- **サイズ制限**: 10MB
- **保持期間**: 30日
- **自動削除**: 古いファイルを自動削除

### 3. API最適化
- **タイムアウト**: 5秒
- **リトライ**: 指数バックオフ
- **レスポンスタイム計測**: デバッグ用

### 4. データベースレス設計
- ファイルベースで軽量
- サーバー負荷最小化
- スケール容易

---

## 環境変数・設定

### .env.php（オプション）

```php
<?php
// API設定
define('PEOPLE_FLOW_API_URL', 'https://api.example.com/people-flow');
define('PEOPLE_FLOW_API_KEY', 'your_api_key_here');

// 環境設定
define('APP_ENV', 'production'); // development, staging, production

// デバッグ設定
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR, CRITICAL
```

### config/api.json（オプション）

```json
{
  "people_flow_api_url": "https://api.example.com/people-flow",
  "people_flow_api_key": "your_api_key_here",
  "timeout": 5,
  "retry_count": 3
}
```

---

## テスト・デバッグ

### API接続テスト

```php
<?php
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/api.php';

$result = testApiConnection();
print_r($result);
```

### ログ確認

```bash
tail -f logs/app.log
tail -f logs/error.log
```

### キャッシュクリア

```php
<?php
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/cache.php';

$cache = new CacheManager(__DIR__ . '/cache');
$cache->clear();
echo "キャッシュクリア完了";
```

### 統計確認

```php
<?php
$stats = json_decode(file_get_contents(__DIR__ . '/cache/statistics.json'), true);
print_r($stats);
```

---

## 拡張・カスタマイズ

### 新しい選択肢追加

`lib/scene_generator.php` の `generateStayChoices()` 内の `$allChoices` 配列に追加：

```php
['icon' => '🆕', 'text' => '新しい行動', 'key' => 'new_action', 'tags' => ['relax', 'fun']]
```

### 新しいメッセージ追加

`lib/scene_generator.php` の `getMessagesForDifficulty()` 内の配列に追加：

```php
'hell' => [
    "新しいメッセージ",
    // ...既存メッセージ
]
```

### 新しい難易度追加

1. `lib/config.php` に閾値追加
2. `SceneGenerator::calculateDifficulty()` 修正
3. メッセージ・選択肢追加

---

## トラブルシューティング

### ログが記録されない
- `logs/` ディレクトリの書き込み権限確認
- `LOG_ENABLED` が `true` か確認

### キャッシュが効かない
- `cache/` ディレクトリの書き込み権限確認
- `CACHE_ENABLED` が `true` か確認

### API接続失敗
- `PEOPLE_FLOW_API_URL` が正しいか確認
- ネットワーク接続確認
- `testApiConnection()` で診断

### セッションエラー
- PHPの `session.save_path` 確認
- セッションディレクトリの書き込み権限確認

---

## 本番環境デプロイ

### 1. 環境設定
```bash
# .env.php作成
cp .env.example.php .env.php
# 設定を編集
nano .env.php
```

### 2. 権限設定
```bash
chmod 755 -R .
chmod 777 logs/
chmod 777 cache/
```

### 3. Apache設定
`.htaccess` を確認・有効化

### 4. セキュリティチェック
- `APP_ENV` を `production` に設定
- `display_errors` を `Off` に設定
- `LOG_LEVEL` を `INFO` 以上に設定
- HTTPS有効化

### 5. パフォーマンスチューニング
- OpCache有効化
- Gzip圧縮有効化
- 静的ファイルキャッシング

---

## ライセンス

MIT License

---

## 貢献者

- Solitude Victory Team

---

**Stay Home is a Win. 🏠✨**
