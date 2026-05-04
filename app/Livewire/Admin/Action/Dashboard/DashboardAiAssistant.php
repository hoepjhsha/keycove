<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Action\Dashboard;

use App\Models\User;
use App\Services\Ai\AiAssistantService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class DashboardAiAssistant extends Component
{
    /** @var array<string, mixed> */
    #[Locked]
    public array $context = [];

    #[Locked]
    public string $rangeLabel = '';

    public string $question = '';

    public string $submittedQuestion = '';

    public ?string $insight = null;

    public string $streamedInsight = '';

    public bool $hasLongInsight = false;

    public bool $isInsightExpanded = false;

    public ?string $answer = null;

    public string $streamedAnswer = '';

    public bool $hasLongAnswer = false;

    public bool $isAnswerExpanded = false;

    public ?string $errorMessage = null;

    /**
     * @param  array<string, mixed>  $context
     */
    public function mount(array $context = [], string $rangeLabel = ''): void
    {
        $this->context = $context;
        $this->rangeLabel = $rangeLabel;

        $this->loadStoredInsight();
    }

    public function queueInsightGeneration(AiAssistantService $aiAssistantService): void
    {
        $this->generateInsight($aiAssistantService);
    }

    public function generateInsight(AiAssistantService $aiAssistantService): void
    {
        $this->ensureIsNotRateLimited('insight', 6);
        $this->errorMessage = null;
        $this->streamedInsight = '';

        try {
            $response = $aiAssistantService->streamDashboardInsight(
                $this->context,
                $this->rangeLabel,
                function (string $chunk): void {
                    $this->streamedInsight .= $chunk;
                    $this->stream(to: 'dashboard-insight-stream', content: $chunk);
                },
            );

            $this->storeInsight($response->content);
            $this->setInsight($response->content);
        } catch (Throwable $exception) {
            $this->errorMessage = $exception->getMessage();
        } finally {
            $this->streamedInsight = '';
        }
    }

    public function toggleInsightPreview(): void
    {
        if (! $this->hasLongInsight) {
            return;
        }

        $this->isInsightExpanded = ! $this->isInsightExpanded;
    }

    public function queueQuestion(AiAssistantService $aiAssistantService): void
    {
        $this->ask($aiAssistantService);
    }

    public function ask(AiAssistantService $aiAssistantService): void
    {
        $question = $this->validatedQuestion();

        $this->submittedQuestion = $question;
        $this->question = '';
        $this->setAnswer(null);

        $this->ensureIsNotRateLimited('question', 10);
        $this->errorMessage = null;
        $this->streamedAnswer = '';

        try {
            $response = $aiAssistantService->streamDashboardQuestion(
                $this->context,
                $this->rangeLabel,
                $question,
                function (string $chunk): void {
                    $this->streamedAnswer .= $chunk;
                    $this->stream(to: 'dashboard-answer-stream', content: $chunk);
                },
            );

            $this->setAnswer($response->content);
        } catch (Throwable $exception) {
            $this->errorMessage = $exception->getMessage();
        } finally {
            $this->streamedAnswer = '';
        }
    }

    public function toggleAnswerPreview(): void
    {
        if (! $this->hasLongAnswer) {
            return;
        }

        $this->isAnswerExpanded = ! $this->isAnswerExpanded;
    }

    public function render(): View
    {
        return view('livewire.admin.action.dashboard.dashboard-ai-assistant');
    }

    protected function ensureIsNotRateLimited(string $action, int $maxAttempts): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($action), $maxAttempts)) {
            RateLimiter::hit($this->throttleKey($action), 60);

            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($action));

        throw ValidationException::withMessages([
            'question' => 'Bạn đã gửi quá nhiều yêu cầu AI. Vui lòng thử lại sau '.$seconds.' giây.',
        ]);
    }

    protected function validatedQuestion(): string
    {
        $validated = $this->validate([
            'question' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        return trim((string) $validated['question']);
    }

    protected function throttleKey(string $action): string
    {
        return Str::transliterate('dashboard-ai|'.$action.'|'.$this->actorKey().'|'.request()->ip());
    }

    protected function actorKey(): string
    {
        $user = auth()->guard('admin')->user() ?? auth()->user();

        return $user instanceof User ? (string) $user->id : 'guest';
    }

    protected function loadStoredInsight(): void
    {
        try {
            $disk = Storage::disk($this->insightDisk());
            $path = $this->insightPath();

            if (! $disk->exists($path)) {
                return;
            }

            $this->setInsight($disk->get($path));
        } catch (Throwable) {
            return;
        }
    }

    protected function storeInsight(string $insight): void
    {
        Storage::disk($this->insightDisk())->put($this->insightPath(), trim($insight));
    }

    protected function setInsight(?string $insight): void
    {
        $normalizedInsight = is_string($insight) ? trim($insight) : '';

        $this->insight = $normalizedInsight !== '' ? $normalizedInsight : null;
        $this->hasLongInsight = $this->insight !== null
            && (Str::length($this->insight) > 650 || substr_count($this->insight, "\n") >= 10);

        if (! $this->hasLongInsight) {
            $this->isInsightExpanded = false;
        }
    }

    protected function setAnswer(?string $answer): void
    {
        $normalizedAnswer = is_string($answer) ? trim($answer) : '';

        $this->answer = $normalizedAnswer !== '' ? $normalizedAnswer : null;
        $this->hasLongAnswer = $this->answer !== null
            && (Str::length($this->answer) > 650 || substr_count($this->answer, "\n") >= 10);

        if (! $this->hasLongAnswer) {
            $this->isAnswerExpanded = false;
        }
    }

    protected function insightPath(): string
    {
        return 'ai/dashboard-insights/'.$this->snapshotSignature().'.md';
    }

    protected function insightDisk(): string
    {
        return (string) config('filesystems.default', 'local');
    }

    protected function snapshotSignature(): string
    {
        $context = json_encode($this->context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return md5($this->rangeLabel.($context ?: '{}'));
    }
}
