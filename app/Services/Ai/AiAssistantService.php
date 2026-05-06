<?php

declare(strict_types=1);

namespace App\Services\Ai;

use Illuminate\Support\Str;

class AiAssistantService
{
    public function __construct(private AiClient $aiClient) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function generateDashboardInsight(array $context, string $rangeLabel): string
    {
        return $this->streamDashboardInsight($context, $rangeLabel, static fn (string $chunk): null => null)->content;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function streamDashboardInsight(array $context, string $rangeLabel, callable $onChunk): AiResponse
    {
        return $this->aiClient->streamChat([
            [
                'role'    => 'system',
                'content' => 'Bạn là trợ lý phân tích vận hành cho admin KeyCove. Chỉ được dùng dữ liệu được cung cấp. Trả lời bằng tiếng Việt, ngắn gọn, thực dụng dưới dạng Markdown. Ưu tiên các chủ đề doanh thu, đơn hàng, sản phẩm, category, seller và complaint. Bắt buộc dùng đúng 3 mục: ## Điểm nổi bật, ## Rủi ro, ## Hành động đề xuất. Nếu dữ liệu chưa đủ thì nói rõ trong đúng mục đó.',
            ],
            [
                'role'    => 'user',
                'content' => "Hãy tạo insight ngắn cho dashboard admin trong giai đoạn {$rangeLabel}. Mỗi mục nên ưu tiên gạch đầu dòng ngắn gọn, không lan man.\n\nDữ liệu:\n{$this->encodeContext($context)}",
            ],
        ], $onChunk);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function answerDashboardQuestion(array $context, string $rangeLabel, string $question): string
    {
        return $this->streamDashboardQuestion($context, $rangeLabel, $question, static fn (string $chunk): null => null)->content;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function streamDashboardQuestion(array $context, string $rangeLabel, string $question, callable $onChunk): AiResponse
    {
        return $this->aiClient->streamChat([
            [
                'role'    => 'system',
                'content' => 'Bạn là trợ lý phân tích số liệu cho admin KeyCove. Chỉ trả lời dựa trên dữ liệu dashboard được cung cấp. Không bịa số liệu. Ưu tiên trả lời về doanh thu, đơn hàng, category, seller, sản phẩm và complaint. Nếu câu hỏi vượt ngoài dữ liệu hiện có, hãy nói chưa đủ dữ liệu và gợi ý dữ liệu còn thiếu.',
            ],
            [
                'role'    => 'user',
                'content' => "Khoảng thời gian: {$rangeLabel}.\nCâu hỏi: {$question}\n\nDữ liệu dashboard:\n{$this->encodeContext($context)}",
            ],
        ], $onChunk);
    }

    /**
     * @param  list<array{role: string, content: string}>  $history
     * @param  array<string, mixed>  $context
     */
    public function answerSiteChat(array $history, array $context): AiResponse
    {
        return $this->streamSiteChat($history, $context, static fn (string $chunk): null => null);
    }

    /**
     * @param  list<array{role: string, content: string}>  $history
     * @param  array<string, mixed>  $context
     */
    public function streamSiteChat(array $history, array $context, callable $onChunk): AiResponse
    {
        $messages = [[
            'role'    => 'system',
            'content' => 'Bạn là chatbot hỗ trợ KeyCove cho buyer, seller và admin. Trả lời bằng tiếng Việt, rõ ràng, hữu ích, không hứa thực hiện hành động hệ thống. Chỉ dựa trên ngữ cảnh được cung cấp. Nếu thiếu dữ liệu cụ thể về đơn hàng, thanh toán hoặc tài khoản, hãy hướng người dùng tới đúng khu vực trong hệ thống.',
        ], [
            'role'    => 'user',
            'content' => "Ngữ cảnh hiện tại của người dùng:\n{$this->encodeContext($context)}",
        ]];

        foreach ($history as $message) {
            $normalizedMessage = [
                'role'    => $message['role'],
                'content' => Str::limit($message['content'], 4000),
            ];

            if (array_key_exists('reasoning_details', $message) && is_array($message['reasoning_details'])) {
                $normalizedMessage['reasoning_details'] = $message['reasoning_details'];
            }

            $messages[] = $normalizedMessage;
        }

        return $this->aiClient->streamChat($messages, $onChunk);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function encodeContext(array $context): string
    {
        $json = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($json) ? $json : '{}';
    }
}
