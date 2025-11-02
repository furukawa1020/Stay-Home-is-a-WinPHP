<?php
/**
 * 孤独優勝クエスト - Stay Home is a Win
 * メインエントリーポイント
 */

require_once __DIR__ . '/lib/api.php';
require_once __DIR__ . '/data/dialogue.php';

// OPI（外出圧力指数）を取得
$opi = fetchOpi();
if ($opi === null) {
    // API失敗時のフォールバック
    $opi = rand(18, 96);
    $isOffline = true;
} else {
    $isOffline = false;
}

// OPIに基づくシーン生成
$scene = generateScene($opi, $isOffline);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>孤独優勝クエスト - Stay Home is a Win</title>
    <link rel="stylesheet" href="style.css">
    <meta name="description" content="外が騒がしいほど、在宅は強い。今日もあなたは、ここで完全勝利。">
    <meta property="og:title" content="孤独優勝クエスト">
    <meta property="og:description" content="外が騒がしいほど、在宅は強い。">
</head>
<body class="difficulty-<?php echo $scene['difficulty']; ?>">
    <div class="container">
        <header class="game-header">
            <h1 class="pixel-text">孤独優勝クエスト</h1>
            <p class="subtitle">Stay Home is a Win</p>
        </header>

        <main class="game-screen">
            <!-- OPI表示 -->
            <div class="opi-display">
                <div class="opi-label">外出圧力指数 (OPI)</div>
                <div class="opi-value"><?php echo $opi; ?></div>
                <div class="opi-bar">
                    <div class="opi-bar-fill" style="width: <?php echo $opi; ?>%"></div>
                </div>
            </div>

            <!-- シーンメッセージ -->
            <div class="scene-message" id="sceneMessage">
                <div class="message-box typing-animation">
                    <?php echo nl2br(htmlspecialchars($scene['message'])); ?>
                </div>
                <?php if ($isOffline): ?>
                <div class="offline-notice">
                    <?php echo htmlspecialchars($scene['offline_message']); ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- 選択肢 -->
            <form action="result.php" method="POST" class="choice-form">
                <input type="hidden" name="opi" value="<?php echo $opi; ?>">
                
                <div class="choices">
                    <div class="choice-section">
                        <h3 class="choice-title">🏠 在宅行動</h3>
                        <?php foreach ($scene['stay_choices'] as $key => $choice): ?>
                        <button type="submit" name="choice" value="stay_<?php echo $key; ?>" class="choice-btn stay-choice">
                            <span class="choice-icon"><?php echo $choice['icon']; ?></span>
                            <span class="choice-text"><?php echo htmlspecialchars($choice['text']); ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!empty($scene['out_choices'])): ?>
                    <div class="choice-section">
                        <h3 class="choice-title">🚶 微外出（任意）</h3>
                        <?php foreach ($scene['out_choices'] as $key => $choice): ?>
                        <button type="submit" name="choice" value="out_<?php echo $key; ?>" class="choice-btn out-choice">
                            <span class="choice-icon"><?php echo $choice['icon']; ?></span>
                            <span class="choice-text"><?php echo htmlspecialchars($choice['text']); ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </form>
        </main>

        <footer class="game-footer">
            <p>※ どの選択も自己肯定が上がる設計です</p>
        </footer>
    </div>

    <script src="script.js"></script>
</body>
</html>
