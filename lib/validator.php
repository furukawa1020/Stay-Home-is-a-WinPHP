<?php
/**
 * バリデータークラス
 * 入力値の検証を行う
 */

class Validator {
    /**
     * OPI値の検証
     */
    public static function isValidOpi($opi) {
        if (!is_numeric($opi)) {
            return false;
        }
        
        $opi = (int)$opi;
        return $opi >= OPI_MIN && $opi <= OPI_MAX;
    }
    
    /**
     * 難易度の検証
     */
    public static function isValidDifficulty($difficulty) {
        $validDifficulties = ['hell', 'warning', 'calm', 'peace'];
        return in_array($difficulty, $validDifficulties, true);
    }
    
    /**
     * 選択肢の検証
     */
    public static function isValidChoice($choice) {
        if (!is_string($choice)) {
            return false;
        }
        
        // stay_* または out_* の形式
        return preg_match('/^(stay|out)_[a-z]+$/', $choice) === 1;
    }
    
    /**
     * セッションIDの検証
     */
    public static function isValidSessionId($sessionId) {
        if (!is_string($sessionId)) {
            return false;
        }
        
        // 16進数文字列
        return preg_match('/^[a-f0-9]{16,}$/', $sessionId) === 1;
    }
    
    /**
     * タイムスタンプの検証
     */
    public static function isValidTimestamp($timestamp, $maxAge = 3600) {
        if (!is_numeric($timestamp)) {
            return false;
        }
        
        $timestamp = (int)$timestamp;
        $now = time();
        
        // 未来のタイムスタンプは無効
        if ($timestamp > $now) {
            return false;
        }
        
        // 古すぎるタイムスタンプは無効
        if ($now - $timestamp > $maxAge) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 文字列のサニタイズ
     */
    public static function sanitizeString($str, $maxLength = 255) {
        if (!is_string($str)) {
            return '';
        }
        
        $str = trim($str);
        $str = strip_tags($str);
        $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
        
        if (strlen($str) > $maxLength) {
            $str = mb_substr($str, 0, $maxLength, 'UTF-8');
        }
        
        return $str;
    }
    
    /**
     * 整数のサニタイズ
     */
    public static function sanitizeInt($value, $min = null, $max = null) {
        $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
        $value = (int)$value;
        
        if ($min !== null && $value < $min) {
            $value = $min;
        }
        
        if ($max !== null && $value > $max) {
            $value = $max;
        }
        
        return $value;
    }
    
    /**
     * 配列のサニタイズ
     */
    public static function sanitizeArray(array $array, $maxItems = 100) {
        if (count($array) > $maxItems) {
            $array = array_slice($array, 0, $maxItems);
        }
        
        return array_map(function($item) {
            if (is_string($item)) {
                return self::sanitizeString($item);
            }
            if (is_int($item) || is_float($item)) {
                return $item;
            }
            return null;
        }, $array);
    }
}
