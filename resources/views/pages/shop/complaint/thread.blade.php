<div class="relative overflow-hidden px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
    <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-gradient-to-b from-[#F6EBD9] via-[#FCF9F4] to-transparent dark:from-gray-900 dark:via-gray-950"></div>

    <div class="mx-auto max-w-7xl space-y-6">
        @if(session('complaint-status'))
            <div class="rounded-[1.75rem] border border-emerald-500/15 bg-emerald-500/8 px-5 py-4 text-sm font-medium text-emerald-700 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                {{ session('complaint-status') }}
            </div>
        @endif

        <section class="rounded-[2rem] border border-black/8 bg-white/90 p-6 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-black px-3 py-1 text-[11px] font-semibold text-white dark:bg-white dark:text-gray-950">{{ $complaint->complaint_code ?? ('Complaint #'.$complaint->id) }}</span>
                        <span class="rounded-full px-3 py-1 text-[11px] font-semibold bg-sky-500/10 text-sky-700 dark:text-sky-300">{{ $complaint->status->label() }}</span>
                    </div>

                    <div>
                        <h1 class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">Complaint thread</h1>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-600 dark:text-gray-400">A shared conversation for the buyer, seller, and admin to review evidence and decide the outcome.</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ $backUrl }}" class="inline-flex items-center justify-center rounded-2xl border border-black/10 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:border-[#D32F2F]/25 hover:text-[#D32F2F] dark:border-white/10 dark:bg-gray-950 dark:text-gray-200 dark:hover:border-[#D32F2F]/25 dark:hover:text-[#ff9c9c]">
                        Back
                    </a>
                </div>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-4 dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Product</p>
                    <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $complaint->orderItem?->product_name_snapshot ?? 'Item' }}</p>
                </article>

                <article class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-4 dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Order</p>
                    <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $complaint->orderItem?->order?->order_code ?? '-' }}</p>
                </article>

                <article class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-4 dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Participants</p>
                    <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $participantName }}</p>
                </article>

                <article class="rounded-2xl border border-black/8 bg-[#FCF9F4] px-4 py-4 dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Opened</p>
                    <p class="mt-2 text-sm font-semibold text-gray-950 dark:text-white">{{ $complaint->created_at?->format('d/m/Y H:i') ?? '--' }}</p>
                </article>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
            <section class="space-y-4 rounded-[2rem] border border-black/8 bg-white/90 p-5 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
                <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] p-4 dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Buyer reason</p>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-700 dark:text-gray-300">{{ $complaint->reason }}</p>
                </div>

                @if($complaint->evidence !== [])
                    <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] p-4 dark:border-white/10 dark:bg-white/5">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Evidence</p>
                        <div class="mt-3 space-y-3">
                            @foreach($complaint->evidence as $evidencePath)
                                @php
                                    $evidenceUrl = \App\Utilities\StorageUtility::getUrl($evidencePath);
                                    $extension = strtolower(pathinfo($evidencePath, PATHINFO_EXTENSION));
                                    $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true);
                                    $isVideo = in_array($extension, ['mp4', 'webm', 'mov'], true);
                                @endphp

                                <div wire:key="complaint-evidence-{{ $loop->index }}" class="rounded-2xl border border-black/8 bg-white p-3 dark:border-white/10 dark:bg-gray-950">
                                    <p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">{{ basename($evidencePath) }}</p>

                                    @if($evidenceUrl && $isImage)
                                        <a href="{{ $evidenceUrl }}" target="_blank" rel="noopener noreferrer" class="block overflow-hidden rounded-xl border border-black/8 dark:border-white/10">
                                            <img src="{{ $evidenceUrl }}" alt="{{ basename($evidencePath) }}" class="max-h-80 w-full object-cover">
                                        </a>
                                    @elseif($evidenceUrl && $isVideo)
                                        <video controls class="w-full rounded-xl border border-black/8 dark:border-white/10">
                                            <source src="{{ $evidenceUrl }}" type="video/{{ $extension }}">
                                        </video>
                                    @elseif($evidenceUrl)
                                        <a href="{{ $evidenceUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-full bg-black px-3 py-2 text-xs font-semibold text-white dark:bg-white dark:text-gray-950">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                            Open file
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="rounded-2xl border border-black/8 bg-white p-4 dark:border-white/10 dark:bg-gray-950">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-sm font-semibold text-gray-950 dark:text-white">Conversation</h2>
                        <span class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">{{ count($messages) }} messages</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        @foreach($messages as $message)
                            <article wire:key="complaint-message-{{ $message['id'] }}" @class([
                                'rounded-2xl border p-4',
                                'border-[#D32F2F]/15 bg-[#FCF9F4] dark:border-[#ff9c9c]/20 dark:bg-white/5' => $message['sender_id'] === $user->id,
                                'border-black/8 bg-white dark:border-white/10 dark:bg-gray-900' => $message['sender_id'] !== $user->id,
                            ])>
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $message['sender_name'] }}</p>
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">{{ $message['sender_role'] }}</p>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $message['created_at'] }}</p>
                                </div>

                                <p class="mt-3 whitespace-pre-line text-sm leading-6 text-gray-700 dark:text-gray-300">{{ $message['message'] }}</p>

                                @if($message['attachments'] !== [])
                                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                        @foreach($message['attachments'] as $attachment)
                                            @php
                                                $extension = strtolower(pathinfo($attachment['label'], PATHINFO_EXTENSION));
                                                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true);
                                                $isVideo = in_array($extension, ['mp4', 'webm', 'mov'], true);
                                            @endphp

                                            @if($attachment['url'] && $isImage)
                                                <a href="{{ $attachment['url'] }}" target="_blank" rel="noopener noreferrer" class="overflow-hidden rounded-xl border border-black/8 dark:border-white/10">
                                                    <img src="{{ $attachment['url'] }}" alt="{{ $attachment['label'] }}" class="max-h-64 w-full object-cover">
                                                </a>
                                            @elseif($attachment['url'] && $isVideo)
                                                <video controls class="w-full rounded-xl border border-black/8 dark:border-white/10">
                                                    <source src="{{ $attachment['url'] }}" type="video/{{ $extension }}">
                                                </video>
                                            @elseif($attachment['url'])
                                                <a href="{{ $attachment['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-black/8 bg-white px-3 py-2 text-xs font-semibold text-gray-700 dark:border-white/10 dark:bg-gray-950 dark:text-gray-200">
                                                    {{ $attachment['label'] }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <aside class="space-y-4 rounded-[2rem] border border-black/8 bg-white/90 p-5 shadow-[0_24px_70px_-45px_rgba(0,0,0,0.45)] backdrop-blur dark:border-white/10 dark:bg-gray-900/85">
                <div class="rounded-2xl border border-black/8 bg-[#FCF9F4] p-4 dark:border-white/10 dark:bg-white/5">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Thread notes</p>
                    <div class="mt-3 space-y-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                        <p>Buyer issues should stay here until admin approves a refund or releases funds.</p>
                        <p>Seller-owned complaints involve buyer, seller, and admin in the same thread.</p>
                    </div>
                </div>

                @if($canReply)
                    <form wire:submit="reply" class="rounded-2xl border border-black/8 bg-[#FCF9F4] p-4 dark:border-white/10 dark:bg-white/5">
                        <div class="space-y-2">
                            <label for="complaint-thread-message" class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Reply</label>
                            <textarea id="complaint-thread-message" wire:model="replyMessage" rows="5" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-[#D32F2F] focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100" placeholder="Add your response, evidence, or clarification..."></textarea>
                            @error('replyMessage')
                                <p class="text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-4 space-y-2">
                            <label for="complaint-thread-attachments" class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Attachments</label>
                            <input id="complaint-thread-attachments" wire:model="replyAttachments" type="file" multiple accept="image/*,video/*,application/pdf" class="block w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm file:mr-4 file:rounded-full file:border-0 file:bg-black file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white dark:border-white/10 dark:bg-gray-950 dark:text-gray-100 file:dark:bg-white file:dark:text-gray-950">
                            @error('replyAttachments')
                                <p class="text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                            @error('replyAttachments.*')
                                <p class="text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" wire:loading.attr="disabled" wire:target="reply" class="mt-4 inline-flex w-full items-center justify-center rounded-2xl bg-black px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-[#D32F2F] disabled:cursor-not-allowed disabled:opacity-70 dark:bg-white dark:text-gray-950 dark:hover:bg-[#D32F2F] dark:hover:text-white">
                            Send message
                        </button>
                    </form>
                @endif

                @if($canResolve)
                    <form class="rounded-2xl border border-black/8 bg-[#FCF9F4] p-4 dark:border-white/10 dark:bg-white/5">
                        <div class="space-y-2">
                            <label for="complaint-resolution-note" class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500 dark:text-gray-400">Resolution note</label>
                            <textarea id="complaint-resolution-note" wire:model="resolutionNote" rows="4" class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:ring-0 dark:border-white/10 dark:bg-gray-950 dark:text-gray-100" placeholder="State why the complaint should be refunded or rejected..."></textarea>
                            @error('resolutionNote')
                                <p class="text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-4 space-y-3">
                            <button type="button" wire:click="refundBuyer" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-70">
                                Refund buyer
                            </button>
                            <button type="button" wire:click="releaseFunds" wire:loading.attr="disabled" class="inline-flex w-full items-center justify-center rounded-2xl bg-gray-900 px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-gray-700 disabled:cursor-not-allowed disabled:opacity-70 dark:bg-white dark:text-gray-950 dark:hover:bg-gray-200">
                                Reject complaint and release funds
                            </button>
                        </div>
                    </form>
                @endif
            </aside>
        </div>
    </div>
</div>
