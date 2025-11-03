# 🏠 孤独優勝クエスト

**Stay Home is a Win - 外出圧力指数で「孤独」を肯定するゲーム**

外出圧力指数（OPI）をリアルタイム取得し、「在宅」を選ぶことで経験値を獲得する、孤独を讃える体験型ゲームアプリケーション。

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg)](https://php.net)
[![Status](https://img.shields.io/badge/Status-Production%20Ready-green.svg)]()

---

## ✨ 特徴

### 🎮 ゲームシステム
- **リアルタイムOPI取得** - 人流APIから外出圧力指数を取得（フォールバック機能付き）
- **豊富な行動選択** - 12種類の引きこもり行動 + 5種類の外出行動
- **6種類のボーナス** - 難易度・時間帯・リスク・コンボ・連続・ラッキーボーナス
- **4パターンのコンボ** - 完全引きこもり・冒険者・不規則生活・バランス重視
- **10段階の称号** - 初心者から伝説の引きこもりまで

### 🛠️ 技術仕様
- **エンタープライズグレードPHP** - OOP設計、8つのライブラリクラス
- **PSR-3準拠ログ** - 詳細なエラー追跡とデバッグ
- **ファイルベースキャッシュ** - API結果の効率的なキャッシング
- **セキュリティ完備** - CSRF保護、XSS対策、入力バリデーション
- **レスポンシブUI** - グラスモーフィズムデザイン、モバイル対応

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
- **Webサーバー**: Apache 2.4+ または PHP内蔵サーバー
- **ブラウザ**: Chrome, Firefox, Safari, Edge（最新版推奨）

### XAMPPでのインストール

1. **XAMPPをインストール**
```bash
# https://www.apachefriends.org/download.html からダウンロード
```

2. **プロジェクトを配置**
```powershell
# ダウンロードまたはクローンしたプロジェクトを移動
Copy-Item -Path ".\PHPdenanika" -Destination "C:\xampp\htdocs\" -Recurse
```

3. **Apache起動**
```
XAMPPコントロールパネルから Apache を Start
```

4. **ブラウザで開く**
```
http://localhost/PHPdenanika/index.php
```

### PHP内蔵サーバーでの起動（開発用）

```bash
cd PHPdenanika
php -S localhost:8000
```

ブラウザで `http://localhost:8000` にアクセス

### 権限設定（Linux/Mac）

```bash
chmod 755 -R .
chmod 777 logs/
chmod 777 cache/
```

Windowsでは自動的に書き込み権限が設定されます。

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
| 🌱 初心者 | 総経験値0-99 | 🌱 |
| 🌿 在宅見習い | 総経験値100-499 | � |
| 🍃 引きこもり初段 | 総経験値500-999 | 🍃 |
| 🌟 引きこもり達人 | 総経験値1,000-2,499 | 🌟 |
| 💫 孤独の探求者 | 総経験値2,500-4,999 | 💫 |
| ⭐ 在宅の賢者 | 総経験値5,000-9,999 | ⭐ |
| 🌠 孤高の哲学者 | 総経験値10,000-24,999 | 🌠 |
| ✨ 引きこもり宗匠 | 総経験値25,000-49,999 | ✨ |
| 🔮 孤独の求道者 | 総経験値50,000-99,999 | 🔮 |
| 👑 伝説の引きこもり | 総経験値100,000以上 | 👑 |

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
├── lib/                   # （続き）
│   ├── dialogue.php       # セリフ生成
│   └── meta.php           # メタデータ・OGP生成
├── logs/                  # ログ（自動生成）
├── cache/                 # キャッシュ（自動生成）
├── DEVELOPMENT.md         # 開発者向けドキュメント
└── README.md              # このファイル
```

---

## ⚙️ 設定

### フォールバックOPI生成

`lib/config.php`の`generateSmartFallbackOpi()`関数：

```php
// 時間帯別の基本範囲
- 朝（6-11時）:   25-50 OPI
- 昼（12-17時）:  45-75 OPI  
- 夕方（18-22時）: 60-90 OPI
- 夜（23-5時）:   20-45 OPI

// ボーナス加算
- 週末: +10 OPI
- 祝日: +15 OPI
```

APIが利用できない場合、現実的な人流パターンを反映したOPIが自動生成されます。

### 環境変数（`.env.php`）

```php
define('APP_ENV', 'production'); // development, staging, production
define('LOG_LEVEL', 'INFO');     // DEBUG, INFO, WARNING, ERROR, CRITICAL
define('CACHE_ENABLED', true);
```

---

## 🐛 トラブルシューティング

## 🐛 トラブルシューティング

### HTTP 403 Forbidden

**原因**: `.htaccess`のシンボリックリンク設定が無効

**解決策**:
```apache
# .htaccessに追加
Options +FollowSymLinks
<IfModule mod_authz_core.c>
    Require all granted
</IfModule>
```

### HTTP 500 Internal Server Error

**原因1**: PHP構文エラー
```bash
# エラーログを確認
tail -f C:\xampp\apache\logs\error.log
```

**原因2**: ファイルパスの不一致
- `lib/`ディレクトリ内のすべてのファイルが存在するか確認
- `require_once`のパスが正しいか確認

### Parse Error in .env.php

**原因**: `define()`文の構文エラー

**解決策**: `.env.php`を再作成
```php
<?php
if (!defined('APP_ENV')) define('APP_ENV', 'production');
if (!defined('LOG_ENABLED')) define('LOG_ENABLED', true);
?>
```

### TypeError: Illegal offset type

**原因**: 配列キーの型エラー

**解決策**: `lib/config.php`の`generateSmartFallbackOpi()`関数で、配列定数ではなくローカル変数を使用

### ログが記録されない

- `logs/`ディレクトリの書き込み権限を確認
- `LOG_ENABLED`が`true`か確認

### キャッシュが効かない

- `cache/`ディレクトリの書き込み権限を確認
- `CACHE_ENABLED`が`true`か確認

### APIフォールバック動作

**確認方法**:
1. ブラウザでゲームをプレイ
2. OPI値が表示されればフォールバック機能が動作中
3. `logs/app.log`で"Fallback OPI generated"を確認

---

## 🎨 カスタマイズ

### テーマカラー変更

`style.css`のカスタムプロパティを編集：

```css
:root {
    --primary-color: #667eea;      /* プライマリカラー */
    --secondary-color: #764ba2;    /* セカンダリカラー */
    --accent-color: #f093fb;       /* アクセントカラー */
}
```

### 行動追加

`lib/config.php`の`STAY_ACTIONS`配列に追加：

```php
const STAY_ACTIONS = [
    // ... 既存の行動
    [
        'id' => 'custom_action',
        'title' => 'カスタム行動',
        'emoji' => '🎯',
        'base_xp' => 80,
        'tags' => ['カスタム', 'ユニーク']
    ]
];
```

---

---

## 📊 実装状況

### ✅ 完了機能

- **コアシステム**
  - ✅ OPI取得（APIフォールバック付き）
  - ✅ セッション管理
  - ✅ 経験値計算
  - ✅ 6種類のボーナス
  - ✅ 4パターンのコンボ
  - ✅ 10段階の称号
  
- **セキュリティ**
  - ✅ CSRF保護
  - ✅ XSS対策
  - ✅ 入力バリデーション
  - ✅ セキュアヘッダー

- **インフラ**
  - ✅ PSR-3準拠ログ
  - ✅ ファイルベースキャッシュ
  - ✅ エラーハンドリング
  - ✅ XAMPP完全対応

### 🚀 デプロイ済み

- **環境**: Windows + XAMPP
- **URL**: `http://localhost/PHPdenanika/index.php`
- **状態**: Production Ready
- **API**: フォールバックモード稼働中

---

## 🤝 コントリビューション

プルリクエスト大歓迎！以下の手順でコントリビュートできます：

1. フォーク
2. フィーチャーブランチ作成（`git checkout -b feature/amazing-feature`）
3. コミット（`git commit -m 'Add amazing feature'`）
4. プッシュ（`git push origin feature/amazing-feature`）
5. プルリクエスト作成

**開発ガイドライン**:
- PSR-12 コーディング規約に準拠
- 新機能には単体テストを追加
- コメントはPHPDocで記述
- エラーハンドリングを必ず実装

---

## 📝 ライセンス

MIT License - 詳細は[LICENSE](LICENSE)ファイルを参照してください。

---

## � リンク

- **Repository**: [Stay-Home-is-a-WinPHP](https://github.com/furukawa1020/Stay-Home-is-a-WinPHP)
- **Issues**: [GitHub Issues](https://github.com/furukawa1020/Stay-Home-is-a-WinPHP/issues)
- **Documentation**: [DEVELOPMENT.md](DEVELOPMENT.md)

---

## 🙏 謝辞

このプロジェクトは以下の技術・コミュニティの恩恵を受けています：

- **PHP Community** - エンタープライズグレードのOOPアーキテクチャ
- **Google Fonts** - Inter & JetBrains Mono
- **Glassmorphism Design Community** - モダンUIインスピレーション
- **Apache HTTP Server** - 安定したWebサーバー
- **XAMPP** - ローカル開発環境

---

## 📞 サポート & フィードバック

**バグ報告**: [GitHub Issues](https://github.com/furukawa1020/Stay-Home-is-a-WinPHP/issues)  
**機能要望**: [GitHub Discussions](https://github.com/furukawa1020/Stay-Home-is-a-WinPHP/discussions)  
**開発者**: [@furukawa1020](https://github.com/furukawa1020)

---

<div align="center">

**🏠 Stay Home is a Win 🏆**

*孤独を讃え、在宅を楽しむ。あなたの選択が、勝利です。*

Made with ❤️ and PHP

</div>

問題が発生した場合は、[Issues](https://github.com/furukawa1020/Stay-Home-is-a-WinPHP/issues)で報告してください。

---

**Stay Home is a Win. 🏠✨**

*孤独を讃え、在宅を楽しもう。*
