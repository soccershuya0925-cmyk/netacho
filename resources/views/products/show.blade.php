<x-layouts.app :title="$product->name">
  <div class="p-6">
    <div class="mb-4 flex items-center justify-between">
      <h2 class="text-xl font-semibold">{{ $product->name }}</h2>
      <a href="{{ route('products.index') }}" class="text-sm text-gray-500 hover:underline">一覧に戻る</a>
    </div>

    <div class="max-w-xl rounded-lg bg-gray-100 p-4 dark:bg-gray-700">
      @if ($product->image_path)
        <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}"
             class="mb-4 max-w-sm rounded">
      @endif

      @if ($product->description)
        <p class="whitespace-pre-line">{{ $product->description }}</p>
      @else
        <p class="text-gray-500">特徴はまだ書かれていません。</p>
      @endif

      <p class="mt-4 text-sm text-gray-500">
        この商品のネタ：<a href="{{ route('ideas.index', ['product_id' => $product->id]) }}"
                          class="text-blue-500 hover:underline">{{ $product->ideas()->count() }}件</a>
      </p>
      <p class="mt-1 text-xs text-gray-500">登録 {{ $product->created_at->format('Y/m/d H:i') }}／更新 {{ $product->updated_at->format('Y/m/d H:i') }}</p>
    </div>

    <div class="mt-4 flex items-center gap-3">
      <a href="{{ route('products.edit', $product) }}"
         class="rounded bg-yellow-500 px-4 py-2 font-bold text-white hover:bg-yellow-700">編集</a>

      <form method="POST" action="{{ route('products.destroy', $product) }}"
            onsubmit="return confirm('この商品を削除します。よろしいですか？');">
        @csrf
        @method('DELETE')
        <button type="submit" class="rounded bg-red-500 px-4 py-2 font-bold text-white hover:bg-red-700">削除</button>
      </form>
    </div>
  </div>
</x-layouts.app>
