<?php

declare(strict_types=1);

use App\Livewire\Admin\Action\Dashboard\DashboardAiAssistant;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('generates and stores admin dashboard insights as markdown', function (): void {
    $diskRoot = storage_path('framework/testing/disks/ai-test-'.uniqid());

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
        ->assertSee('Markdown preview')
        ->assertSee('Điểm nổi bật')
        ->assertSee('Ưu tiên xử lý khiếu nại mở')
        ->assertSeeHtml('<h2>Điểm nổi bật</h2>');

    expect(Storage::disk('ai_test')->exists($path))->toBeTrue();

    expect(Storage::disk('ai_test')->get($path))
        ->toContain('## Điểm nổi bật')
        ->toContain('## Hành động đề xuất');

    expect($component->get('insight'))
        ->toContain('## Điểm nổi bật');

    Http::assertSent(function ($request): bool {
        $payload = $request->data();

        return $request->url() === 'https://api.openai.com/v1/chat/completions'
            && ($payload['stream'] ?? null) === true;
    });
});

it('generates dashboard insights with gemini', function (): void {
    $diskRoot = storage_path('framework/testing/disks/ai-test-'.uniqid());

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
        ->assertSee('Điểm nổi bật')
        ->assertSee('Doanh thu nền tảng tăng')
        ->assertSeeHtml('<h2>Điểm nổi bật</h2>');

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

    Livewire::actingAs($admin, 'admin')
        ->test(DashboardAiAssistant::class, [
            'rangeLabel' => $rangeLabel,
            'context'    => $context,
        ])
        ->assertSee('Markdown preview')
        ->assertSet('insight', $longInsight)
        ->assertSee('Xem thêm')
        ->assertSeeHtml('<h2>Điểm nổi bật</h2>')
        ->call('toggleInsightPreview')
        ->assertSee('Thu gọn');
});

it('answers questions about dashboard metrics', function (): void {
    Http::fake([
        'https://api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Seller tạo doanh thu cao nhất hiện tại là Keycove Seller trong snapshot này.',
                    ],
                ],
            ],
        ]),
    ]);

    config()->set('services.ai.api_key', 'test-key');
    config()->set('services.ai.base_url', 'https://api.openai.com/v1');
    config()->set('services.ai.model', 'test-model');

    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(DashboardAiAssistant::class, [
            'rangeLabel' => '01/05/2026 - 02/05/2026',
            'context'    => [
                'top_sellers' => [
                    ['shop_name' => 'Keycove Seller', 'gross_revenue' => 120000],
                ],
            ],
        ])
        ->set('question', 'Seller nào đang tạo doanh thu cao nhất?')
        ->call('ask')
        ->assertSee('Markdown preview')
        ->assertSee('Keycove Seller')
        ->assertSeeHtml('<p>Seller tạo doanh thu cao nhất hiện tại là Keycove Seller trong snapshot này.</p>');

    Http::assertSent(function ($request): bool {
        $payload = $request->data();

        return $request->url() === 'https://api.openai.com/v1/chat/completions'
            && ($payload['stream'] ?? null) === true;
    });
});

it('prepares question streaming immediately and clears the input first', function (): void {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin, 'admin')
        ->test(DashboardAiAssistant::class, [
            'rangeLabel' => '01/05/2026 - 02/05/2026',
            'context'    => [
                'top_sellers' => [
                    ['shop_name' => 'Keycove Seller', 'gross_revenue' => 120000],
                ],
            ],
        ])
        ->set('question', 'Seller nào đang tạo doanh thu cao nhất?')
        ->call('ask')
        ->assertSet('question', '')
        ->assertSet('submittedQuestion', 'Seller nào đang tạo doanh thu cao nhất?')
        ->assertSet('streamedAnswer', '');
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

    config()->set('services.ai.api_key', 'test-key');
    config()->set('services.ai.base_url', 'https://api.openai.com/v1');
    config()->set('services.ai.model', 'test-model');

    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin, 'admin')
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
        ->assertSee('Xem thêm')
        ->call('toggleAnswerPreview')
        ->assertSee('Thu gọn');
});
