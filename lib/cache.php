<?php
/**
 * キャッシュマネージャー
 * ファイルベースのシンプルなキャッシュシステム
 */

class CacheManager {
    private $cacheDir;
    
    public function __construct($cacheDir) {
        $this->cacheDir = $cacheDir;
        
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
    }
    
    /**
     * キャッシュ取得
     */
    public function get($key) {
        if (!CACHE_ENABLED) return null;
        
        $cacheFile = $this->getCacheFile($key);
        
        if (!file_exists($cacheFile)) {
            return null;
        }
        
        $data = @file_get_contents($cacheFile);
        if ($data === false) {
            return null;
        }
        
        $cache = json_decode($data, true);
        if (!$cache || !isset($cache['expires_at']) || !isset($cache['value'])) {
            $this->delete($key);
            return null;
        }
        
        // 有効期限チェック
        if (time() > $cache['expires_at']) {
            $this->delete($key);
            return null;
        }
        
        return $cache['value'];
    }
    
    /**
     * キャッシュ保存
     */
    public function set($key, $value, $ttl = null) {
        if (!CACHE_ENABLED) return false;
        
        if ($ttl === null) {
            $ttl = CACHE_DEFAULT_TTL;
        }
        
        $cacheFile = $this->getCacheFile($key);
        $cacheDir = dirname($cacheFile);
        
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        
        $cache = [
            'key' => $key,
            'value' => $value,
            'created_at' => time(),
            'expires_at' => time() + $ttl
        ];
        
        $data = json_encode($cache, JSON_PRETTY_PRINT);
        return @file_put_contents($cacheFile, $data, LOCK_EX) !== false;
    }
    
    /**
     * キャッシュ削除
     */
    public function delete($key) {
        $cacheFile = $this->getCacheFile($key);
        
        if (file_exists($cacheFile)) {
            return @unlink($cacheFile);
        }
        
        return true;
    }
    
    /**
     * キャッシュクリア
     */
    public function clear() {
        $this->clearDirectory($this->cacheDir);
    }
    
    /**
     * 期限切れキャッシュを削除
     */
    public function gc() {
        $this->gcDirectory($this->cacheDir);
    }
    
    /**
     * キャッシュファイルパスを取得
     */
    private function getCacheFile($key) {
        $hash = md5($key);
        $subdir = substr($hash, 0, 2);
        return $this->cacheDir . '/' . $subdir . '/' . $hash . '.cache';
    }
    
    /**
     * ディレクトリをクリア
     */
    private function clearDirectory($dir) {
        if (!is_dir($dir)) return;
        
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->clearDirectory($file);
                @rmdir($file);
            } else {
                @unlink($file);
            }
        }
    }
    
    /**
     * 期限切れファイルを削除（ガベージコレクション）
     */
    private function gcDirectory($dir) {
        if (!is_dir($dir)) return;
        
        $files = glob($dir . '/*.cache');
        foreach ($files as $file) {
            $data = @file_get_contents($file);
            if ($data === false) continue;
            
            $cache = json_decode($data, true);
            if (!$cache || !isset($cache['expires_at'])) {
                @unlink($file);
                continue;
            }
            
            if (time() > $cache['expires_at']) {
                @unlink($file);
            }
        }
        
        // サブディレクトリも処理
        $subdirs = glob($dir . '/*', GLOB_ONLYDIR);
        foreach ($subdirs as $subdir) {
            $this->gcDirectory($subdir);
        }
    }
}
