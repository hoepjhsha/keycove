<div class="fixed bottom-4 right-4 z-50 flex max-w-[calc(100vw-2rem)] flex-col items-end gap-3">
    @if($isOpen)
        <section class="flex h-[32rem] w-[22rem] max-w-full flex-col overflow-hidden rounded-[1.75rem] border border-black/8 bg-white shadow-[0_24px_70px_-35px_rgba(15,23,42,0.45)] dark:border-white/10 dark:bg-slate-900 sm:w-[24rem]">
            <div class="flex items-center justify-between gap-3 border-b border-black/6 px-4 py-4 dark:border-white/10">
                <div>
                    <p class="text-sm font-semibold text-slate-950 dark:text-white">Trợ lý KeyCove</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Hỗ trợ buyer, seller và admin theo trang hiện tại.</p>
                </div>

                <button type="button" wire:click="toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-black/8 text-slate-600 transition hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="flex-1 space-y-3 overflow-y-auto bg-slate-50 px-4 py-4 dark:bg-slate-950/70">
                @if($messages === [])
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
                        Bạn có thể hỏi về cách mua hàng, seller portal, khiếu nại, hoặc cách điều hướng trong KeyCove.
                    </div>
                @endif

                @foreach($messages as $chatMessage)
                    <article wire:key="chat-message-{{ $chatMessage->id }}" class="flex {{ $chatMessage->role === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] rounded-2xl px-4 py-3 text-sm leading-6 {{ $chatMessage->role === 'user' ? 'bg-slate-950 text-white dark:bg-sky-500 dark:text-slate-950' : 'border border-black/8 bg-white text-slate-700 dark:border-white/10 dark:bg-slate-900 dark:text-slate-200' }}">
                            {!! nl2br(e($chatMessage->content)) !!}
                        </div>
                    </article>
                @endforeach

                @if($isReplyStreaming)
                    <article class="flex justify-start">
                        <div class="max-w-[85%] rounded-2xl border border-black/8 bg-white px-4 py-3 text-sm leading-6 text-slate-700 dark:border-white/10 dark:bg-slate-900 dark:text-slate-200">
                            <div class="mb-2 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400 dark:text-slate-500">
                                <span class="inline-flex h-2 w-2 rounded-full bg-sky-500"></span>
                                Đang trả lời...
                            </div>

                            <pre wire:stream="chatbot-reply-stream" class="whitespace-pre-wrap font-sans text-sm leading-6">{{ $streamedReply }}</pre>
                        </div>
                    </article>
                @endif
            </div>

            <form wire:submit="send" class="border-t border-black/6 bg-white px-4 py-4 dark:border-white/10 dark:bg-slate-900">
                @if($errorMessage)
                    <div class="mb-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-200">
                        {{ $errorMessage }}
                    </div>
                @endif

                <label for="site-chatbot-message" class="sr-only">Tin nhắn</label>
                <div class="flex items-end gap-3">
                    <textarea id="site-chatbot-message"
                              wire:model="message"
                              rows="2"
                              class="min-h-[5.5rem] flex-1 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
                              placeholder="Hỏi trợ lý KeyCove..."></textarea>

                    <button type="submit"
                            class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-950 text-white transition hover:bg-slate-800 data-loading:cursor-wait data-loading:opacity-60 dark:bg-sky-500 dark:text-slate-950 dark:hover:bg-sky-400">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>

                @error('message')
                    <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                @enderror
            </form>
        </section>
    @endif

    <button type="button"
            wire:click="toggle"
            class="inline-flex items-center gap-3 rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-slate-800 dark:bg-sky-500 dark:text-slate-950 dark:hover:bg-sky-400">
        <i class="fa-solid fa-robot text-base"></i>
        <span>{{ $isOpen ? 'Ẩn chatbot' : 'Mở chatbot' }}</span>
    </button>
</div>
