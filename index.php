<?php
/**
 * 孤独優勝クエスト - Stay Home is a Win
 * メインエントリーポイント
 * 
 * @version 1.0.0
 * @author Solitude Victory Team
 * @copyright 2025 Solitude Victory Project
 */

// エラーハンドリング設定
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// セキュリティヘッダー
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: no-referrer-when-downgrade');

// セッション開始（コンボ追跡・履歴用）
session_start();

// 依存ファイル読み込み
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/api.php';
require_once __DIR__ . '/lib/logger.php';
require_once __DIR__ . '/lib/cache.php';
require_once __DIR__ . '/lib/validator.php';
require_once __DIR__ . '/lib/scene_generator.php';
require_once __DIR__ . '/data/dialogue.php';
require_once __DIR__ . '/data/meta.php';

// ロガー初期化
$logger = new Logger(__DIR__ . '/logs/app.log');
$logger->info('=== 孤独優勝クエスト 起動 ===');

// キャッシュマネージャー初期化
$cache = new CacheManager(__DIR__ . '/cache');

try {
    // ユーザーセッション初期化
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = generateUserId();
        $_SESSION['visit_count'] = 0;
        $_SESSION['total_exp'] = 0;
        $_SESSION['action_history'] = [];
        $_SESSION['combo_streak'] = 0;
        $logger->info('新規ユーザーセッション作成: ' . $_SESSION['user_id']);
    }
    
    $_SESSION['visit_count']++;
    $_SESSION['last_visit'] = date('Y-m-d H:i:s');
    
    // OPI取得（キャッシュ優先）
    $cacheKey = 'opi_' . date('YmdH'); // 1時間キャッシュ
    $apiResult = $cache->get($cacheKey);
    
    if ($apiResult === null) {
        $logger->info('OPI APIリクエスト開始');
        $apiResult = fetchOpiWithRetry(3); // 3回リトライ
        
        if ($apiResult['success']) {
            $cache->set($cacheKey, $apiResult, 3600); // 1時間
            $logger->info('OPI取得成功: ' . $apiResult['opi']);
        } else {
            $logger->warning('OPI取得失敗: ' . $apiResult['error']);
        }
    } else {
        $logger->info('OPIキャッシュヒット');
    }
    
    // OPI値とオフライン状態を設定
    $opi = $apiResult['opi'] ?? generateSmartFallbackOpi();
    $isOffline = !$apiResult['success'];
    $apiSource = $apiResult['source'] ?? 'fallback';
    $apiTimestamp = $apiResult['timestamp'] ?? time();
    
    // OPI値のバリデーション
    if (!Validator::isValidOpi($opi)) {
        throw new InvalidArgumentException('Invalid OPI value: ' . $opi);
    }
    
    // シーン生成（複雑なロジック）
    $sceneGenerator = new SceneGenerator($opi, $isOffline, $_SESSION);
    $scene = $sceneGenerator->generate();
    
    // 統計記録
    recordStatistics($opi, $scene['difficulty'], $_SESSION['user_id']);
    
    // デバッグ情報
    $debugInfo = [
        'opi' => $opi,
        'difficulty' => $scene['difficulty'],
        'is_offline' => $isOffline,
        'api_source' => $apiSource,
        'user_id' => $_SESSION['user_id'],
        'visit_count' => $_SESSION['visit_count'],
        'combo_streak' => $_SESSION['combo_streak']
    ];
    
    $logger->debug('シーン生成完了', $debugInfo);
    
} catch (Exception $e) {
    // 例外ハンドリング
    $logger->error('エラー発生: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // フォールバック処理
    $opi = generateSmartFallbackOpi();
    $isOffline = true;
    $scene = generateFallbackScene($opi);
    $apiSource = 'error_fallback';
}
?>
<!DOCTYPE html>
<html lang="ja" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <title>孤独優勝クエスト - Stay Home is a Win | 外出圧力指数 <?php echo $opi; ?></title>
    <meta name="description" content="外が騒がしいほど、在宅は強い。今日もあなたは、ここで完全勝利。リアルタイム人流データで「孤独」を肯定する体験型ゲーム。">
    <meta name="keywords" content="孤独,在宅,引きこもり,人流データ,メンタルヘルス,自己肯定感">
    <meta name="author" content="Solitude Victory Project">
    <meta name="robots" content="index, follow">
    
    <!-- OGP -->
    <meta property="og:title" content="孤独優勝クエスト - Stay Home is a Win">
    <meta property="og:description" content="外が騒がしいほど、在宅は強い。今日もあなたは、ここで完全勝利。">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars(getCurrentUrl()); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars(getBaseUrl() . '/assets/ogp.png'); ?>">
    <meta property="og:locale" content="ja_JP">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="孤独優勝クエスト">
    <meta name="twitter:description" content="外が騒がしいほど、在宅は強い。">
    <meta name="twitter:image" content="<?php echo htmlspecialchars(getBaseUrl() . '/assets/ogp.png'); ?>">
    
    <!-- スタイルシート -->
    <link rel="stylesheet" href="style.css?v=<?php echo ASSET_VERSION; ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- ファビコン -->
    <link rel="icon" type="image/png" href="assets/favicon.png">
    <link rel="apple-touch-icon" href="assets/apple-touch-icon.png">
    
    <!-- JSON-LD構造化データ -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "孤独優勝クエスト",
        "description": "外が騒がしいほど、在宅は強い。今日もあなたは、ここで完全勝利。",
        "applicationCategory": "EntertainmentApplication",
        "operatingSystem": "Any",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "JPY"
        }
    }
    </script>
</head>
<body class="difficulty-<?php echo htmlspecialchars($scene['difficulty']); ?>" 
      data-opi="<?php echo $opi; ?>" 
      data-difficulty="<?php echo htmlspecialchars($scene['difficulty']); ?>"
      data-user-id="<?php echo htmlspecialchars($_SESSION['user_id']); ?>">
    
    <!-- メインコンテナ -->
    <div class="container">
        <!-- ヘッダー -->
        <header class="game-header">
            <h1 class="pixel-text" aria-label="孤独優勝クエスト">孤独優勝クエスト</h1>
            <p class="subtitle">Stay Home is a Win</p>
            
            <?php if ($_SESSION['visit_count'] > 1): ?>
            <div class="user-stats">
                <span class="stat-item" title="累計訪問回数">
                    🏠 <?php echo $_SESSION['visit_count']; ?>回目の在宅
                </span>
                <span class="stat-item" title="累計経験値">
                    ⭐ Total EXP: <?php echo number_format($_SESSION['total_exp']); ?>
                </span>
                <?php if ($_SESSION['combo_streak'] > 0): ?>
                <span class="stat-item combo-badge" title="連続コンボ">
                    🔥 <?php echo $_SESSION['combo_streak']; ?> COMBO
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </header>

        <!-- メインゲーム画面 -->
        <main class="game-screen">
            <!-- OPI表示パネル -->
            <section class="opi-display" role="region" aria-label="外出圧力指数">
                <div class="opi-header">
                    <div class="opi-label">外出圧力指数 (OPI)</div>
                    <div class="opi-meta">
                        <?php if (!$isOffline): ?>
                        <span class="opi-status online" title="リアルタイムデータ">🟢 LIVE</span>
                        <span class="opi-timestamp"><?php echo date('H:i', $apiTimestamp); ?> 更新</span>
                        <?php else: ?>
                        <span class="opi-status offline" title="オフライン">🔴 OFFLINE</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="opi-value" aria-live="polite"><?php echo $opi; ?></div>
                
                <div class="opi-bar" role="progressbar" aria-valuenow="<?php echo $opi; ?>" aria-valuemin="0" aria-valuemax="100">
                    <div class="opi-bar-fill" style="width: <?php echo $opi; ?>%"></div>
                </div>
                
                <div class="opi-description">
                    <span class="difficulty-badge difficulty-<?php echo $scene['difficulty']; ?>">
                        <?php echo getDifficultyLabel($scene['difficulty']); ?>
                    </span>
                    <span class="opi-tip"><?php echo getOpiTip($opi); ?></span>
                </div>
            </section>

            <!-- シーンメッセージ -->
            <section class="scene-message" id="sceneMessage" role="article">
                <div class="message-box typing-animation">
                    <?php echo nl2br(htmlspecialchars($scene['message'])); ?>
                </div>
                
                <?php if ($isOffline && !empty($scene['offline_message'])): ?>
                <div class="offline-notice">
                    <?php echo htmlspecialchars($scene['offline_message']); ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($scene['special_event'])): ?>
                <div class="special-event">
                    <span class="event-icon"><?php echo $scene['special_event']['icon']; ?></span>
                    <span class="event-text"><?php echo htmlspecialchars($scene['special_event']['text']); ?></span>
                </div>
                <?php endif; ?>
            </section>

            <!-- 選択肢フォーム -->
            <form action="result.php" method="POST" class="choice-form" id="choiceForm">
                <!-- CSRFトークン -->
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                <input type="hidden" name="opi" value="<?php echo $opi; ?>">
                <input type="hidden" name="difficulty" value="<?php echo htmlspecialchars($scene['difficulty']); ?>">
                <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($_SESSION['user_id']); ?>">
                <input type="hidden" name="timestamp" value="<?php echo time(); ?>">
                
                <div class="choices">
                    <!-- 在宅行動セクション -->
                    <div class="choice-section stay-section">
                        <h3 class="choice-title">
                            <span class="title-icon">🏠</span>
                            在宅行動
                            <span class="title-badge">勝利確定</span>
                        </h3>
                        <?php foreach ($scene['stay_choices'] as $index => $choice): ?>
                        <button type="submit" 
                                name="choice" 
                                value="stay_<?php echo htmlspecialchars($choice['key']); ?>" 
                                class="choice-btn stay-choice"
                                data-action-type="stay"
                                data-action-key="<?php echo htmlspecialchars($choice['key']); ?>"
                                aria-label="<?php echo htmlspecialchars($choice['text']); ?>を選択">
                            <span class="choice-icon"><?php echo $choice['icon']; ?></span>
                            <span class="choice-text"><?php echo htmlspecialchars($choice['text']); ?></span>
                            <?php if (isset($choice['bonus'])): ?>
                            <span class="choice-bonus">+<?php echo $choice['bonus']; ?>XP</span>
                            <?php endif; ?>
                        </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- 微外出セクション（条件付き） -->
                    <?php if (!empty($scene['out_choices'])): ?>
                    <div class="choice-section out-section">
                        <h3 class="choice-title">
                            <span class="title-icon">🚶</span>
                            微外出（任意）
                            <span class="title-badge optional">出なくてもOK</span>
                        </h3>
                        <?php foreach ($scene['out_choices'] as $index => $choice): ?>
                        <button type="submit" 
                                name="choice" 
                                value="out_<?php echo htmlspecialchars($choice['key']); ?>" 
                                class="choice-btn out-choice"
                                data-action-type="out"
                                data-action-key="<?php echo htmlspecialchars($choice['key']); ?>"
                                aria-label="<?php echo htmlspecialchars($choice['text']); ?>を選択">
                            <span class="choice-icon"><?php echo $choice['icon']; ?></span>
                            <span class="choice-text"><?php echo htmlspecialchars($choice['text']); ?></span>
                            <?php if (isset($choice['risk'])): ?>
                            <span class="choice-risk">リスク: <?php echo $choice['risk']; ?></span>
                            <?php endif; ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </form>
        </main>

        <!-- フッター -->
        <footer class="game-footer">
            <p class="footer-notice">※ どの選択も自己肯定が上がる設計です</p>
            <div class="footer-meta">
                <span>Session: <?php echo substr($_SESSION['user_id'], 0, 8); ?></span>
                <span>API: <?php echo $apiSource; ?></span>
                <span>Ver <?php echo APP_VERSION; ?></span>
            </div>
        </footer>
    </div>

    <!-- JavaScript -->
    <script>
        // 初期データをJSに渡す
        window.gameData = {
            opi: <?php echo $opi; ?>,
            difficulty: '<?php echo htmlspecialchars($scene['difficulty']); ?>',
            isOffline: <?php echo $isOffline ? 'true' : 'false'; ?>,
            userId: '<?php echo htmlspecialchars($_SESSION['user_id']); ?>',
            visitCount: <?php echo $_SESSION['visit_count']; ?>,
            comboStreak: <?php echo $_SESSION['combo_streak']; ?>
        };
    </script>
    <script src="script.js?v=<?php echo ASSET_VERSION; ?>"></script>
</body>
</html>
