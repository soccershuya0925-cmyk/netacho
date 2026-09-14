<x-layouts.app :title="__('商品を追加')">
  <div class="p-6">
    <h2 class="mb-4 text-xl font-semibold">商品を追加</h2>

    {{-- ファイルを送るので enctype="multipart/form-data" が必須 --}}
    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="max-w-xl">
      @csrf

      <div class="mb-4">
        <label for="name" class="mb-2 block text-sm font-bold">商品名</label>
        <input type="text" name="name" id="name" value="{{ old('name') }}"
               class="w-full rounded border px-3 py-2 dark:bg-gray-700">
        @error('name')
          <span class="text-xs italic text-red-500">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-4">
        <label for="description" class="mb-2 block text-sm font-bold">特徴・売り（任意）</label>
        <textarea name="description" id="description" rows="4"
                  class="w-full rounded border px-3 py-2 dark:bg-gray-700">{{ old('description') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">ここに書いた内容が、投稿の型に差し込まれます。</p>
        @error('description')
          <span class="text-xs italic text-red-500">{{ $message }}</span>
        @enderror
      </div>

      <div class="mb-4">
        <label for="image" class="mb-2 block text-sm font-bold">商品写真（任意・2MBまで）</label>
        <input type="file" name="image" id="image" accept="image/*" class="block w-full text-sm">
        @error('image')
          <span class="text-xs italic text-red-500">{{ $message }}</span>
        @enderror
      </div>

      <div class="flex items-center gap-3">
        <button type="submit" class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">登録する</button>
        <a href="{{ route('products.index') }}" class="text-sm text-gray-500 hover:underline">一覧に戻る</a>
      </div>
    </form>
  </div>
</x-layouts.app>
