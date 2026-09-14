<x-layouts.app :title="__('投稿の型')">
  <div class="p-6">
    <div class="mb-4 flex items-center justify-between">
      <h2 class="text-xl font-semibold">投稿の型</h2>
      <a href="{{ route('templates.create') }}"
         class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">型を追加</a>
    </div>

    <p class="mb-4 text-sm text-gray-500">
      本文に <code class="rounded bg-gray-200 px-1 dark:bg-gray-700">{商品名}</code>
      <code class="rounded bg-gray-200 px-1 dark:bg-gray-700">{特徴}</code> と書いておくと、
      ネタを作る時に選んだ商品の中身に置き換わります。
    </p>

    @if ($templates->isEmpty())
      <p class="text-gray-500">型がまだありません。</p>
    @else
      <div class="space-y-3">
        @foreach ($templates as $template)
          <div class="rounded-lg bg-gray-100 p-4 dark:bg-gray-700">
            <div class="flex items-center justify-between">
              <p class="font-bold">{{ $template->name }}</p>
              <div class="flex items-center gap-3">
                <a href="{{ route('templates.edit', $template) }}" class="text-sm text-blue-500 hover:underline">編集</a>
                <form method="POST" action="{{ route('templates.destroy', $template) }}"
                      onsubmit="return confirm('この型を削除します。よろしいですか？');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="text-sm text-red-500 hover:underline">削除</button>
                </form>
              </div>
            </div>
            <p class="mt-2 whitespace-pre-line text-sm text-gray-600 dark:text-gray-300">{{ $template->body }}</p>
          </div>
        @endforeach
      </div>
    @endif
  </div>
</x-layouts.app>
