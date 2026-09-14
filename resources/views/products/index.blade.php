<x-layouts.app :title="__('商品')">
  <div class="p-6">
    <div class="mb-4 flex items-center justify-between">
      <h2 class="text-xl font-semibold">商品</h2>
      <a href="{{ route('products.create') }}"
         class="rounded bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700">商品を追加</a>
    </div>

    @if ($products->isEmpty())
      <p class="text-gray-500">まだ商品がありません。「商品を追加」から登録してください。</p>
    @else
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($products as $product)
          <a href="{{ route('products.show', $product) }}"
             class="block rounded-lg bg-gray-100 p-4 hover:ring-2 hover:ring-blue-400 dark:bg-gray-700">
            @if ($product->image_path)
              <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}"
                   class="mb-3 h-40 w-full rounded object-cover">
            @endif
            <p class="font-bold">{{ $product->name }}</p>
            @if ($product->description)
              <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                {{ \Illuminate\Support\Str::limit($product->description, 60) }}
              </p>
            @endif
            <p class="mt-2 text-xs text-gray-500">ネタ {{ $product->ideas_count }}件</p>
          </a>
        @endforeach
      </div>
    @endif
  </div>
</x-layouts.app>
