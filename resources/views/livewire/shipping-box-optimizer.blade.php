<div x-data="{ focusOnLength() { this.$refs.length.focus() } }" @add-item.window="focusOnLength" class="space-y-6">
    <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Shipping Box Optimizer</h1>

    <div class="bg-white p-6 rounded-lg shadow-lg mt-6">
        <h2 class="text-xl font-semibold mb-4">Predefined Boxes</h2>
        <div id="predefinedBoxes" class="space-y-2">
            <ul class="list-disc pl-6 text-gray-700">
                @foreach ($predefinedBoxes as $index => $box)
                    <li>{{ $box['name'] }}: {{ $box['length'] }}x{{ $box['width'] }}x{{ $box['height'] }} (Weight Limit: {{ $box['weight_limit'] }} kg)</li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Add Item</h2>
        <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-8">
            <div>
                <label for="length" class="block text-sm font-medium text-gray-700">Length</label>
                <div class="mt-1">
                    <input type="number" wire:model="length" id="length" x-ref="length" class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('length') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="width" class="block text-sm font-medium text-gray-700">Width</label>
                <div class="mt-1">
                    <input type="number" wire:model="width" id="width" class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('width') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="height" class="block text-sm font-medium text-gray-700">Height</label>
                <div class="mt-1">
                    <input type="number" wire:model="height" id="height" class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('height') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="weight" class="block text-sm font-medium text-gray-700">Weight</label>
                <div class="mt-1">
                    <input type="number" wire:model="weight" id="weight" class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('weight') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                <div class="mt-1">
                    <input type="number" wire:model="quantity" id="quantity" class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    @error('quantity') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="mt-6">
            <button wire:click="addItem" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Add Item
            </button>
        </div>
        <p class="text-gray-500 mt-4">You can add up to 10 items.</p>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Items Added</h2>
        <ul role="list" class="divide-y divide-gray-100">
            @foreach($items as $index => $item)
                <li class="flex justify-between gap-x-6 py-5">
                    <div class="flex min-w-0 gap-x-4">
                        <img class="h-12 w-12 flex-none rounded-full bg-gray-50" src="{{asset('images/new_3180183.png')}}" alt="">
                        <div class="min-w-0 flex-auto">
                            <p class="text-sm font-semibold leading-6 text-gray-900">Item {{ $index + 1 }}</p>
                            <p class="mt-1 truncate text-md leading-5 text-gray-500">{{ $item['length'] }}x{{ $item['width'] }}x{{ $item['height'] }}, {{ $item['weight'] }}kg</p>
                        </div>
                    </div>
                    <div class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                        <p class="text-sm leading-6 text-gray-900">Quantity: {{ $item['quantity'] }}</p>
                        <button wire:click="removeItem({{ $index }})" class="mt-2 inline-flex items-center justify-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Remove
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="mt-6">
        <button wire:click="process" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            Calculate Optimal Boxes
        </button>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-lg mt-6">
        <h2 class="text-xl font-semibold mb-4">Optimal Boxes</h2>
        <ul role="list" class="grid grid-cols-1 gap-x-6 gap-y-8 lg:grid-cols-3 xl:gap-x-8">
            @foreach($boxAssignments as $assignment)
                <li class="overflow-hidden rounded-xl border border-gray-200">
                    <div class="flex items-center gap-x-4 border-b border-gray-900/5 bg-gray-50 p-6">
                        <img src="{{asset('images/box_679821.png')}}" alt="Box Image" class="h-12 w-12 flex-none rounded-lg bg-white object-cover ring-1 ring-gray-900/10">
                        <div class="text-sm font-medium leading-6 text-gray-900">{{ $assignment['box']['name'] }}</div>
                    </div>
                    <dl class="-my-3 divide-y divide-gray-100 px-6 py-4 text-sm leading-6">
                        <div class="flex justify-between gap-x-4 py-3">
                            <dt class="text-gray-500">Dimensions</dt>
                            <dd class="text-gray-700">{{ $assignment['box']['length'] }}x{{ $assignment['box']['width'] }}x{{ $assignment['box']['height'] }}</dd>
                        </div>
                        <div class="flex justify-between gap-x-4 py-3">
                            <dt class="text-gray-500">Weight Limit</dt>
                            <dd class="text-gray-700">{{ $assignment['box']['weight_limit'] }} kg</dd>
                        </div>
                        <div class="flex justify-between gap-x-4 py-3">
                            <dt class="text-gray-500">Products</dt>
                            <dd class="text-gray-700">
                                <ul class="list-disc pl-5">
                                    @foreach($assignment['products'] as $index => $product)
                                        <li>Product {{ $index + 1 }}: {{ $product['length'] }}x{{ $product['width'] }}x{{ $product['height'] }}, {{ $product['weight'] }}kg</li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>
                    </dl>
                </li>
            @endforeach
            @if(count($unfitProducts) > 0)
                <li class="overflow-hidden rounded-xl border border-red-500">
                    <div class="flex items-center gap-x-4 border-b border-red-900/5 bg-red-50 p-6">
                        <img src="{{asset('images/box_679821.png')}}" alt="Unfit Product" class="h-12 w-12 flex-none rounded-lg bg-white object-cover ring-1 ring-red-900/10">
                        <div class="text-sm font-medium leading-6 text-red-900">Unfit Products</div>
                    </div>
                    <dl class="-my-3 divide-y divide-gray-100 px-6 py-4 text-sm leading-6">
                        {{-- @foreach($unfitProducts as $product) --}}
                            <div class="flex justify-between gap-x-4 py-3">
                                <dt class="text-gray-500">Dimensions</dt>
                                <dd class="text-gray-700">N/A</dd>
                            </div>
                            <div class="flex justify-between gap-x-4 py-3">
                                <dt class="text-gray-500">Weight</dt>
                                <dd class="text-gray-700">N/A</dd>
                            </div>
                            <div class="flex justify-between gap-x-4 py-3">
                                <dt class="text-gray-500">Products</dt>
                                <dd class="text-gray-700">
                                    <ul class="list-disc pl-5">
                                        @foreach($unfitProducts as $index => $product)
                                            <li>Product {{ $index + 1 }}: {{ $product['length'] }}x{{ $product['width'] }}x{{ $product['height'] }}, {{ $product['weight'] }}kg</li>
                                        @endforeach
                                    </ul>
                                </dd>
                            </div>
                        {{-- @endforeach --}}
                    </dl>
                </li>
            @endif
        </ul>
    </div>

    <script>
        document.addEventListener('alert', event => {
            alert(event.detail[0].message);
        });
    </script>
</div>
