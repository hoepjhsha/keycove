@php
    $logoutUrl = \Illuminate\Support\Facades\Route::has('app.auth.logout')
        ? route('app.auth.logout')
        : route('app.shop.index');

    $libraryUrl = \Illuminate\Support\Facades\Route::has('app.library.show')
        ? route('app.library.show')
        : route('app.shop.index');

    $initials = collect(explode(' ', $fullName))
        ->filter()
        ->take(2)
        ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');

    $statusClasses = match ($user->status?->value) {
        1 => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
        2 => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
        3 => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
        default => 'bg-gray-500/10 text-gray-700 dark:text-gray-300',
    };

    $navItems = [
        ['key' => 'profile', 'label' => 'Profile', 'icon' => 'fa-regular fa-id-card', 'description' => 'Identity and contact'],
        ['key' => 'security', 'label' => 'Security', 'icon' => 'fa-solid fa-shield-halved', 'description' => 'Verification and password'],
    ];
@endphp

<div class="relative overflow-hidden px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-80 bg-gradient-to-b from-[#F6EBD9] via-[#FCF9F4] to-transparent dark:from-gray-900 dark:via-gray-950"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -top-10 right-0 -z-10 h-64 w-64 rounded-full bg-[#D32F2F]/8 blur-3xl dark:bg-[#D32F2F]/10"></div>
    <div aria-hidden="true" class="pointer-events-none absolute left-0 top-24 -z-10 h-64 w-64 rounded-full bg-indigo-500/8 blur-3xl dark:bg-indigo-400/10"></div>

    <div class="mx-auto max-w-7xl space-y-6">
        <section class="relative overflow-hidden rounded-[2rem] border border-black/8 bg-linear-to-br from-white via-[#FCF9F4] to-[#F6EBD9] p-5 shadow-[0_24px_80px_-40px_rgba(0,0,0,0.35)] sm:p-6 lg:p-8 dark:border-white/10 dark:from-gray-900 dark:via-gray-950 dark:to-gray-900">
            <div aria-hidden="true" class="pointer-events-none absolute inset-y-0 right-0 hidden w-1/2 bg-radial from-white/80 via-white/10 to-transparent lg:block dark:from-white/10 dark:via-white/5"></div>

            <div class="relative grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem] lg:items-end">
                <div class="space-y-5">
                    <nav aria-label="Breadcrumb">
                        <ol class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <li>
                                <a href="{{ route('app.shop.index') }}" class="transition-colors hover:text-[#D32F2F] dark:hover:text-[#ff8b8b]">
                                    <i class="fa-solid fa-house"></i>
                                </a>
                            </li>
                            <li><i class="fa-solid fa-chevron-right text-[10px]"></i></li>
                            <li class="font-semibold text-gray-800 dark:text-gray-200" aria-current="page">My Profile</li>
                        </ol>
                    </nav>

                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                        <div class="relative shrink-0">
                            <div class="flex h-28 w-28 items-center justify-center overflow-hidden rounded-[2rem] border border-black/8 bg-black text-3xl font-semibold text-white shadow-lg shadow-black/15 dark:border-white/10 dark:bg-white dark:text-gray-950">
                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="{{ $fullName }}" class="h-full w-full object-cover">
                                @else
                                    <span>{{ $initials !== '' ? $initials : 'KC' }}</span>
                                @endif
                            </div>
                            <div class="absolute -bottom-2 -right-2 rounded-full border border-white/70 bg-white px-3 py-1 text-[11px] font-semibold text-gray-700 shadow-sm dark:border-gray-900 dark:bg-gray-950 dark:text-gray-300">{{ $profileCompletion }}%</div>
                        </div>

                        <div class="min-w-0 space-y-4">
                            <div class="space-y-3">
                                <p class="inline-flex w-fit items-center gap-2 rounded-full border border-[#D32F2F]/15 bg-white/80 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.24em] text-[#D32F2F] shadow-sm dark:border-[#D32F2F]/20 dark:bg-white/5 dark:text-[#ff9c9c]">
                                    <span class="h-1.5 w-1.5 rounded-full bg-[#D32F2F]"></span>
                                    Account center
                                </p>
                                <div>
                                    <h1 class="text-3xl font-semibold tracking-tight text-gray-950 sm:text-4xl dark:text-white">{{ $fullName }}</h1>
                                    <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 sm:text-base dark:text-gray-400">
                                        {{ $profile?->bio ?: 'Manage your identity and account security here, while purchases now live in My Library.' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <span class="rounded-full bg-black px-3 py-1 text-[11px] font-semibold text-white dark:bg-white dark:text-gray-950">{{ $user->role?->label() ?? 'User' }}</span>
                                <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $statusClasses }}">{{ $user->status?->label() ?? 'Unknown' }}</span>
                                <span class="rounded-full border border-black/8 bg-white px-3 py-1 text-[11px] font-semibold text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-300">{{ $user->email_verified_at ? 'Email verified' : 'Email pending' }}</span>
                            </div>

                            <div class="grid gap-3 text-sm sm:grid-cols-2 xl:grid-cols-3">
                                <div class="rounded-2xl border border-black/8 bg-white/80 px-4 py-3 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Username</div>
                                    <div class="mt-2 font-semibold text-gray-950 dark:text-white">{{ '@'.$user->username }}</div>
                                </div>
                                <div class="rounded-2xl border border-black/8 bg-white/80 px-4 py-3 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Email</div>
                                    <div class="mt-2 truncate font-semibold text-gray-950 dark:text-white">{{ $user->email }}</div>
                                </div>
                                <div class="rounded-2xl border border-black/8 bg-white/80 px-4 py-3 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Workspace</div>
                                    <div class="mt-2 font-semibold text-gray-950 dark:text-white">Profile & security</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Profile completion</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ $profileCompletion }}%</p>
                    </div>

                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Email status</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ $user->hasVerifiedEmail() ? 'Verified' : 'Pending' }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Verification link available in security</p>
                    </div>

                    <div class="rounded-3xl border border-black/8 bg-white/80 px-4 py-4 shadow-sm backdrop-blur dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Member since</p>
                        <p class="mt-3 text-2xl font-semibold text-gray-950 dark:text-white">{{ $user->created_at?->format('d/m/Y') ?? '--' }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Account creation date</p>
                    </div>
                </div>
            </div>
        </section>

        @if(session('profile-status'))
            <div class="rounded-[1.75rem] border border-emerald-500/15 bg-emerald-500/8 px-5 py-4 text-sm font-medium text-emerald-700 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                {{ session('profile-status') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[18rem_minmax(0,1fr)] lg:items-start">
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="overflow-hidden rounded-[2rem] border border-black/8 bg-white/90 p-3 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
                    <div class="space-y-2">
                        @foreach($navItems as $item)
                            <button
                                type="button"
                                wire:click="setSection('{{ $item['key'] }}')"
                                @class([
                                    'flex w-full items-center justify-between gap-3 rounded-[1.4rem] px-4 py-3 text-left transition-all duration-200',
                                    'bg-[#F6EBD9] text-black shadow-sm dark:bg-white/10 dark:text-white' => $section === $item['key'],
                                    'text-gray-600 hover:bg-[#FCF9F4] hover:text-[#D32F2F] dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-[#ff9c9c]' => $section !== $item['key'],
                                ])>
                                <span class="flex min-w-0 items-center gap-3">
                                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-gray-900 shadow-sm dark:bg-gray-950 dark:text-white">
                                        <i class="{{ $item['icon'] }} text-[14px]"></i>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold">{{ $item['label'] }}</span>
                                        <span class="block truncate text-xs text-gray-500 dark:text-gray-400">{{ $item['description'] }}</span>
                                    </span>
                                </span>
                                <i class="fa-solid fa-chevron-right text-[11px] opacity-50"></i>
                            </button>
                        @endforeach

                        <a href="{{ $libraryUrl }}" class="flex w-full items-center justify-between gap-3 rounded-[1.4rem] px-4 py-3 text-left text-gray-600 transition-all duration-200 hover:bg-[#FCF9F4] hover:text-[#D32F2F] dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-[#ff9c9c]">
                            <span class="flex min-w-0 items-center gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-gray-900 shadow-sm dark:bg-gray-950 dark:text-white">
                                    <i class="fa-solid fa-folder-open text-[14px]"></i>
                                </span>
                                <span>
                                    <span class="block text-sm font-semibold">My Library</span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">Orders, keys, and complaints</span>
                                </span>
                            </span>
                            <i class="fa-solid fa-arrow-right text-[11px] opacity-60"></i>
                        </a>

                        <a href="{{ $logoutUrl }}" class="flex w-full items-center justify-between gap-3 rounded-[1.4rem] px-4 py-3 text-left text-rose-600 transition-all duration-200 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-500/10">
                            <span class="flex min-w-0 items-center gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white text-rose-600 shadow-sm dark:bg-gray-950 dark:text-rose-300">
                                    <i class="fa-solid fa-right-from-bracket text-[14px]"></i>
                                </span>
                                <span>
                                    <span class="block text-sm font-semibold">Logout</span>
                                    <span class="block text-xs text-rose-400 dark:text-rose-300/70">End this session</span>
                                </span>
                            </span>
                            <i class="fa-solid fa-arrow-right text-[11px] opacity-60"></i>
                        </a>
                    </div>
                </div>
            </aside>

            <section class="space-y-6">
                @if($section === 'profile')
                    @if(! $profile)
                        <div class="rounded-[2rem] border border-dashed border-black/15 bg-white/90 p-8 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
                            <div class="flex items-start gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#FCF9F4] text-gray-600 dark:bg-white/5 dark:text-gray-300">
                                    <i class="fa-solid fa-user-pen"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">No profile row found yet</h2>
                                    <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">This account exists, but it does not currently have a matching record in `user_profiles`. You still have access to account-level information and security controls.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="grid gap-6 xl:grid-cols-2">
                        <article class="rounded-[2rem] border border-black/8 bg-white/90 p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#FCF9F4] text-gray-700 dark:bg-white/5 dark:text-gray-300">
                                    <i class="fa-regular fa-address-card"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Personal identity</h2>
                                </div>
                            </div>

                            <div class="mt-6 grid gap-3 text-sm">
                                <div class="rounded-2xl bg-[#FCF9F4] px-4 py-3 dark:bg-gray-800/70">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">First name</div>
                                    <div class="mt-2 font-semibold text-gray-950 dark:text-white">{{ $profile?->first_name ?? '--' }}</div>
                                </div>
                                <div class="rounded-2xl bg-[#FCF9F4] px-4 py-3 dark:bg-gray-800/70">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Last name</div>
                                    <div class="mt-2 font-semibold text-gray-950 dark:text-white">{{ $profile?->last_name ?? '--' }}</div>
                                </div>
                                <div class="rounded-2xl bg-[#FCF9F4] px-4 py-3 dark:bg-gray-800/70">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Date of birth</div>
                                    <div class="mt-2 font-semibold text-gray-950 dark:text-white">{{ $profile?->dob?->format('d/m/Y') ?? '--' }}</div>
                                </div>
                                <div class="rounded-2xl bg-[#FCF9F4] px-4 py-3 dark:bg-gray-800/70">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Gender</div>
                                    <div class="mt-2 font-semibold text-gray-950 dark:text-white">{{ $profile?->gender?->label() ?? 'Unknown' }}</div>
                                </div>
                            </div>
                        </article>

                        <article class="rounded-[2rem] border border-black/8 bg-white/90 p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#FCF9F4] text-gray-700 dark:bg-white/5 dark:text-gray-300">
                                    <i class="fa-solid fa-signal"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Contact & presence</h2>
                                </div>
                            </div>

                            <div class="mt-6 grid gap-3 text-sm">
                                <div class="rounded-2xl bg-[#FCF9F4] px-4 py-3 dark:bg-gray-800/70">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Phone number</div>
                                    <div class="mt-2 font-semibold text-gray-950 dark:text-white">{{ $profile?->phone_number ?? '--' }}</div>
                                </div>
                                <div class="rounded-2xl bg-[#FCF9F4] px-4 py-3 dark:bg-gray-800/70">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Bio</div>
                                    <p class="mt-2 text-sm leading-6 text-gray-700 dark:text-gray-300">{{ $profile?->bio ?: 'No bio has been added to this profile yet.' }}</p>
                                </div>
                                <div class="rounded-2xl bg-[#FCF9F4] px-4 py-3 dark:bg-gray-800/70">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Member since</div>
                                    <div class="mt-2 font-semibold text-gray-950 dark:text-white">{{ $user->created_at?->format('d/m/Y') ?? '--' }}</div>
                                </div>
                            </div>
                        </article>
                    </div>
                @endif

                @if($section === 'security')
                    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                        <article class="rounded-[2rem] border border-black/8 bg-white/90 p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#FCF9F4] text-gray-700 dark:bg-white/5 dark:text-gray-300">
                                    <i class="fa-regular fa-envelope"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Email verification</h2>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Use a signed verification link to confirm the email address attached to this account.</p>
                                </div>
                            </div>

                            <div class="mt-6 space-y-4">
                                <div class="rounded-2xl bg-[#FCF9F4] px-4 py-4 dark:bg-gray-800/70">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Current email</p>
                                            <p class="mt-2 font-semibold text-gray-950 dark:text-white">{{ $user->email }}</p>
                                        </div>
                                        <span class="rounded-full px-3 py-1 text-[11px] font-semibold {{ $user->hasVerifiedEmail() ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'bg-amber-500/10 text-amber-700 dark:text-amber-300' }}">
                                            {{ $user->hasVerifiedEmail() ? 'Verified' : 'Pending verification' }}
                                        </span>
                                    </div>
                                </div>

                                @if($user->hasVerifiedEmail())
                                    <div class="rounded-2xl border border-emerald-500/15 bg-emerald-500/8 px-4 py-4 text-sm text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                                        Verified on {{ $user->email_verified_at?->format('d/m/Y H:i') ?? '--' }}.
                                    </div>
                                @else
                                    <button type="button" wire:click="sendVerificationLink" wire:loading.attr="disabled" wire:target="sendVerificationLink" class="inline-flex items-center justify-center rounded-2xl bg-black px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] disabled:cursor-not-allowed disabled:opacity-70 dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                        Send verification link
                                    </button>
                                @endif
                            </div>
                        </article>

                        <article class="rounded-[2rem] border border-black/8 bg-white/90 p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#FCF9F4] text-gray-700 dark:bg-white/5 dark:text-gray-300">
                                    <i class="fa-solid fa-key"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Change password</h2>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rotate your password here without leaving the account center.</p>
                                </div>
                            </div>

                            <form wire:submit="changePassword" class="mt-6 space-y-4">
                                <div>
                                    <label for="current-password" class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Current password</label>
                                    <input id="current-password" wire:model="currentPassword" type="password" class="mt-2 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                                    @error('currentPassword')
                                        <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="new-password" class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">New password</label>
                                    <input id="new-password" wire:model="newPassword" type="password" class="mt-2 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                                    @error('newPassword')
                                        <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="new-password-confirmation" class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Confirm new password</label>
                                    <input id="new-password-confirmation" wire:model="newPasswordConfirmation" type="password" class="mt-2 w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100">
                                    @error('newPasswordConfirmation')
                                        <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit" wire:loading.attr="disabled" wire:target="changePassword" class="inline-flex items-center justify-center rounded-2xl bg-black px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] disabled:cursor-not-allowed disabled:opacity-70 dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                                    Update password
                                </button>
                            </form>
                        </article>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
