# netacho（ネタ帳）— 投稿ネタ帳 ＋ 投稿カレンダー

SNS で発信を続けるための、**投稿ネタを貯めて・いつ出すかを決めて・出したかを記録する** Web アプリです。

NEXT ENGINEER CATAPULT 2026 Phase01 の課題（上級＝オリジナル Web アプリ）として制作しました。

---

## 作った理由

小さな食品メーカーの EC（自社ブランド）を手伝っていて、Instagram と X の投稿を毎週続けています。
そこで一番つらいのは「書くこと」ではなく、

- 思いついたネタが LINE のメモやスマホの写真フォルダに散らばって、探せなくなる
- 「今週は何を出す予定だったか」がどこにも無い
- 出したかどうかを覚えていられない

の3つでした。ネタ・予定・実績が別々の場所にあるのが原因なので、**1つのアプリにまとめた**のがこのアプリです。

---

## 主な機能

| 画面 | URL | できること |
|---|---|---|
| ダッシュボード | `/dashboard` | 今週の予定件数・まだ使っていないネタの件数・直近で出した投稿が一目で分かる |
| 商品 | `/products` | 商品の登録・一覧・詳細・編集・削除（**写真のアップロード付き**） |
| 投稿ネタ | `/ideas` | ネタの登録・一覧・詳細・編集・削除／**商品・タグ・言葉での絞り込み**／**型からの下書き生成** |
| 投稿の型 | `/templates` | 投稿文のひな形を登録。`{商品名}` `{特徴}` が選んだ商品の中身に置き換わる |
| 投稿カレンダー | `/posts` | 1週間を7日ぶん並べ、ネタを日付と出し先（X / Instagram / note）に割り当てる。「投稿した」を押すとその瞬間の日時を記録 |
| 投稿の実績 | `/posts/history` | 出し終えた投稿を新しい順に一覧 |

### 使う流れ

1. **商品**を登録する（例：かけるとポン酢）
2. **投稿の型**を作る（例：レシピ型 `【{商品名}でもう一品】\n{特徴}`）
3. **投稿ネタ**を追加する。商品と型を選んで「下書きを作る」を押すと、本文が差し込まれた状態で開く
4. **投稿カレンダー**の「＋」から、ネタを日付と出し先に割り当てる
5. 実際に出したら「投稿した」を押す → **投稿の実績**に残る

---

## 技術構成

- PHP 8.5 / Laravel 12（Livewire スターターキット）
- MySQL 8.4
- Laravel Sail（Docker）
- Tailwind CSS / Blade
- PHPUnit（Feature テスト 61 件）

認証はスターターキットの物をそのまま使い、**データは全てログインしたユーザ本人の物だけが見える**ようにしています（他人のデータは 403）。

---

## データベース設計

5つのテーブルを追加しました。**1対多と多対多の両方**を使っています。

```
users ─┬─< products ─< ideas ─< posts
       │                 │
       │                 └──>< tags （中間テーブル idea_tag ＝多対多）
       │
       └─< templates
```

| テーブル | 主なカラム | 役割 |
|---|---|---|
| `products` | user_id / name / description / image_path | 商品。写真つき |
| `ideas` | user_id / product_id / title / body / status | 投稿ネタ。status＝下書き・予定あり・使用済み |
| `tags` | user_id / name | タグ。ユーザごとに同名は1つ（unique） |
| `idea_tag` | idea_id / tag_id | ネタ ⇄ タグ の多対多をつなぐ中間テーブル |
| `templates` | user_id / name / body | 投稿文のひな形。差し込み記号入り |
| `posts` | user_id / idea_id / platform / scheduled_for / posted_at | 投稿の予定と実績。`posted_at` が null なら未投稿 |

**予定と実績を1つのテーブルで兼ねている**のがポイントです。`posted_at` に日時が入った瞬間、その行は「予定」から「実績」になります。

ネタの `status` は、予定を入れると「予定あり」、投稿したら「使用済み」、予定を消したら「下書き」に自動で戻ります。

---

## 動かし方

前提：Docker Desktop が動いていること。

```bash
git clone <このリポジトリ>
cd netacho

cp .env.example .env

# 依存関係（vendor が無い状態から）
docker run --rm -v "$(pwd)":/opt -w /opt \
  laravelsail/php84-composer:latest composer install

./vendor/bin/sail up -d
./vendor/bin/sail php artisan key:generate
./vendor/bin/sail php artisan migrate
./vendor/bin/sail php artisan storage:link
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

- アプリ … http://localhost:8000
- phpMyAdmin … http://localhost:8081

### ポートについて

同じ PC で講義用アプリ（laratter）も動かしているため、**ぶつからないようにポートをずらしています**。

| 用途 | ポート | `.env` の設定 |
|---|---|---|
| アプリ本体 | 8000 | `APP_PORT=8000` |
| MySQL | 33061 | `FORWARD_DB_PORT=33061` |
| Vite | 5174 | `VITE_PORT=5174` |
| phpMyAdmin | 8081 | `FORWARD_PMA_PORT=8081` |

---

## テスト

```bash
./vendor/bin/sail php artisan test
```

Feature テスト 61 件。内訳：

- `ProductTest`（10件）… CRUD・画像の保存・画像以外は弾く・他人の商品は 403
- `IdeaTest`（10件）… CRUD・タグの作成と使い回し・商品/タグ/言葉での絞り込み・他人の商品は選べない
- `TemplateTest`（4件）… CRUD・**型の差し込みが効いているか**
- `PostTest`（10件）… 予定の登録・「投稿した」の記録と取り消し・週の絞り込み・実績一覧・状態の自動遷移
- 残りはスターターキット付属（認証・プロフィール）

---

## 工夫した点

1. **予定と実績を1つのテーブルにまとめた** … `posted_at` の null / 非 null だけで両方を表せるので、テーブルも画面も増えない
2. **型の差し込みを、JavaScript ではなくサーバ側でやった** … 商品と型を選んで送ると、差し込み済みの本文でフォームが開き直す。講義で習った範囲だけで作れて、動きも追いやすい
3. **タグはカンマ区切りの1行で入力** … `レシピ, あるある` と書くだけで、既にあるタグは使い回し（`firstOrCreate`）、無ければ作って多対多でつなぐ
4. **入力エラーを日本語にした** … `lang/ja/validation.php` を置き、`:attribute` も画面の言葉（商品名・見出しなど）に合わせた
5. **他人のデータは必ず 403** … 一覧はログイン中のユーザから辿り、詳細・編集・削除は持ち主かどうかを毎回チェック

---

## これから足したいこと

- ネタの並べ替え（よく出しているタグ順）
- 実績の集計（今月は X に何本出したか）
- スマホからの追加をもっと速く
