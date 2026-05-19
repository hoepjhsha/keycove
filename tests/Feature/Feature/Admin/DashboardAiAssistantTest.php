<?php

declare(strict_types=1);

use App\Livewire\Admin\Action\Dashboard\DashboardAiAssistant;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('generates and stores admin dashboard insights as markdown', function (): void {
    $diskRoot = storage_path('framework/testing/disks/ai-test-'.uniqid());
    File::ensureDirectoryExists($diskRoot);
    File::ensureDirectoryExists($diskRoot);

    config()->set('filesystems.disks.ai_test', [
        'driver' => 'local',
        'root'   => $diskRoot,
        'throw'  => false,
    ]);

    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => "## Điểm nổi bật\n- GMV đang ổn định.\n\n## Rủi ro\n- Tỷ lệ tranh chấp tăng.\n\n## Hành động đề xuất\n- Ưu tiên xử lý khiếu nại mở và KYC pending.",
                    ],
                ],
            ],
        ]),
    ]);

    config()->set('filesystems.default', 'ai_test');
    config()->set('services.ai.provider', 'openai');
    config()->set('services.ai.api_key', 'test-key');
    config()->set('services.ai.base_url', 'https://api.openai.com/v1');
    config()->set('services.ai.model', 'test-model');

    $admin = User::factory()->admin()->create();
    $rangeLabel = '01/05/2026 - 02/05/2026';
    $context = [
        'quick_stats' => [
            'gmv'            => 219000,
            'netRevenue'     => 21900,
            'openComplaints' => 3,
        ],
        'operations' => [
            'disputeRate' => 6.2,
            'pendingKyc'  => 4,
        ],
        'top_sellers' => [
            ['shop_name' => 'Keycove Seller', 'gross_revenue' => 120000],
        ],
    ];

    $path = 'ai/dashboard-insights/'.md5($rangeLabel.json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)).'.md';

    $component = Livewire::actingAs($admin, 'admin')
        ->test(DashboardAiAssistant::class, [
            'rangeLabel' => $rangeLabel,
            'context'    => $context,
        ])
        ->call('generateInsight')
        ->assertSee('Nhận định AI');

    expect(Storage::disk('ai_test')->exists($path))->toBeTrue();

    expect(Storage::disk('ai_test')->get($path))
        ->toContain('## Điểm nổi bật')
        ->toContain('## Hành động đề xuất');

    expect($component->get('insight'))
        ->toContain('## Điểm nổi bật');

    expect($component->get('hasLongInsight'))->toBeFalse();

    Http::assertSent(function ($request): bool {
        $payload = $request->data();

        return $request->url() === 'https://api.openai.com/v1/chat/completions'
            && ! ($payload['stream'] ?? false);
    });
});

it('generates dashboard insights with gemini', function (): void {
    $diskRoot = storage_path('framework/testing/disks/ai-test-'.uniqid());
    File::ensureDirectoryExists($diskRoot);

    config()->set('filesystems.disks.ai_test', [
        'driver' => 'local',
        'root'   => $diskRoot,
        'throw'  => false,
    ]);

    Http::fake([
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'parts' => [
                            [
                                'text' => "## Điểm nổi bật\n- Doanh thu nền tảng tăng.\n\n## Rủi ro\n- Complaint seller tăng.\n\n## Hành động đề xuất\n- Ưu tiên seller có complaint rate cao.",
                            ],
                        ],
                    ],
                ],
            ],
            'usageMetadata' => [
                'promptTokenCount'     => 120,
                'candidatesTokenCount' => 40,
                'totalTokenCount'      => 160,
            ],
        ]),
    ]);

    config()->set('filesystems.default', 'ai_test');
    config()->set('services.ai.provider', 'gemini');
    config()->set('services.ai.api_key', 'test-gemini-key');
    config()->set('services.ai.base_url', 'https://generativelanguage.googleapis.com/v1beta');
    config()->set('services.ai.model', 'gemini-flash-latest');

    $admin = User::factory()->admin()->create();
    $rangeLabel = '01/05/2026 - 02/05/2026';
    $context = [
        'revenue' => [
            'platformRevenue' => 219000,
        ],
    ];

    $path = 'ai/dashboard-insights/'.md5($rangeLabel.json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)).'.md';

    Livewire::actingAs($admin, 'admin')
        ->test(DashboardAiAssistant::class, [
            'rangeLabel' => $rangeLabel,
            'context'    => $context,
        ])
        ->call('generateInsight')
        ->assertSet('hasLongInsight', false);

    expect(Storage::disk('ai_test')->exists($path))->toBeTrue();

    Http::assertSent(function ($request): bool {
        $payload = $request->data();

        return $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent'
            && $request->header('X-goog-api-key')[0] === 'test-gemini-key'
            && data_get($payload, 'contents.0.role') === 'user'
            && data_get($payload, 'contents.0.parts.0.text') !== null;
    });
});

it('loads the saved markdown insight and lets admins collapse long content', function (): void {
    $diskRoot = storage_path('framework/testing/disks/ai-test-'.uniqid());
    File::ensureDirectoryExists($diskRoot);

    config()->set('filesystems.disks.ai_test', [
        'driver' => 'local',
        'root'   => $diskRoot,
        'throw'  => false,
    ]);
    config()->set('filesystems.default', 'ai_test');

    $rangeLabel = '01/05/2026 - 02/05/2026';
    $context = [
        'quick_stats' => [
            'gmv' => 219000,
        ],
    ];

    $longInsight = implode("\n", [
        '## Điểm nổi bật',
        '- GMV ổn định.',
        '- Đơn hàng tăng đều.',
        '- Seller mới tăng.',
        '',
        '## Rủi ro',
        '- Tỷ lệ tranh chấp tăng.',
        '- KYC pending còn nhiều.',
        '- Listing chờ duyệt đang dồn.',
        '',
        '## Hành động đề xuất',
        '- Ưu tiên xử lý khiếu nại mở.',
        '- Chia ca xử lý KYC.',
        '- Dọn hàng đợi listing.',
        '- Theo dõi lại sau 24 giờ.',
    ]);

    $path = 'ai/dashboard-insights/'.md5($rangeLabel.json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)).'.md';

    Storage::disk('ai_test')->put($path, $longInsight);

    $admin = User::factory()->admin()->create();

    $component = Livewire::actingAs($admin, 'admin')
        ->test(DashboardAiAssistant::class, [
            'rangeLabel' => $rangeLabel,
            'context'    => $context,
        ])
        ->call('openAssistant', 'insight')
        ->assertSee('Nhận định AI');

    expect($component->get('insight'))->toBe($longInsight);
    expect($component->get('hasLongInsight'))->toBeTrue();

    $component->call('toggleInsightPreview');

    expect($component->get('isInsightExpanded'))->toBeTrue();
});

it('answers questions about dashboard metrics', function (): void {
    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Nhà bán tạo doanh thu cao nhất hiện tại là Keycove Seller trong dữ liệu này.',
                    ],
                ],
            ],
        ]),
    ]);

    config()->set('services.ai.provider', 'openai');
    config()->set('services.ai.api_key', 'test-key');
    config()->set('services.ai.base_url', 'https://api.openai.com/v1');
    config()->set('services.ai.model', 'test-model');

    $admin = User::factory()->admin()->create();

    $component = Livewire::actingAs($admin, 'admin')
        ->test(DashboardAiAssistant::class, [
            'rangeLabel' => '01/05/2026 - 02/05/2026',
            'context'    => [
                'top_sellers' => [
                    ['shop_name' => 'Keycove Seller', 'gross_revenue' => 120000],
                ],
            ],
        ])
        ->set('question', 'Nhà bán nào đang tạo doanh thu cao nhất?')
        ->call('ask')
        ->assertSee('Hỏi về dashboard');

    expect($component->get('submittedQuestion'))->toBe('Nhà bán nào đang tạo doanh thu cao nhất?');
    expect($component->get('answer'))->toBe('Nhà bán tạo doanh thu cao nhất hiện tại là Keycove Seller trong dữ liệu này.');

    Http::assertSent(function ($request): bool {
        $payload = $request->data();

        return $request->url() === 'https://api.openai.com/v1/chat/completions'
            && ! ($payload['stream'] ?? false);
    });
});

it('runs suggested dashboard questions through the chat action', function (): void {
    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Danh mục nổi bật nhất là Action trong dữ liệu này.',
                    ],
                ],
            ],
        ]),
    ]);

    config()->set('services.ai.provider', 'openai');
    config()->set('services.ai.api_key', 'test-key');
    config()->set('services.ai.base_url', 'https://api.openai.com/v1');
    config()->set('services.ai.model', 'test-model');

    $admin = User::factory()->admin()->create();

    $component = Livewire::actingAs($admin, 'admin')
        ->test(DashboardAiAssistant::class, [
            'rangeLabel' => '01/05/2026 - 02/05/2026',
            'context'    => [
                'categories' => [
                    'favoriteCategories' => [
                        ['category_name' => 'Action', 'units_sold' => 12, 'revenue' => 120000],
                    ],
                ],
            ],
        ])
        ->call('askSuggested', 'Danh mục nào đang nổi bật?')
        ->assertSee('Hỏi về dashboard');

    expect($component->get('submittedQuestion'))->toBe('Danh mục nào đang nổi bật?');
    expect($component->get('answer'))->toBe('Danh mục nổi bật nhất là Action trong dữ liệu này.');

    Http::assertSent(function ($request): bool {
        $payload = $request->data();

        return $request->url() === 'https://api.openai.com/v1/chat/completions'
            && ! ($payload['stream'] ?? false);
    });
});

it('keeps dashboard chat history across multiple questions', function (): void {
    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::sequence()
            ->push([
                'choices' => [[
                    'message' => [
                        'content' => 'Nhà bán đang dẫn đầu là Keycove Seller.',
                    ],
                ]],
            ])
            ->push([
                'choices' => [[
                    'message' => [
                        'content' => 'Tỷ lệ khiếu nại hiện chưa vượt ngưỡng báo động.',
                    ],
                ]],
            ]),
    ]);

    config()->set('services.ai.provider', 'openai');
    config()->set('services.ai.api_key', 'test-key');
    config()->set('services.ai.base_url', 'https://api.openai.com/v1');
    config()->set('services.ai.model', 'test-model');

    $admin = User::factory()->admin()->create();

    $component = Livewire::actingAs($admin, 'admin')
        ->test(DashboardAiAssistant::class, [
            'rangeLabel' => '01/05/2026 - 02/05/2026',
            'context'    => [
                'top_sellers' => [
                    ['shop_name' => 'Keycove Seller', 'gross_revenue' => 120000],
                ],
                'complaints' => [
                    'openComplaints' => 2,
                ],
            ],
        ])
        ->set('question', 'Nhà bán nào đang dẫn đầu?')
        ->call('ask')
        ->set('question', 'Tỷ lệ khiếu nại có đáng lo không?')
        ->call('ask');

    expect($component->get('submittedQuestion'))->toBe('Tỷ lệ khiếu nại có đáng lo không?');
    expect($component->get('answer'))->toBe('Tỷ lệ khiếu nại hiện chưa vượt ngưỡng báo động.');

    expect($component->get('chatMessages'))->toBe([
        [
            'id'      => 1,
            'role'    => 'user',
            'content' => 'Nhà bán nào đang dẫn đầu?',
        ],
        [
            'id'      => 2,
            'role'    => 'assistant',
            'content' => 'Nhà bán đang dẫn đầu là Keycove Seller.',
        ],
        [
            'id'      => 3,
            'role'    => 'user',
            'content' => 'Tỷ lệ khiếu nại có đáng lo không?',
        ],
        [
            'id'      => 4,
            'role'    => 'assistant',
            'content' => 'Tỷ lệ khiếu nại hiện chưa vượt ngưỡng báo động.',
        ],
    ]);
});

it('prepares question streaming immediately and clears the input first', function (): void {
    $admin = User::factory()->admin()->create();

    $component = Livewire::actingAs($admin, 'admin')
        ->test(DashboardAiAssistant::class, [
            'rangeLabel' => '01/05/2026 - 02/05/2026',
            'context'    => [
                'top_sellers' => [
                    ['shop_name' => 'Keycove Seller', 'gross_revenue' => 120000],
                ],
            ],
        ])
        ->set('question', 'Nhà bán nào đang tạo doanh thu cao nhất?')
        ->call('ask')
        ->assertSee('Hỏi về dashboard');

    expect($component->get('question'))->toBe('');
    expect($component->get('submittedQuestion'))->toBe('Nhà bán nào đang tạo doanh thu cao nhất?');
    expect($component->get('streamedAnswer'))->toBe('');
});

it('shows the show more button for long markdown answers', function (): void {
    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => "## Điểm nổi bật\n- GMV ổn định.\n- Đơn hàng tăng đều.\n- Seller mới tăng.\n\n## Rủi ro\n- Tỷ lệ tranh chấp tăng.\n- KYC pending còn nhiều.\n- Listing chờ duyệt đang dồn.\n\n## Hành động đề xuất\n- Ưu tiên xử lý khiếu nại mở.\n- Chia ca xử lý KYC.\n- Dọn hàng đợi listing.\n- Theo dõi lại sau 24 giờ.",
                ],
            ]],
        ]),
    ]);

    config()->set('services.ai.provider', 'openai');
    config()->set('services.ai.api_key', 'test-key');
    config()->set('services.ai.base_url', 'https://api.openai.com/v1');
    config()->set('services.ai.model', 'test-model');

    $admin = User::factory()->admin()->create();

    $component = Livewire::actingAs($admin, 'admin')
        ->test(DashboardAiAssistant::class, [
            'rangeLabel' => '01/05/2026 - 02/05/2026',
            'context'    => [
                'top_sellers' => [
                    ['shop_name' => 'Keycove Seller', 'gross_revenue' => 120000],
                ],
            ],
        ])
        ->set('question', 'Tóm tắt giúp mình các điểm chính?')
        ->call('ask')
        ->assertSee('Hỏi về dashboard');

    expect($component->get('hasLongAnswer'))->toBeTrue();

    $component->call('toggleAnswerPreview');

    expect($component->get('isAnswerExpanded'))->toBeTrue();
});

it('retries transient dashboard insight failures before succeeding', function (): void {
    $diskRoot = storage_path('framework/testing/disks/ai-test-'.uniqid());
    File::ensureDirectoryExists($diskRoot);

    config()->set('filesystems.disks.ai_test', [
        'driver' => 'local',
        'root'   => $diskRoot,
        'throw'  => false,
    ]);

    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::sequence()
            ->pushStatus(429)
            ->push([
                'choices' => [[
                    'message' => [
                        'content' => "## Điểm nổi bật\n- Retry đã chạy.\n\n## Rủi ro\n- Provider thỉnh thoảng trả 429.\n\n## Hành động đề xuất\n- Giữ retry ngắn để giảm fail ngẫu nhiên.",
                    ],
                ]],
            ]),
    ]);

    config()->set('filesystems.default', 'ai_test');
    config()->set('services.ai.provider', 'openai');
    config()->set('services.ai.api_key', 'test-key');
    config()->set('services.ai.base_url', 'https://api.openai.com/v1');
    config()->set('services.ai.model', 'test-model');
    config()->set('services.ai.retry_times', 2);
    config()->set('services.ai.retry_sleep_ms', 1);

    $admin = User::factory()->admin()->create();

    $component = Livewire::actingAs($admin, 'admin')
        ->test(DashboardAiAssistant::class, [
            'rangeLabel' => '01/05/2026 - 02/05/2026',
            'context'    => [
                'top_sellers' => [
                    ['shop_name' => 'Keycove Seller', 'gross_revenue' => 120000],
                ],
            ],
        ])
        ->call('generateInsight')
        ->assertSee('Nhận định AI');

    expect($component->get('insight'))->toContain('Retry đã chạy.');

    Http::assertSentCount(2);
});

it('shows a friendly dashboard timeout error when the AI connection fails', function (): void {
    Http::fake([
        'https://api.openai.com/v1/chat/completions' => static function (): never {
            throw new ConnectionException('cURL error 28: Operation timed out after 30001 milliseconds');
        },
    ]);

    config()->set('services.ai.provider', 'openai');
    config()->set('services.ai.api_key', 'test-key');
    config()->set('services.ai.base_url', 'https://api.openai.com/v1');
    config()->set('services.ai.model', 'test-model');
    config()->set('services.ai.retry_times', 2);
    config()->set('services.ai.retry_sleep_ms', 1);

    $admin = User::factory()->admin()->create();

    $component = Livewire::actingAs($admin, 'admin')
        ->test(DashboardAiAssistant::class, [
            'rangeLabel' => '01/05/2026 - 02/05/2026',
            'context'    => [
                'top_sellers' => [
                    ['shop_name' => 'Keycove Seller', 'gross_revenue' => 120000],
                ],
            ],
        ])
        ->call('generateInsight')
        ->assertSee('Dịch vụ AI phản hồi quá chậm nên yêu cầu đã hết thời gian chờ. Vui lòng thử lại sau.');

    expect($component->get('errorMessage'))
        ->toBe('Dịch vụ AI phản hồi quá chậm nên yêu cầu đã hết thời gian chờ. Vui lòng thử lại sau.');
});

it('opens the requested drawer panel and keeps chat history after closing', function (): void {
    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => 'AI vẫn giữ lịch sử cuộc hội thoại trong drawer hiện tại.',
                ],
            ]],
        ]),
    ]);

    config()->set('services.ai.provider', 'openai');
    config()->set('services.ai.api_key', 'test-key');
    config()->set('services.ai.base_url', 'https://api.openai.com/v1');
    config()->set('services.ai.model', 'test-model');

    $admin = User::factory()->admin()->create();

    $component = Livewire::actingAs($admin, 'admin')
        ->test(DashboardAiAssistant::class, [
            'rangeLabel' => '01/05/2026 - 02/05/2026',
            'context'    => [
                'top_sellers' => [
                    ['shop_name' => 'Keycove Seller', 'gross_revenue' => 120000],
                ],
            ],
        ])
        ->call('openAssistant', 'chat')
        ->assertSet('isOpen', true)
        ->assertSet('activePanel', 'chat')
        ->set('question', 'Giữ lịch sử chat chứ?')
        ->call('ask')
        ->call('closeAssistant')
        ->assertSet('isOpen', false)
        ->call('openAssistant', 'insight')
        ->assertSet('isOpen', true)
        ->assertSet('activePanel', 'insight')
        ->call('setPanel', 'chat')
        ->assertSet('activePanel', 'chat');

    expect($component->get('chatMessages'))->toHaveCount(2)
        ->and($component->get('chatMessages.0.content'))->toBe('Giữ lịch sử chat chứ?')
        ->and($component->get('chatMessages.1.content'))->toBe('AI vẫn giữ lịch sử cuộc hội thoại trong drawer hiện tại.');
});
