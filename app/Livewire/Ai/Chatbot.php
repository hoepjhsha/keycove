<?php

declare(strict_types=1);

namespace App\Livewire\Ai;

use App\Enums\UserRole;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\User;
use App\Services\Ai\AiAssistantService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Throwable;

class Chatbot extends Component
{
    public bool $isOpen = false;

    public string $message = '';

    public string $streamedReply = '';

    public bool $isReplyStreaming = false;

    public ?string $errorMessage = null;

    public ?int $conversationId = null;

    public function mount(): void
    {
        $conversation = $this->resolveConversation();

        $this->conversationId = $conversation?->id;
    }

    public function toggle(): void
    {
        $this->isOpen = ! $this->isOpen;
    }

    public function send(AiAssistantService $aiAssistantService): void
    {
        $message = $this->validatedMessage();

        $this->ensureIsNotRateLimited();
        $this->errorMessage = null;
        $this->isOpen = true;
        $this->isReplyStreaming = true;
        $this->streamedReply = '';

        $conversation = $this->recordUserMessage($message);
        $this->message = '';

        try {
            $response = $aiAssistantService->streamSiteChat(
                $this->conversationHistory($conversation),
                $this->chatContext(),
                function (string $chunk): void {
                    $this->streamedReply .= $chunk;
                    $this->stream(to: 'chatbot-reply-stream', content: $chunk);
                },
            );

            $conversation->messages()->create([
                'role'     => 'assistant',
                'content'  => $response->content,
                'metadata' => [
                    'model'             => config('services.ai.model'),
                    'reasoning_details' => $response->reasoningDetails,
                    'usage'             => $response->usage,
                ],
            ]);

            $conversation->forceFill([
                'last_interacted_at' => now(),
            ])->save();
        } catch (Throwable $exception) {
            $this->errorMessage = $exception->getMessage();
        } finally {
            $this->isReplyStreaming = false;
            $this->streamedReply = '';
        }
    }

    public function render(): View
    {
        return view('livewire.ai.chatbot', [
            'messages' => $this->messages(),
        ]);
    }

    /**
     * @return list<AiMessage>
     */
    protected function messages(): array
    {
        $conversation = $this->resolveConversation();

        if (! $conversation instanceof AiConversation) {
            return [];
        }

        return $conversation->messages()
            ->orderByDesc('id')
            ->limit((int) config('services.ai.chat_history_limit', 10))
            ->get()
            ->reverse()
            ->values()
            ->all();
    }

    protected function validatedMessage(): string
    {
        $validated = $this->validate([
            'message' => ['required', 'string', 'min:2', 'max:1200'],
        ]);

        return trim((string) $validated['message']);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 8)) {
            RateLimiter::hit($this->throttleKey(), 60);

            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'message' => 'Bạn đang gửi quá nhanh. Vui lòng thử lại sau '.$seconds.' giây.',
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate('site-chatbot|'.$this->actorKey().'|'.request()->ip());
    }

    protected function actorKey(): string
    {
        $user = auth()->guard('admin')->user() ?? auth()->user();

        return $user instanceof User ? (string) $user->id : session()->getId();
    }

    protected function resolveConversation(bool $createIfMissing = false): ?AiConversation
    {
        $user = auth()->guard('admin')->user() ?? auth()->user();
        $query = AiConversation::query()->where('scope', 'site-chatbot');

        if ($user instanceof User) {
            $query->where('user_id', $user->id);
        } else {
            $query->whereNull('user_id')->where('session_id', session()->getId());
        }

        $conversation = $query->latest('last_interacted_at')->first();

        if ($conversation instanceof AiConversation || ! $createIfMissing) {
            return $conversation;
        }

        $conversation = AiConversation::query()->create([
            'user_id'            => $user instanceof User ? $user->id : null,
            'session_id'         => $user instanceof User ? null : session()->getId(),
            'scope'              => 'site-chatbot',
            'title'              => 'KeyCove chatbot',
            'context'            => $this->conversationContext(),
            'last_interacted_at' => now(),
        ]);

        $this->conversationId = $conversation->id;

        return $conversation;
    }

    protected function recordUserMessage(string $message): AiConversation
    {
        $conversation = $this->resolveConversation(createIfMissing: true);

        $conversation->messages()->create([
            'role'     => 'user',
            'content'  => $message,
            'metadata' => ['route' => request()->route()?->getName()],
        ]);

        $conversation->forceFill([
            'context'            => $this->conversationContext(),
            'last_interacted_at' => now(),
            'title'              => $conversation->title ?: Str::limit($message, 80),
        ])->save();

        return $conversation;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function conversationHistory(AiConversation $conversation): array
    {
        return $conversation->messages()
            ->orderByDesc('id')
            ->limit((int) config('services.ai.chat_history_limit', 10))
            ->get()
            ->reverse()
            ->map(function (AiMessage $message): array {
                $payload = [
                    'role'    => $message->role,
                    'content' => $message->content,
                ];

                $reasoningDetails = $message->metadata['reasoning_details'] ?? null;

                if (is_array($reasoningDetails)) {
                    $payload['reasoning_details'] = $reasoningDetails;
                }

                return $payload;
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function chatContext(): array
    {
        $user = auth()->guard('admin')->user() ?? auth()->user();

        return [
            'app_name'      => config('app.name'),
            'current_route' => request()->route()?->getName(),
            'current_path'  => request()->path(),
            'current_url'   => request()->fullUrl(),
            'user'          => [
                'authenticated' => $user instanceof User,
                'role'          => $user instanceof User ? $this->roleLabel($user) : 'guest',
                'username'      => $user instanceof User ? $user->username : null,
                'seller_shop'   => $user instanceof User ? $user->seller?->shop_name : null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function conversationContext(): array
    {
        return [
            'route' => request()->route()?->getName(),
            'path'  => request()->path(),
        ];
    }

    protected function roleLabel(User $user): string
    {
        return match ($user->role) {
            UserRole::Seller => 'seller',
            UserRole::Admin, UserRole::SuperAdmin => 'admin',
            default => 'buyer',
        };
    }
}
