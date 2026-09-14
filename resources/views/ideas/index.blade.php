<x-layouts.app :title="__('投稿ネタ')">
  <div class="p-6">
    <div class="mb-4 flex items-center justify-between">
      <h2 class="text-xl font-semibold">投稿ネタ</h2>
      <a href="{{ route('ideas.create') }}"
         class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">ネタを追加</a>
    </div>

    {{-- 絞り込み --}}
    <form method="GET" action="{{ route('ideas.index') }}" class="mb-6 flex flex-wrap items-end gap-3 rounded-lg bg-gray-100 p-4 dark:bg-gray-700">
      <div>
        <label for="product_id" class="mb-1 block text-xs font-bold">商品</label>
        <select name="product_id" id="product_id" class="rounded border px-3 py-2 dark:bg-gray-800">
          <option value="">すべて</option>
          @foreach ($products as $product)
            <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>{{ $product->name }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label for="tag_id" class="mb-1 block text-xs font-bold">タグ</label>
        <select name="tag_id" id="tag_id" class="rounded border px-3 py-2 dark:bg-gray-800">
          <option value="">すべて</option>
          @foreach ($tags as $tag)
            <option value="{{ $tag->id }}" @selected(request('tag_id') == $tag->id)>{{ $tag->name }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label for="q" class="mb-1 block text-xs font-bold">言葉で探す</label>
        <input type="text" name="q" id="q" value="{{ request('q') }}" placeholder="見出し・本文"
               class="rounded border px-3 py-2 dark:bg-gray-800">
      </div>

      <button type="submit" class="rounded bg-gray-700 px-4 py-2 font-bold text-white hover:bg-gray-900 dark:bg-gray-600">絞り込む</button>
      @if (request()->hasAny(['product_id', 'tag_id', 'q']))
        <a href="{{ route('ideas.index') }}" class="text-sm text-gray-500 hover:underline">条件を消す</a>
      @endif
    </form>

    @if ($ideas->isEmpty())
      <p class="text-gray-500">ネタがありません。「ネタを追加」から貯めていってください。</p>
    @else
      <p class="mb-3 text-sm text-gray-500">{{ $ideas->count() }}件</p>
      <div class="space-y-3">
        @foreach ($ideas as $idea)
          <a href="{{ route('ideas.show', $idea) }}"
             class="block rounded-lg bg-gray-100 p-4 hover:ring-2 hover:ring-blue-400 dark:bg-gray-700">
            <div class="flex flex-wrap items-center gap-2">
              <span class="font-bold">{{ $idea->title }}</span>
              <x-idea-status :status="$idea->status" />
              @if ($idea->product)
                <span class="rounded bg-white px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $idea->product->name }}</span>
              @endif
            </div>

            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
              {{ \Illuminate\Support\Str::limit($idea->body, 80) }}
            </p>

            @if ($idea->tags->isNotEmpty())
              <div class="mt-2 flex flex-wrap gap-1">
                @foreach ($idea->tags as $tag)
                  <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-800 dark:bg-blue-900 dark:text-blue-100">#{{ $tag->name }}</span>
                @endforeach
              </div>
            @endif
          </a>
        @endforeach
      </div>
    @endif
  </div>
</x-layouts.app>
