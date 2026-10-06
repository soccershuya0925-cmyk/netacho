<?php

namespace Database\Seeders;

use App\Models\Idea;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * 動作確認・画面収録用のサンプルデータ（商品名はすべて架空）
 *
 * ./vendor/bin/sail php artisan migrate:fresh --seed
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 商品
        $shoyu = $user->products()->create([
            'name' => '濃口しょうゆ',
            'description' => '昔ながらの木桶で1年寝かせた、香りの立つしょうゆ。卵かけごはんに合う。',
        ]);
        $goma = $user->products()->create([
            'name' => 'ごまだれ',
            'description' => '煎りたてのごまをすりつぶした濃いめのたれ。しゃぶしゃぶにも冷やし中華にも。',
        ]);
        $miso = $user->products()->create([
            'name' => '甘口みそ',
            'description' => '麦こうじをたっぷり使った甘めのみそ。みそ汁のほか、炒め物の味付けにも。',
        ]);

        // 投稿の型
        $user->templates()->create([
            'name' => 'レシピ型',
            'body' => "【{商品名}でもう一品】\n\n{特徴}\n\n今日の晩ごはんにどうですか。",
        ]);
        $user->templates()->create([
            'name' => '紹介型',
            'body' => "{商品名}のご紹介です。\n\n{特徴}\n\n気になった方はプロフィールのリンクからどうぞ。",
        ]);

        // タグ
        $tags = collect(['レシピ', 'あるある', '季節', '作り手の話'])
            ->mapWithKeys(fn ($name) => [$name => $user->tags()->firstOrCreate(['name' => $name])]);

        // 投稿ネタ
        $ideas = [
            [$shoyu, '卵かけごはんの日', "【濃口しょうゆでもう一品】\n\n卵かけごはんは、しょうゆを先に卵に混ぜると全体に味が回ります。", ['レシピ']],
            [$goma, '冷やし中華のたれを手作り', "ごまだれ大さじ2に酢を少し足すだけで、冷やし中華のたれになります。", ['レシピ', '季節']],
            [$miso, 'みそ汁以外の使い道', "甘口みそは炒め物にも使えます。豚肉とキャベツを炒めて、最後にみそを溶かすだけ。", ['レシピ']],
            [null, '作りたくない夜に', "今日はもう何もしたくない。そんな夜は、ごはんにかけるだけで1品になる物に頼ります。", ['あるある']],
            [$shoyu, '仕込みの日の話', "しょうゆを仕込む日は朝が早い。木桶の香りが工場の外まで届きます。", ['作り手の話']],
            [$goma, 'ごまをする音', "ごまだれを作る日、工場の中はずっとごまの香り。", ['作り手の話', 'あるある']],
        ];

        $created = [];
        foreach ($ideas as [$product, $title, $body, $tagNames]) {
            $idea = $user->ideas()->create([
                'product_id' => $product?->id,
                'title' => $title,
                'body' => $body,
                'status' => '下書き',
            ]);
            $idea->tags()->attach($tags->only($tagNames)->pluck('id'));
            $created[] = $idea;
        }

        // 投稿カレンダー（今週に予定3件、出し終えた投稿1件）
        $monday = Carbon::today()->startOfWeek();

        $this->schedule($user, $created[0], 'Instagram', $monday->copy(), $monday->copy()->setTime(19, 5));
        $this->schedule($user, $created[1], 'X', $monday->copy()->addDays(2));
        $this->schedule($user, $created[3], 'X', $monday->copy()->addDays(4));
        $this->schedule($user, $created[4], 'note', $monday->copy()->addDays(5));
    }

    private function schedule(User $user, Idea $idea, string $platform, Carbon $date, ?Carbon $postedAt = null): void
    {
        $user->posts()->create([
            'idea_id' => $idea->id,
            'platform' => $platform,
            'scheduled_for' => $date->toDateString(),
            'posted_at' => $postedAt,
        ]);

        $idea->update(['status' => $postedAt ? '使用済み' : '予定あり']);
    }
}
