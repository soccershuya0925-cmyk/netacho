<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    // テストごとにデータベースを元に戻す
    use RefreshDatabase;

    // 未ログインだと商品一覧は見られない（ログイン画面へ）
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('products.index'))->assertRedirect(route('login'));
    }

    // ログインすれば自分の商品の一覧が見られる
    public function test_displays_own_products(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $user->products()->create(['name' => 'かけるとポン酢']);

        $this->get(route('products.index'))
            ->assertStatus(200)
            ->assertSee('かけるとポン酢');
    }

    // 他人の商品は一覧に出てこない
    public function test_does_not_display_other_users_products(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $other->products()->create(['name' => 'よその商品']);

        $this->actingAs($me);

        $this->get(route('products.index'))
            ->assertStatus(200)
            ->assertDontSee('よその商品');
    }

    // 商品を登録できる
    public function test_creates_product(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('products.store'), [
            'name' => '杏仁タレ',
            'description' => 'かけるだけで杏仁豆腐になるタレ',
        ]);

        $product = Product::first();
        $response->assertRedirect(route('products.show', $product));

        $this->assertDatabaseHas('products', [
            'user_id' => $user->id,
            'name' => '杏仁タレ',
            'description' => 'かけるだけで杏仁豆腐になるタレ',
        ]);
    }

    // 商品名が空だとエラーになる
    public function test_requires_name(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('products.store'), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('products', 0);
    }

    // 商品を更新できる
    public function test_updates_product(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = $user->products()->create(['name' => '古い名前']);

        $this->put(route('products.update', $product), [
            'name' => '新しい名前',
            'description' => '書き直した特徴',
        ])->assertRedirect(route('products.show', $product));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => '新しい名前',
            'description' => '書き直した特徴',
        ]);
    }

    // 商品を削除できる
    public function test_deletes_product(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = $user->products()->create(['name' => '消す商品']);

        $this->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    // 他人の商品は詳細も編集もできない（403）
    public function test_cannot_touch_other_users_product(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();
        $product = $other->products()->create(['name' => 'よその商品']);

        $this->actingAs($me);

        $this->get(route('products.show', $product))->assertStatus(403);
        $this->get(route('products.edit', $product))->assertStatus(403);
        $this->put(route('products.update', $product), ['name' => '乗っ取り'])->assertStatus(403);
        $this->delete(route('products.destroy', $product))->assertStatus(403);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'よその商品']);
    }

    // 写真つきで登録すると、ファイルが保存されパスが DB に入る
    public function test_stores_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('products.store'), [
            'name' => '写真つき商品',
            'image' => UploadedFile::fake()->image('product.jpg'),
        ]);

        $product = Product::first();

        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    // 画像以外のファイルは受け付けない
    public function test_rejects_non_image_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('products.store'), [
            'name' => 'PDFを送る商品',
            'image' => UploadedFile::fake()->create('memo.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('image');

        $this->assertDatabaseCount('products', 0);
    }
}
