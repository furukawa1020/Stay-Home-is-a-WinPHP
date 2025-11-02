<?php
/**
 * ロガークラス
 * PSR-3互換のシンプルなロギングシステム
 */

class Logger {
    private $logFile;
    private $logLevel;
    private $levels = [
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'CRITICAL' => 4
    ];
    
    public function __construct($logFile, $logLevel = 'INFO') {
        $this->logFile = $logFile;
        $this->logLevel = $logLevel;
        
        // ログディレクトリ作成
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        // ログローテーション
        $this->rotateLog();
    }
    
    /**
     * DEBUGレベルログ
     */
    public function debug($message, array $context = []) {
        $this->log('DEBUG', $message, $context);
    }
    
    /**
     * INFOレベルログ
     */
    public function info($message, array $context = []) {
        $this->log('INFO', $message, $context);
    }
    
    /**
     * WARNINGレベルログ
     */
    public function warning($message, array $context = []) {
        $this->log('WARNING', $message, $context);
    }
    
    /**
     * ERRORレベルログ
     */
    public function error($message, array $context = []) {
        $this->log('ERROR', $message, $context);
    }
    
    /**
     * CRITICALレベルログ
     */
    public function critical($message, array $context = []) {
        $this->log('CRITICAL', $message, $context);
    }
    
    /**
     * ログ出力
     */
    private function log($level, $message, array $context = []) {
        if (!LOG_ENABLED) return;
        
        // レベルチェック
        if ($this->levels[$level] < $this->levels[$this->logLevel]) {
            return;
        }
        
        // ログフォーマット
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logLine = sprintf("[%s] [%s] %s%s\n", $timestamp, $level, $message, $contextStr);
        
        // ファイル書き込み
        @file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * ログローテーション
     */
    private function rotateLog() {
        if (!file_exists($this->logFile)) return;
        
        $fileSize = filesize($this->logFile);
        if ($fileSize < LOG_MAX_SIZE) return;
        
        // バックアップ作成
        $timestamp = date('YmdHis');
        $backupFile = $this->logFile . '.' . $timestamp;
        @rename($this->logFile, $backupFile);
        
        // 古いバックアップを削除
        $this->cleanOldLogs();
    }
    
    /**
     * 古いログファイルを削除
     */
    private function cleanOldLogs() {
        $logDir = dirname($this->logFile);
        $logBasename = basename($this->logFile);
        
        $files = glob($logDir . '/' . $logBasename . '.*');
        if (count($files) <= LOG_MAX_FILES) return;
        
        // 作成日時でソート
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        // 古いものから削除
        $toDelete = count($files) - LOG_MAX_FILES;
        for ($i = 0; $i < $toDelete; $i++) {
            @unlink($files[$i]);
        }
    }
}
