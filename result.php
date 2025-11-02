<?php
/**
 * result.php - 結果ページ（Enterprise-Grade Implementation）
 * 
 * 機能:
 * - CSRF検証
 * - 経験値計算（ボーナス・コンボ・時間帯考慮）
 * - 称号システム
 * - コンボ検出（パターンマッチング）
 * - セッション更新（統計・履歴）
 * - ログ記録
 * - リッチなアニメーション
 * 
 * @version 1.0.0
 */

// ライブラリ読み込み
require_once __DIR__ . '/lib/config.php';
require_once __DIR__ . '/lib/logger.php';
require_once __DIR__ . '/lib/validator.php';
require_once __DIR__ . '/data/dialogue.php';

// エラーハンドリング
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    global $logger;
    $logger->error("Error [$errno]: $errstr in $errfile:$errline");
    if (APP_ENV === 'development') {
        echo "Error: $errstr";
    }
    return true;
});

// ロガー初期化
$logger = new Logger(__DIR__ . '/logs/app.log', LOG_LEVEL);
$logger->info("=== Result Page Start ===");

// セッション開始
session_start();

// POSTデータ取得と検証
$choice = $_POST['choice'] ?? null;
$opi = isset($_POST['opi']) ? (int)$_POST['opi'] : null;
$difficulty = $_POST['difficulty'] ?? null;
$csrfToken = $_POST['csrf_token'] ?? null;

// CSRF検証
if (!verifyCsrfToken($csrfToken)) {
    $logger->warning("CSRF token verification failed");
    header("Location: index.php?error=csrf");
    exit;
}

// 入力検証
if (!Validator::isValidChoice($choice)) {
    $logger->warning("Invalid choice: $choice");
    header("Location: index.php?error=invalid_choice");
    exit;
}

if (!Validator::isValidOpi($opi)) {
    $logger->warning("Invalid OPI: $opi");
    header("Location: index.php?error=invalid_opi");
    exit;
}

if (!Validator::isValidDifficulty($difficulty)) {
    $logger->warning("Invalid difficulty: $difficulty");
    header("Location: index.php?error=invalid_difficulty");
    exit;
}

// ユーザーセッション取得
$userId = $_SESSION['user_id'] ?? generateUserId();
$_SESSION['user_id'] = $userId;

// セッション統計初期化
if (!isset($_SESSION['total_exp'])) $_SESSION['total_exp'] = 0;
if (!isset($_SESSION['action_history'])) $_SESSION['action_history'] = [];
if (!isset($_SESSION['combo_streak'])) $_SESSION['combo_streak'] = 0;
if (!isset($_SESSION['max_combo'])) $_SESSION['max_combo'] = 0;
if (!isset($_SESSION['titles_earned'])) $_SESSION['titles_earned'] = [];

$logger->info("Processing result for user: $userId, choice: $choice, OPI: $opi");

// ===========================
// 行動データ定義
// ===========================
$actionData = [
    // 在宅行動
    'stay_tea' => ['name' => 'お茶を淹れる', 'icon' => '🍵', 'base_exp' => 30, 'tags' => ['relax', 'warm']],
    'stay_nap' => ['name' => '15分仮眠', 'icon' => '😴', 'base_exp' => 40, 'tags' => ['relax', 'refresh']],
    'stay_music' => ['name' => '音楽を聴く', 'icon' => '🎵', 'base_exp' => 25, 'tags' => ['mood', 'relax']],
    'stay_breath' => ['name' => '深呼吸5回', 'icon' => '🌬️', 'base_exp' => 20, 'tags' => ['mind', 'calm']],
    'stay_stretch' => ['name' => 'ストレッチ', 'icon' => '🤸', 'base_exp' => 35, 'tags' => ['body', 'refresh']],
    'stay_window' => ['name' => '窓を開ける', 'icon' => '🪟', 'base_exp' => 15, 'tags' => ['refresh', 'mood']],
    'stay_read' => ['name' => '本を読む', 'icon' => '📖', 'base_exp' => 50, 'tags' => ['mind', 'calm']],
    'stay_pet' => ['name' => 'ぬいぐるみを抱く', 'icon' => '🧸', 'base_exp' => 45, 'tags' => ['warm', 'nostalgic']],
    'stay_cook' => ['name' => '簡単な料理', 'icon' => '🍳', 'base_exp' => 60, 'tags' => ['fun', 'creative']],
    'stay_clean' => ['name' => '5分掃除', 'icon' => '🧹', 'base_exp' => 40, 'tags' => ['refresh', 'mood']],
    'stay_game' => ['name' => 'ゲーム', 'icon' => '🎮', 'base_exp' => 35, 'tags' => ['fun', 'mood']],
    'stay_write' => ['name' => '日記を書く', 'icon' => '✍️', 'base_exp' => 55, 'tags' => ['mind', 'expression']],
    
    // 微外出
    'out_walk' => ['name' => '近所を散歩', 'icon' => '🚶', 'base_exp' => 80, 'tags' => ['body', 'refresh'], 'risk' => true],
    'out_convenience' => ['name' => 'コンビニ', 'icon' => '🏪', 'base_exp' => 70, 'tags' => ['refresh'], 'risk' => true],
    'out_vending' => ['name' => '自販機', 'icon' => '🥤', 'base_exp' => 60, 'tags' => ['refresh'], 'risk' => true],
    'out_mailbox' => ['name' => '郵便ポスト', 'icon' => '📬', 'base_exp' => 50, 'tags' => ['refresh'], 'risk' => true],
    'out_park' => ['name' => '公園のベンチ', 'icon' => '🏞️', 'base_exp' => 90, 'tags' => ['body', 'refresh'], 'risk' => true],
];

// 選択肢データ取得
$action = $actionData[$choice] ?? ['name' => '不明な行動', 'icon' => '❓', 'base_exp' => 10, 'tags' => []];
$isOutAction = isset($action['risk']) && $action['risk'];

// ===========================
// 経験値計算システム
// ===========================
$baseExp = $action['base_exp'];
$bonuses = [];
$totalBonus = 0;

// 1. 難易度ボーナス
$difficultyBonus = [
    'hell' => 2.5,
    'warning' => 2.0,
    'calm' => 1.5,
    'peace' => 1.0,
];
$difficultyMultiplier = $difficultyBonus[$difficulty] ?? 1.0;
$bonuses['難易度'] = ['multiplier' => $difficultyMultiplier, 'value' => ($baseExp * $difficultyMultiplier) - $baseExp];

// 2. 時間帯ボーナス
$timeOfDay = getTimeOfDay();
$timeBonus = 0;
switch ($timeOfDay) {
    case 'night':
        $timeBonus = 20;
        $bonuses['深夜'] = ['value' => 20, 'label' => '夜更かし'];
        break;
    case 'morning':
        $timeBonus = 15;
        $bonuses['朝活'] = ['value' => 15, 'label' => '早起き'];
        break;
    case 'evening':
        $timeBonus = 10;
        $bonuses['夕方'] = ['value' => 10, 'label' => '黄昏'];
        break;
}

// 3. 外出リスクボーナス（OPIが高い時の外出）
if ($isOutAction && $opi >= OPI_THRESHOLD_HELL) {
    $riskBonus = 100;
    $bonuses['勇気'] = ['value' => 100, 'label' => '高OPI外出'];
    $totalBonus += $riskBonus;
} elseif ($isOutAction && $opi >= OPI_THRESHOLD_WARNING) {
    $riskBonus = 50;
    $bonuses['勇気'] = ['value' => 50, 'label' => '中OPI外出'];
    $totalBonus += $riskBonus;
}

// 4. 連続行動ボーナス（コンボ）
$_SESSION['action_history'][] = $choice;
$_SESSION['action_history'] = array_slice($_SESSION['action_history'], -10); // 最新10件保持

$comboDetected = false;
$comboBonus = 0;
$comboType = '';

// コンボパターン定義
$comboPatterns = [
    ['pattern' => ['stay_tea', 'stay_music', 'stay_breath'], 'name' => 'リラックス3連鎖', 'bonus' => 50],
    ['pattern' => ['stay_stretch', 'stay_walk', 'stay_breath'], 'name' => '健康3連鎖', 'bonus' => 60],
    ['pattern' => ['stay_read', 'stay_tea', 'stay_write'], 'name' => '文化人3連鎖', 'bonus' => 70],
    ['pattern' => ['stay_clean', 'stay_cook', 'stay_stretch'], 'name' => '生活改善3連鎖', 'bonus' => 65],
];

// コンボ検出（最新3件）
$recentActions = array_slice($_SESSION['action_history'], -3);
if (count($recentActions) === 3) {
    foreach ($comboPatterns as $combo) {
        if ($recentActions === $combo['pattern']) {
            $comboDetected = true;
            $comboBonus = $combo['bonus'];
            $comboType = $combo['name'];
            $bonuses['コンボ'] = ['value' => $comboBonus, 'label' => $comboType];
            $_SESSION['combo_streak']++;
            $_SESSION['max_combo'] = max($_SESSION['max_combo'], $_SESSION['combo_streak']);
            break;
        }
    }
}

if (!$comboDetected && $_SESSION['combo_streak'] > 0) {
    $_SESSION['combo_streak'] = 0;
}

// 5. 連続在宅ボーナス
$stayStreak = 0;
foreach (array_reverse($_SESSION['action_history']) as $pastAction) {
    if (strpos($pastAction, 'stay_') === 0) {
        $stayStreak++;
    } else {
        break;
    }
}

if ($stayStreak >= 5) {
    $streakBonus = $stayStreak * 5;
    $bonuses['在宅連続'] = ['value' => $streakBonus, 'label' => "{$stayStreak}回連続"];
    $totalBonus += $streakBonus;
}

// 6. ランダムボーナス（10%確率）
if (rand(1, 100) <= 10) {
    $luckyBonus = rand(20, 50);
    $bonuses['ラッキー'] = ['value' => $luckyBonus, 'label' => '運が良い'];
    $totalBonus += $luckyBonus;
}

// 総経験値計算
$baseExpWithDifficulty = $baseExp * $difficultyMultiplier;
$totalExp = $baseExpWithDifficulty + $timeBonus + $comboBonus + $totalBonus;
$totalExp = (int)$totalExp;

// セッション更新
$_SESSION['total_exp'] += $totalExp;
$_SESSION['last_action'] = $choice;
$_SESSION['last_exp'] = $totalExp;

$logger->info("EXP Calculation: base={$baseExp}, difficulty_multi={$difficultyMultiplier}, total={$totalExp}");

// ===========================
// 称号システム
// ===========================
$newTitle = null;
$titles = [
    'first_stay' => ['condition' => 'stay_count_1', 'name' => '初めての在宅', 'icon' => '🏠'],
    'tea_master' => ['condition' => 'stay_tea_5', 'name' => '茶道初段', 'icon' => '🍵'],
    'nap_king' => ['condition' => 'stay_nap_10', 'name' => '昼寝の達人', 'icon' => '😴'],
    'combo_beginner' => ['condition' => 'combo_3', 'name' => 'コンボ初心者', 'icon' => '🔗'],
    'combo_master' => ['condition' => 'combo_10', 'name' => 'コンボマスター', 'icon' => '⚡'],
    'exp_1000' => ['condition' => 'total_exp_1000', 'name' => '経験値1K達成', 'icon' => '🌟'],
    'exp_5000' => ['condition' => 'total_exp_5000', 'name' => '経験値5K達成', 'icon' => '💫'],
    'night_owl' => ['condition' => 'night_action_5', 'name' => '夜更かし族', 'icon' => '🌙'],
    'early_bird' => ['condition' => 'morning_action_5', 'name' => '早起き族', 'icon' => '🌅'],
    'hermit' => ['condition' => 'stay_streak_20', 'name' => '孤高の引きこもり', 'icon' => '🏔️'],
];

// 称号チェック
if ($_SESSION['total_exp'] >= 1000 && !in_array('exp_1000', $_SESSION['titles_earned'])) {
    $newTitle = $titles['exp_1000'];
    $_SESSION['titles_earned'][] = 'exp_1000';
}
if ($_SESSION['total_exp'] >= 5000 && !in_array('exp_5000', $_SESSION['titles_earned'])) {
    $newTitle = $titles['exp_5000'];
    $_SESSION['titles_earned'][] = 'exp_5000';
}
if ($_SESSION['combo_streak'] >= 3 && !in_array('combo_beginner', $_SESSION['titles_earned'])) {
    $newTitle = $titles['combo_beginner'];
    $_SESSION['titles_earned'][] = 'combo_beginner';
}
if ($_SESSION['combo_streak'] >= 10 && !in_array('combo_master', $_SESSION['titles_earned'])) {
    $newTitle = $titles['combo_master'];
    $_SESSION['titles_earned'][] = 'combo_master';
}

// ===========================
// 結果メッセージ生成
// ===========================
$resultMessages = [
    'hell' => [
        "地獄級の状況で在宅を選んだあなた…最高にクールです！",
        "まさに孤独優勝！この判断力、素晴らしい！",
        "外出圧力に屈しないメンタル、尊敬します！",
    ],
    'warning' => [
        "警戒レベルの中、賢明な判断でした！",
        "この状況でこの選択…さすがです！",
        "リスクを回避する判断力、見事！",
    ],
    'calm' => [
        "穏やかな時間を上手に使いましたね！",
        "自分を大切にする選択、素晴らしい！",
        "心地よい時間を過ごせたようですね！",
    ],
    'peace' => [
        "平和な時間を満喫できましたね！",
        "心穏やかに過ごせたようで何よりです！",
        "リラックスできた時間、最高ですね！",
    ],
];

$resultMessage = $resultMessages[$difficulty][array_rand($resultMessages[$difficulty])];

if ($isOutAction) {
    $resultMessage = "外に出る勇気、素晴らしい！ただし、無理は禁物ですよ！";
}

if ($comboDetected) {
    $resultMessage = "【{$comboType}達成！】" . $resultMessage;
}

$logger->info("Result: user={$userId}, exp={$totalExp}, total_exp={$_SESSION['total_exp']}, combo={$_SESSION['combo_streak']}");
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>結果 - 孤独優勝クエスト</title>
    <meta name="description" content="あなたの選択の結果発表！経験値・称号・コンボをチェック！">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* アニメーション追加スタイル */
        .result-container {
            animation: slideInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .exp-counter {
            font-size: 4rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.3s both;
        }
        
        @keyframes popIn {
            from {
                opacity: 0;
                transform: scale(0.3);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .bonus-item {
            animation: fadeInLeft 0.4s ease-out both;
        }
        
        .bonus-item:nth-child(1) { animation-delay: 0.5s; }
        .bonus-item:nth-child(2) { animation-delay: 0.6s; }
        .bonus-item:nth-child(3) { animation-delay: 0.7s; }
        .bonus-item:nth-child(4) { animation-delay: 0.8s; }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .title-badge {
            display: inline-block;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 50px;
            color: white;
            font-weight: 700;
            font-size: 1.5rem;
            box-shadow: 0 10px 40px rgba(240, 147, 251, 0.4);
            animation: bounce 1s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container result-container">
        <div class="card">
            <!-- タイトル -->
            <h1 class="gradient-text mb-4">🎉 結果発表</h1>
            
            <!-- 選択した行動 -->
            <div class="result-action mb-4">
                <div style="font-size: 4rem; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($action['icon']); ?>
                </div>
                <h2 style="font-size: 2rem; font-weight: 700;">
                    <?php echo htmlspecialchars($action['name']); ?>
                </h2>
            </div>
            
            <!-- 結果メッセージ -->
            <div class="message-box success mb-4">
                <p style="font-size: 1.2rem; margin: 0;">
                    <?php echo htmlspecialchars($resultMessage); ?>
                </p>
            </div>
            
            <!-- 経験値表示 -->
            <div class="text-center mb-4">
                <div style="font-size: 1.2rem; color: rgba(255,255,255,0.7); margin-bottom: 1rem;">
                    獲得経験値
                </div>
                <div class="exp-counter">
                    +<?php echo number_format($totalExp); ?> XP
                </div>
            </div>
            
            <!-- ボーナス詳細 -->
            <?php if (count($bonuses) > 0): ?>
            <div class="bonus-list mb-4">
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">💰 ボーナス詳細</h3>
                <?php foreach ($bonuses as $bonusName => $bonus): ?>
                <div class="bonus-item" style="display: flex; justify-content: space-between; padding: 0.75rem; background: rgba(255,255,255,0.05); border-radius: 8px; margin-bottom: 0.5rem;">
                    <span>
                        <?php echo htmlspecialchars($bonusName); ?>
                        <?php if (isset($bonus['label'])): ?>
                            <span style="color: rgba(255,255,255,0.6); font-size: 0.9rem;">
                                (<?php echo htmlspecialchars($bonus['label']); ?>)
                            </span>
                        <?php endif; ?>
                    </span>
                    <span style="color: #4ade80; font-weight: 700;">
                        <?php 
                        if (isset($bonus['multiplier'])) {
                            echo 'x' . $bonus['multiplier'];
                        } else {
                            echo '+' . number_format($bonus['value']);
                        }
                        ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- 新称号獲得 -->
            <?php if ($newTitle): ?>
            <div class="text-center mb-4" style="animation: bounceIn 0.8s;">
                <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">🏆 新しい称号を獲得！</h3>
                <div class="title-badge">
                    <?php echo htmlspecialchars($newTitle['icon'] . ' ' . $newTitle['name']); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- 統計情報 -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div style="font-size: 0.9rem; color: rgba(255,255,255,0.6); margin-bottom: 0.5rem;">
                        総経験値
                    </div>
                    <div class="stat-value">
                        <?php echo number_format($_SESSION['total_exp']); ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div style="font-size: 0.9rem; color: rgba(255,255,255,0.6); margin-bottom: 0.5rem;">
                        現在コンボ
                    </div>
                    <div class="stat-value">
                        <?php echo $_SESSION['combo_streak']; ?>連鎖
                    </div>
                </div>
                
                <div class="stat-card">
                    <div style="font-size: 0.9rem; color: rgba(255,255,255,0.6); margin-bottom: 0.5rem;">
                        最大コンボ
                    </div>
                    <div class="stat-value">
                        <?php echo $_SESSION['max_combo']; ?>連鎖
                    </div>
                </div>
                
                <div class="stat-card">
                    <div style="font-size: 0.9rem; color: rgba(255,255,255,0.6); margin-bottom: 0.5rem;">
                        獲得称号数
                    </div>
                    <div class="stat-value">
                        <?php echo count($_SESSION['titles_earned']); ?>個
                    </div>
                </div>
            </div>
            
            <!-- 次のアクション -->
            <div class="mt-5 text-center">
                <a href="index.php" class="btn btn-primary btn-lg">
                    🏠 続けてプレイ
                </a>
            </div>
            
            <!-- デバッグ情報（開発環境のみ） -->
            <?php if (APP_ENV === 'development'): ?>
            <details class="mt-4" style="font-size: 0.8rem; color: rgba(255,255,255,0.5);">
                <summary>Debug Info</summary>
                <pre style="background: rgba(0,0,0,0.3); padding: 1rem; border-radius: 8px; overflow-x: auto; margin-top: 1rem;">
User ID: <?php echo $userId; ?>

Choice: <?php echo $choice; ?>

OPI: <?php echo $opi; ?>

Difficulty: <?php echo $difficulty; ?>

Base EXP: <?php echo $baseExp; ?>

Total EXP: <?php echo $totalExp; ?>

Combo Streak: <?php echo $_SESSION['combo_streak']; ?>

Action History: <?php echo implode(', ', $_SESSION['action_history']); ?>

Bonuses: <?php echo json_encode($bonuses, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?>
                </pre>
            </details>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // カウントアップアニメーション
        document.addEventListener('DOMContentLoaded', () => {
            const expCounter = document.querySelector('.exp-counter');
            if (expCounter) {
                const targetExp = <?php echo $totalExp; ?>;
                let currentExp = 0;
                const duration = 1500; // 1.5秒
                const stepTime = 20;
                const steps = duration / stepTime;
                const increment = targetExp / steps;
                
                const timer = setInterval(() => {
                    currentExp += increment;
                    if (currentExp >= targetExp) {
                        currentExp = targetExp;
                        clearInterval(timer);
                    }
                    expCounter.textContent = '+' + Math.floor(currentExp).toLocaleString() + ' XP';
                }, stepTime);
            }
        });
    </script>
</body>
</html>
