<div class="relative overflow-hidden px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-80 bg-gradient-to-b from-[#F6EBD9] via-[#FCF9F4] to-transparent dark:from-gray-900 dark:via-gray-950"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -top-10 right-0 -z-10 h-64 w-64 rounded-full bg-[#D32F2F]/8 blur-3xl dark:bg-[#D32F2F]/10"></div>

    <div class="mx-auto max-w-6xl space-y-6" x-data="{ checkoutConfirmOpen: false }">
        <section class="rounded-[2rem] border border-black/8 bg-white/90 p-6 shadow-[0_24px_80px_-40px_rgba(0,0,0,0.35)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
            <nav aria-label="Breadcrumb">
                <ol class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <li><a href="{{ route('app.shop.index') }}" class="transition-colors hover:text-[#D32F2F] dark:hover:text-[#ff8b8b]"><i class="fa-solid fa-house"></i></a></li>
                    <li><i class="fa-solid fa-chevron-right text-[10px]"></i></li>
                    <li><span class="transition-colors hover:text-[#D32F2F] dark:hover:text-[#ff8b8b]">Giỏ hàng</span></li>
                    <li><i class="fa-solid fa-chevron-right text-[10px]"></i></li>
                    <li class="font-semibold text-gray-800 dark:text-gray-200" aria-current="page">Thanh toán</li>
                </ol>
            </nav>

            <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="inline-flex w-fit items-center gap-2 rounded-full border border-[#D32F2F]/15 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] shadow-sm dark:border-[#D32F2F]/20 dark:bg-white/5 dark:text-[#ff9c9c]">
                        Kiểm tra thanh toán
                    </p>
                    <h1 class="mt-3 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">Kiểm tra đơn hàng</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">Kiểm tra sản phẩm đã chọn, tổng tiền và thông tin thanh toán trước khi xác nhận.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                    <div class="rounded-3xl border border-black/8 bg-[#FCF9F4] px-4 py-4 dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Sản phẩm đã chọn</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ $selectedCount }}</p>
                    </div>
                    <div class="rounded-3xl border border-black/8 bg-[#FCF9F4] px-4 py-4 dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Tạm tính</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format((float) $subtotal, 0, ',', '.') }} VND</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <section class="space-y-4">
                @foreach($items as $item)
                    <article class="rounded-[1.75rem] border border-black/8 bg-white/90 p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="space-y-2">
                                <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ $item->listing?->display_name ?: ($item->listing?->variant?->product?->name ?? 'Sản phẩm') }}</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ collect([$item->listing?->variant?->product?->name, $item->listing?->variant?->edition])->filter()->implode(' • ') }}</p>
                                <div class="flex flex-wrap gap-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">
                                    <span class="rounded-full border border-black/8 bg-[#FCF9F4] px-3 py-1 dark:border-white/10 dark:bg-white/5">SL {{ $item->quantity }}</span>
                                    <span class="rounded-full border border-black/8 bg-[#FCF9F4] px-3 py-1 dark:border-white/10 dark:bg-white/5">Còn {{ (int) $item->listing?->stock_count }} key</span>
                                </div>
                            </div>

                            <div class="text-left sm:text-right">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Tổng sản phẩm</p>
                                <p class="mt-1 text-base font-semibold text-gray-950 dark:text-white">{{ number_format((float) ($item->listing?->price ?? 0) * (int) $item->quantity, 0, ',', '.') }} VND</p>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <aside class="space-y-4">
                <div class="rounded-[1.75rem] border border-black/8 bg-white/90 p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Tóm tắt thanh toán</h2>
                    <div class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex items-center justify-between gap-3">
                            <span>Phương thức thanh toán</span>
                            <span class="font-semibold text-gray-950 dark:text-white">VNPay</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span>Sản phẩm đã chọn</span>
                            <span class="font-semibold text-gray-950 dark:text-white">{{ $selectedCount }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-t border-black/8 pt-3 dark:border-white/10">
                            <span class="font-medium text-gray-800 dark:text-gray-200">Tổng cộng</span>
                            <span class="text-lg font-semibold text-gray-950 dark:text-white">{{ number_format((float) $subtotal, 0, ',', '.') }} VND</span>
                        </div>
                    </div>

                    <p class="mt-4 rounded-2xl bg-[#F6EBD9] px-4 py-3 text-xs leading-6 text-gray-600 dark:bg-white/5 dark:text-gray-400">
                        Key sẽ được giữ trong lúc chờ thanh toán. Nếu thanh toán không hoàn tất trong 24 giờ, đơn hàng sẽ tự động bị hủy.
                    </p>
                </div>

                <form method="POST" action="{{ route('app.cart.checkout') }}" class="space-y-3" x-ref="checkoutForm">
                    @csrf

                    @foreach($items as $item)
                        <input type="hidden" name="item_codes[]" value="{{ $item->cart_item_code }}">
                    @endforeach

                    <button type="button" x-on:click="checkoutConfirmOpen = true" class="inline-flex w-full items-center justify-center rounded-2xl bg-black px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                        Thanh toán ngay
                    </button>

                    <a href="{{ route('app.shop.index') }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-black/10 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition-colors hover:bg-black hover:text-white dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-white dark:hover:text-gray-950">
                        Quay lại cửa hàng
                    </a>

                    <template x-if="checkoutConfirmOpen">
                        <div class="fixed inset-0 z-[95] flex items-center justify-center">
                            <div class="absolute inset-0 bg-black/50" x-on:click.self="checkoutConfirmOpen = false"></div>

                            <div class="relative mx-4 w-full max-w-sm rounded-[1.75rem] border border-black/10 bg-white p-5 shadow-2xl dark:border-white/10 dark:bg-gray-950">
                                <h3 class="text-lg font-semibold text-black dark:text-white">Xác nhận thanh toán?</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Bạn sắp thanh toán {{ $selectedCount }} sản phẩm đã chọn.</p>

                                <div class="mt-5 flex items-center justify-end gap-3">
                                    <button type="button" x-on:click="checkoutConfirmOpen = false" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:bg-black hover:text-white dark:border-white/10 dark:bg-gray-900 dark:text-white dark:hover:bg-white dark:hover:text-gray-950">
                                        Hủy
                                    </button>

                                    <button type="button" x-on:click.prevent="$refs.checkoutForm.requestSubmit(); checkoutConfirmOpen = false" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                        Xác nhận
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </form>
            </aside>
        </div>
    </div>
</div>
