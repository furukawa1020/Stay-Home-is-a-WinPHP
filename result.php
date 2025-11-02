<?php
/**
 * 孤独優勝クエスト - 結果ページ
 */

require_once __DIR__ . '/data/dialogue.php';
require_once __DIR__ . '/data/meta.php';

// POST データ取得
$opi = isset($_POST['opi']) ? (int)$_POST['opi'] : rand(18, 96);
$choice = isset($_POST['choice']) ? $_POST['choice'] : 'stay_0';

// 結果生成
$result = resolveChoice($opi, $choice);

// 共有テキスト生成
$shareText = generateShareText($opi, $result);
$shareUrl = urlencode('https://yourdomain.com/'); // 実際のURLに変更
$shareTextEncoded = urlencode($shareText);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>結果 - 孤独優勝クエスト</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="result-page difficulty-<?php echo $result['difficulty']; ?>">
    <div class="container">
        <header class="game-header">
            <h1 class="pixel-text">孤独優勝クエスト</h1>
        </header>

        <main class="result-screen">
            <!-- 称号カード -->
            <div class="title-card" id="titleCard">
                <div class="card-shine"></div>
                <div class="card-content">
                    <div class="result-status"><?php echo htmlspecialchars($result['status']); ?></div>
                    <div class="result-title">
                        <div class="title-label">本日の称号</div>
                        <div class="title-name pixel-text"><?php echo htmlspecialchars($result['title']); ?></div>
                    </div>
                    <div class="result-message">
                        <?php echo nl2br(htmlspecialchars($result['message'])); ?>
                    </div>
                    <div class="exp-gain">
                        <span class="exp-label">経験値</span>
                        <span class="exp-value">+<?php echo $result['exp']; ?> XP</span>
                    </div>
                    <div class="opi-result">
                        <span>OPI: <?php echo $opi; ?></span>
                    </div>
                </div>
            </div>

            <!-- 決めゼリフ -->
            <div class="final-message">
                <p class="catchphrase pixel-text"><?php echo htmlspecialchars($result['catchphrase']); ?></p>
            </div>

            <!-- アクションボタン -->
            <div class="action-buttons">
                <button onclick="shareToTwitter()" class="action-btn share-btn">
                    <span>🐦 Xで共有（任意）</span>
                </button>
                <button onclick="copyToClipboard()" class="action-btn copy-btn">
                    <span>📋 テキストコピー</span>
                </button>
                <a href="index.php" class="action-btn restart-btn">
                    <span>🔄 もう一度</span>
                </a>
            </div>

            <!-- 共有用テキスト（非表示） -->
            <textarea id="shareText" style="display: none;"><?php echo htmlspecialchars($shareText); ?></textarea>
        </main>
    </div>

    <script>
        const shareText = <?php echo json_encode($shareText); ?>;
        const shareUrl = <?php echo json_encode($shareUrl); ?>;
        
        function shareToTwitter() {
            const twitterUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}&url=${shareUrl}`;
            window.open(twitterUrl, '_blank', 'width=550,height=420');
        }

        function copyToClipboard() {
            const textarea = document.getElementById('shareText');
            textarea.style.display = 'block';
            textarea.select();
            document.execCommand('copy');
            textarea.style.display = 'none';
            
            alert('✅ テキストをコピーしました！');
        }

        // カードアニメーション
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                document.querySelector('.title-card').classList.add('appear');
            }, 300);
        });
    </script>
</body>
</html>
