/**
 * 孤独優勝クエスト - クライアントサイドスクリプト
 */

document.addEventListener('DOMContentLoaded', () => {
    // バイブレーション（モバイル対応）
    setupVibration();
    
    // OPIバーアニメーション
    animateOpiBar();
    
    // ボタンホバーエフェクト
    setupButtonEffects();
});

/**
 * バイブレーション設定
 */
function setupVibration() {
    if (!('vibrate' in navigator)) return;
    
    const buttons = document.querySelectorAll('.choice-btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            navigator.vibrate(50); // 50ms振動
        });
    });
}

/**
 * OPIバーアニメーション
 */
function animateOpiBar() {
    const bar = document.querySelector('.opi-bar-fill');
    if (!bar) return;
    
    const targetWidth = bar.style.width;
    bar.style.width = '0%';
    
    setTimeout(() => {
        bar.style.width = targetWidth;
    }, 100);
}

/**
 * ボタンエフェクト
 */
function setupButtonEffects() {
    const buttons = document.querySelectorAll('.choice-btn');
    
    buttons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            // ホバー時に軽いアニメーション
            this.style.transition = 'all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transition = 'all 0.3s ease';
        });
    });
}

/**
 * メッセージのタイピング効果（オプション）
 */
function typeMessage(element, text, speed = 30) {
    element.textContent = '';
    let i = 0;
    
    const interval = setInterval(() => {
        if (i < text.length) {
            element.textContent += text.charAt(i);
            i++;
        } else {
            clearInterval(interval);
        }
    }, speed);
}

/**
 * スクリーンショット推奨通知（結果ページ用）
 */
function suggestScreenshot() {
    if (document.querySelector('.title-card')) {
        setTimeout(() => {
            console.log('📸 称号カードをスクショして共有できます！');
        }, 2000);
    }
}

// 結果ページでスクショ推奨
if (document.body.classList.contains('result-page')) {
    suggestScreenshot();
}

/**
 * コンボ検出（将来の拡張用）
 */
let actionHistory = [];

function trackAction(actionKey) {
    actionHistory.push(actionKey);
    if (actionHistory.length > 3) {
        actionHistory.shift();
    }
    
    // コンボパターンチェック
    checkCombo(actionHistory);
}

function checkCombo(history) {
    // 特定のパターンでボーナス演出
    const comboPatterns = {
        'tea,music,breath': '三位一体の安らぎコンボ！',
        'stretch,breath,tea': 'セルフケアコンボ！'
    };
    
    const currentPattern = history.join(',');
    if (comboPatterns[currentPattern]) {
        console.log('🎉 ' + comboPatterns[currentPattern]);
        // TODO: UI表示
    }
}
