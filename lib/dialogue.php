<?php
/**
 * 孤独優勝クエスト - Dialogue Generator
 * セリフ・メッセージ生成ライブラリ
 * 
 * @version 1.0.0
 */

/**
 * 結果メッセージを生成
 * 
 * @param array $data 結果データ
 * @return string メッセージ
 */
function generateResultMessage($data) {
    $exp = $data['gained_exp'] ?? 0;
    $combo = $data['combo'] ?? null;
    
    $messages = [];
    
    // 経験値メッセージ
    if ($exp >= 200) {
        $messages[] = "素晴らしい選択です！";
    } elseif ($exp >= 150) {
        $messages[] = "良い判断でした！";
    } elseif ($exp >= 100) {
        $messages[] = "順調に成長しています。";
    } else {
        $messages[] = "着実に進んでいます。";
    }
    
    // コンボメッセージ
    if ($combo) {
        $messages[] = "コンボ継続中：" . $combo['name'];
    }
    
    return implode(' ', $messages);
}

/**
 * 励ましメッセージを生成
 * 
 * @param int $totalExp 総経験値
 * @return string メッセージ
 */
function generateEncouragementMessage($totalExp) {
    if ($totalExp < 500) {
        return "良いスタートです！引き続き自分のペースで。";
    } elseif ($totalExp < 1500) {
        return "順調に成長していますね！";
    } elseif ($totalExp < 3000) {
        return "素晴らしい！あなたのスタイルが確立されています。";
    } elseif ($totalExp < 5000) {
        return "見事です！真の引きこもりマスター！";
    } else {
        return "伝説の域に達しています！完璧です！";
    }
}

/**
 * 行動選択肢のセリフを生成
 * 
 * @param array $choice 選択肢データ
 * @return string セリフ
 */
function generateChoiceDialogue($choice) {
    $dialogues = [
        'stay_game' => "ゲームの世界に没頭する...",
        'stay_anime' => "アニメ視聴でリラックス...",
        'stay_manga' => "マンガの世界へ...",
        'stay_sns' => "SNSで情報収集...",
        'stay_music' => "音楽鑑賞でリフレッシュ...",
        'stay_study' => "自己学習で成長...",
        'stay_create' => "創作活動に集中...",
        'stay_chat' => "オンライン交流...",
        'stay_read' => "読書で知識を深める...",
        'stay_cook' => "料理で自己表現...",
        'stay_clean' => "部屋を整える...",
        'stay_sleep' => "十分な休息を...",
        'out_walk' => "軽い散歩に...",
        'out_shop' => "必要な買い物を...",
        'out_cafe' => "カフェでブレイク...",
        'out_work' => "外での活動を...",
        'out_meet' => "人と会う...",
    ];
    
    $type = $choice['type'] ?? '';
    return $dialogues[$type] ?? "行動する...";
}
