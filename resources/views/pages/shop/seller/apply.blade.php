<div class="relative overflow-hidden px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-gradient-to-b from-[#F6EBD9] via-[#FCF9F4] to-transparent dark:from-gray-900 dark:via-gray-950"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -top-8 right-0 -z-10 h-56 w-56 rounded-full bg-[#D32F2F]/8 blur-3xl dark:bg-[#D32F2F]/10"></div>
    <div aria-hidden="true" class="pointer-events-none absolute left-0 top-32 -z-10 h-56 w-56 rounded-full bg-indigo-500/8 blur-3xl dark:bg-indigo-400/10"></div>

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
                            <li class="font-semibold text-gray-800 dark:text-gray-200" aria-current="page">Đăng ký người bán</li>
                        </ol>
                    </nav>

                    <div class="space-y-3">
                        <p class="inline-flex w-fit items-center gap-2 rounded-full border border-[#D32F2F]/15 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] shadow-sm dark:border-[#D32F2F]/20 dark:bg-white/5 dark:text-[#ff9c9c]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#D32F2F]"></span>
                            Đăng ký người bán
                        </p>
                        <div>
                            <h1 class="text-3xl font-semibold tracking-tight text-gray-950 sm:text-4xl dark:text-white">Trở thành người bán</h1>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 sm:text-base dark:text-gray-400">Gửi tên cửa hàng và thông tin định danh. Sau khi được duyệt, dashboard người bán sẽ tự động mở khóa.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Trạng thái email</p>
                    <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">Đã xác minh</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Đăng ký người bán yêu cầu email đã xác minh.</p>
                </div>
            </div>
        </section>

        @if(session('seller-status'))
            <div class="rounded-[1.75rem] border border-emerald-500/15 bg-emerald-500/8 px-5 py-4 text-sm font-medium text-emerald-700 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                {{ session('seller-status') }}
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
            <section class="space-y-6">
                <article class="rounded-[2rem] border border-black/8 bg-white/90 p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-3">
                            <p class="inline-flex w-fit items-center gap-2 rounded-full border border-[#D32F2F]/15 bg-[#FCF9F4] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] dark:border-[#D32F2F]/20 dark:bg-white/5 dark:text-[#ff9c9c]">
                                Đơn đăng ký người bán
                            </p>
                            <div>
                                <h2 class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">{{ $portalState['headline'] }}</h2>
                                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $portalState['description'] }}</p>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-black/8 bg-gray-50 px-4 py-4 dark:border-white/10 dark:bg-white/5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Trạng thái hiện tại</p>
                            <div class="mt-3 inline-flex rounded-full px-3 py-1 text-[11px] font-semibold {{ $portalState['badgeClass'] }}">{{ $seller?->kyc_status?->label() ?? $portalState['badge'] }}</div>
                            <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">{{ $seller?->kyc_rejected_reason ?: 'Hồ sơ người bán của bạn sẽ ở đây trong lúc quản trị viên xét duyệt.' }}</p>
                        </div>
                    </div>
                </article>

                @if($seller?->kyc_status === \App\Enums\KycStatus::Approved)
                    <article class="rounded-[2rem] border border-emerald-500/15 bg-emerald-500/8 p-6 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-700 dark:text-emerald-300">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div class="space-y-3">
                                <h3 class="text-lg font-semibold text-emerald-900 dark:text-emerald-200">Tài khoản người bán đã được duyệt</h3>
                                <p class="text-sm leading-6 text-emerald-800/90 dark:text-emerald-200/80">Bạn có thể mở dashboard người bán ngay bây giờ.</p>
                                <a href="{{ $portalState['actionUrl'] }}" class="inline-flex items-center justify-center rounded-2xl px-5 py-3 text-sm font-semibold transition-colors {{ $portalState['actionClass'] }}">
                                    {{ $portalState['actionLabel'] }}
                                </a>
                            </div>
                        </div>
                    </article>
                @else
                    <article class="rounded-[2rem] border border-black/8 bg-white/90 p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#FCF9F4] text-gray-700 dark:bg-white/5 dark:text-gray-300">
                                <i class="fa-regular fa-id-card"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Thông tin đăng ký</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Điền biểu mẫu bên dưới. Đội ngũ quản trị sẽ xét duyệt KYC sau khi bạn gửi.</p>
                            </div>
                        </div>

                        <form wire:submit="submit" class="mt-6 space-y-5">
                            <div class="grid gap-5 md:grid-cols-2">
                                <label class="block space-y-2 md:col-span-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tên cửa hàng</span>
                                    <input type="text" wire:model="shopName" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100" placeholder="Tên cửa hàng của bạn">
                                    @error('shopName')
                                        <p class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                    @enderror
                                </label>

                                <label class="block space-y-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Số CCCD</span>
                                    <input type="text" wire:model="cccdNumber" inputmode="numeric" maxlength="12" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100" placeholder="12 chữ số">
                                    @error('cccdNumber')
                                        <p class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                    @enderror
                                </label>

                                <label class="block space-y-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Ảnh CCCD mặt trước</span>
                                    <input type="file" wire:model="cccdFrontImage" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-full file:border-0 file:bg-black file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#D32F2F] dark:text-gray-400">
                                    @if($cccdFrontImage)
                                        <img src="{{ $cccdFrontImage->temporaryUrl() }}" alt="Xem trước CCCD mặt trước" class="h-28 w-28 rounded-2xl border border-black/10 object-cover dark:border-white/10">
                                    @endif
                                    @error('cccdFrontImage')
                                        <p class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                    @enderror
                                </label>

                                <label class="block space-y-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Ảnh CCCD mặt sau</span>
                                    <input type="file" wire:model="cccdBackImage" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-full file:border-0 file:bg-black file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#D32F2F] dark:text-gray-400">
                                    @if($cccdBackImage)
                                        <img src="{{ $cccdBackImage->temporaryUrl() }}" alt="Xem trước CCCD mặt sau" class="h-28 w-28 rounded-2xl border border-black/10 object-cover dark:border-white/10">
                                    @endif
                                    @error('cccdBackImage')
                                        <p class="text-xs text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                    @enderror
                                </label>
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <button type="submit" wire:loading.attr="disabled" wire:target="submit" class="inline-flex items-center justify-center rounded-2xl bg-black px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] disabled:cursor-not-allowed disabled:opacity-70 dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                    <span wire:loading.remove wire:target="submit">{{ $portalState['actionLabel'] }}</span>
                                    <span wire:loading wire:target="submit">Đang gửi...</span>
                                </button>

                                <a href="{{ url('/my-profile') }}" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                                    Quay lại hồ sơ
                                </a>
                            </div>
                        </form>
                    </article>
                @endif
            </section>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:self-start">
                <article class="rounded-[2rem] border border-black/8 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Danh sách cần chuẩn bị</p>
                    <div class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-check mt-1 text-emerald-500"></i>
                            <p>Dùng tên cửa hàng thật của bạn.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-check mt-1 text-emerald-500"></i>
                            <p>Tải ảnh CCCD mặt trước và mặt sau rõ nét.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-check mt-1 text-emerald-500"></i>
                            <p>Sau khi được duyệt, dashboard người bán sẽ tự động mở khóa.</p>
                        </div>
                    </div>
                </article>

                <article class="rounded-[2rem] border border-black/8 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900/85">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Trạng thái kênh bán</p>
                            <h3 class="mt-2 text-lg font-semibold text-gray-950 dark:text-white">{{ $portalState['headline'] }}</h3>
                        </div>
                        <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $portalState['badgeClass'] }}">{{ $portalState['badge'] }}</span>
                    </div>

                    <p class="mt-4 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ $portalState['description'] }}</p>

                    @if(filled($portalState['actionUrl']))
                        <a href="{{ $portalState['actionUrl'] }}" class="mt-5 inline-flex items-center justify-center rounded-2xl px-5 py-3 text-sm font-semibold transition-colors {{ $portalState['actionClass'] }}">
                            {{ $portalState['actionLabel'] }}
                        </a>
                    @endif
                </article>
            </aside>
        </div>
    </div>
</div>
