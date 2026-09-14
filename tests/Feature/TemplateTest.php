<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateTest extends TestCase
{
    use RefreshDatabase;

    // 型を登録できる
    public function test_creates_template(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('templates.store'), [
            'name' => 'レシピ型',
            'body' => "{商品名}を使った1品。\n{特徴}",
        ])->assertRedirect(route('templates.index'));

        $this->assertDatabaseHas('templates', ['user_id' => $user->id, 'name' => 'レシピ型']);
    }

    // 型を選ぶと、商品の中身が差し込まれた下書きが本文に入る
    public function test_fills_draft_from_template(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = $user->products()->create([
            'name' => '濃口しょうゆ',
            'description' => 'かけるだけで一品',
        ]);

        $template = $user->templates()->create([
            'name' => 'レシピ型',
            'body' => '{商品名}を使った1品。{特徴}',
        ]);

        $this->get(route('ideas.create', [
            'product_id' => $product->id,
            'template_id' => $template->id,
        ]))
            ->assertStatus(200)
            ->assertSee('濃口しょうゆを使った1品。かけるだけで一品');
    }

    // 他人の型は編集できない
    public function test_cannot_edit_other_users_template(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $template = $other->templates()->create(['name' => 'よその型', 'body' => 'x']);

        $this->actingAs($me);

        $this->get(route('templates.edit', $template))->assertStatus(403);
        $this->put(route('templates.update', $template), ['name' => 'a', 'body' => 'b'])->assertStatus(403);
        $this->delete(route('templates.destroy', $template))->assertStatus(403);
    }

    // 型を削除できる
    public function test_deletes_template(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $template = $user->templates()->create(['name' => '消す型', 'body' => 'x']);

        $this->delete(route('templates.destroy', $template))
            ->assertRedirect(route('templates.index'));

        $this->assertDatabaseMissing('templates', ['id' => $template->id]);
    }
}
