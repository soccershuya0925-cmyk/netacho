<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdeaTest extends TestCase
{
    use RefreshDatabase;

    // ネタを登録できる（タグも一緒に作られる）
    public function test_creates_idea_with_tags(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = $user->products()->create(['name' => 'かけるとポン酢']);

        $this->post(route('ideas.store'), [
            'product_id' => $product->id,
            'title' => '厚揚げ、焼くだけで一品',
            'body' => '焼いた厚揚げにかけるだけ。',
            'status' => '下書き',
            'tags' => 'レシピ, あるある',
        ]);

        $idea = Idea::first();

        $this->assertSame('厚揚げ、焼くだけで一品', $idea->title);
        $this->assertSame($product->id, $idea->product_id);
        $this->assertSame(['あるある', 'レシピ'], $idea->tags->pluck('name')->sort()->values()->all());
        $this->assertDatabaseCount('tags', 2);
    }

    // 同じタグを使い回す（同じ名前のタグが増えない）
    public function test_reuses_existing_tag(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('ideas.store'), [
            'title' => '1本目', 'body' => '本文', 'status' => '下書き', 'tags' => 'レシピ',
        ]);
        $this->post(route('ideas.store'), [
            'title' => '2本目', 'body' => '本文', 'status' => '下書き', 'tags' => 'レシピ',
        ]);

        $this->assertDatabaseCount('ideas', 2);
        $this->assertDatabaseCount('tags', 1);
        $this->assertDatabaseCount('idea_tag', 2);
    }

    // 見出しと本文は必須
    public function test_requires_title_and_body(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('ideas.store'), ['title' => '', 'body' => '', 'status' => '下書き'])
            ->assertSessionHasErrors(['title', 'body']);
    }

    // 他人の商品は選べない
    public function test_cannot_choose_other_users_product(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $otherProduct = $other->products()->create(['name' => 'よその商品']);

        $this->actingAs($me);

        $this->post(route('ideas.store'), [
            'product_id' => $otherProduct->id,
            'title' => '乗っ取り', 'body' => '本文', 'status' => '下書き',
        ])->assertSessionHasErrors('product_id');
    }

    // 商品で絞り込める
    public function test_filters_by_product(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $ponzu = $user->products()->create(['name' => 'ポン酢']);
        $annin = $user->products()->create(['name' => '杏仁タレ']);

        $user->ideas()->create(['product_id' => $ponzu->id, 'title' => 'ポン酢のネタ', 'body' => 'x', 'status' => '下書き']);
        $user->ideas()->create(['product_id' => $annin->id, 'title' => '杏仁のネタ', 'body' => 'x', 'status' => '下書き']);

        $this->get(route('ideas.index', ['product_id' => $ponzu->id]))
            ->assertSee('ポン酢のネタ')
            ->assertDontSee('杏仁のネタ');
    }

    // タグで絞り込める
    public function test_filters_by_tag(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('ideas.store'), ['title' => 'レシピのネタ', 'body' => 'x', 'status' => '下書き', 'tags' => 'レシピ']);
        $this->post(route('ideas.store'), ['title' => 'あるあるのネタ', 'body' => 'x', 'status' => '下書き', 'tags' => 'あるある']);

        $recipeTag = $user->tags()->where('name', 'レシピ')->first();

        $this->get(route('ideas.index', ['tag_id' => $recipeTag->id]))
            ->assertSee('レシピのネタ')
            ->assertDontSee('あるあるのネタ');
    }

    // 言葉で探せる
    public function test_searches_by_keyword(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $user->ideas()->create(['title' => '厚揚げの話', 'body' => 'x', 'status' => '下書き']);
        $user->ideas()->create(['title' => '冷ややっこの話', 'body' => 'y', 'status' => '下書き']);

        $this->get(route('ideas.index', ['q' => '厚揚げ']))
            ->assertSee('厚揚げの話')
            ->assertDontSee('冷ややっこの話');
    }

    // 編集でタグを付け替えられる
    public function test_updates_tags(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('ideas.store'), ['title' => 'ネタ', 'body' => 'x', 'status' => '下書き', 'tags' => 'レシピ']);
        $idea = Idea::first();

        $this->put(route('ideas.update', $idea), [
            'title' => 'ネタ', 'body' => 'x', 'status' => '予定あり', 'tags' => '実績',
        ]);

        $idea->refresh();

        $this->assertSame(['実績'], $idea->tags->pluck('name')->all());
        $this->assertSame('予定あり', $idea->status);
    }

    // 他人のネタは見られない
    public function test_cannot_see_other_users_idea(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $idea = $other->ideas()->create(['title' => 'よそのネタ', 'body' => 'x', 'status' => '下書き']);

        $this->actingAs($me);

        $this->get(route('ideas.show', $idea))->assertStatus(403);
        $this->get(route('ideas.edit', $idea))->assertStatus(403);
        $this->delete(route('ideas.destroy', $idea))->assertStatus(403);
    }

    // ネタを削除できる
    public function test_deletes_idea(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $idea = $user->ideas()->create(['title' => '消すネタ', 'body' => 'x', 'status' => '下書き']);

        $this->delete(route('ideas.destroy', $idea))
            ->assertRedirect(route('ideas.index'));

        $this->assertDatabaseMissing('ideas', ['id' => $idea->id]);
    }
}
