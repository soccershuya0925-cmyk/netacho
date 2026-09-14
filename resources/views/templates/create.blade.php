<x-layouts.app :title="__('型を追加')">
  <div class="p-6">
    <h2 class="mb-4 text-xl font-semibold">型を追加</h2>

    <form method="POST" action="{{ route('templates.store') }}" class="max-w-xl">
      @csrf
      

      <div class="mb-4">
        <label for="name" class="mb-2 block text-sm font-bold">型の名前</label>
        <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="レシピ型"
               class="w-full rounded border px-3 py-2 dark:bg-gray-700">
        @error('name')
          <span class="text-xs italic text-red-500">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-4">
        <label for="body" class="mb-2 block text-sm font-bold">ひな形の本文</label>
        <textarea name="body" id="body" rows="10"
                  class="w-full rounded border px-3 py-2 dark:bg-gray-700">{{ old('body') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">
          {商品名} と {特徴} は、ネタを作る時に選んだ商品の中身に置き換わります。
        </p>
        @error('body')
          <span class="text-xs italic text-red-500">{{ $message }}</span>
        @enderror
      </div>

      <div class="flex items-center gap-3">
        <button type="submit" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">登録する</button>
        <a href="{{ route('templates.index') }}" class="text-sm text-gray-500 hover:underline">一覧に戻る</a>
      </div>
    </form>
  </div>
</x-layouts.app>
