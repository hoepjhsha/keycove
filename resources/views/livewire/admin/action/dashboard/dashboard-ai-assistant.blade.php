@php
    $insightHtml = $insight
        ? \Illuminate\Support\Str::markdown($insight, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ])
        : null;

    $answerHtml = $answer
        ? \Illuminate\Support\Str::markdown($answer, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ])
        : null;

    $suggestedQuestions = [
        'Seller nào đang bán tốt nhất?',
        'Complaint rate có đáng lo không?',
        'Doanh thu đến từ platform fee hay product owned?',
        'Category nào đang hot?',
    ];
@endphp

<section class="grid gap-6 xl:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-600 dark:text-sky-400">AI Analyst</p>
                <h3 class="mt-2 text-lg font-semibold text-slate-950 dark:text-white">AI Insight</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tóm tắt cho giai đoạn {{ $rangeLabel }}.</p>
            </div>

            <button type="button"
                    wire:click="queueInsightGeneration"
                    class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 data-loading:cursor-wait data-loading:opacity-60 dark:bg-sky-500 dark:text-slate-950 dark:hover:bg-sky-400">
                {{ $insight ? 'Làm mới insight' : 'Tạo insight' }}
            </button>
        </div>

        @if($errorMessage)
            <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-200">
                {{ $errorMessage }}
            </div>
        @endif

        <div class="mt-5 rounded-3xl border border-slate-200 bg-slate-50 px-4 py-4 dark:border-slate-800 dark:bg-slate-950/40">
            <div wire:loading wire:target="queueInsightGeneration" class="space-y-4">
                <div class="flex items-center gap-3 text-sm font-semibold text-slate-700 dark:text-slate-200">
                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-sky-500"></span>
                    AI đang tạo insight...
                </div>

                <div class="space-y-3">
                    <div class="h-4 w-3/4 rounded-full bg-slate-200 dark:bg-slate-800"></div>
                    <div class="h-4 w-full rounded-full bg-slate-200 dark:bg-slate-800"></div>
                    <div class="h-4 w-5/6 rounded-full bg-slate-200 dark:bg-slate-800"></div>
                </div>
            </div>

            <div wire:loading.remove wire:target="queueInsightGeneration">
                @if($insight)
                    <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3 dark:border-slate-800">
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                            <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                            Snapshot insight
                        </div>

                        @if($hasLongInsight)
                            <button type="button"
                                    wire:click="toggleInsightPreview"
                                    class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 transition hover:border-sky-200 hover:text-sky-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-sky-500/30 dark:hover:text-sky-300">
                                {{ $isInsightExpanded ? 'Thu gọn' : 'Xem thêm' }}
                            </button>
                        @endif
                    </div>

                    <div @class([
                        'relative mt-4 overflow-hidden transition-all',
                        'max-h-80' => $hasLongInsight && ! $isInsightExpanded,
                    ])>
                        <article class="space-y-3 text-sm leading-6 text-slate-700 dark:text-slate-200 [&_h2:first-child]:mt-0 [&_h2]:mt-5 [&_h2]:text-base [&_h2]:font-semibold [&_li]:mt-1 [&_ol]:list-decimal [&_ol]:space-y-1 [&_ol]:pl-5 [&_p:first-child]:mt-0 [&_p]:mt-3 [&_strong]:font-semibold [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:pl-5">
                            {!! $insightHtml !!}
                        </article>

                        @if($hasLongInsight && ! $isInsightExpanded)
                            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-linear-to-t from-slate-50 to-transparent dark:from-slate-950/95"></div>
                        @endif
                    </div>
                @else
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-5 text-sm leading-6 text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
                        Chưa có insight cho snapshot này. Bấm <span class="font-semibold text-slate-900 dark:text-white">Tạo insight</span> để AI đọc dashboard hiện tại.
                    </div>
                @endif
            </div>
        </div>
    </article>

    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-600 dark:text-sky-400">AI Chat</p>
                <h3 class="mt-2 text-lg font-semibold text-slate-950 dark:text-white">Hỏi về dashboard</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Hỏi nhanh về doanh thu, seller, category hoặc complaint.</p>
            </div>

            <div class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-500 dark:border-slate-800 dark:bg-slate-950/60 dark:text-slate-400">
                Gemini / {{ $rangeLabel }}
            </div>
        </div>

        <div class="mt-5 flex flex-wrap gap-2">
            @foreach($suggestedQuestions as $suggestedQuestion)
                <button type="button"
                        wire:key="suggested-question-{{ $loop->index }}"
                        wire:click="askSuggested(@js($suggestedQuestion))"
                        class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:border-sky-200 hover:text-sky-700 data-loading:cursor-wait data-loading:opacity-60 dark:border-slate-700 dark:bg-slate-950/60 dark:text-slate-300 dark:hover:border-sky-500/30 dark:hover:text-sky-300">
                    {{ $suggestedQuestion }}
                </button>
            @endforeach
        </div>

        <div class="mt-5 rounded-3xl border border-slate-200 bg-slate-50 px-4 py-4 dark:border-slate-800 dark:bg-slate-950/40">
            @if($submittedQuestion !== '')
                <div class="flex justify-end">
                    <div class="max-w-[85%] rounded-3xl rounded-br-md bg-slate-950 px-4 py-3 text-sm leading-6 text-white shadow-sm dark:bg-sky-500 dark:text-slate-950">
                        {{ $submittedQuestion }}
                    </div>
                </div>
            @endif

            @if($errorMessage)
                <div class="mt-4 flex justify-start">
                    <div class="max-w-[85%] rounded-3xl rounded-bl-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm leading-6 text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-200">
                        {{ $errorMessage }}
                    </div>
                </div>
            @endif

            <div wire:loading wire:target="queueQuestion,askSuggested" class="mt-4 flex justify-start">
                <div class="max-w-[85%] rounded-3xl rounded-bl-md border border-slate-200 bg-white px-4 py-3 text-sm leading-6 text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
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

            <div wire:loading.remove wire:target="queueQuestion,askSuggested">
                @if($answer)
                    <div class="mt-4 flex justify-start">
                        <div class="max-w-[85%] rounded-3xl rounded-bl-md bg-white px-4 py-4 text-sm leading-6 text-slate-700 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-800">
                            <div class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                                AI trả lời
                            </div>

                            <div @class([
                                'relative overflow-hidden transition-all',
                                'max-h-80' => $hasLongAnswer && ! $isAnswerExpanded,
                            ])>
                                <article class="space-y-3 [&_h2:first-child]:mt-0 [&_h2]:mt-5 [&_h2]:text-base [&_h2]:font-semibold [&_li]:mt-1 [&_ol]:list-decimal [&_ol]:space-y-1 [&_ol]:pl-5 [&_p:first-child]:mt-0 [&_p]:mt-3 [&_strong]:font-semibold [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:pl-5">
                                    {!! $answerHtml !!}
                                </article>

                                @if($hasLongAnswer && ! $isAnswerExpanded)
                                    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-linear-to-t from-white to-transparent dark:from-slate-900 dark:to-transparent"></div>
                                @endif
                            </div>

                            @if($hasLongAnswer)
                                <div class="mt-4 flex justify-end">
                                    <button type="button"
                                            wire:click="toggleAnswerPreview"
                                            class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600 transition hover:border-sky-200 hover:text-sky-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300 dark:hover:border-sky-500/30 dark:hover:text-sky-300">
                                        {{ $isAnswerExpanded ? 'Thu gọn' : 'Xem thêm' }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="flex justify-start">
                        <div class="max-w-[85%] rounded-3xl rounded-bl-md border border-dashed border-slate-300 bg-white px-4 py-4 text-sm leading-6 text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
                            Chọn câu hỏi gợi ý hoặc nhập câu hỏi của bạn.
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <form wire:submit="queueQuestion" class="mt-5">
            <label for="dashboard-ai-question" class="sr-only">Câu hỏi</label>
            <textarea id="dashboard-ai-question"
                      wire:model="question"
                      rows="3"
                      class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
                      placeholder="Hỏi về doanh thu, seller, category hoặc complaint..."></textarea>
            @error('question')
                <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
            @enderror

            <div class="mt-3 flex items-center justify-end gap-3">
                <button type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 data-loading:cursor-wait data-loading:opacity-60 dark:bg-sky-500 dark:text-slate-950 dark:hover:bg-sky-400">
                    Gửi
                </button>
            </div>
        </form>
    </article>
</section>
