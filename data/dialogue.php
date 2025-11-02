<?php
/**
 * セリフ辞書（ネタ全開・尖り文言）
 */

/**
 * OPIに基づくシーン生成
 */
function generateScene($opi, $isOffline = false) {
    $difficulty = getDifficulty($opi);
    $messages = getMessages($opi, $isOffline);
    $stayChoices = getStayChoices($opi);
    $outChoices = getOutChoices($opi);
    
    return [
        'difficulty' => $difficulty,
        'message' => $messages['main'],
        'offline_message' => $messages['offline'] ?? '',
        'stay_choices' => $stayChoices,
        'out_choices' => $outChoices
    ];
}

/**
 * 難易度判定
 */
function getDifficulty($opi) {
    if ($opi >= 80) return 'hell';      // 修羅場
    if ($opi >= 50) return 'warning';   // 警戒
    if ($opi >= 30) return 'calm';      // 微風
    return 'peace';                     // 静寂
}

/**
 * メッセージ取得（ネタ辞書）
 */
function getMessages($opi, $isOffline) {
    $messages = [
        'hell' => [
            "外界、恋のエンカウント多発。\n今日は部屋がチート。",
            "外界の\"陽\"濃度が危険域。\nお茶を盾にせよ。",
            "今日の勇者はドアノブに触れない。",
            "世界のBGMが大きい日は、\n耳じゃなく**心の音量**を下げる。"
        ],
        'warning' => [
            "\"そこそこ混雑\"が一番メンタルに効く。\n布団を固めよ。",
            "社会が『来いよ』って言ってくる。\nやだ（即答）。",
            "そこそこ出やすい日は、\nそこそこ後悔しがち。",
            "あなたの部屋は、あなたの王国。\n王は外に並ばない。"
        ],
        'calm' => [
            "街のBGMが小さい。\n窓開けて勝ち。",
            "出ない勇気は、出る勇気よりもレア。",
            "外ガラガラ。主役になれるが、\n主役しない自由もある。"
        ],
        'peace' => [
            "世界がスリープ。\n在宅は完全勝利。",
            "静けさは才能。\nあなたは今、世界最高のスタジアム（自室）にいる。",
            "\"何もしない\"は負けじゃない。\n**回復コマンド**だ。"
        ]
    ];
    
    $offlineMessages = [
        "電波の向こうも孤独。\n今日は**在宅SSランク**。",
        "現実の整合性が崩壊。\nメタ的に言うと**寝よ**。",
        "焦らなくていい。\n孤独は待ってくれる。"
    ];
    
    $difficulty = getDifficulty($opi);
    $main = $messages[$difficulty][array_rand($messages[$difficulty])];
    $offline = $isOffline ? $offlineMessages[array_rand($offlineMessages)] : '';
    
    return [
        'main' => $main,
        'offline' => $offline
    ];
}

/**
 * 在宅行動の選択肢
 */
function getStayChoices($opi) {
    $allChoices = [
        ['icon' => '☕', 'text' => '湯を沸かす', 'key' => 'tea'],
        ['icon' => '🎵', 'text' => '好きな音を流す', 'key' => 'music'],
        ['icon' => '🧘', 'text' => '5分だけ伸び', 'key' => 'stretch'],
        ['icon' => '📖', 'text' => '積ん読を1ページ', 'key' => 'book'],
        ['icon' => '🌙', 'text' => '深呼吸×3回', 'key' => 'breath'],
        ['icon' => '🎮', 'text' => 'セーブデータを眺める', 'key' => 'game']
    ];
    
    // ランダムに3つ選択
    shuffle($allChoices);
    return array_slice($allChoices, 0, 3);
}

/**
 * 微外出の選択肢（OPI≤49の時のみ）
 */
function getOutChoices($opi) {
    if ($opi > 49) {
        return [];
    }
    
    $choices = [
        ['icon' => '🌟', 'text' => '夜空を1分見る', 'key' => 'sky'],
        ['icon' => '🚪', 'text' => '玄関先で深呼吸', 'key' => 'door'],
        ['icon' => '📮', 'text' => 'ポスト確認', 'key' => 'post']
    ];
    
    return array_slice($choices, 0, 2);
}

/**
 * 選択肢の解決（常に勝利）
 */
function resolveChoice($opi, $choice) {
    $difficulty = getDifficulty($opi);
    $isStay = strpos($choice, 'stay_') === 0;
    
    // 称号取得
    $title = getTitle($opi, $choice);
    
    // 結果メッセージ
    $messages = getResultMessages($opi, $choice);
    
    // 経験値（OPIが高いほど多い）
    $baseExp = $isStay ? 100 : 80;
    $exp = $baseExp + floor($opi * 0.5);
    
    // 決めゼリフ
    $catchphrases = [
        "今日は\"在宅が正解\"。",
        "孤独、優勝。",
        "Stay Home is a Win."
    ];
    
    return [
        'difficulty' => $difficulty,
        'status' => '✨ 完全勝利 ✨',
        'title' => $title,
        'message' => $messages[array_rand($messages)],
        'exp' => $exp,
        'catchphrase' => $catchphrases[array_rand($catchphrases)]
    ];
}

/**
 * 称号取得
 */
function getTitle($opi, $choice) {
    $titles = [
        'hell' => [
            '賢者の部屋着',
            '引きこもりの正当防衛',
            '修羅場回避の達人',
            '在宅チート使用者'
        ],
        'warning' => [
            '窓際の賢王',
            '布団の守護者',
            '静寂の魔術師',
            '部屋活マスター'
        ],
        'calm' => [
            '微風の舵取り人',
            '選択的孤独者',
            '自由の体現者',
            '静かなる勝者'
        ],
        'peace' => [
            '完全勝利者',
            '在宅SSランク',
            '孤独の王',
            '部屋の勇者'
        ]
    ];
    
    $difficulty = getDifficulty($opi);
    return $titles[$difficulty][array_rand($titles[$difficulty])];
}

/**
 * 結果メッセージ
 */
function getResultMessages($opi, $choice) {
    $isStay = strpos($choice, 'stay_') === 0;
    
    if ($isStay) {
        return [
            "部屋という名の無敵城塞。\n今日もあなたは守り抜いた。",
            "外の喧騒を横目に、\nあなたは最適解を選んだ。",
            "湯気が立つ。音が鳴る。\nこの瞬間、あなたは完全に自由だ。",
            "出ない勇気に拍手を。\n誰も褒めないなら、私が褒める。"
        ];
    } else {
        return [
            "微外出、英断。\n戻る場所があるから強い。",
            "1分の外気で十分。\nあなたは自分のペースを知っている。",
            "外の空気も、部屋の静けさも。\n両方があなたのもの。"
        ];
    }
}

/**
 * 共有テキスト生成
 */
function generateShareText($opi, $result) {
    $text = sprintf(
        "OPI:%d。%s\n本日の称号：%s\n#孤独優勝クエスト #在宅が正解",
        $opi,
        $result['catchphrase'],
        $result['title']
    );
    return $text;
}
