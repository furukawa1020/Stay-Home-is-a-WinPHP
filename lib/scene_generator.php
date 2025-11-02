<?php
/**
 * シーン生成クラス
 * OPI・ユーザーセッション・時間帯を考慮した複雑なシーン生成
 */

class SceneGenerator {
    private $opi;
    private $isOffline;
    private $session;
    private $difficulty;
    private $timeOfDay;
    
    public function __construct($opi, $isOffline, $session) {
        $this->opi = $opi;
        $this->isOffline = $isOffline;
        $this->session = $session;
        $this->difficulty = $this->calculateDifficulty();
        $this->timeOfDay = getTimeOfDay();
    }
    
    /**
     * シーン生成
     */
    public function generate() {
        $scene = [
            'difficulty' => $this->difficulty,
            'message' => $this->generateMessage(),
            'offline_message' => $this->isOffline ? $this->generateOfflineMessage() : '',
            'stay_choices' => $this->generateStayChoices(),
            'out_choices' => $this->generateOutChoices(),
            'special_event' => $this->generateSpecialEvent()
        ];
        
        return $scene;
    }
    
    /**
     * 難易度計算
     */
    private function calculateDifficulty() {
        if ($this->opi >= OPI_THRESHOLD_HELL) return 'hell';
        if ($this->opi >= OPI_THRESHOLD_WARNING) return 'warning';
        if ($this->opi >= OPI_THRESHOLD_CALM) return 'calm';
        return 'peace';
    }
    
    /**
     * メインメッセージ生成
     */
    private function generateMessage() {
        $messages = $this->getMessagesForDifficulty();
        
        // 時間帯による補正
        if ($this->timeOfDay === 'night' && $this->opi < 40) {
            $messages[] = "夜の静けさは特別。\n今夜のあなたは、都市の支配者。";
        }
        
        if ($this->timeOfDay === 'morning' && $this->opi > 70) {
            $messages[] = "朝から外界は全開モード。\n部屋という名の避難所へようこそ。";
        }
        
        // コンボボーナスメッセージ
        if ($this->session['combo_streak'] >= 3) {
            $messages[] = "連続{$this->session['combo_streak']}回目の在宅。\nあなたは既に伝説。";
        }
        
        return $messages[array_rand($messages)];
    }
    
    /**
     * 難易度別メッセージ取得
     */
    private function getMessagesForDifficulty() {
        $allMessages = [
            'hell' => [
                "外界、恋のエンカウント多発。\n今日は部屋がチート。",
                "外界の\"陽\"濃度が危険域。\nお茶を盾にせよ。",
                "今日の勇者はドアノブに触れない。",
                "世界のBGMが大きい日は、\n耳じゃなく**心の音量**を下げる。",
                "OPI {$this->opi}。完全修羅場。\n在宅は戦略的正解。",
                "外が混むほど、\n部屋の価値が上がる法則。",
                "今日出る人は勇者じゃなく**無謀者**。\nあなたは賢者。"
            ],
            'warning' => [
                "\"そこそこ混雑\"が一番メンタルに効く。\n布団を固めよ。",
                "社会が『来いよ』って言ってくる。\nやだ（即答）。",
                "そこそこ出やすい日は、\nそこそこ後悔しがち。",
                "あなたの部屋は、あなたの王国。\n王は外に並ばない。",
                "OPI {$this->opi}。警戒レベル。\n無理しない勇気を。",
                "外の\"普通\"に合わせる必要、\n本当にありますか？"
            ],
            'calm' => [
                "街のBGMが小さい。\n窓開けて勝ち。",
                "出ない勇気は、出る勇気よりもレア。",
                "外ガラガラ。主役になれるが、\n主役しない自由もある。",
                "OPI {$this->opi}。微風モード。\n選択肢は全て正解。",
                "静かな日は、\n自分との対話日和。"
            ],
            'peace' => [
                "世界がスリープ。\n在宅は完全勝利。",
                "静けさは才能。\nあなたは今、世界最高のスタジアム（自室）にいる。",
                "\"何もしない\"は負けじゃない。\n**回復コマンド**だ。",
                "OPI {$this->opi}。完全静寂。\n宇宙レベルの平和。",
                "今この瞬間、\n世界で一番落ち着いてるのはあなた。"
            ]
        ];
        
        return $allMessages[$this->difficulty];
    }
    
    /**
     * オフラインメッセージ生成
     */
    private function generateOfflineMessage() {
        $messages = [
            "電波の向こうも孤独。\n今日は**在宅SSランク**。",
            "現実の整合性が崩壊。\nメタ的に言うと**寝よ**。",
            "焦らなくていい。\n孤独は待ってくれる。",
            "データが取れない？\nなら、心のデータを信じよう。",
            "オフラインこそ、\n最も「今」を生きてる証拠。"
        ];
        
        return $messages[array_rand($messages)];
    }
    
    /**
     * 在宅行動選択肢生成
     */
    private function generateStayChoices() {
        $allChoices = [
            ['icon' => '☕', 'text' => '湯を沸かす', 'key' => 'tea', 'tags' => ['relax', 'warm']],
            ['icon' => '🎵', 'text' => '好きな音を流す', 'key' => 'music', 'tags' => ['relax', 'mood']],
            ['icon' => '🧘', 'text' => '5分だけ伸び', 'key' => 'stretch', 'tags' => ['body', 'refresh']],
            ['icon' => '📖', 'text' => '積ん読を1ページ', 'key' => 'book', 'tags' => ['mind', 'calm']],
            ['icon' => '🌙', 'text' => '深呼吸×3回', 'key' => 'breath', 'tags' => ['mind', 'body']],
            ['icon' => '🎮', 'text' => 'セーブデータを眺める', 'key' => 'game', 'tags' => ['nostalgic', 'fun']],
            ['icon' => '🛋️', 'text' => 'ソファに身を委ねる', 'key' => 'sofa', 'tags' => ['relax', 'rest']],
            ['icon' => '🍵', 'text' => 'お気に入りカップを選ぶ', 'key' => 'cup', 'tags' => ['warm', 'mood']],
            ['icon' => '📱', 'text' => '好きな動画1本', 'key' => 'video', 'tags' => ['fun', 'mood']],
            ['icon' => '🕯️', 'text' => 'アロマを焚く', 'key' => 'aroma', 'tags' => ['relax', 'calm']],
            ['icon' => '📝', 'text' => '今の気持ちを3行書く', 'key' => 'write', 'tags' => ['mind', 'expression']],
            ['icon' => '🎨', 'text' => '5分だけ落書き', 'key' => 'draw', 'tags' => ['creative', 'fun']]
        ];
        
        // OPIが高いほどリラックス系を優先
        if ($this->opi >= 70) {
            $choices = $this->filterChoicesByTag($allChoices, ['relax', 'calm']);
        } else {
            $choices = $allChoices;
        }
        
        // 前回の選択を履歴から除外（バリエーション確保）
        $lastActions = array_slice($this->session['action_history'], -3);
        $choices = array_filter($choices, function($choice) use ($lastActions) {
            return !in_array($choice['key'], $lastActions);
        });
        
        // ランダムに3つ選択
        shuffle($choices);
        $selected = array_slice($choices, 0, 3);
        
        // ボーナスEXP付与（ランダム）
        foreach ($selected as &$choice) {
            if (rand(1, 3) === 1) {
                $choice['bonus'] = rand(10, 30);
            }
        }
        
        return $selected;
    }
    
    /**
     * 微外出選択肢生成
     */
    private function generateOutChoices() {
        // OPI > 49 の場合は微外出なし
        if ($this->opi > 49) {
            return [];
        }
        
        $allChoices = [
            ['icon' => '🌟', 'text' => '夜空を1分見る', 'key' => 'sky', 'risk' => '低'],
            ['icon' => '🚪', 'text' => '玄関先で深呼吸', 'key' => 'door', 'risk' => '低'],
            ['icon' => '📮', 'text' => 'ポスト確認', 'key' => 'post', 'risk' => '低'],
            ['icon' => '🌱', 'text' => 'ベランダに出る', 'key' => 'balcony', 'risk' => '低'],
            ['icon' => '🏪', 'text' => 'コンビニまで往復', 'key' => 'convenience', 'risk' => '中']
        ];
        
        // OPIが低いほど外出選択肢を増やす
        $maxChoices = $this->opi < 30 ? 3 : 2;
        
        shuffle($allChoices);
        return array_slice($allChoices, 0, $maxChoices);
    }
    
    /**
     * 特別イベント生成
     */
    private function generateSpecialEvent() {
        // 10%の確率で特別イベント
        if (rand(1, 10) !== 1) {
            return null;
        }
        
        $events = [
            ['icon' => '🎁', 'text' => 'ラッキー！ 今日はボーナスEXP×1.5'],
            ['icon' => '⭐', 'text' => '特別な日。すべての選択に+50XP'],
            ['icon' => '🌈', 'text' => 'レアイベント発生！称号が豪華版に'],
            ['icon' => '🎉', 'text' => 'おめでとう！在宅マスターの道を極めつつある']
        ];
        
        return $events[array_rand($events)];
    }
    
    /**
     * タグでフィルタリング
     */
    private function filterChoicesByTag($choices, $tags) {
        return array_filter($choices, function($choice) use ($tags) {
            return !empty(array_intersect($choice['tags'], $tags));
        });
    }
}

/**
 * フォールバックシーン生成
 */
function generateFallbackScene($opi) {
    return [
        'difficulty' => getDifficulty($opi),
        'message' => "システムエラーが発生しました。\nでも大丈夫。在宅は変わらず正解です。",
        'offline_message' => '',
        'stay_choices' => [
            ['icon' => '☕', 'text' => '湯を沸かす', 'key' => 'tea'],
            ['icon' => '🎵', 'text' => '好きな音を流す', 'key' => 'music'],
            ['icon' => '🧘', 'text' => '深呼吸×3回', 'key' => 'breath']
        ],
        'out_choices' => [],
        'special_event' => null
    ];
}