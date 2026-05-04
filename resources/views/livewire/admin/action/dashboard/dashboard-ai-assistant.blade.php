<section class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        @php
            $insightHtml = $insight
                ? \Illuminate\Support\Str::markdown($insight, [
                    'html_input' => 'strip',
                    'allow_unsafe_links' => false,
                ])
                : null;
        @endphp

        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-slate-950 dark:text-white">AI Insight</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Tóm tắt điểm nổi bật, rủi ro và hành động gợi ý cho giai đoạn {{ $rangeLabel }}.</p>
            </div>

            <button type="button"
                    wire:click="queueInsightGeneration"
                    class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 data-loading:cursor-wait data-loading:opacity-60 dark:bg-sky-500 dark:text-slate-950 dark:hover:bg-sky-400">
                {{ $insight ? 'Tạo lại insight' : 'Tạo insight' }}
            </button>
        </div>

        @if($errorMessage)
            <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-200">
                {{ $errorMessage }}
            </div>
        @endif

        <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-600 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-300">
            <div wire:loading wire:target="queueInsightGeneration" class="flex items-center gap-2 border-b border-slate-200 pb-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:border-slate-800 dark:text-slate-400">
                <span class="inline-flex h-2 w-2 rounded-full bg-sky-500"></span>
                Đang stream insight...
            </div>

            <pre wire:loading wire:target="queueInsightGeneration" wire:stream="dashboard-insight-stream" class="mt-4 overflow-x-auto whitespace-pre-wrap font-sans text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $streamedInsight }}</pre>

            <div wire:loading.remove wire:target="queueInsightGeneration">
                @if($insight)
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 pb-3 dark:border-slate-800">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Markdown preview</p>

                    <div class="flex items-center gap-3">
                        @if($hasLongInsight)
                            <button type="button"
                                    wire:click="toggleInsightPreview"
                                    class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 transition hover:border-sky-200 hover:text-sky-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-sky-500/30 dark:hover:text-sky-300">
                                {{ $isInsightExpanded ? 'Thu gọn' : 'Xem thêm' }}
                            </button>
                        @endif
                    </div>
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
                    Chưa tạo insight. Bấm <span class="font-semibold text-slate-900 dark:text-white">Tạo insight</span> để AI đọc snapshot dashboard hiện tại.
                @endif
            </div>
        </div>
    </article>

    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div>
            <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Hỏi AI về số liệu</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Ví dụ: Tỷ lệ tranh chấp có đáng lo không? Seller nào đang tạo doanh thu cao nhất?</p>
        </div>

        @php
            $answerHtml = $answer
                ? \Illuminate\Support\Str::markdown($answer, [
                    'html_input' => 'strip',
                    'allow_unsafe_links' => false,
                ])
                : null;
        @endphp

        <form wire:submit="queueQuestion" class="mt-5 space-y-4">
            <div>
                <label for="dashboard-ai-question" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Câu hỏi</label>
                <textarea id="dashboard-ai-question"
                          wire:model="question"
                          rows="4"
                          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200"
                          placeholder="Hỏi về doanh thu, tăng trưởng, khiếu nại, seller, hoặc vận hành..."></textarea>
                @error('question')
                    <p class="mt-2 text-sm text-rose-600 dark:text-rose-300">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-sky-200 hover:text-sky-700 data-loading:cursor-wait data-loading:opacity-60 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:hover:border-sky-500/30 dark:hover:text-sky-300">
                Gửi câu hỏi
            </button>
        </form>

        <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm leading-6 text-slate-600 dark:border-slate-700 dark:bg-slate-950/50 dark:text-slate-300">
            @if($submittedQuestion !== '')
                <p class="mb-3 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Câu hỏi gần nhất</p>
                <p class="rounded-2xl bg-white px-4 py-3 text-sm text-slate-700 dark:bg-slate-900 dark:text-slate-200">{{ $submittedQuestion }}</p>
            @endif

            <div wire:loading wire:target="queueQuestion" class="mt-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                <span class="inline-flex h-2 w-2 rounded-full bg-sky-500"></span>
                Đang stream câu trả lời...
            </div>

            <pre wire:loading wire:target="queueQuestion" wire:stream="dashboard-answer-stream" class="mt-3 overflow-x-auto whitespace-pre-wrap font-sans text-sm leading-6 text-slate-700 dark:text-slate-200">{{ $streamedAnswer }}</pre>

            <div wire:loading.remove wire:target="queueQuestion">
                @if($answer)
                <div class="mt-4 flex items-center justify-between gap-3 border-b border-slate-200 pb-3 dark:border-slate-800">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Markdown preview</p>

                    @if($hasLongAnswer)
                        <button type="button"
                                wire:click="toggleAnswerPreview"
                                class="inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-600 transition hover:border-sky-200 hover:text-sky-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-sky-500/30 dark:hover:text-sky-300">
                            {{ $isAnswerExpanded ? 'Thu gọn' : 'Xem thêm' }}
                        </button>
                    @endif
                </div>

                <div @class([
                    'relative mt-4 overflow-hidden transition-all',
                    'max-h-80' => $hasLongAnswer && ! $isAnswerExpanded,
                ])>
                    <article class="space-y-3 text-sm leading-6 text-slate-700 dark:text-slate-200 [&_h2:first-child]:mt-0 [&_h2]:mt-5 [&_h2]:text-base [&_h2]:font-semibold [&_li]:mt-1 [&_ol]:list-decimal [&_ol]:space-y-1 [&_ol]:pl-5 [&_p:first-child]:mt-0 [&_p]:mt-3 [&_strong]:font-semibold [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:pl-5">
                        {!! $answerHtml !!}
                    </article>

                    @if($hasLongAnswer && ! $isAnswerExpanded)
                        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-20 bg-linear-to-t from-slate-50 to-transparent dark:from-slate-950/95"></div>
                    @endif
                </div>
                @else
                    Câu trả lời sẽ xuất hiện ở đây.
                @endif
            </div>
        </div>
    </article>
</section>
