<?php
/**
 * 設定ファイル
 * アプリケーション全体の設定を管理
 */

// アプリケーション情報
define('APP_NAME', '孤独優勝クエスト');
define('APP_VERSION', '1.0.0');
define('APP_ENV', getenv('APP_ENV') ?: 'production'); // development, staging, production
define('ASSET_VERSION', '1.0.0'); // キャッシュバスティング用

// ディレクトリ設定
define('ROOT_DIR', dirname(__DIR__));
define('LOG_DIR', ROOT_DIR . '/logs');
define('CACHE_DIR', ROOT_DIR . '/cache');
define('DATA_DIR', ROOT_DIR . '/data');

// API設定
define('API_TIMEOUT', 5); // 秒
define('API_RETRY_COUNT', 3);
define('API_RETRY_DELAY', 1000); // ミリ秒
define('API_CACHE_TTL', 3600); // 1時間

// OPI設定
define('OPI_MIN', 0);
define('OPI_MAX', 100);
define('OPI_THRESHOLD_HELL', 80);
define('OPI_THRESHOLD_WARNING', 50);
define('OPI_THRESHOLD_CALM', 30);
define('OPI_THRESHOLD_PEACE', 0);

// セッション設定
define('SESSION_LIFETIME', 86400); // 24時間
define('SESSION_NAME', 'solitude_victory_session');

// セキュリティ設定
define('CSRF_TOKEN_LENGTH', 32);
define('CSRF_TOKEN_LIFETIME', 3600); // 1時間
define('USER_ID_LENGTH', 16);

// キャッシュ設定
define('CACHE_ENABLED', true);
define('CACHE_DEFAULT_TTL', 3600);

// ログ設定
define('LOG_ENABLED', true);
define('LOG_LEVEL', APP_ENV === 'production' ? 'INFO' : 'DEBUG');
define('LOG_MAX_FILES', 30);
define('LOG_MAX_SIZE', 10485760); // 10MB

// データベース設定（将来の拡張用）
define('DB_ENABLED', false);
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'solitude_victory');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// 外部API設定（環境変数から取得）
define('PEOPLE_FLOW_API_URL', getenv('PEOPLE_FLOW_API_URL') ?: '');
define('PEOPLE_FLOW_API_KEY', getenv('PEOPLE_FLOW_API_KEY') ?: '');

// 経験値設定
define('BASE_EXP_STAY', 100);
define('BASE_EXP_OUT', 80);
define('EXP_MULTIPLIER_OPI', 0.5);
define('EXP_BONUS_COMBO', 50);
define('EXP_BONUS_STREAK', 25);

// コンボ設定
define('COMBO_TIMEOUT', 1800); // 30分
define('COMBO_MAX_HISTORY', 10);

// フォールバックOPI設定
define('FALLBACK_OPI_MIN', 18);
define('FALLBACK_OPI_MAX', 96);
define('FALLBACK_OPI_WEIGHTS', [
    'morning' => ['min' => 25, 'max' => 50],   // 朝
    'noon' => ['min' => 45, 'max' => 75],      // 昼
    'evening' => ['min' => 60, 'max' => 90],   // 夕方
    'night' => ['min' => 20, 'max' => 45]      // 夜
]);

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

// ディレクトリ作成（存在しない場合）
$directories = [LOG_DIR, CACHE_DIR, CACHE_DIR . '/opi', CACHE_DIR . '/scenes'];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

/**
 * 環境別設定読み込み
 */
if (file_exists(ROOT_DIR . '/.env.php')) {
    require_once ROOT_DIR . '/.env.php';
}

/**
 * ヘルパー関数
 */

/**
 * ベースURLを取得
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . '://' . $host . $script;
}

/**
 * 現在のURLを取得
 */
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return $protocol . '://' . $host . $uri;
}

/**
 * ユーザーIDを生成
 */
function generateUserId() {
    return bin2hex(random_bytes(USER_ID_LENGTH / 2));
}

/**
 * CSRFトークンを生成
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time']) ||
        time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_LIFETIME) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH / 2));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRFトークンを検証
 */
function verifyCsrfToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_LIFETIME) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * 難易度ラベルを取得
 */
function getDifficultyLabel($difficulty) {
    $labels = [
        'hell' => '修羅場',
        'warning' => '警戒',
        'calm' => '微風',
        'peace' => '静寂'
    ];
    return $labels[$difficulty] ?? '不明';
}

/**
 * OPIに応じたヒントを取得
 */
function getOpiTip($opi) {
    if ($opi >= 80) {
        return '外出は高リスク。在宅が最適解。';
    } elseif ($opi >= 50) {
        return '混雑が予想されます。慎重に。';
    } elseif ($opi >= 30) {
        return '比較的空いています。';
    } else {
        return '静かな時間帯です。';
    }
}

/**
 * 時間帯を取得
 */
if (!function_exists('getTimeOfDay')) {
    function getTimeOfDay() {
        $hour = (int)date('H');
        if ($hour >= 5 && $hour < 12) return 'morning';
        if ($hour >= 12 && $hour < 17) return 'noon';
        if ($hour >= 17 && $hour < 21) return 'evening';
        return 'night';
    }
}

/**
 * スマートフォールバックOPI生成
 * 時間帯を考慮したリアルな値を生成
 */
if (!function_exists('generateSmartFallbackOpi')) {
    function generateSmartFallbackOpi() {
        $hour = (int)date('H');
        
        // 時間帯判定
        if ($hour >= 5 && $hour < 12) {
            $timeOfDay = 'morning';
        } elseif ($hour >= 12 && $hour < 17) {
            $timeOfDay = 'noon';
        } elseif ($hour >= 17 && $hour < 21) {
            $timeOfDay = 'evening';
        } else {
            $timeOfDay = 'night';
        }
        
        // 時間帯別の重み付け
        $weights_config = [
            'morning' => ['min' => 25, 'max' => 50],
            'noon' => ['min' => 45, 'max' => 75],
            'evening' => ['min' => 60, 'max' => 90],
            'night' => ['min' => 20, 'max' => 45]
        ];
        
        $weights = $weights_config[$timeOfDay];
        
        // 曜日による補正
        $dayOfWeek = (int)date('w');
        $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6);
        
        $min = $weights['min'];
        $max = $weights['max'];
        
        // 週末は+10
        if ($isWeekend) {
            $min = min(100, $min + 10);
            $max = min(100, $max + 10);
        }
        
        // 祝日判定（簡易版）
        $holidays = ['01-01', '01-02', '01-03', '05-03', '05-04', '05-05'];
        if (in_array(date('m-d'), $holidays)) {
            $min = min(100, $min + 15);
            $max = min(100, $max + 15);
        }
        
        return rand($min, $max);
    }
}

/**
 * 祝日判定（簡易版）
 */
if (!function_exists('isHoliday')) {
    function isHoliday() {
        // TODO: 実際の祝日カレンダーと連携
        $holidays = [
            '01-01', '01-02', '01-03', // 正月
            '05-03', '05-04', '05-05', // GW
        '08-11', '08-12', '08-13', '08-14', '08-15', // お盆
        '12-29', '12-30', '12-31' // 年末
    ];
    
    return in_array(date('m-d'), $holidays);
    }
}

/**
 * 統計記録
 */
if (!function_exists('recordStatistics')) {
    function recordStatistics($opi, $difficulty, $userId) {
        if (!CACHE_ENABLED) return;
        
        $statsFile = CACHE_DIR . '/statistics.json';
        $stats = [];
        
        if (file_exists($statsFile)) {
            $stats = json_decode(file_get_contents($statsFile), true) ?: [];
    }
    
    $today = date('Y-m-d');
    if (!isset($stats[$today])) {
        $stats[$today] = [
            'visits' => 0,
            'opi_total' => 0,
            'difficulties' => ['hell' => 0, 'warning' => 0, 'calm' => 0, 'peace' => 0],
            'unique_users' => []
        ];
    }
    
    $stats[$today]['visits']++;
    $stats[$today]['opi_total'] += $opi;
    $stats[$today]['difficulties'][$difficulty]++;
    
    if (!in_array($userId, $stats[$today]['unique_users'])) {
        $stats[$today]['unique_users'][] = $userId;
    }
    
    // 古い統計を削除（30日以上前）
    $cutoffDate = date('Y-m-d', strtotime('-30 days'));
    foreach ($stats as $date => $data) {
        if ($date < $cutoffDate) {
            unset($stats[$date]);
        }
    }
    
    file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT));
    }
}
