/**
 * 孤独優勝クエスト - モダンUI インタラクション
 * スムーズアニメーション × マイクロインタラクション
 */

// グローバル状態管理
const app = {
    initialized: false,
    actionHistory: [],
    animationQueue: []
};

// DOMロード時の初期化
document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
});

/**
 * アプリケーション初期化
 */
function initializeApp() {
    if (app.initialized) return;
    
    // 各種機能の初期化
    setupVibration();
    animateOpiBar();
    setupButtonEffects();
    setupParallax();
    setupIntersectionObserver();
    typeMessage();
    
    app.initialized = true;
    console.log('🏠 孤独優勝クエスト - 起動完了');
}

/**
 * バイブレーション設定（モバイル）
 */
function setupVibration() {
    if (!('vibrate' in navigator)) return;
    
    const buttons = document.querySelectorAll('.choice-btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            navigator.vibrate([30, 10, 20]); // パターン振動
        });
    });
}

/**
 * OPIバーのスムーズアニメーション
 */
function animateOpiBar() {
    const bar = document.querySelector('.opi-bar-fill');
    if (!bar) return;
    
    const targetWidth = bar.style.width;
    const targetValue = parseInt(targetWidth);
    
    bar.style.width = '0%';
    
    // カウントアップアニメーション
    let currentValue = 0;
    const duration = 1500; // 1.5秒
    const startTime = performance.now();
    
    function animate(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // イージング関数（ease-out-cubic）
        const eased = 1 - Math.pow(1 - progress, 3);
        currentValue = Math.floor(targetValue * eased);
        
        bar.style.width = currentValue + '%';
        
        if (progress < 1) {
            requestAnimationFrame(animate);
        }
    }
    
    setTimeout(() => {
        requestAnimationFrame(animate);
    }, 300);
}

/**
 * ボタンエフェクト強化
 */
function setupButtonEffects() {
    const buttons = document.querySelectorAll('.choice-btn, .action-btn');
    
    buttons.forEach(btn => {
        // リップル効果
        btn.addEventListener('click', function(e) {
            createRipple(e, this);
        });
        
        // マウスフォロー効果
        btn.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            this.style.setProperty('--mouse-x', `${x}px`);
            this.style.setProperty('--mouse-y', `${y}px`);
        });
        
        // ホバー時のサウンドフィードバック（オプション）
        btn.addEventListener('mouseenter', () => {
            playHoverSound();
        });
    });
}

/**
 * リップル効果生成
 */
function createRipple(event, element) {
    const ripple = document.createElement('span');
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;
    
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.classList.add('ripple');
    
    // 既存のリップルを削除
    const existingRipple = element.querySelector('.ripple');
    if (existingRipple) {
        existingRipple.remove();
    }
    
    element.appendChild(ripple);
    
    // アニメーション後削除
    setTimeout(() => {
        ripple.remove();
    }, 600);
}

/**
 * パララックス効果
 */
function setupParallax() {
    const parallaxElements = document.querySelectorAll('.opi-display, .message-box, .choice-section');
    
    if (!parallaxElements.length) return;
    
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        
        parallaxElements.forEach((el, index) => {
            const speed = 0.05 * (index + 1);
            const yPos = -(scrolled * speed);
            el.style.transform = `translateY(${yPos}px)`;
        });
    });
}

/**
 * Intersection Observer（スクロールアニメーション）
 */
function setupIntersectionObserver() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    const animatedElements = document.querySelectorAll('.choice-btn, .action-btn, .title-card');
    animatedElements.forEach(el => observer.observe(el));
}

/**
 * タイピングエフェクト（メッセージ）
 */
function typeMessage() {
    const messageBox = document.querySelector('.message-box');
    if (!messageBox || messageBox.dataset.typed) return;
    
    const text = messageBox.textContent.trim();
    messageBox.textContent = '';
    messageBox.dataset.typed = 'true';
    
    let index = 0;
    const speed = 30; // ミリ秒
    
    function type() {
        if (index < text.length) {
            messageBox.textContent += text.charAt(index);
            index++;
            setTimeout(type, speed);
        }
    }
    
    // 初回のみタイピングエフェクト
    if (sessionStorage.getItem('firstVisit') !== 'false') {
        type();
        sessionStorage.setItem('firstVisit', 'false');
    } else {
        messageBox.textContent = text;
    }
}

/**
 * ホバーサウンド（Web Audio API）
 */
function playHoverSound() {
    // オプション：サウンド有効時のみ
    if (!app.soundEnabled) return;
    
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 800; // Hz
        gainNode.gain.value = 0.05; // 音量
        
        oscillator.start();
        oscillator.stop(audioContext.currentTime + 0.05); // 50ms
    } catch (e) {
        // サウンド再生失敗時は無視
    }
}

/**
 * スクリーンショット推奨通知
 */
function suggestScreenshot() {
    if (!document.querySelector('.title-card')) return;
    
    setTimeout(() => {
        const toast = createToast('📸 称号カードをスクショして共有できます！');
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }, 2000);
}

/**
 * トースト通知作成
 */
function createToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        font-size: 14px;
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        z-index: 9999;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    `;
    
    toast.classList.add('show');
    
    // showクラスのスタイル
    const style = document.createElement('style');
    style.textContent = `
        .toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    `;
    document.head.appendChild(style);
    
    return toast;
}

/**
 * コンボ検出システム
 */
function trackAction(actionKey) {
    app.actionHistory.push(actionKey);
    if (app.actionHistory.length > 3) {
        app.actionHistory.shift();
    }
    
    checkCombo(app.actionHistory);
}

function checkCombo(history) {
    const comboPatterns = {
        'tea,music,breath': {
            title: '三位一体の安らぎ',
            bonus: 50,
            message: '湯と音と呼吸。完璧なコンボ。'
        },
        'stretch,breath,tea': {
            title: 'セルフケアの達人',
            bonus: 50,
            message: '身体も心も、丁寧に扱った証。'
        }
    };
    
    const currentPattern = history.join(',');
    if (comboPatterns[currentPattern]) {
        const combo = comboPatterns[currentPattern];
        showComboNotification(combo);
    }
}

/**
 * コンボ通知表示
 */
function showComboNotification(combo) {
    const notification = document.createElement('div');
    notification.className = 'combo-notification';
    notification.innerHTML = `
        <div class="combo-title">🎉 ${combo.title}</div>
        <div class="combo-message">${combo.message}</div>
        <div class="combo-bonus">+${combo.bonus} XP</div>
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0);
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.95), rgba(139, 92, 246, 0.95));
        backdrop-filter: blur(20px);
        padding: 32px;
        border-radius: 16px;
        text-align: center;
        z-index: 9999;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        animation: comboAppear 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    `;
    
    // アニメーション定義
    if (!document.getElementById('combo-animation-style')) {
        const style = document.createElement('style');
        style.id = 'combo-animation-style';
        style.textContent = `
            @keyframes comboAppear {
                0% {
                    opacity: 0;
                    transform: translate(-50%, -50%) scale(0);
                }
                100% {
                    opacity: 1;
                    transform: translate(-50%, -50%) scale(1);
                }
            }
            .combo-title {
                font-size: 24px;
                font-weight: 700;
                margin-bottom: 8px;
            }
            .combo-message {
                font-size: 16px;
                margin-bottom: 12px;
                opacity: 0.9;
            }
            .combo-bonus {
                font-size: 20px;
                font-weight: 700;
                color: #10b981;
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'comboAppear 0.4s cubic-bezier(0.16, 1, 0.3, 1) reverse forwards';
        setTimeout(() => notification.remove(), 400);
    }, 3000);
}

// 結果ページでスクショ推奨
if (document.body.classList.contains('result-page')) {
    suggestScreenshot();
}

/**
 * スムーズスクロール
 */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

/**
 * パフォーマンス最適化：デバウンス
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// リサイズイベントのデバウンス
window.addEventListener('resize', debounce(() => {
    console.log('🔄 ウィンドウリサイズ検出');
}, 250));

// エクスポート（必要に応じて）
window.app = app;

