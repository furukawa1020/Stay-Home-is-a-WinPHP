<?php
/**
 * API通信・OPI取得
 */

/**
 * OPI（外出圧力指数）を取得
 * @return int|null 0-100のOPI値、失敗時はnull
 */
function fetchOpi() {
    // TODO: 実際のAPIエンドポイントに差し替え
    // 例: 都市人流データのオープンAPI
    $apiUrl = getApiUrl();
    
    if (empty($apiUrl)) {
        return null; // API未設定時はフォールバック
    }
    
    try {
        $data = fetchApiData($apiUrl);
        if ($data === null) {
            return null;
        }
        
        // JSONから人流データを抽出してOPIに変換
        $opi = convertToOpi($data);
        return $opi;
        
    } catch (Exception $e) {
        error_log("API Error: " . $e->getMessage());
        return null;
    }
}

/**
 * APIエンドポイントURL取得
 */
function getApiUrl() {
    // 環境変数または設定ファイルから取得
    // 例: 東京都人流データAPI（架空）
    // return 'https://example.com/api/people-flow?area=tokyo';
    
    // 未設定の場合は空文字（フォールバック動作）
    return '';
}

/**
 * APIデータ取得
 */
function fetchApiData($url) {
    $context = stream_context_create([
        'http' => [
            'timeout' => API_TIMEOUT,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Parse Error: " . json_last_error_msg());
        return null;
    }
    
    return $data;
}

/**
 * APIデータをOPIに変換
 * @param array $data APIレスポンスデータ
 * @return int 0-100のOPI値
 */
function convertToOpi($data) {
    // データ構造に応じて変換ロジックを実装
    // 想定: { "people_percent": 75.5, "area": "shibuya", ... }
    
    if (isset($data['people_percent'])) {
        $percent = (float)$data['people_percent'];
        return max(0, min(100, (int)round($percent)));
    }
    
    // 代替フィールド名に対応
    if (isset($data['congestion_rate'])) {
        $rate = (float)$data['congestion_rate'];
        return max(0, min(100, (int)round($rate)));
    }
    
    if (isset($data['crowd_level'])) {
        // 5段階評価 → 0-100に変換
        $level = (int)$data['crowd_level'];
        return max(0, min(100, $level * 20));
    }
    
    // フォールバック
    return rand(18, 96);
}

/**
 * フォールバックOPI生成
 * API失敗時に使用
 */
function generateFallbackOpi() {
    // 現実的な範囲でランダム生成
    // 極端に低い/高い値は避ける
    return rand(18, 96);
}
