<x-layouts.app :title="__('ネタを追加')">
  <div class="p-6">
    <h2 class="mb-4 text-xl font-semibold">ネタを追加</h2>

    {{-- 型から下書きを作る（商品と型を選ぶと、下の本文に差し込まれた状態で開き直す） --}}
    @if ($templates->isNotEmpty())
      <form method="GET" action="{{ route('ideas.create') }}"
            class="mb-6 max-w-xl rounded-lg bg-yellow-50 p-4 dark:bg-gray-700">
        <p class="mb-2 text-sm font-bold">型から下書きを作る</p>
        <div class="flex flex-wrap items-end gap-3">
          <div>
            <label for="t_product" class="mb-1 block text-xs">商品</label>
            <select name="product_id" id="t_product" class="rounded border px-3 py-2 dark:bg-gray-800">
              <option value="">選ばない</option>
              @foreach ($products as $product)
                <option value="{{ $product->id }}" @selected($productId == $product->id)>{{ $product->name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label for="t_template" class="mb-1 block text-xs">型</label>
            <select name="template_id" id="t_template" class="rounded border px-3 py-2 dark:bg-gray-800">
              <option value="">選ばない</option>
              @foreach ($templates as $template)
                <option value="{{ $template->id }}" @selected($templateId == $template->id)>{{ $template->name }}</option>
              @endforeach
            </select>
          </div>
          <button type="submit" class="rounded bg-yellow-600 px-4 py-2 font-bold text-white hover:bg-yellow-700">下書きを作る</button>
        </div>
      </form>
    @else
      <p class="mb-6 text-sm text-gray-500">
        <a href="{{ route('templates.create') }}" class="text-blue-500 hover:underline">投稿の型</a>を作っておくと、ここから下書きを自動で差し込めます。
      </p>
    @endif

    <form method="POST" action="{{ route('ideas.store') }}" class="max-w-xl">
      @csrf

      <div class="mb-4">
        <label for="title" class="mb-2 block text-sm font-bold">見出し</label>
        <input type="text" name="title" id="title" value="{{ old('title') }}"
               class="w-full rounded border px-3 py-2 dark:bg-gray-700">
        @error('title')
          <span class="text-xs italic text-red-500">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-4">
        <label for="product_id" class="mb-2 block text-sm font-bold">商品（任意）</label>
        <select name="product_id" id="product_id" class="w-full rounded border px-3 py-2 dark:bg-gray-700">
          <option value="">選ばない</option>
          @foreach ($products as $product)
            <option value="{{ $product->id }}" @selected(old('product_id', $productId) == $product->id)>{{ $product->name }}</option>
          @endforeach
        </select>
        @error('product_id')
          <span class="text-xs italic text-red-500">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-4">
        <label for="body" class="mb-2 block text-sm font-bold">投稿文の下書き</label>
        <textarea name="body" id="body" rows="10"
                  class="w-full rounded border px-3 py-2 dark:bg-gray-700">{{ old('body', $draft) }}</textarea>
        @error('body')
          <span class="text-xs italic text-red-500">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-4">
        <label for="tags" class="mb-2 block text-sm font-bold">タグ（任意・カンマ区切り）</label>
        <input type="text" name="tags" id="tags" value="{{ old('tags') }}" placeholder="レシピ, あるある"
               class="w-full rounded border px-3 py-2 dark:bg-gray-700">
        @error('tags')
          <span class="text-xs italic text-red-500">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-4">
        <label for="status" class="mb-2 block text-sm font-bold">状態</label>
        <select name="status" id="status" class="rounded border px-3 py-2 dark:bg-gray-700">
          @foreach (['下書き', '予定あり', '使用済み'] as $status)
            <option value="{{ $status }}" @selected(old('status', '下書き') === $status)>{{ $status }}</option>
          @endforeach
        </select>
        @error('status')
          <span class="text-xs italic text-red-500">{{ $message }}</span>
        @enderror
      </div>

      <div class="flex items-center gap-3">
        <button type="submit" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">登録する</button>
        <a href="{{ route('ideas.index') }}" class="text-sm text-gray-500 hover:underline">一覧に戻る</a>
      </div>
    </form>
  </div>
</x-layouts.app>
