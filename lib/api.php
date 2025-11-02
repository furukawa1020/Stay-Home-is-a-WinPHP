<?php
/**
 * API通信・OPI取得
 * リトライ機能・タイムアウト・エラーハンドリング完備
 */

/**
 * OPI取得（リトライ機能付き）
 * @param int $maxRetries 最大リトライ回数
 * @return array ['success' => bool, 'opi' => int, 'source' => string, 'timestamp' => int, 'error' => string]
 */
function fetchOpiWithRetry($maxRetries = API_RETRY_COUNT) {
    $apiUrl = getApiUrl();
    
    if (empty($apiUrl)) {
        return [
            'success' => false,
            'opi' => generateSmartFallbackOpi(),
            'source' => 'no_api',
            'timestamp' => time(),
            'error' => 'API URL not configured'
        ];
    }
    
    $lastError = null;
    
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            $result = fetchOpiFromApi($apiUrl);
            
            if ($result['success']) {
                $result['attempt'] = $attempt;
                return $result;
            }
            
            $lastError = $result['error'];
            
            // リトライ前に待機
            if ($attempt < $maxRetries) {
                usleep(API_RETRY_DELAY * 1000 * $attempt); // 指数バックオフ
            }
            
        } catch (Exception $e) {
            $lastError = $e->getMessage();
        }
    }
    
    // 全リトライ失敗
    return [
        'success' => false,
        'opi' => generateSmartFallbackOpi(),
        'source' => 'fallback_after_retry',
        'timestamp' => time(),
        'error' => $lastError ?? 'Unknown error',
        'attempts' => $maxRetries
    ];
}

/**
 * APIからOPIを取得
 */
function fetchOpiFromApi($apiUrl) {
    $startTime = microtime(true);
    
    // HTTPコンテキスト設定
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => API_TIMEOUT,
            'ignore_errors' => true,
            'header' => buildApiHeaders()
        ]
    ]);
    
    // API リクエスト
    $response = @file_get_contents($apiUrl, false, $context);
    $responseTime = (microtime(true) - $startTime) * 1000; // ミリ秒
    
    if ($response === false) {
        return [
            'success' => false,
            'opi' => null,
            'source' => 'api_request_failed',
            'timestamp' => time(),
            'error' => 'HTTP request failed',
            'response_time' => $responseTime
        ];
    }
    
    // HTTPステータスコード確認
    $httpCode = getHttpResponseCode($http_response_header ?? []);
    if ($httpCode !== 200) {
        return [
            'success' => false,
            'opi' => null,
            'source' => 'api_http_error',
            'timestamp' => time(),
            'error' => 'HTTP ' . $httpCode,
            'response_time' => $responseTime
        ];
    }
    
    // JSON パース
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'opi' => null,
            'source' => 'api_json_error',
            'timestamp' => time(),
            'error' => 'JSON parse error: ' . json_last_error_msg(),
            'response_time' => $responseTime
        ];
    }
    
    // OPI変換
    $opi = convertToOpi($data);
    
    // バリデーション
    if (!Validator::isValidOpi($opi)) {
        return [
            'success' => false,
            'opi' => null,
            'source' => 'api_invalid_opi',
            'timestamp' => time(),
            'error' => 'Invalid OPI value: ' . $opi,
            'response_time' => $responseTime
        ];
    }
    
    return [
        'success' => true,
        'opi' => $opi,
        'source' => 'api',
        'timestamp' => time(),
        'response_time' => $responseTime,
        'data' => $data // デバッグ用
    ];
}

/**
 * APIエンドポイントURL取得
 */
function getApiUrl() {
    // 環境変数から取得
    if (defined('PEOPLE_FLOW_API_URL') && !empty(PEOPLE_FLOW_API_URL)) {
        return PEOPLE_FLOW_API_URL;
    }
    
    // 設定ファイルから取得（存在する場合）
    $configFile = ROOT_DIR . '/config/api.json';
    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true);
        if (isset($config['people_flow_api_url'])) {
            return $config['people_flow_api_url'];
        }
    }
    
    // デフォルト（未設定）
    return '';
}

/**
 * APIヘッダーを構築
 */
function buildApiHeaders() {
    $headers = [
        'User-Agent: SolitudeVictoryQuest/' . APP_VERSION,
        'Accept: application/json',
        'Accept-Language: ja,en',
        'Cache-Control: no-cache'
    ];
    
    // APIキーがあれば追加
    if (defined('PEOPLE_FLOW_API_KEY') && !empty(PEOPLE_FLOW_API_KEY)) {
        $headers[] = 'Authorization: Bearer ' . PEOPLE_FLOW_API_KEY;
    }
    
    return implode("\r\n", $headers);
}

/**
 * HTTPレスポンスコードを取得
 */
function getHttpResponseCode(array $headers) {
    foreach ($headers as $header) {
        if (preg_match('/^HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
            return (int)$matches[1];
        }
    }
    return 0;
}

/**
 * APIデータをOPIに変換（複数フォーマット対応）
 * @param array $data APIレスポンスデータ
 * @return int 0-100のOPI値
 */
function convertToOpi($data) {
    // パターン1: people_percent フィールド
    if (isset($data['people_percent'])) {
        $percent = (float)$data['people_percent'];
        return clamp((int)round($percent), OPI_MIN, OPI_MAX);
    }
    
    // パターン2: congestion_rate フィールド
    if (isset($data['congestion_rate'])) {
        $rate = (float)$data['congestion_rate'];
        return clamp((int)round($rate), OPI_MIN, OPI_MAX);
    }
    
    // パターン3: crowd_level フィールド（5段階評価）
    if (isset($data['crowd_level'])) {
        $level = (int)$data['crowd_level'];
        $opi = clamp($level * 20, OPI_MIN, OPI_MAX);
        return $opi;
    }
    
    // パターン4: density フィールド（密度 0-1）
    if (isset($data['density'])) {
        $density = (float)$data['density'];
        $opi = clamp((int)($density * 100), OPI_MIN, OPI_MAX);
        return $opi;
    }
    
    // パターン5: traffic_index フィールド
    if (isset($data['traffic_index'])) {
        $index = (float)$data['traffic_index'];
        return clamp((int)$index, OPI_MIN, OPI_MAX);
    }
    
    // パターン6: ネストされたデータ
    if (isset($data['data']['congestion'])) {
        $congestion = (float)$data['data']['congestion'];
        return clamp((int)round($congestion), OPI_MIN, OPI_MAX);
    }
    
    // パターン7: 配列の最初の要素
    if (isset($data['results']) && is_array($data['results']) && !empty($data['results'])) {
        return convertToOpi($data['results'][0]);
    }
    
    // フォールバック
    return generateSmartFallbackOpi();
}

/**
 * 値を範囲内に制限
 */
function clamp($value, $min, $max) {
    return max($min, min($max, $value));
}

/**
 * APIデータのサンプル生成（テスト用）
 */
function generateMockApiResponse($opi = null) {
    if ($opi === null) {
        $opi = generateSmartFallbackOpi();
    }
    
    return [
        'timestamp' => date('c'),
        'area' => 'test_area',
        'people_percent' => $opi,
        'congestion_rate' => $opi,
        'crowd_level' => (int)($opi / 20),
        'density' => $opi / 100,
        'traffic_index' => $opi,
        'metadata' => [
            'source' => 'mock',
            'version' => '1.0'
        ]
    ];
}

/**
 * API接続テスト
 */
function testApiConnection() {
    $result = fetchOpiWithRetry(1);
    
    return [
        'success' => $result['success'],
        'opi' => $result['opi'],
        'source' => $result['source'],
        'error' => $result['error'] ?? null,
        'response_time' => $result['response_time'] ?? null
    ];
}
