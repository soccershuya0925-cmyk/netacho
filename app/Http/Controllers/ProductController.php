<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * 商品の一覧（自分の商品だけ）
     */
    public function index()
    {
        // withCount('ideas') ＝ ネタの件数も一緒に数えておく（N+1 問題の回避）
        $products = auth()->user()->products()->withCount('ideas')->latest()->get();

        return view('products.index', compact('products'));
    }

    /**
     * 商品の登録フォーム
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * 商品を登録する
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|max:1000',
            'image' => 'nullable|image|max:2048',   // 画像ファイルのみ・2MB まで
        ]);

        // 画像があれば storage/app/public/products に保存し、そのパスを DB に入れる
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = $request->user()->products()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('products.show', $product);
    }

    /**
     * 商品の詳細
     */
    public function show(Product $product)
    {
        $this->authorizeOwner($product);

        return view('products.show', compact('product'));
    }

    /**
     * 商品の編集フォーム
     */
    public function edit(Product $product)
    {
        $this->authorizeOwner($product);

        return view('products.edit', compact('product'));
    }

    /**
     * 商品を更新する
     */
    public function update(Request $request, Product $product)
    {
        $this->authorizeOwner($product);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|max:1000',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ];

        // 新しい画像が来たら、古い画像ファイルを消してから差し替える
        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('products.show', $product);
    }

    /**
     * 商品を削除する
     */
    public function destroy(Product $product)
    {
        $this->authorizeOwner($product);

        // 商品を消す時は画像ファイルも一緒に消す（ゴミを残さない）
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('products.index');
    }

    /**
     * 他人の商品には触れないようにする（自分の物でなければ 403）
     */
    private function authorizeOwner(Product $product): void
    {
        abort_if($product->user_id !== auth()->id(), 403);
    }
}
