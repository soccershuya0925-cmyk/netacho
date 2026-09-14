<x-layouts.app :title="$idea->title">
  <div class="p-6">
    <div class="mb-4 flex items-center justify-between">
      <h2 class="text-xl font-semibold">{{ $idea->title }}</h2>
      <a href="{{ route('ideas.index') }}" class="text-sm text-gray-500 hover:underline">一覧に戻る</a>
    </div>

    <div class="max-w-2xl rounded-lg bg-gray-100 p-4 dark:bg-gray-700">
      <div class="mb-3 flex flex-wrap items-center gap-2">
        <x-idea-status :status="$idea->status" />
        @if ($idea->product)
          <a href="{{ route('products.show', $idea->product) }}"
             class="rounded bg-white px-2 py-0.5 text-xs text-gray-600 hover:underline dark:bg-gray-800 dark:text-gray-300">{{ $idea->product->name }}</a>
        @endif
        @foreach ($idea->tags as $tag)
          <a href="{{ route('ideas.index', ['tag_id' => $tag->id]) }}"
             class="rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-800 hover:underline dark:bg-blue-900 dark:text-blue-100">#{{ $tag->name }}</a>
        @endforeach
      </div>

      <p class="whitespace-pre-line">{{ $idea->body }}</p>

      <p class="mt-4 text-xs text-gray-500">
        登録 {{ $idea->created_at->format('Y/m/d H:i') }}／更新 {{ $idea->updated_at->format('Y/m/d H:i') }}
      </p>
    </div>

    <div class="mt-4 flex items-center gap-3">
      <a href="{{ route('ideas.edit', $idea) }}"
         class="rounded bg-yellow-500 px-4 py-2 font-bold text-white hover:bg-yellow-700">編集</a>

      <form method="POST" action="{{ route('ideas.destroy', $idea) }}"
            onsubmit="return confirm('このネタを削除します。よろしいですか？');">
        @csrf
        @method('DELETE')
        <button type="submit" class="rounded bg-red-500 px-4 py-2 font-bold text-white hover:bg-red-700">削除</button>
      </form>
    </div>
  </div>
</x-layouts.app>
