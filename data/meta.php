<?php
/**
 * メタデータ・定数定義
 */

// プロジェクト情報
define('PROJECT_NAME', '孤独優勝クエスト');
define('PROJECT_TAGLINE', 'Stay Home is a Win');
define('PROJECT_VERSION', '1.0.0');

// OPI閾値
define('OPI_HELL', 80);      // 修羅場
define('OPI_WARNING', 50);   // 警戒
define('OPI_CALM', 30);      // 微風
define('OPI_PEACE', 0);      // 静寂

// 経験値設定
define('BASE_EXP_STAY', 100);
define('BASE_EXP_OUT', 80);
define('EXP_MULTIPLIER', 0.5);

// API設定
define('API_TIMEOUT', 5);
define('API_RETRY_COUNT', 2);

// コンボ設定（将来の拡張用）
$COMBO_PATTERNS = [
    ['tea', 'music', 'breath'] => [
        'title' => '三位一体の安らぎ',
        'bonus_exp' => 50,
        'message' => '湯と音と呼吸。完璧なコンボ。'
    ],
    ['stretch', 'breath', 'tea'] => [
        'title' => 'セルフケアの達人',
        'bonus_exp' => 50,
        'message' => '身体も心も、丁寧に扱った証。'
    ]
];

// ハッシュタグ
define('HASHTAG_MAIN', '#孤独優勝クエスト');
define('HASHTAG_SUB', '#在宅が正解');

// カラーテーマ（CSS変数と連動）
$COLOR_THEMES = [
    'hell' => [
        'primary' => '#d32f2f',
        'secondary' => '#ff6659',
        'bg' => '#1a0000'
    ],
    'warning' => [
        'primary' => '#f57c00',
        'secondary' => '#ffad42',
        'bg' => '#1a0f00'
    ],
    'calm' => [
        'primary' => '#0288d1',
        'secondary' => '#5eb8ff',
        'bg' => '#001a33'
    ],
    'peace' => [
        'primary' => '#7b1fa2',
        'secondary' => '#ae52d4',
        'bg' => '#1a001a'
    ]
];
