<x-layouts.app :title="__('投稿の実績')">
  <div class="p-6">
    <div class="mb-4 flex items-center justify-between">
      <h2 class="text-xl font-semibold">投稿の実績</h2>
      <a href="{{ route('posts.index') }}" class="text-sm text-blue-500 hover:underline">カレンダーに戻る</a>
    </div>

    @if ($posts->isEmpty())
      <p class="text-gray-500">まだ「投稿した」を押した記録がありません。</p>
    @else
      <p class="mb-3 text-sm text-gray-500">{{ $posts->count() }}件</p>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="border-b border-gray-300 dark:border-gray-600">
            <tr>
              <th class="py-2 pr-4">出した日時</th>
              <th class="py-2 pr-4">出す先</th>
              <th class="py-2 pr-4">商品</th>
              <th class="py-2">ネタ</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($posts as $post)
              <tr class="border-b border-gray-200 dark:border-gray-700">
                <td class="py-2 pr-4 whitespace-nowrap">{{ $post->posted_at->format('Y/m/d H:i') }}</td>
                <td class="py-2 pr-4 whitespace-nowrap">{{ $post->platform }}</td>
                <td class="py-2 pr-4 whitespace-nowrap">{{ $post->idea->product?->name ?? '—' }}</td>
                <td class="py-2">
                  <a href="{{ route('ideas.show', $post->idea) }}" class="text-blue-500 hover:underline">{{ $post->idea->title }}</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</x-layouts.app>
