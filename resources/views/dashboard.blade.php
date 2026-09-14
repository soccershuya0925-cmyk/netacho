<x-layouts.app :title="__('ダッシュボード')">
  <div class="p-6">
    <h2 class="mb-4 text-xl font-semibold">今週やること</h2>

    {{-- 数字 --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
      <div class="rounded-lg bg-gray-100 p-4 dark:bg-gray-700">
        <p class="text-sm text-gray-500">今週の予定</p>
        <p class="text-3xl font-bold">{{ $thisWeek->count() }}<span class="ml-1 text-base font-normal">件</span></p>
        <p class="mt-1 text-xs text-gray-500">{{ $start->format('n/j') }}〜{{ $end->format('n/j') }}</p>
      </div>

      <div class="rounded-lg bg-gray-100 p-4 dark:bg-gray-700">
        <p class="text-sm text-gray-500">まだ使っていないネタ</p>
        <p class="text-3xl font-bold">{{ $unusedIdeaCount }}<span class="ml-1 text-base font-normal">件</span></p>
        <p class="mt-1 text-xs">
          <a href="{{ route('ideas.index') }}" class="text-blue-500 hover:underline">ネタ帳を開く</a>
        </p>
      </div>

      <div class="rounded-lg bg-gray-100 p-4 dark:bg-gray-700">
        <p class="text-sm text-gray-500">出し終えた投稿</p>
        <p class="text-3xl font-bold">{{ $thisWeek->whereNotNull('posted_at')->count() }}<span class="ml-1 text-base font-normal">/ {{ $thisWeek->count() }}</span></p>
        <p class="mt-1 text-xs">
          <a href="{{ route('posts.history') }}" class="text-blue-500 hover:underline">実績を見る</a>
        </p>
      </div>
    </div>

    {{-- 今週の予定 --}}
    <div class="mb-6 max-w-2xl">
      <div class="mb-2 flex items-center justify-between">
        <h3 class="font-bold">今週の予定</h3>
        <a href="{{ route('posts.index') }}" class="text-sm text-blue-500 hover:underline">カレンダーで見る</a>
      </div>

      @if ($thisWeek->isEmpty())
        <p class="text-sm text-gray-500">今週の予定はまだありません。カレンダーの「＋」から入れられます。</p>
      @else
        <div class="space-y-2">
          @foreach ($thisWeek as $post)
            <div class="flex flex-wrap items-center gap-3 rounded-lg bg-gray-100 p-3 text-sm dark:bg-gray-700">
              <span class="w-20 shrink-0 font-bold">{{ $post->scheduled_for->format('n/j') }}（{{ ['日','月','火','水','木','金','土'][$post->scheduled_for->dayOfWeek] }}）</span>
              <span class="rounded bg-white px-2 py-0.5 text-xs dark:bg-gray-800">{{ $post->platform }}</span>
              <a href="{{ route('ideas.show', $post->idea) }}" class="flex-1 hover:underline">{{ $post->idea->title }}</a>
              @if ($post->posted_at)
                <span class="text-xs text-green-600 dark:text-green-400">✓ 出した</span>
              @else
                <span class="text-xs text-gray-500">未</span>
              @endif
            </div>
          @endforeach
        </div>
      @endif
    </div>

    {{-- 直近で出した投稿 --}}
    <div class="max-w-2xl">
      <h3 class="mb-2 font-bold">直近で出した投稿</h3>
      @if ($recentPosts->isEmpty())
        <p class="text-sm text-gray-500">まだありません。</p>
      @else
        <ul class="space-y-1 text-sm">
          @foreach ($recentPosts as $post)
            <li class="flex flex-wrap items-center gap-2">
              <span class="text-gray-500">{{ $post->posted_at->format('n/j H:i') }}</span>
              <span class="rounded bg-gray-200 px-2 py-0.5 text-xs dark:bg-gray-700">{{ $post->platform }}</span>
              <a href="{{ route('ideas.show', $post->idea) }}" class="hover:underline">{{ $post->idea->title }}</a>
            </li>
          @endforeach
        </ul>
      @endif
    </div>
  </div>
</x-layouts.app>
