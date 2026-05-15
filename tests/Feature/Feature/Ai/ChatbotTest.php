<?php

declare(strict_types=1);

use App\Livewire\Ai\Chatbot;
use App\Models\AiConversation;
use App\Models\AiMessage;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

it('stores user and assistant messages in the site chatbot conversation', function (): void {
    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content'           => 'Bạn có thể vào khu vực seller để quản lý listing và rút tiền.',
                        'reasoning_details' => [
                            ['type' => 'reasoning.text', 'text' => 'Need direct seller navigation answer.'],
                        ],
                    ],
                ],
            ],
            'usage' => [
                'prompt_tokens'     => 10,
                'completion_tokens' => 12,
                'total_tokens'      => 22,
            ],
        ]),
    ]);

    config()->set('services.ai.api_key', 'test-key');
    config()->set('services.ai.provider', 'openai');
    config()->set('services.ai.base_url', 'https://api.openai.com/v1');
    config()->set('services.ai.model', 'test-model');

    Livewire::test(Chatbot::class)
        ->set('isOpen', true)
        ->set('message', 'Tôi muốn biết seller quản lý listing ở đâu?')
        ->call('send')
        ->assertSet('message', '')
        ->assertSee('seller để quản lý listing');

    expect(AiConversation::query()->count())->toBe(1)
        ->and(AiMessage::query()->count())->toBe(2)
        ->and(AiMessage::query()->where('role', 'user')->exists())->toBeTrue()
        ->and(AiMessage::query()->where('role', 'assistant')->exists())->toBeTrue()
        ->and(AiMessage::query()->where('role', 'assistant')->first()?->metadata['reasoning_details'] ?? null)->toBeArray();

    Http::assertSent(function ($request): bool {
        $payload = $request->data();

        return $request->url() === 'https://api.openai.com/v1/chat/completions'
            && ($payload['reasoning']['enabled'] ?? null) === true
            && ($payload['stream'] ?? null) === true
            && ($request->header('HTTP-Referer')[0] ?? null) !== null
            && ($request->header('X-Title')[0] ?? null) !== null
            && ($request->header('X-OpenRouter-Title')[0] ?? null) !== null;
    });
});

it('passes reasoning details back to the provider on follow-up messages', function (): void {
    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::sequence()
            ->push([
                'choices' => [[
                    'message' => [
                        'content'           => 'Có 3 chữ r trong strawberry.',
                        'reasoning_details' => [
                            ['type' => 'reasoning.text', 'text' => 'Count letters carefully.'],
                        ],
                    ],
                ]],
            ])
            ->push([
                'choices' => [[
                    'message' => [
                        'content' => 'Đúng, strawberry có 3 chữ r.',
                    ],
                ]],
            ]),
    ]);

    config()->set('services.ai.api_key', 'test-key');
    config()->set('services.ai.provider', 'openai');
    config()->set('services.ai.base_url', 'https://api.openai.com/v1');
    config()->set('services.ai.model', 'test-model');

    $component = Livewire::test(Chatbot::class)
        ->set('isOpen', true)
        ->set('message', 'Có bao nhiêu chữ r trong strawberry?')
        ->call('send')
        ->set('message', 'Bạn chắc chưa? Hãy nghĩ kỹ.')
        ->call('send');

    $component->assertSee('Đúng, strawberry có 3 chữ r.');

    Http::assertSent(function ($request): bool {
        $payload = $request->data();
        $messages = $payload['messages'] ?? [];

        if (! is_array($messages)) {
            return false;
        }

        foreach ($messages as $message) {
            if (($message['role'] ?? null) === 'assistant' && isset($message['reasoning_details'])) {
                return is_array($message['reasoning_details']);
            }
        }

        return false;
    });
});

it('shows a friendly configuration error when the AI service is missing', function (): void {
    config()->set('services.ai.api_key', null);
    config()->set('services.ai.provider', 'openai');
    config()->set('services.ai.base_url', 'https://api.openai.com/v1');
    config()->set('services.ai.model', 'test-model');

    Livewire::test(Chatbot::class)
        ->set('isOpen', true)
        ->set('message', 'Xin chào')
        ->call('send')
        ->assertSee('AI chưa được cấu hình đầy đủ');
});
