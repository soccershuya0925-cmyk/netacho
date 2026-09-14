<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IdeaController extends Controller
{
    /**
     * 投稿ネタの一覧（商品・タグ・言葉で絞り込める）
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $ideas = $user->ideas()
            ->with(['product', 'tags'])
            // 商品で絞り込む
            ->when($request->filled('product_id'), function ($query) use ($request) {
                $query->where('product_id', $request->input('product_id'));
            })
            // タグで絞り込む（中間テーブルをたどる）
            ->when($request->filled('tag_id'), function ($query) use ($request) {
                $query->whereHas('tags', function ($q) use ($request) {
                    $q->where('tags.id', $request->input('tag_id'));
                });
            })
            // 言葉で探す
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = $request->input('q');
                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', '%'.$keyword.'%')
                        ->orWhere('body', 'like', '%'.$keyword.'%');
                });
            })
            ->latest()
            ->get();

        $products = $user->products()->orderBy('name')->get();
        $tags = $user->tags()->orderBy('name')->get();

        return view('ideas.index', compact('ideas', 'products', 'tags'));
    }

    /**
     * ネタの作成フォーム（型を選んでいれば下書きを差し込んだ状態で開く）
     */
    public function create(Request $request)
    {
        $user = $request->user();

        $products = $user->products()->orderBy('name')->get();
        $templates = $user->templates()->orderBy('name')->get();

        $productId = $request->input('product_id');
        $templateId = $request->input('template_id');

        // 型から下書きを作る：{商品名} {特徴} を選んだ商品の中身に置き換える
        $draft = '';
        if ($templateId) {
            $template = $user->templates()->find($templateId);
            $product = $productId ? $user->products()->find($productId) : null;

            if ($template) {
                $draft = $this->fillTemplate($template->body, $product);
            }
        }

        return view('ideas.create', compact('products', 'templates', 'draft', 'productId', 'templateId'));
    }

    /**
     * ネタを登録する
     */
    public function store(Request $request)
    {
        $validated = $this->validateIdea($request);

        $idea = $request->user()->ideas()->create([
            'product_id' => $validated['product_id'] ?? null,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'status' => $validated['status'],
        ]);

        $this->syncTags($idea, $request->input('tags'));

        return redirect()->route('ideas.show', $idea);
    }

    /**
     * ネタの詳細
     */
    public function show(Idea $idea)
    {
        $this->authorizeOwner($idea);

        $idea->load(['product', 'tags']);

        return view('ideas.show', compact('idea'));
    }

    /**
     * ネタの編集フォーム
     */
    public function edit(Request $request, Idea $idea)
    {
        $this->authorizeOwner($idea);

        $idea->load('tags');
        $products = $request->user()->products()->orderBy('name')->get();

        return view('ideas.edit', compact('idea', 'products'));
    }

    /**
     * ネタを更新する
     */
    public function update(Request $request, Idea $idea)
    {
        $this->authorizeOwner($idea);

        $validated = $this->validateIdea($request);

        $idea->update([
            'product_id' => $validated['product_id'] ?? null,
            'title' => $validated['title'],
            'body' => $validated['body'],
            'status' => $validated['status'],
        ]);

        $this->syncTags($idea, $request->input('tags'));

        return redirect()->route('ideas.show', $idea);
    }

    /**
     * ネタを削除する
     */
    public function destroy(Idea $idea)
    {
        $this->authorizeOwner($idea);

        $idea->delete();

        return redirect()->route('ideas.index');
    }

    /**
     * 入力のチェック（作成と更新で同じものを使う）
     */
    private function validateIdea(Request $request): array
    {
        return $request->validate([
            // 自分の商品しか選べないようにする
            'product_id' => [
                'nullable',
                Rule::exists('products', 'id')->where('user_id', $request->user()->id),
            ],
            'title' => 'required|max:255',
            'body' => 'required|max:5000',
            'status' => 'required|in:下書き,予定あり,使用済み',
            'tags' => 'nullable|max:255',
        ]);
    }

    /**
     * 型の差し込み記号を、選んだ商品の中身に置き換える
     */
    private function fillTemplate(string $body, ?Product $product): string
    {
        return str_replace(
            ['{商品名}', '{特徴}'],
            [$product?->name ?? '', $product?->description ?? ''],
            $body
        );
    }

    /**
     * 「レシピ, あるある」のような文字列をタグに変換して付け直す（多対多）
     */
    private function syncTags(Idea $idea, ?string $tagsText): void
    {
        $names = collect(preg_split('/[,、\s]+/u', (string) $tagsText))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique()
            ->take(10);

        $tagIds = $names->map(function ($name) use ($idea) {
            // 同じ名前のタグが既にあれば使い回す（無ければ作る）
            return $idea->user->tags()->firstOrCreate(['name' => $name])->id;
        });

        $idea->tags()->sync($tagIds);
    }

    /**
     * 他人のネタには触れないようにする
     */
    private function authorizeOwner(Idea $idea): void
    {
        abort_if($idea->user_id !== auth()->id(), 403);
    }
}
