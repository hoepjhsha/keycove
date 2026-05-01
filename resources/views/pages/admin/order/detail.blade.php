<div>
    @section('pageTitle', __('admin.titles.order_details'))

    @push('breadcrumbs')
        <x-partials.dashboard.breadcrumb :items="[
            ['label' => __('admin.nav.orders'), 'url' => 'javascript:void(0)'],
            ['label' => 'Orders History', 'url' => route('admin.orders.index')],
            ['label' => 'Details', 'url' => 'javascript:void(0)'],
        ]" />
    @endpush

    <div class="space-y-6">
        <!-- Order Header Card -->
        <div class="bg-white dark:bg-slate-800 shadow rounded-md">
            <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
                <h4 class="font-medium">Order #{{ $order->order_code }}</h4>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- ID -->
                    <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
                        <dt class="font-medium text-slate-500 dark:text-slate-400 text-sm mb-2">Order ID</dt>
                        <dd class="text-slate-900 dark:text-white font-semibold text-lg">#{{ $order->id }}</dd>
                    </div>

                    <!-- Order Code -->
                    <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
                        <dt class="font-medium text-slate-500 dark:text-slate-400 text-sm mb-2">{{ __('admin.common.order_code') }}</dt>
                        <dd class="text-slate-900 dark:text-white font-semibold text-lg">#{{ $order->order_code }}</dd>
                    </div>

                    <!-- Status -->
                    <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
                        <dt class="font-medium text-slate-500 dark:text-slate-400 text-sm mb-2">{{ __('admin.common.status') }}</dt>
                        <dd class="text-slate-900 dark:text-white">
                            @php
                                $status = $order->status;
                                $labelText = method_exists($status, 'label') ? $status->label() : $status->name;
                                $colorClass = match ($status) {
                                    \App\Enums\OrderStatus::PendingPayment => 'bg-yellow-500/10 text-yellow-500',
                                    \App\Enums\OrderStatus::Processing => 'bg-blue-500/10 text-blue-500',
                                    \App\Enums\OrderStatus::Delivered => 'bg-purple-500/10 text-purple-500',
                                    \App\Enums\OrderStatus::Disputing => 'bg-orange-500/10 text-orange-500',
                                    \App\Enums\OrderStatus::Completed => 'bg-green-500/10 text-green-500',
                                    \App\Enums\OrderStatus::Cancelled => 'bg-red-500/10 text-red-500',
                                    \App\Enums\OrderStatus::Refunded => 'bg-red-500/10 text-red-500',
                                    default => 'bg-gray-500/10 text-gray-500',
                                };
                            @endphp
                            <span class="{{ $colorClass }} text-[11px] font-medium px-2.5 py-0.5 rounded-full">{{ $labelText }}</span>
                        </dd>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
                        <dt class="font-medium text-slate-500 dark:text-slate-400 text-sm mb-2">{{ __('admin.common.payment_method') }}</dt>
                        <dd class="text-slate-900 dark:text-white">
                            @php
                                $method = $order->payment_method;
                                $methodLabel = method_exists($method, 'label') ? $method->label() : $method->name;
                                $methodColorClass = match ($method) {
                                    \App\Enums\PaymentMethod::VNPay => 'bg-indigo-500/10 text-indigo-500',
                                    \App\Enums\PaymentMethod::Stripe => 'bg-blue-500/10 text-blue-500',
                                    default => 'bg-gray-500/10 text-gray-500',
                                };
                            @endphp
                            <span class="{{ $methodColorClass }} text-[11px] font-medium px-2.5 py-0.5 rounded-full">{{ $methodLabel }}</span>
                        </dd>
                    </div>

                    <!-- Payment Status -->
                    <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
                        <dt class="font-medium text-slate-500 dark:text-slate-400 text-sm mb-2">{{ __('admin.common.payment_status') }}</dt>
                        <dd class="text-slate-900 dark:text-white">
                            @php
                                $paymentStatus = $order->payment_status;
                                $paymentStatusLabel = method_exists($paymentStatus, 'label') ? $paymentStatus->label() : $paymentStatus->name;
                                $paymentStatusColorClass = match ($paymentStatus) {
                                    \App\Enums\PaymentStatus::Pending => 'bg-yellow-500/10 text-yellow-500',
                                    \App\Enums\PaymentStatus::Completed => 'bg-green-500/10 text-green-500',
                                    \App\Enums\PaymentStatus::Failed => 'bg-red-500/10 text-red-500',
                                    \App\Enums\PaymentStatus::Cancelled => 'bg-gray-500/10 text-gray-500',
                                    \App\Enums\PaymentStatus::Refunded => 'bg-orange-500/10 text-orange-500',
                                    default => 'bg-gray-500/10 text-gray-500',
                                };
                            @endphp
                            <span class="{{ $paymentStatusColorClass }} text-[11px] font-medium px-2.5 py-0.5 rounded-full">{{ $paymentStatusLabel }}</span>
                        </dd>
                    </div>

                    <!-- Total Price -->
                    <div class="bg-slate-50 dark:bg-slate-900 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
                        <dt class="font-medium text-slate-500 dark:text-slate-400 text-sm mb-2">{{ __('admin.common.total_price') }}</dt>
                        <dd class="text-slate-900 dark:text-white font-semibold text-lg">{{ number_format((float) $order->total_price, 2) }} VND</dd>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Summary Card -->
        <div class="bg-white dark:bg-slate-800 shadow rounded-md">
            <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
                <h4 class="font-medium">{{ __('admin.common.payment_summary') }}</h4>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="font-medium text-slate-500 dark:text-slate-400 text-sm">Payment Transactions</label>
                        <p class="text-slate-900 dark:text-white mt-1">{{ $order->paymentTransactions->count() }}</p>
                    </div>
                    <div>
                        <label class="font-medium text-slate-500 dark:text-slate-400 text-sm">Transaction Record</label>
                        <p class="text-slate-900 dark:text-white mt-1">{{ $order->transaction?->id ? '#'.$order->transaction->id : '-' }}</p>
                    </div>
                    <div>
                        <label class="font-medium text-slate-500 dark:text-slate-400 text-sm">Wallet</label>
                        <p class="text-slate-900 dark:text-white mt-1">{{ $order->transaction?->wallet?->code ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="font-medium text-slate-500 dark:text-slate-400 text-sm">Transaction Amount</label>
                        <p class="text-slate-900 dark:text-white mt-1">{{ $order->transaction?->amount ? number_format((float) $order->transaction->amount, 2).' VND' : '-' }}</p>
                    </div>
                </div>

                @if($order->paymentTransactions->isNotEmpty())
                    <div class="mt-6 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-400">Gateway</th>
                                    <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-400">Gateway Tx ID</th>
                                    <th class="px-4 py-3 text-right font-medium text-slate-600 dark:text-slate-400">Amount</th>
                                    <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-400">{{ __('admin.common.status') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-400">Paid At</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach($order->paymentTransactions as $paymentTransaction)
                                    <tr>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white">{{ $paymentTransaction->gateway?->label() ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white break-all">{{ $paymentTransaction->gateway_transaction_id ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white text-right">{{ number_format((float) $paymentTransaction->amount, 2) }} VND</td>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white">{{ $paymentTransaction->status?->label() ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white">{{ $paymentTransaction->paid_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Buyer Information Card -->
        <div class="bg-white dark:bg-slate-800 shadow rounded-md">
            <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
                <h4 class="font-medium">Buyer Information</h4>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="font-medium text-slate-500 dark:text-slate-400 text-sm">Buyer ID</label>
                        <p class="text-slate-900 dark:text-white mt-1">#{{ $order->buyer_id }}</p>
                    </div>
                    <div>
                        <label class="font-medium text-slate-500 dark:text-slate-400 text-sm">Username</label>
                        <p class="text-slate-900 dark:text-white mt-1">{{ $order->buyer?->username ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="font-medium text-slate-500 dark:text-slate-400 text-sm">Email</label>
                        <p class="text-slate-900 dark:text-white mt-1">{{ $order->buyer?->email ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items Card -->
        <div class="bg-white dark:bg-slate-800 shadow rounded-md overflow-hidden">
            <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
                <h4 class="font-medium">Order Items ({{ $order->items->count() }})</h4>
            </div>
            <div class="p-4">
                @if($order->items->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-400">#</th>
                                    <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-400">Product Name</th>
                                    <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-400">Shop Name</th>
                                    <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-400">{{ __('admin.common.seller') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-400">{{ __('admin.common.status') }}</th>
                                    <th class="px-4 py-3 text-center font-medium text-slate-600 dark:text-slate-400">Quantity</th>
                                    <th class="px-4 py-3 text-right font-medium text-slate-600 dark:text-slate-400">Unit Price</th>
                                    <th class="px-4 py-3 text-right font-medium text-slate-600 dark:text-slate-400">Subtotal</th>
                                    <th class="px-4 py-3 text-center font-medium text-slate-600 dark:text-slate-400">{{ __('admin.common.available_keys') }}</th>
                                    <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-400">Settlement</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                @foreach($order->items as $key => $item)
                                    @php
                                        $listing = $item->listing;
                                        $seller = $item->seller;
                                        $product = $listing?->variant?->product;
                                        $variant = $listing?->variant;
                                        $variantSummary = collect([
                                            $variant?->region?->name,
                                            $variant?->platform?->name,
                                            $variant?->operatingSystem?->name,
                                            $variant?->edition,
                                        ])->filter()->implode(' • ');
                                        $isAdminListing = $seller === null;
                                        $itemStatus = $item->status;
                                        $itemLabel = method_exists($itemStatus, 'label') ? $itemStatus->label() : $itemStatus->name;
                                        $itemColorClass = match ($itemStatus) {
                                            \App\Enums\OrderStatus::PendingPayment => 'bg-yellow-500/10 text-yellow-500',
                                            \App\Enums\OrderStatus::Processing => 'bg-blue-500/10 text-blue-500',
                                            \App\Enums\OrderStatus::Delivered => 'bg-purple-500/10 text-purple-500',
                                            \App\Enums\OrderStatus::Disputing => 'bg-orange-500/10 text-orange-500',
                                            \App\Enums\OrderStatus::Completed => 'bg-green-500/10 text-green-500',
                                            \App\Enums\OrderStatus::Cancelled => 'bg-red-500/10 text-red-500',
                                            \App\Enums\OrderStatus::Refunded => 'bg-red-500/10 text-red-500',
                                            default => 'bg-gray-500/10 text-gray-500',
                                        };
                                    @endphp
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors">
                                        <td class="px-4 py-3 text-slate-900 dark:text-white font-semibold">{{ $key + 1 }}</td>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white">
                                            <div class="space-y-1">
                                                <div class="font-medium">{{ $item->product_name_snapshot ?? '-' }}</div>
                                                @if($product)
                                                    <a href="{{ route('admin.products.detail', $product->id) }}" class="inline-flex items-center text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                                                        <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i>
                                                        View product
                                                    </a>
                                                @endif
                                                <div class="flex flex-wrap gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                                                    @if($listing)
                                                        <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5">Listing #{{ $listing->id }}</span>
                                                    @endif
                                                    @if($variant)
                                                        <span class="rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5">Variant #{{ $variant->id }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white">
                                            <div class="space-y-1">
                                                <span class="bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs px-2 py-1 rounded inline-flex">{{ $seller?->shop_name ?? 'Shop Admin' }}</span>
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ $variantSummary ?: 'No variant metadata' }}</div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white">
                                            <div class="space-y-1">
                                                <div>{{ $seller?->user?->username ?? 'Shop Admin' }}</div>
                                                @if($listing)
                                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Listing ID #{{ $listing->id }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white">
                                            <span class="{{ $itemColorClass }} text-[11px] font-medium px-2.5 py-0.5 rounded-full">{{ $itemLabel }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white text-center">{{ $item->quantity }}</td>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white text-right">{{ number_format((float) $item->unit_price, 2) }} VND</td>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white text-right font-semibold">{{ number_format((float) $item->subtotal, 2) }} VND</td>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white text-center">
                                            <div class="space-y-1">
                                                <div>{{ $item->keys->count() }}</div>
                                                <div class="text-[11px] text-slate-500 dark:text-slate-400">Key(s)</div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-900 dark:text-white">
                                            @if($isAdminListing)
                                                <span class="text-xs text-slate-500 dark:text-slate-400">Shop admin listing</span>
                                            @else
                                                <div class="space-y-1 text-xs text-slate-600 dark:text-slate-400">
                                                    <div><span class="font-medium">Platform Fee:</span> {{ number_format((float) $item->platform_fee, 2) }} VND</div>
                                                    <div><span class="font-medium">Seller Amount:</span> {{ number_format((float) $item->seller_amount, 2) }} VND</div>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-50 dark:bg-slate-900 border-t-2 border-slate-300 dark:border-slate-700">
                                <tr>
                                    <td colspan="9" class="px-4 py-3 text-right font-semibold text-slate-600 dark:text-slate-400">Total:</td>
                                    <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white text-lg">{{ number_format((float) $order->total_price, 2) }} VND</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fa-regular fa-inbox text-4xl text-slate-300 dark:text-slate-600 mb-3"></i>
                        <p class="text-slate-500 dark:text-slate-400">No items found in this order</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Timestamps Card -->
        <div class="bg-white dark:bg-slate-800 shadow rounded-md">
            <div class="border-b border-dashed border-slate-200 dark:border-slate-700 py-3 px-4 dark:text-slate-300/70">
                <h4 class="font-medium">Timestamps</h4>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="font-medium text-slate-500 dark:text-slate-400 text-sm">Created At</label>
                        <p class="text-slate-900 dark:text-white mt-1">{{ $order->created_at->format('d/m/Y H:i:s') }}</p>
                    </div>
                    <div>
                        <label class="font-medium text-slate-500 dark:text-slate-400 text-sm">Updated At</label>
                        <p class="text-slate-900 dark:text-white mt-1">{{ $order->updated_at->format('d/m/Y H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="flex justify-start">
            <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-50 transition-colors dark:bg-slate-800 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Orders
            </a>
        </div>
    </div>
</div>
