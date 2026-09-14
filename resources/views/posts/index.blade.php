<x-layouts.app :title="__('投稿カレンダー')">
  <div class="p-6">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
      <h2 class="text-xl font-semibold">投稿カレンダー</h2>
      <a href="{{ route('posts.history') }}" class="text-sm text-blue-500 hover:underline">投稿の実績を見る</a>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-3">
      <a href="{{ route('posts.index', ['week' => $start->copy()->subWeek()->toDateString()]) }}"
         class="rounded bg-gray-200 px-3 py-1 text-sm hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600">← 前の週</a>
      <span class="text-sm font-bold">{{ $start->format('Y/m/d') }} 〜 {{ $end->format('m/d') }}</span>
      <a href="{{ route('posts.index', ['week' => $start->copy()->addWeek()->toDateString()]) }}"
         class="rounded bg-gray-200 px-3 py-1 text-sm hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600">次の週 →</a>
      <a href="{{ route('posts.index') }}" class="text-sm text-gray-500 hover:underline">今週へ</a>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-7">
      @foreach ($days as $day)
        @php
          $key = $day->toDateString();
          $dayPosts = $postsByDate[$key] ?? collect();
          $isToday = $day->isToday();
        @endphp

        <div class="min-h-40 rounded-lg border p-2 {{ $isToday ? 'border-blue-400 bg-blue-50 dark:bg-gray-700' : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800' }}">
          <p class="mb-2 text-xs font-bold {{ $isToday ? 'text-blue-700 dark:text-blue-300' : 'text-gray-500' }}">
            {{ $day->format('n/j') }}（{{ ['日','月','火','水','木','金','土'][$day->dayOfWeek] }}）
          </p>

          @foreach ($dayPosts as $post)
            <div class="mb-2 rounded bg-white p-2 text-xs dark:bg-gray-700">
              <p class="font-bold">{{ $post->platform }}</p>
              <a href="{{ route('ideas.show', $post->idea) }}" class="block hover:underline">
                {{ \Illuminate\Support\Str::limit($post->idea->title, 20) }}
              </a>

              @if ($post->posted_at)
                <p class="mt-1 text-green-600 dark:text-green-400">✓ {{ $post->posted_at->format('n/j H:i') }}</p>
                <form method="POST" action="{{ route('posts.unposted', $post) }}" class="mt-1">
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="text-gray-500 hover:underline">取り消す</button>
                </form>
              @else
                <form method="POST" action="{{ route('posts.posted', $post) }}" class="mt-1">
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="rounded bg-green-500 px-2 py-0.5 text-white hover:bg-green-700">投稿した</button>
                </form>
              @endif

              <form method="POST" action="{{ route('posts.destroy', $post) }}" class="mt-1"
                    onsubmit="return confirm('この予定を消します。よろしいですか？');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-500 hover:underline">予定を消す</button>
              </form>
            </div>
          @endforeach

          <a href="{{ route('posts.create', ['date' => $key]) }}"
             class="block rounded border border-dashed border-gray-300 py-1 text-center text-xs text-gray-500 hover:border-blue-400 hover:text-blue-500 dark:border-gray-600">＋</a>
        </div>
      @endforeach
    </div>
  </div>
</x-layouts.app>
