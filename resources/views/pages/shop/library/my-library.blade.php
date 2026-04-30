<div class="relative overflow-hidden px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-80 bg-gradient-to-b from-[#F6EBD9] via-[#FCF9F4] to-transparent dark:from-gray-900 dark:via-gray-950"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -top-10 right-0 -z-10 h-64 w-64 rounded-full bg-[#D32F2F]/8 blur-3xl dark:bg-[#D32F2F]/10"></div>
    <div aria-hidden="true" class="pointer-events-none absolute left-0 top-24 -z-10 h-64 w-64 rounded-full bg-indigo-500/8 blur-3xl dark:bg-indigo-400/10"></div>

    <div class="mx-auto max-w-7xl space-y-6">
        <section class="relative overflow-hidden rounded-[2rem] border border-black/8 bg-linear-to-br from-white via-[#FCF9F4] to-[#F6EBD9] p-5 shadow-[0_24px_80px_-40px_rgba(0,0,0,0.35)] sm:p-6 lg:p-8 dark:border-white/10 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
            <div aria-hidden="true" class="pointer-events-none absolute inset-y-0 right-0 hidden w-1/2 bg-radial from-white/80 via-white/10 to-transparent lg:block dark:from-white/10 dark:via-white/5"></div>

            <div class="relative grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-end">
                <div class="space-y-4">
                    <nav aria-label="Breadcrumb">
                        <ol class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <li>
                                <a href="{{ route('app.shop.index') }}" class="transition-colors hover:text-[#D32F2F] dark:hover:text-[#ff8b8b]">
                                    <i class="fa-solid fa-house"></i>
                                </a>
                            </li>
                            <li><i class="fa-solid fa-chevron-right text-[10px]"></i></li>
                            <li class="font-semibold text-gray-800 dark:text-gray-200" aria-current="page">My Library</li>
                        </ol>
                    </nav>

                    <div class="space-y-3">
                        <p class="inline-flex w-fit items-center gap-2 rounded-full border border-[#D32F2F]/15 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] shadow-sm dark:border-[#D32F2F]/20 dark:bg-white/5 dark:text-[#ff9c9c]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#D32F2F]"></span>
                            Buyer workspace
                        </p>
                        <div>
                            <h1 class="text-3xl font-semibold tracking-tight text-gray-950 sm:text-4xl dark:text-white">My Library</h1>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 sm:text-base dark:text-gray-400">Manage pending payments, completed purchases, revealed keys, and complaints from a single buyer-focused workspace.</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Orders</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ $orders->count() }}</p>
                    </div>
                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Pending payment</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ $pendingPaymentCount }}</p>
                    </div>
                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Completed</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ $completedOrderCount }}</p>
                    </div>
                </div>
            </div>
        </section>

        @if(session('library-status'))
            <div class="rounded-[1.75rem] border border-emerald-500/15 bg-emerald-500/8 px-5 py-4 text-sm font-medium text-emerald-700 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                {{ session('library-status') }}
            </div>
        @endif

        <div class="space-y-4">
            @forelse($orders as $order)
                @php
                    $orderStatusClasses = match ($order->status->value) {
                        0, 1 => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                        2, 4 => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                        3, 5, 6 => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
                        default => 'bg-gray-500/10 text-gray-700 dark:text-gray-300',
                    };

                    $paymentStatusClasses = match ($order->payment_status?->value) {
                        1 => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                        0 => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                        default => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
                    };

                    $isPendingPayment = $order->payment_status === \App\Enums\PaymentStatus::Pending;
                    $isPaidOrder = $order->payment_status === \App\Enums\PaymentStatus::Completed;
                @endphp

                <article class="rounded-[2rem] border border-black/8 bg-white/90 p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-black px-3 py-1 text-[11px] font-semibold text-white dark:bg-white dark:text-gray-950">{{ $order->order_code }}</span>
                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $orderStatusClasses }}">{{ $order->status->label() }}</span>
                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $paymentStatusClasses }}">{{ $order->payment_status?->label() ?? 'Unknown' }}</span>
                            </div>

                            <div>
                                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">{{ $order->payment_method?->label() ?? 'Payment' }} order</h2>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Placed on {{ $order->created_at?->format('d/m/Y H:i') ?? '--' }} with {{ $order->items_count }} item{{ $order->items_count === 1 ? '' : 's' }}.</p>
                            </div>

                            @if($isPendingPayment)
                                <div class="flex flex-wrap gap-3">
                                    <button type="button" wire:click="continuePayment({{ $order->id }})" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                        Continue payment
                                    </button>
                                    <button type="button" wire:click="cancelOrder({{ $order->id }})" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-rose-500/30 hover:text-rose-600 dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-rose-400/30 dark:hover:text-rose-300">
                                        Cancel order
                                    </button>
                                </div>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-3 text-left shadow-sm dark:border-white/10 dark:bg-gray-800/70 lg:min-w-44 lg:text-right">
                            <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Order total</div>
                            <div class="mt-2 text-lg font-semibold text-gray-950 dark:text-white">{{ number_format((float) $order->total_price, 0, ',', '.') }} VND</div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4 border-t border-black/8 pt-6 dark:border-white/10">
                        @foreach($order->items as $item)
                            @php
                                $itemStatusClasses = match ($item->status?->value) {
                                    0, 1 => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                                    2, 4 => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                                    3, 5, 6 => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
                                    default => 'bg-gray-500/10 text-gray-700 dark:text-gray-300',
                                };

                                $visibleKeys = $revealedKeys[$item->id] ?? [];
                                $canViewKeys = in_array($item->status, [
                                    \App\Enums\OrderStatus::Delivered,
                                    \App\Enums\OrderStatus::Disputing,
                                    \App\Enums\OrderStatus::Completed,
                                ], true);
                            @endphp

                            <div class="rounded-[1.6rem] border border-black/8 bg-[#FCF9F4] p-4 dark:border-white/10 dark:bg-white/5">
                                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                    <div class="space-y-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $itemStatusClasses }}">{{ $item->status?->label() ?? 'Unknown' }}</span>
                                            <span class="rounded-full border border-black/8 bg-white px-3 py-1 text-[11px] font-semibold text-gray-700 dark:border-white/10 dark:bg-gray-950 dark:text-gray-300">Qty {{ $item->quantity }}</span>
                                            <span class="rounded-full border border-black/8 bg-white px-3 py-1 text-[11px] font-semibold text-gray-700 dark:border-white/10 dark:bg-gray-950 dark:text-gray-300">{{ $item->keys_count }} key{{ $item->keys_count === 1 ? '' : 's' }}</span>
                                            @if($item->complaint)
                                                <span class="rounded-full bg-orange-500/10 px-3 py-1 text-[11px] font-semibold text-orange-700 dark:text-orange-300">Complaint open</span>
                                            @endif
                                        </div>

                                        <div>
                                            <h3 class="text-base font-semibold text-gray-950 dark:text-white">{{ $item->product_name_snapshot }}</h3>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $item->listing?->variant?->product?->name ?? 'Store item' }}</p>
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-start gap-3 xl:items-end">
                                        <div class="text-left xl:text-right">
                                            <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Item subtotal</div>
                                            <div class="mt-1 text-base font-semibold text-gray-950 dark:text-white">{{ number_format((float) $item->subtotal, 0, ',', '.') }} VND</div>
                                        </div>

                                        <div class="flex flex-wrap gap-2">
                                            @if($item->keys_count > 0 && $canViewKeys)
                                                @if($visibleKeys !== [])
                                                    <button type="button" wire:click="hideOrderItemKeys({{ $item->id }})" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/30 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/30 dark:hover:text-[#ff9c9c]">
                                                        Hide keys
                                                    </button>
                                                @else
                                                    <button type="button" wire:click="promptKeyReveal({{ $item->id }})" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                                        View key
                                                    </button>
                                                @endif
                                            @endif

                                            @if($isPaidOrder && $item->status === \App\Enums\OrderStatus::Delivered)
                                                <button type="button" wire:click="confirmReceived({{ $item->id }})" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-emerald-500/30 hover:text-emerald-600 dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-emerald-400/30 dark:hover:text-emerald-300">
                                                    Confirm received
                                                </button>
                                            @endif

                                            @if($isPaidOrder && ! $item->complaint)
                                                <button type="button" wire:click="openComplaintForm({{ $item->id }})" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-rose-500/30 hover:text-rose-600 dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-rose-400/30 dark:hover:text-rose-300">
                                                    Open complaint
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($visibleKeys !== [])
                                    <div class="mt-4 space-y-3 border-t border-black/8 pt-4 dark:border-white/10">
                                        <div class="rounded-2xl border border-amber-500/15 bg-amber-500/8 px-4 py-3 text-sm text-amber-700 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300">
                                            Stay on this screen while reviewing and activating your key. Do not leave the verification and usage flow midway so your purchase rights remain easier to protect.
                                        </div>

                                        <div class="space-y-2">
                                            @foreach($visibleKeys as $keyCode)
                                                <div class="overflow-x-auto rounded-2xl bg-gray-950 px-4 py-3 font-mono text-sm text-white">{{ $keyCode }}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="rounded-[2rem] border border-dashed border-black/15 bg-white/90 p-10 text-center shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#FCF9F4] text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <i class="fa-solid fa-gamepad"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-950 dark:text-white">Your library is empty</h2>
                    <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Completed purchases and pending payment orders will appear here.</p>
                </div>
            @endforelse
        </div>

        @if($keyAccessOrderItemId)
            <div class="fixed inset-0 z-[80] flex items-center justify-center p-4">
                <button type="button" wire:click="cancelKeyReveal" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></button>

                <div class="relative z-10 w-full max-w-lg rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.5)] dark:border-white/10 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#D32F2F] dark:text-[#ff9c9c]">Protected access</p>
                            <h2 class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">Confirm password to view your key</h2>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Before revealing a purchased key, re-enter your account password and read the warning below carefully.</p>
                        </div>

                        <button type="button" wire:click="cancelKeyReveal" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-black/10 bg-white text-black transition-colors hover:bg-black hover:text-white dark:border-white/10 dark:bg-gray-950 dark:text-white dark:hover:bg-white dark:hover:text-gray-950">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="mt-5 rounded-2xl border border-amber-500/15 bg-amber-500/8 px-4 py-4 text-sm leading-6 text-amber-700 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300">
                        Stay on the key viewing and activation screen while using the code. Avoid breaking the process halfway so your support and rights remain easier to verify if something goes wrong.
                    </div>

                    <form wire:submit="revealOrderItemKeys" class="mt-5 space-y-4">
                        <div>
                            <label for="library-key-access-password" class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Account password</label>
                            <input id="library-key-access-password" wire:model="keyAccessPassword" type="password" autofocus class="mt-2 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                            @error('keyAccessPassword')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-wrap justify-end gap-3">
                            <button type="button" wire:click="cancelKeyReveal" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                                Cancel
                            </button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="revealOrderItemKeys" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] disabled:cursor-not-allowed disabled:opacity-70 dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                Show key
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if($complaintOrderItemId)
            <div class="fixed inset-0 z-[80] flex items-center justify-center p-4">
                <button type="button" wire:click="cancelComplaintForm" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></button>

                <div class="relative z-10 w-full max-w-xl rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_30px_80px_-30px_rgba(0,0,0,0.5)] dark:border-white/10 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#D32F2F] dark:text-[#ff9c9c]">Complaint</p>
                            <h2 class="mt-2 text-xl font-semibold text-gray-950 dark:text-white">Describe the issue</h2>
                            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Explain what went wrong so the order item can move into the dispute flow with clear context.</p>
                        </div>

                        <button type="button" wire:click="cancelComplaintForm" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-black/10 bg-white text-black transition-colors hover:bg-black hover:text-white dark:border-white/10 dark:bg-gray-950 dark:text-white dark:hover:bg-white dark:hover:text-gray-950">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <form wire:submit="submitComplaint" class="mt-5 space-y-4">
                        <div>
                            <label for="complaint-reason" class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Reason</label>
                            <textarea id="complaint-reason" wire:model="complaintReason" rows="5" class="mt-2 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100" placeholder="Describe the key issue, wrong region, activation failure, or any mismatch..."></textarea>
                            @error('complaintReason')
                                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-wrap justify-end gap-3">
                            <button type="button" wire:click="cancelComplaintForm" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                                Cancel
                            </button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="submitComplaint" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] disabled:cursor-not-allowed disabled:opacity-70 dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                Submit complaint
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
