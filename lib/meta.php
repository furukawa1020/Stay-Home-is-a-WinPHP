<?php
/**
 * 孤独優勝クエスト - Meta Data Generator
 * メタデータ・OGP生成ライブラリ
 * 
 * @version 1.0.0
 */

/**
 * OGP用タイトルを生成
 * 
 * @param int $opi OPI値
 * @return string タイトル
 */
function generateOgTitle($opi) {
    return "孤独優勝クエスト | 外出圧力指数: {$opi}";
}

/**
 * OGP用説明文を生成
 * 
 * @param int $opi OPI値
 * @param string $scene シーン説明
 * @return string 説明文
 */
function generateOgDescription($opi, $scene = '') {
    if ($opi >= 80) {
        $situation = "外は大混雑！今日は完全に引きこもり勝利の日です。";
    } elseif ($opi >= 60) {
        $situation = "外出圧力が高まっています。在宅こそ最高の選択。";
    } elseif ($opi >= 40) {
        $situation = "ほどほどの人出。あなたの選択が勝利を決めます。";
    } elseif ($opi >= 20) {
        $situation = "比較的静か。でも在宅は常に正解です。";
    } else {
        $situation = "外は閑散。それでも家が一番快適です。";
    }
    
    return $situation . " あなたの「孤独」を肯定する物語が始まります。";
}

/**
 * ページタイトルを生成
 * 
 * @param string $page ページ種別
 * @param array $data 追加データ
 * @return string タイトル
 */
function generatePageTitle($page = 'index', $data = []) {
    switch ($page) {
        case 'result':
            $exp = $data['exp'] ?? 0;
            return "結果: +{$exp}EXP | 孤独優勝クエスト";
        case 'about':
            return "このゲームについて | 孤独優勝クエスト";
        default:
            return "孤独優勝クエスト - Stay Home is a Win";
    }
}

/**
 * メタキーワードを生成
 * 
 * @param string $scene シーン
 * @return string キーワード（カンマ区切り）
 */
function generateMetaKeywords($scene = '') {
    $base = "孤独,在宅,引きこもり,人流データ,OPI,外出圧力指数,メンタルヘルス,自己肯定感";
    
    // シーンに応じたキーワード追加
    if (stripos($scene, 'ゲーム') !== false) {
        $base .= ",ゲーム,オンライン";
    }
    if (stripos($scene, 'アニメ') !== false) {
        $base .= ",アニメ,視聴";
    }
    if (stripos($scene, '散歩') !== false) {
        $base .= ",散歩,外出";
    }
    
    return $base;
}

/**
 * JSON-LD構造化データを生成
 * 
 * @param array $data ページデータ
 * @return string JSON-LD文字列
 */
function generateJsonLd($data = []) {
    $jsonLd = [
        "@context" => "https://schema.org",
        "@type" => "WebApplication",
        "name" => "孤独優勝クエスト",
        "description" => "外が騒がしいほど、在宅は強い。リアルタイム人流データで「孤独」を肯定する体験型ゲーム。",
        "url" => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
        "applicationCategory" => "Game",
        "genre" => "Interactive Fiction",
        "inLanguage" => "ja",
        "author" => [
            "@type" => "Organization",
            "name" => "Solitude Victory Project"
        ],
        "datePublished" => "2025-01-01",
        "keywords" => "孤独,在宅,引きこもり,人流データ,OPI,メンタルヘルス"
    ];
    
    return json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}
