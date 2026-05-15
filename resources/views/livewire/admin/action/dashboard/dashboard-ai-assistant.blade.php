@php
    $insightHtml = $insight
        ? \Illuminate\Support\Str::markdown($insight, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ])
        : null;

    $suggestedQuestions = [
        'Nhà bán nào đang bán tốt nhất?',
        'Tỷ lệ khiếu nại có đáng lo không?',
        'Doanh thu đến từ phí nền tảng hay hàng tự vận hành?',
        'Danh mục nào đang nổi bật?',
    ];
@endphp

<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50" aria-modal="true" role="dialog">
            <button type="button"
                    wire:click="closeAssistant"
                    aria-label="Đóng trợ lý AI"
                    class="absolute inset-0 bg-slate-950/45 backdrop-blur-[2px]"></button>

            <section class="absolute inset-y-0 right-0 flex h-full w-full flex-col border-l border-slate-200 bg-white shadow-[0_20px_80px_-32px_rgba(15,23,42,0.45)] dark:border-slate-800 dark:bg-slate-900 sm:max-w-[30rem] lg:max-w-[32rem]">
            <header class="border-b border-slate-200 px-5 py-5 dark:border-slate-800 sm:px-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-600 dark:text-sky-400">Trợ lý AI</p>
                        <h3 class="mt-2 text-lg font-semibold text-slate-950 dark:text-white">Trợ lý dashboard</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $rangeLabel }}</p>
                    </div>

                    <button type="button"
                            wire:click="closeAssistant"
                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="mt-4 inline-flex rounded-2xl bg-slate-100 p-1 dark:bg-slate-800/80">
                    <button type="button"
                            wire:click="setPanel('insight')"
                            @class([
                                'rounded-xl px-4 py-2 text-sm font-semibold transition',
                                'bg-white text-slate-950 shadow-sm dark:bg-slate-900 dark:text-white' => $activePanel === 'insight',
                                'text-slate-500 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white' => $activePanel !== 'insight',
                            ])>
                        Nhận định AI
                    </button>
                    <button type="button"
                            wire:click="setPanel('chat')"
                            @class([
                                'rounded-xl px-4 py-2 text-sm font-semibold transition',
                                'bg-white text-slate-950 shadow-sm dark:bg-slate-900 dark:text-white' => $activePanel === 'chat',
                                'text-slate-500 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white' => $activePanel !== 'chat',
                            ])>
                        Hỏi về dashboard
                    </button>
                </div>
            </header>

            @if($activePanel === 'insight')
                <div class="flex min-h-0 flex-1 flex-col">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4 dark:border-slate-800 sm:px-6">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-950 dark:text-white">Nhận định AI</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tóm tắt nhanh cho dữ liệu hiện tại.</p>
                        </div>

                        <button type="button"
                                wire:click="queueInsightGeneration"
                                wire:loading.attr="disabled"
                                wire:target="queueInsightGeneration"
                                class="inline-flex shrink-0 items-center justify-center rounded-2xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 data-loading:cursor-wait data-loading:opacity-60 dark:bg-sky-500 dark:text-slate-950 dark:hover:bg-sky-400">
                            {{ $insight ? 'Làm mới' : 'Tạo nhận định' }}
                        </button>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto px-5 py-5 sm:px-6">
                        @if($insightErrorMessage)
                            <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-200">
                                {{ $insightErrorMessage }}
                            </div>
                        @endif

                        <div wire:loading wire:target="queueInsightGeneration" class="space-y-4 rounded-3xl border border-slate-200 bg-slate-50 px-4 py-4 dark:border-slate-800 dark:bg-slate-950/40">
                            <div class="flex items-center gap-3 text-sm font-semibold text-slate-700 dark:text-slate-200">
                                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-sky-500"></span>
                                AI đang tạo nhận định...
                            </div>

                            <div class="space-y-3">
                                <div class="h-4 w-3/4 rounded-full bg-slate-200 dark:bg-slate-800"></div>
                                <div class="h-4 w-full rounded-full bg-slate-200 dark:bg-slate-800"></div>
                                <div class="h-4 w-5/6 rounded-full bg-slate-200 dark:bg-slate-800"></div>
                            </div>
                        </div>

                        <div wire:loading.remove wire:target="queueInsightGeneration">
                            @if($insight)
                                <article class="rounded-3xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-700 dark:border-slate-800 dark:bg-slate-950/40 dark:text-slate-200 [&_h2:first-child]:mt-0 [&_h2]:mt-5 [&_h2]:text-base [&_h2]:font-semibold [&_li]:mt-1 [&_ol]:list-decimal [&_ol]:space-y-1 [&_ol]:pl-5 [&_p:first-child]:mt-0 [&_p]:mt-3 [&_strong]:font-semibold [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:pl-5">
                                    {!! $insightHtml !!}
                                </article>
                            @else
                                <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-sm leading-6 text-slate-500 dark:border-slate-700 dark:bg-slate-950/40 dark:text-slate-400">
                                    Chưa có nhận định cho dữ liệu này. Bấm <span class="font-semibold text-slate-900 dark:text-white">Tạo nhận định</span> để AI đọc dashboard hiện tại.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="flex min-h-0 flex-1 flex-col">
                    <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-800 sm:px-6">
                        <div class="flex flex-wrap gap-2">
                            @foreach($suggestedQuestions as $suggestedQuestion)
                                <button type="button"
                                        wire:key="suggested-question-{{ $loop->index }}"
                                        wire:click="askSuggested(@js($suggestedQuestion))"
                                        wire:loading.attr="disabled"
                                        wire:target="askSuggested"
                                        class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-sky-200 hover:text-sky-700 data-loading:cursor-wait data-loading:opacity-60 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-300 dark:hover:border-sky-500/30 dark:hover:text-sky-300">
                                    {{ $suggestedQuestion }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto bg-slate-50 px-5 py-5 dark:bg-slate-950/60 sm:px-6">
                        <div class="flex min-h-full flex-col gap-4">
                            @forelse($chatMessages as $chatMessage)
                                @php
                                    $isUserMessage = $chatMessage['role'] === 'user';
                                    $isErrorMessage = $chatMessage['role'] === 'error';
                                    $chatMessageHtml = ! $isUserMessage && ! $isErrorMessage
                                        ? \Illuminate\Support\Str::markdown($chatMessage['content'], [
                                            'html_input' => 'strip',
                                            'allow_unsafe_links' => false,
                                        ])
                                        : null;
                                @endphp

                                <article wire:key="dashboard-chat-message-{{ $chatMessage['id'] }}"
                                         class="flex {{ $isUserMessage ? 'justify-end' : 'justify-start' }}">
                                    <div @class([
                                        'max-w-[88%] rounded-3xl px-4 py-3 text-sm leading-6 shadow-sm',
                                        'rounded-br-md bg-slate-950 text-white dark:bg-sky-500 dark:text-slate-950' => $isUserMessage,
                                        'rounded-bl-md border border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-200' => $isErrorMessage,
                                        'rounded-bl-md bg-white text-slate-700 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-800' => ! $isUserMessage && ! $isErrorMessage,
                                    ])>
                                        @if($isUserMessage || $isErrorMessage)
                                            {{ $chatMessage['content'] }}
                                        @else
                                            <div class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                                                AI trả lời
                                            </div>

                                            <article class="space-y-3 break-words [&_h2:first-child]:mt-0 [&_h2]:mt-5 [&_h2]:text-base [&_h2]:font-semibold [&_li]:mt-1 [&_ol]:list-decimal [&_ol]:space-y-1 [&_ol]:pl-5 [&_p:first-child]:mt-0 [&_p]:mt-3 [&_strong]:font-semibold [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:pl-5">
                                                {!! $chatMessageHtml !!}
                                            </article>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <div class="flex min-h-full items-center">
                                    <div class="w-full rounded-3xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm leading-6 text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
                                         Chọn câu hỏi gợi ý hoặc nhập câu hỏi để bắt đầu cuộc hội thoại.
                                    </div>
                                </div>
                            @endforelse

                            <div wire:loading wire:target="queueQuestion,askSuggested" class="flex justify-start">
                                <div class="max-w-[88%] rounded-3xl rounded-bl-md border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                                    <div class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                        <span class="inline-flex h-2 w-2 rounded-full bg-sky-500"></span>
                                        AI đang phân tích...
                                    </div>

                                    <div class="space-y-2">
                                        <div class="h-4 w-2/3 rounded-full bg-slate-200 dark:bg-slate-800"></div>
                                        <div class="h-4 w-full rounded-full bg-slate-200 dark:bg-slate-800"></div>
                                        <div class="h-4 w-5/6 rounded-full bg-slate-200 dark:bg-slate-800"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form wire:submit="queueQuestion" class="border-t border-slate-200 bg-white px-5 py-4 dark:border-slate-800 dark:bg-slate-900 sm:px-6">
                        <label for="dashboard-ai-question" class="sr-only">Câu hỏi</label>
                        <textarea id="dashboard-ai-question"
                                  wire:model="question"
                                  wire:loading.attr="disabled"
                                  wire:target="queueQuestion"
                                  rows="3"
                                  class="w-full resize-none rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
                                   placeholder="Hỏi về doanh thu, nhà bán, danh mục hoặc khiếu nại..."></textarea>
                        @error('question')
                            <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                        @enderror

                        <div class="mt-3 flex items-center justify-end gap-3">
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="queueQuestion"
                                    class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 data-loading:cursor-wait data-loading:opacity-60 dark:bg-sky-500 dark:text-slate-950 dark:hover:bg-sky-400">
                                Gửi
                            </button>
                        </div>
                    </form>
                </div>
            @endif
            </section>
        </div>
    @endif
</div>
