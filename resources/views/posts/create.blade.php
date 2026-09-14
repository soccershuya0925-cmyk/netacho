<x-layouts.app :title="__('予定を入れる')">
  <div class="p-6">
    <h2 class="mb-4 text-xl font-semibold">予定を入れる</h2>

    @if ($ideas->isEmpty())
      <p class="text-gray-500">
        先に<a href="{{ route('ideas.create') }}" class="text-blue-500 hover:underline">投稿ネタ</a>を作ってください。
      </p>
    @else
      <form method="POST" action="{{ route('posts.store') }}" class="max-w-xl">
        @csrf

        <div class="mb-4">
          <label for="scheduled_for" class="mb-2 block text-sm font-bold">出す予定の日</label>
          <input type="date" name="scheduled_for" id="scheduled_for" value="{{ old('scheduled_for', $date) }}"
                 class="rounded border px-3 py-2 dark:bg-gray-700">
          @error('scheduled_for')
            <span class="text-xs italic text-red-500">{{ $message }}</span>
          @enderror
        </div>

        <div class="mb-4">
          <label for="idea_id" class="mb-2 block text-sm font-bold">出すネタ</label>
          <select name="idea_id" id="idea_id" class="w-full rounded border px-3 py-2 dark:bg-gray-700">
            @foreach ($ideas as $idea)
              <option value="{{ $idea->id }}" @selected(old('idea_id') == $idea->id)>
                [{{ $idea->status }}]{{ $idea->product ? ' '.$idea->product->name.' /' : '' }} {{ $idea->title }}
              </option>
            @endforeach
          </select>
          @error('idea_id')
            <span class="text-xs italic text-red-500">{{ $message }}</span>
          @enderror
        </div>

        <div class="mb-4">
          <label for="platform" class="mb-2 block text-sm font-bold">出す先</label>
          <select name="platform" id="platform" class="rounded border px-3 py-2 dark:bg-gray-700">
            @foreach (\App\Models\Post::PLATFORMS as $platform)
              <option value="{{ $platform }}" @selected(old('platform') === $platform)>{{ $platform }}</option>
            @endforeach
          </select>
          @error('platform')
            <span class="text-xs italic text-red-500">{{ $message }}</span>
          @enderror
        </div>

        <div class="flex items-center gap-3">
          <button type="submit" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">予定に入れる</button>
          <a href="{{ route('posts.index') }}" class="text-sm text-gray-500 hover:underline">カレンダーに戻る</a>
        </div>
      </form>
    @endif
  </div>
</x-layouts.app>
