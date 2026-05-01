<div class="space-y-6">
    @if(session('withdraw-status'))
        <div class="rounded-[1.75rem] border border-emerald-500/15 bg-emerald-500/8 px-5 py-4 text-sm font-medium text-emerald-700 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
            {{ session('withdraw-status') }}
        </div>
    @endif

    @if(session('withdraw-error'))
        <div class="rounded-[1.75rem] border border-rose-500/15 bg-rose-500/8 px-5 py-4 text-sm font-medium text-rose-700 shadow-sm dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-300">
            {{ session('withdraw-error') }}
        </div>
    @endif

    <section class="overflow-hidden rounded-[2rem] border border-black/8 bg-linear-to-br from-white via-[#FCF9F4] to-[#F6EBD9] p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-3">
                <p class="inline-flex w-fit items-center gap-2 rounded-full border border-[#D32F2F]/15 bg-[#FCF9F4] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] dark:border-[#D32F2F]/20 dark:bg-white/5 dark:text-[#ff9c9c]">
                    Rút tiền
                </p>
                <div>
                    <h2 class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">Rút số dư cửa hàng</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">Yêu cầu rút tiền về tài khoản ngân hàng. Số dư khả dụng sẽ được trừ ngay và xác nhận VNPay được xử lý tự động.</p>
                </div>
            </div>

            <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Giới hạn</p>
                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">Tối thiểu {{ number_format($minimumWithdrawal, 0, ',', '.') }} VND</p>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Tối đa {{ number_format($maximumWithdrawal, 0, ',', '.') }} VND</p>
            </div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-[1.75rem] border border-black/8 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Số dư khả dụng</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['walletBalance'], 0, ',', '.') }} VND</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">VND có thể rút</p>
        </article>

        <article class="rounded-[1.75rem] border border-black/8 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Đang giữ</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['walletHolding'], 0, ',', '.') }} VND</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Bị khóa bởi ký quỹ hoặc yêu cầu rút đang chờ</p>
        </article>

        <article class="rounded-[1.75rem] border border-black/8 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Doanh thu cửa hàng</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['sellerEarnings'], 0, ',', '.') }} VND</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Phần doanh thu của người bán từ giao dịch hoàn tất</p>
        </article>

        <article class="rounded-[1.75rem] border border-black/8 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Phí nền tảng</p>
            <p class="mt-3 text-3xl font-semibold text-gray-950 dark:text-white">{{ number_format($metrics['platformFee'], 0, ',', '.') }} VND</p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Hoa hồng nền tảng đã thu</p>
        </article>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:bg-gray-900/85">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Yêu cầu rút tiền</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Điền thông tin ngân hàng và số tiền muốn rút.</p>
                </div>
            </div>

            <form wire:submit.prevent="submit" class="mt-5 space-y-4">
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Số tiền</label>
                    <input id="amount" type="number" step="0.01" min="{{ $minimumWithdrawal }}" max="{{ $maximumWithdrawal }}" wire:model.live="amount" class="mt-2 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-gray-950 shadow-sm outline-none transition focus:border-[#D32F2F] focus:ring-2 focus:ring-[#D32F2F]/15 dark:border-white/10 dark:bg-gray-950 dark:text-white" placeholder="100000" />
                    @error('amount')
                        <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="bankName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tên ngân hàng</label>
                    <input id="bankName" type="text" wire:model.live="bankName" class="mt-2 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-gray-950 shadow-sm outline-none transition focus:border-[#D32F2F] focus:ring-2 focus:ring-[#D32F2F]/15 dark:border-white/10 dark:bg-gray-950 dark:text-white" placeholder="Vietcombank" />
                    @error('bankName')
                        <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="bankCode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mã ngân hàng</label>
                    <input id="bankCode" type="text" wire:model.live="bankCode" class="mt-2 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-gray-950 shadow-sm outline-none transition focus:border-[#D32F2F] focus:ring-2 focus:ring-[#D32F2F]/15 dark:border-white/10 dark:bg-gray-950 dark:text-white" placeholder="VCB" />
                    @error('bankCode')
                        <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="bankAccountNumber" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Số tài khoản</label>
                        <input id="bankAccountNumber" type="text" wire:model.live="bankAccountNumber" class="mt-2 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-gray-950 shadow-sm outline-none transition focus:border-[#D32F2F] focus:ring-2 focus:ring-[#D32F2F]/15 dark:border-white/10 dark:bg-gray-950 dark:text-white" placeholder="0123456789" />
                        @error('bankAccountNumber')
                            <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bankAccountName" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tên tài khoản</label>
                        <input id="bankAccountName" type="text" wire:model.live="bankAccountName" class="mt-2 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm text-gray-950 shadow-sm outline-none transition focus:border-[#D32F2F] focus:ring-2 focus:ring-[#D32F2F]/15 dark:border-white/10 dark:bg-gray-950 dark:text-white" placeholder="Nguyen Van A" />
                        @error('bankAccountName')
                            <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 pt-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Yêu cầu được xử lý tự động qua VNPay.</p>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-black px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                        Gửi yêu cầu rút tiền
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-[2rem] border border-black/8 bg-white p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] dark:border-white/10 dark:bg-gray-900/85">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Yêu cầu rút gần đây</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Các yêu cầu rút tiền mới nhất và trạng thái xử lý.</p>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                @forelse($withdrawals as $withdrawal)
                    @php
                        $statusClass = match ($withdrawal->status) {
                            \App\Enums\WithdrawStatus::Completed => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                            \App\Enums\WithdrawStatus::Processing, \App\Enums\WithdrawStatus::Pending => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                            \App\Enums\WithdrawStatus::Failed, \App\Enums\WithdrawStatus::Rejected => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
                            default => 'bg-slate-500/10 text-slate-700 dark:text-slate-300',
                        };
                    @endphp

                    <article class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-3 dark:border-white/10 dark:bg-white/5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h4 class="font-semibold text-gray-950 dark:text-white">{{ number_format((float) $withdrawal->amount, 0, ',', '.') }} VND</h4>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $withdrawal->bank_name }} · {{ $withdrawal->bank_account_name }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $statusClass }}">{{ $withdrawal->status->label() }}</span>
                        </div>
                        <p class="mt-3 text-xs uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">{{ $withdrawal->created_at?->format('d/m/Y H:i') ?? '--' }}</p>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-black/15 px-4 py-6 text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                        Chưa có yêu cầu rút tiền.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>
