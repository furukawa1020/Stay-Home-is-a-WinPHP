<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>孤独優勝クエスト - セットアップ完了</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            max-width: 600px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .status.error {
            background: #fef2f2;
            border-left-color: #ef4444;
        }
        .check-list {
            list-style: none;
            margin: 2rem 0;
        }
        .check-list li {
            padding: 0.75rem;
            margin: 0.5rem 0;
            background: #f8fafc;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .check-list li::before {
            content: "✅";
            font-size: 1.5rem;
        }
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        .info-box {
            background: #f1f5f9;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
        }
        .info-box h3 {
            margin-bottom: 0.5rem;
            color: #334155;
        }
        .info-box p {
            color: #64748b;
            line-height: 1.6;
        }
        code {
            background: #1e293b;
            color: #22c55e;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏠 孤独優勝クエスト</h1>
        <p style="color: #64748b; margin-bottom: 2rem;">Stay Home is a Win.</p>

        <?php
        // 環境チェック
        $checks = [
            'PHP バージョン' => version_compare(PHP_VERSION, '7.4.0', '>='),
            'logs ディレクトリ' => is_dir(__DIR__ . '/logs') && is_writable(__DIR__ . '/logs'),
            'cache ディレクトリ' => is_dir(__DIR__ . '/cache') && is_writable(__DIR__ . '/cache'),
            'lib/config.php' => file_exists(__DIR__ . '/lib/config.php'),
            'index.php' => file_exists(__DIR__ . '/index.php'),
            'result.php' => file_exists(__DIR__ . '/result.php'),
        ];

        $allPassed = !in_array(false, $checks, true);
        ?>

        <?php if ($allPassed): ?>
        <div class="status">
            <span style="font-size: 2rem;">✅</span>
            <div>
                <strong>セットアップ完了！</strong>
                <p style="margin: 0; color: #16a34a;">すべての準備が整いました。</p>
            </div>
        </div>
        <?php else: ?>
        <div class="status error">
            <span style="font-size: 2rem;">⚠️</span>
            <div>
                <strong>セットアップに問題があります</strong>
                <p style="margin: 0; color: #dc2626;">下記のチェックリストを確認してください。</p>
            </div>
        </div>
        <?php endif; ?>

        <ul class="check-list">
            <?php foreach ($checks as $name => $passed): ?>
            <li style="<?php echo $passed ? '' : 'background: #fef2f2; border-left: 3px solid #ef4444;'; ?>">
                <?php if (!$passed): ?>
                <span style="filter: grayscale(1);">❌</span>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($name); ?></span>
                <?php if ($name === 'PHP バージョン'): ?>
                <small style="color: #64748b;">(<?php echo PHP_VERSION; ?>)</small>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="info-box">
            <h3>📋 システム情報</h3>
            <p>
                <strong>PHP:</strong> <?php echo PHP_VERSION; ?><br>
                <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?><br>
                <strong>Document Root:</strong> <code><?php echo __DIR__; ?></code>
            </p>
        </div>

        <?php if ($allPassed): ?>
        <div style="text-align: center; margin-top: 2rem;">
            <a href="index.php" class="btn">🎮 ゲームを開始</a>
        </div>
        <?php else: ?>
        <div class="info-box">
            <h3>🔧 トラブルシューティング</h3>
            <p>
                <strong>ディレクトリが見つからない場合:</strong><br>
                <code>mkdir logs cache</code> を実行してください。
            </p>
            <p style="margin-top: 1rem;">
                <strong>書き込み権限がない場合:</strong><br>
                <code>chmod 777 logs cache</code> (Linux/Mac)<br>
                Windows: エクスプローラーでフォルダのプロパティから設定
            </p>
        </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 2rem; color: #94a3b8; font-size: 0.9rem;">
            <p>Stay Home is a Win. 🏠✨</p>
        </div>
    </div>
</body>
</html>
