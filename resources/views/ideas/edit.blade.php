<x-layouts.app :title="__('ネタを編集')">
  <div class="p-6">
    <h2 class="mb-4 text-xl font-semibold">ネタを編集</h2>

    <form method="POST" action="{{ route('ideas.update', $idea) }}" class="max-w-xl">
      @csrf
      @method('PUT')

      <div class="mb-4">
        <label for="title" class="mb-2 block text-sm font-bold">見出し</label>
        <input type="text" name="title" id="title" value="{{ old('title', $idea->title) }}"
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
            <option value="{{ $product->id }}" @selected(old('product_id', $idea->product_id) == $product->id)>{{ $product->name }}</option>
          @endforeach
        </select>
        @error('product_id')
          <span class="text-xs italic text-red-500">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-4">
        <label for="body" class="mb-2 block text-sm font-bold">投稿文の下書き</label>
        <textarea name="body" id="body" rows="10"
                  class="w-full rounded border px-3 py-2 dark:bg-gray-700">{{ old('body', $idea->body) }}</textarea>
        @error('body')
          <span class="text-xs italic text-red-500">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-4">
        <label for="tags" class="mb-2 block text-sm font-bold">タグ（任意・カンマ区切り）</label>
        <input type="text" name="tags" id="tags"
               value="{{ old('tags', $idea->tags->pluck('name')->implode(', ')) }}"
               class="w-full rounded border px-3 py-2 dark:bg-gray-700">
        @error('tags')
          <span class="text-xs italic text-red-500">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-4">
        <label for="status" class="mb-2 block text-sm font-bold">状態</label>
        <select name="status" id="status" class="rounded border px-3 py-2 dark:bg-gray-700">
          @foreach (['下書き', '予定あり', '使用済み'] as $status)
            <option value="{{ $status }}" @selected(old('status', $idea->status) === $status)>{{ $status }}</option>
          @endforeach
        </select>
        @error('status')
          <span class="text-xs italic text-red-500">{{ $message }}</span>
        @enderror
      </div>

      <div class="flex items-center gap-3">
        <button type="submit" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">更新する</button>
        <a href="{{ route('ideas.show', $idea) }}" class="text-sm text-gray-500 hover:underline">やめる</a>
      </div>
    </form>
  </div>
</x-layouts.app>
