# 🏠 孤独優勝クエスト

**Stay Home is a Win.**

外出圧力指数（OPI）をリアルタイム取得し、「在宅」を選ぶことで経験値を獲得する、孤独を讃えるゲームアプリ。

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg)](https://php.net)
[![Status](https://img.shields.io/badge/Status-Production%20Ready-green.svg)]()

---

## ✨ 特徴

- **🌐 リアルタイムOPI取得** - 人流APIから外出圧力指数を取得
- **🎮 シンプルなゲーム性** - 在宅行動を選んで経験値獲得
- **🏆 称号システム** - 累計経験値や行動パターンで称号解除
- **⚡ コンボシステム** - 特定の行動パターンでボーナス獲得
- **📊 統計追跡** - 訪問回数、総経験値、最大コンボを記録
- **🎨 モダンUI** - グラスモーフィズムデザイン + Google Fonts
- **🔒 エンタープライズグレード** - ログ、キャッシュ、バリデーション完備
- **📱 レスポンシブ** - スマホ・タブレット・PC対応

---

## 🎯 コンセプト

> "外に出たくない"そんな気持ちを肯定し、在宅時間を楽しく過ごすためのゲーム。  
> **孤独は勝利。Stay Home is a Win.**

### OPI（Outing Pressure Index）とは？

外出圧力指数（0-100）は、人流データから算出される「今、外に出る人の多さ」を示す指標です。

- **🔥 地獄級（75-100）** - 超高難易度、在宅選択で最大ボーナス
- **⚠️ 警戒（50-74）** - 高難易度、賢明な判断でボーナス
- **😌 穏やか（25-49）** - 中難易度、適度なリラックス
- **☮️ 平和（0-24）** - 低難易度、心穏やかな時間

---

## 🚀 クイックスタート

### 必要要件

- **PHP**: 7.4以上
- **Apache**: mod_rewrite, mod_headers有効
- **ブラウザ**: Chrome, Firefox, Safari, Edge（最新版推奨）

### インストール

1. **リポジトリをクローン**

```bash
git clone https://github.com/furukawa1020/Stay-Home-is-a-WinPHP.git
cd Stay-Home-is-a-WinPHP
```

2. **権限設定**

```bash
chmod 755 -R .
chmod 777 logs/
chmod 777 cache/
```

Windowsの場合は、`logs/`と`cache/`ディレクトリに書き込み権限を付与してください。

3. **環境設定（オプション）**

API設定をカスタマイズする場合：

```bash
cp .env.example.php .env.php
# .env.phpを編集
```

4. **ブラウザで開く**

```
http://localhost/PHPdenanika/index.php
```

---

## 🎮 使い方

### 基本フロー

1. **アクセス** → OPIが自動取得される
2. **行動選択** → 在宅行動（12種類）または微外出（5種類）から選ぶ
3. **結果表示** → 経験値、ボーナス、称号を獲得
4. **繰り返し** → コンボを狙って連続プレイ

### 在宅行動例

| アイコン | 行動 | 基礎経験値 | タグ |
|---------|------|-----------|------|
| 🍵 | お茶を淹れる | 30 XP | リラックス、温かい |
| 😴 | 15分仮眠 | 40 XP | リラックス、リフレッシュ |
| 📖 | 本を読む | 50 XP | 知的、静寂 |
| 🍳 | 簡単な料理 | 60 XP | 楽しい、クリエイティブ |
| ✍️ | 日記を書く | 55 XP | メンタル、表現 |

### コンボパターン

特定の行動を3連続で実行すると、特別ボーナス！

- **リラックス3連鎖** - お茶→音楽→深呼吸（+50 XP）
- **文化人3連鎖** - 本→お茶→日記（+70 XP）
- **生活改善3連鎖** - 掃除→料理→ストレッチ（+65 XP）

---

## 📊 経験値システム

### ボーナス一覧

| ボーナス | 条件 | 倍率/値 |
|---------|------|---------|
| **難易度** | OPIに応じて | x1.0~x2.5 |
| **時間帯** | 深夜/朝/夕方 | +10~+20 |
| **勇気** | 高OPI時の外出 | +50~+100 |
| **コンボ** | 3連続パターン | +50~+70 |
| **在宅連続** | 5回以上連続 | +5×回数 |
| **ラッキー** | 10%確率 | +20~+50 |

### 総経験値計算式

```
総経験値 = (基礎経験値 × 難易度倍率) + 時間帯ボーナス + コンボボーナス + その他ボーナス
```

---

## 🏆 称号システム

| 称号 | 条件 | アイコン |
|-----|------|---------|
| 経験値1K達成 | 総経験値1,000以上 | 🌟 |
| 経験値5K達成 | 総経験値5,000以上 | 💫 |
| コンボ初心者 | コンボ3連鎖達成 | 🔗 |
| コンボマスター | コンボ10連鎖達成 | ⚡ |
| 夜更かし族 | 深夜行動5回 | 🌙 |
| 早起き族 | 朝行動5回 | 🌅 |
| 孤高の引きこもり | 在宅20連続 | 🏔️ |

---

## 🛠️ 技術スタック

### バックエンド

- **PHP 7.4+** - OOP アーキテクチャ
- **PSR-3互換ログ** - ログローテーション
- **ファイルベースキャッシュ** - TTL管理
- **セッション管理** - 統計追跡
- **バリデーション層** - XSS対策

### フロントエンド

- **HTML5** - セマンティックマークアップ
- **CSS3** - グラスモーフィズム、カスタムプロパティ
- **Vanilla JavaScript** - マイクロインタラクション
- **Google Fonts** - Inter + JetBrains Mono

### セキュリティ

- ✅ CSRF保護
- ✅ XSS対策
- ✅ 入力バリデーション
- ✅ セキュアヘッダー
- ✅ セッションハイジャック対策

---

## 📁 プロジェクト構成

```
PHPdenanika/
├── index.php              # エントリーポイント
├── result.php             # 結果ページ
├── style.css              # スタイルシート
├── script.js              # JavaScript
├── .htaccess              # Apache設定
├── lib/                   # ライブラリ
│   ├── config.php         # 設定管理
│   ├── api.php            # API通信
│   ├── logger.php         # ロガー
│   ├── cache.php          # キャッシュ
│   ├── validator.php      # バリデーター
│   └── scene_generator.php # シーン生成
├── data/                  # データ
│   ├── dialogue.php       # セリフ辞書
│   └── meta.php           # メタデータ
├── logs/                  # ログ（自動生成）
├── cache/                 # キャッシュ（自動生成）
├── DEVELOPMENT.md         # 開発者向けドキュメント
└── README.md              # このファイル
```

---

## ⚙️ 設定

### API設定

`lib/config.php`で人流APIのエンドポイントを設定：

```php
define('PEOPLE_FLOW_API_URL', 'https://api.example.com/people-flow');
define('PEOPLE_FLOW_API_KEY', 'your_api_key_here');
```

APIが利用できない場合、スマートフォールバック機能により、時間帯と曜日を考慮したOPIが自動生成されます。

### 環境変数

`.env.php`（オプション）でカスタマイズ可能：

```php
define('APP_ENV', 'production'); // development, staging, production
define('LOG_LEVEL', 'INFO');     // DEBUG, INFO, WARNING, ERROR, CRITICAL
define('CACHE_ENABLED', true);
```

---

## 🐛 トラブルシューティング

### ログが記録されない

- `logs/`ディレクトリの書き込み権限を確認
- `LOG_ENABLED`が`true`か確認

### キャッシュが効かない

- `cache/`ディレクトリの書き込み権限を確認
- `CACHE_ENABLED`が`true`か確認

### セッションエラー

- PHPの`session.save_path`を確認
- セッションディレクトリの書き込み権限を確認

### API接続失敗

- `PEOPLE_FLOW_API_URL`が正しいか確認
- ネットワーク接続を確認
- フォールバック機能により、動作は継続します

---

## 🤝 コントリビューション

プルリクエスト大歓迎！以下の手順でコントリビュートできます：

1. フォーク
2. フィーチャーブランチ作成（`git checkout -b feature/amazing-feature`）
3. コミット（`git commit -m 'Add amazing feature'`）
4. プッシュ（`git push origin feature/amazing-feature`）
5. プルリクエスト作成

---

## 📝 ライセンス

MIT License - 詳細は[LICENSE](LICENSE)ファイルを参照してください。

---

## 👨‍💻 開発者

- **GitHub**: [@furukawa1020](https://github.com/furukawa1020)
- **Repository**: [Stay-Home-is-a-WinPHP](https://github.com/furukawa1020/Stay-Home-is-a-WinPHP)

---

## 🙏 謝辞

- Google Fonts（Inter, JetBrains Mono）
- グラスモーフィズムデザインコミュニティ
- PHP & Apache コミュニティ

---

## 📞 サポート

問題が発生した場合は、[Issues](https://github.com/furukawa1020/Stay-Home-is-a-WinPHP/issues)で報告してください。

---

**Stay Home is a Win. 🏠✨**

*孤独を讃え、在宅を楽しもう。*
