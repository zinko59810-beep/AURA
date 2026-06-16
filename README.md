# AURA - 学習コミュニティサイト

<!-- ![PHP](https://img.shields.io/badge/PHP-8.x-blue)
![MySQL](https://img.shields.io/badge/MySQL-8.x-orange)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-purple)
![License](https://img.shields.io/badge/License-Educational-green) -->

---

## 概要

AURA（Academic User Relation Application）は、学生同士が学び合い、交流できる学習コミュニティサイトです。

授業や課題で分からない内容を質問し、他の学生が回答することで知識を共有できます。また、ダイレクトチャット機能を利用して学生同士が交流しながら学習できる環境を提供します。

---

## 開発目的

学生同士が気軽に質問や相談を行える環境を作ることを目的として開発しました。

AURAでは、学習に関する質問を共有し、回答やチャットを通して知識を深めることができます。さらに、ランキングやポイント機能によって、継続的な学習意欲の向上を目指しています。

---

## 主な機能

###  ユーザー機能

- ユーザー登録
- ログイン / ログアウト
- セッション認証
- プロフィール表示
- プロフィール画像アップロード

###  質問・回答機能

- 質問投稿
- 質問一覧表示
- 回答投稿
- 回答一覧表示
- 自分の質問一覧
- 自分の回答一覧
- コピー投稿判定

### コミュニケーション機能

- ダイレクトメッセージ
- 既読機能
- オンライン状態表示
- フォロー / フォロワー機能

### 通知機能

- 通知一覧表示
- 未読通知表示
- フォロー通知
- Helpful通知

ランキング機能

- ポイントランキング
- レベル表示
- 学習活動の可視化

### プロジェクト機能

- プロジェクト登録
- プロジェクト編集
- プロジェクト削除
- GitHub URL登録
- Demo URL登録

---

## 使用技術

### フロントエンド

- HTML
- CSS
- Bootstrap 5
- JavaScript

### バックエンド

- PHP

### データベース

- MySQL

### 開発ツール

- VS Code
- Git
- GitHub
- ChatGPT
- Teams

---

## データベース構成

主なテーブルは以下の通りです。

| テーブル名 | 内容 |
|---|---|
| users | ユーザー情報 |
| questions | 質問情報 |
| answers | 回答情報 |
| direct_messages | ダイレクトメッセージ |
| notifications | 通知情報 |
| follows | フォロー情報 |
| helpfuls | 役に立った評価 |
| projects | プロジェクト情報 |
| reports | 通報情報 |
| subjects | 科目情報 |
| user_subjects | ユーザーと科目の関係 |

---

## スクリーンショット

### ホーム画面

質問一覧を表示し、学習に関する質問を確認できます。

<!--
![Home](images/home.png)
-->

### プロフィール画面

ユーザー情報、投稿数、回答数、フォロー数、ポイントなどを確認できます。

<!--
![Profile](images/profile.png)
-->

### チャット画面

学生同士でメッセージを送受信できます。

<!--
![Chat](images/chat.png)
-->

### ランキング画面

ポイントに応じたランキングを表示します。

<!--
![Ranking](images/ranking.png)
-->

---

## セキュリティ対策

- PDOによるSQLインジェクション対策
- password_hash() によるパスワードハッシュ化
- password_verify() によるログイン認証
- Sessionによるログイン管理
- htmlspecialchars() によるXSS対策

---

## 今後の課題

- グループチャット機能
- 学習グループ機能
- 管理者機能
- 通報システムの強化
- UI/UX改善
- モバイル対応の強化

---

## セットアップ方法

### 1. リポジトリをクローン

```bash
git clone https://github.com/zinko59810-beep/AURA.git
```

### 2. データベースを作成

```sql
CREATE DATABASE student_db;
```

### 3. SQLファイルをインポート

phpMyAdminなどを使用して、テーブル作成用SQLをインポートします。

### 4. DB接続設定

`02_backend/db/db_.php` の接続情報を環境に合わせて変更します。

```php
$host = "localhost";
$dbname = "student_db";
$user = "root";
$password = "";
```

### 5. ブラウザでアクセス

ローカル環境の場合：

```text
http://localhost/AURA/
```

---

## 🔗 URL

### GitHub

https://github.com/zinko59810-beep/AURA

---

## 制作

ECCコンピュータ専門学校  
チーム開発作品

---

## ライセンス

本作品は学習・教育目的で制作されています。
