<?php

declare(strict_types=1);

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class AiClient
{
    public function chat(array $messages): AiResponse
    {
        if ($this->provider() === 'gemini') {
            return $this->geminiChat($messages);
        }

        $apiKey = (string) config('services.ai.api_key');
        $baseUrl = rtrim((string) config('services.ai.base_url', ''), '/');
        $model = (string) config('services.ai.model', '');

        if ($apiKey === '' || $baseUrl === '' || $model === '') {
            throw new RuntimeException('AI chưa được cấu hình đầy đủ. Hãy kiểm tra AI_BASE_URL, AI_API_KEY và AI_MODEL.');
        }

        try {
            $response = $this->request()
                ->acceptJson()
                ->withToken($apiKey)
                ->withHeaders($this->providerHeaders())
                ->post("$baseUrl/chat/completions", $this->payload($model, $messages));

            $response->throw();
        } catch (ConnectionException|RequestException $exception) {
            throw $this->toServiceException($exception);
        }

        return $this->responseFromPayload($response->json());
    }

    public function streamChat(array $messages, callable $onChunk): AiResponse
    {
        if ($this->provider() === 'gemini') {
            $response = $this->geminiChat($messages);
            $onChunk($response->content);

            return $response;
        }

        $apiKey = (string) config('services.ai.api_key');
        $baseUrl = rtrim((string) config('services.ai.base_url', ''), '/');
        $model = (string) config('services.ai.model', '');

        if ($apiKey === '' || $baseUrl === '' || $model === '') {
            throw new RuntimeException('AI chưa được cấu hình đầy đủ. Hãy kiểm tra AI_BASE_URL, AI_API_KEY và AI_MODEL.');
        }

        try {
            $response = $this->request()
                ->accept('text/event-stream')
                ->withToken($apiKey)
                ->withHeaders($this->providerHeaders())
                ->withOptions(['stream' => true])
                ->post("$baseUrl/chat/completions", $this->payload($model, $messages, stream: true));

            $response->throw();
        } catch (ConnectionException|RequestException $exception) {
            throw $this->toServiceException($exception);
        }

        return $this->streamedResponse($response, $onChunk);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function responseFromPayload(array $payload): AiResponse
    {
        $content = $this->extractContent($payload);

        if ($content === '') {
            throw new RuntimeException('Dịch vụ AI không trả về nội dung hợp lệ.');
        }

        /** @var array<string, mixed>|null $usage */
        $usage = data_get($payload, 'usage');
        /** @var list<array<string, mixed>>|null $reasoningDetails */
        $reasoningDetails = data_get($payload, 'choices.0.message.reasoning_details');

        return new AiResponse(
            content: $content,
            raw: $payload,
            usage: is_array($usage) ? $usage : null,
            reasoningDetails: is_array($reasoningDetails) ? $reasoningDetails : null,
        );
    }

    /**
     * @param  array<string, mixed>  $messages
     */
    protected function geminiChat(array $messages): AiResponse
    {
        $apiKey = (string) config('services.ai.api_key');
        $baseUrl = rtrim((string) config('services.ai.base_url', ''), '/');
        $model = (string) config('services.ai.model', '');

        if ($apiKey === '' || $baseUrl === '' || $model === '') {
            throw new RuntimeException('AI chưa được cấu hình đầy đủ. Hãy kiểm tra AI_PROVIDER, AI_BASE_URL, AI_API_KEY và AI_MODEL.');
        }

        try {
            $response = $this->request()
                ->acceptJson()
                ->withHeaders([
                    'X-goog-api-key' => $apiKey,
                ])
                ->post("$baseUrl/models/$model:generateContent", $this->geminiPayload($messages));

            $response->throw();
        } catch (ConnectionException|RequestException $exception) {
            throw $this->toServiceException($exception);
        }

        return $this->responseFromGeminiPayload($response->json());
    }

    protected function payload(string $model, array $messages, bool $stream = false): array
    {
        $payload = [
            'model'     => $model,
            'messages'  => $this->normalizeMessages($messages),
            'reasoning' => [
                'enabled' => (bool) config('services.ai.reasoning_enabled', true),
            ],
        ];

        if ($stream) {
            $payload['stream'] = true;
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    protected function geminiPayload(array $messages): array
    {
        $normalizedMessages = $this->normalizeGeminiMessages($messages);
        $payload = [
            'contents'         => $normalizedMessages['contents'],
            'generationConfig' => [
                'temperature' => (float) config('services.ai.temperature', 0.3),
            ],
        ];

        if ($normalizedMessages['systemInstruction'] !== null) {
            $payload['systemInstruction'] = $normalizedMessages['systemInstruction'];
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function responseFromGeminiPayload(array $payload): AiResponse
    {
        $content = $this->extractGeminiContent($payload);

        if ($content === '') {
            throw new RuntimeException('Dịch vụ AI không trả về nội dung hợp lệ.');
        }

        /** @var array<string, mixed>|null $usage */
        $usage = data_get($payload, 'usageMetadata');

        return new AiResponse(
            content: $content,
            raw: $payload,
            usage: is_array($usage) ? $usage : null,
            reasoningDetails: null,
        );
    }

    protected function normalizeMessages(array $messages): array
    {
        return collect($messages)
            ->map(function (array $message): array {
                $normalized = [
                    'role'    => (string) ($message['role'] ?? 'user'),
                    'content' => $message['content'] ?? '',
                ];

                if (array_key_exists('reasoning_details', $message) && is_array($message['reasoning_details'])) {
                    $normalized['reasoning_details'] = $message['reasoning_details'];
                }

                return $normalized;
            })
            ->values()
            ->all();
    }

    /**
     * @return array{systemInstruction: array{parts: list<array{text: string}>}|null, contents: list<array{role: string, parts: list<array{text: string}>}>}
     */
    protected function normalizeGeminiMessages(array $messages): array
    {
        $systemParts = [];
        $contents = [];

        foreach ($messages as $message) {
            $role = (string) ($message['role'] ?? 'user');
            $content = $this->messageContentToText($message['content'] ?? '');

            if ($content === '') {
                continue;
            }

            if ($role === 'system') {
                $systemParts[] = ['text' => $content];

                continue;
            }

            $contents[] = [
                'role'  => $role === 'assistant' ? 'model' : 'user',
                'parts' => [
                    ['text' => $content],
                ],
            ];
        }

        return [
            'systemInstruction' => $systemParts === [] ? null : ['parts' => $systemParts],
            'contents'          => $contents,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function providerHeaders(): array
    {
        $headers = [];
        $httpReferer = (string) config('services.ai.http_referer', '');
        $appName = (string) config('services.ai.app_name', '');

        if ($httpReferer !== '') {
            $headers['HTTP-Referer'] = $httpReferer;
        }

        if ($appName !== '') {
            $headers['X-Title'] = $appName;
            $headers['X-OpenRouter-Title'] = $appName;
        }

        return $headers;
    }

    protected function provider(): string
    {
        $provider = strtolower(trim((string) config('services.ai.provider', '')));

        if ($provider !== '') {
            return $provider;
        }

        $baseUrl = strtolower((string) config('services.ai.base_url', ''));

        return str_contains($baseUrl, 'generativelanguage.googleapis.com') ? 'gemini' : 'openai';
    }

    protected function request(): PendingRequest
    {
        return Http::connectTimeout(max(1, (int) config('services.ai.connect_timeout', 10)))
            ->timeout(max(1, (int) config('services.ai.timeout', 30)))
            ->retry(
                max(1, (int) config('services.ai.retry_times', 2)),
                fn (int $attempt, Throwable $exception): int             => max(0, (int) config('services.ai.retry_sleep_ms', 250)) * $attempt,
                fn (Throwable $exception, PendingRequest $request): bool => $this->shouldRetry($exception),
            );
    }

    protected function shouldRetry(Throwable $exception): bool
    {
        if ($exception instanceof ConnectionException) {
            return true;
        }

        if (! $exception instanceof RequestException) {
            return false;
        }

        $status = $exception->response?->status();

        return $status === 408 || $status === 429 || ($status !== null && $status >= 500);
    }

    protected function toServiceException(ConnectionException|RequestException $exception): RuntimeException
    {
        if ($exception instanceof ConnectionException) {
            $message = str_contains(strtolower($exception->getMessage()), 'timed out')
                ? 'Dịch vụ AI phản hồi quá chậm nên yêu cầu đã hết thời gian chờ. Vui lòng thử lại sau.'
                : 'Kết nối tới dịch vụ AI đang không ổn định. Vui lòng thử lại sau.';

            return new RuntimeException($message, previous: $exception);
        }

        $status = $exception->response?->status();

        return match (true) {
            $status === 408                    => new RuntimeException('Dịch vụ AI phản hồi quá chậm nên yêu cầu đã hết thời gian chờ. Vui lòng thử lại sau.', previous: $exception),
            $status === 429                    => new RuntimeException('Dịch vụ AI đang bận. Vui lòng thử lại sau ít phút.', previous: $exception),
            $status !== null && $status >= 500 => new RuntimeException('Dịch vụ AI đang tạm lỗi. Vui lòng thử lại sau.', previous: $exception),
            default                            => new RuntimeException('Không thể kết nối tới dịch vụ AI lúc này. Vui lòng thử lại sau.', previous: $exception),
        };
    }

    protected function streamedResponse(Response $response, callable $onChunk): AiResponse
    {
        $content = '';
        $rawBody = '';
        $events = [];
        $usage = null;
        $reasoningDetails = null;
        $eventDataLines = [];
        $resource = $response->resource();

        try {
            while (! feof($resource)) {
                $line = fgets($resource);

                if ($line === false) {
                    continue;
                }

                $rawBody .= $line;
                $line = rtrim($line, "\r\n");

                if ($line === '') {
                    if ($this->consumeStreamEvent($eventDataLines, $onChunk, $content, $events, $usage, $reasoningDetails)) {
                        break;
                    }

                    $eventDataLines = [];

                    continue;
                }

                if (str_starts_with($line, 'data:')) {
                    $eventDataLines[] = ltrim(substr($line, 5));
                }
            }

            $this->consumeStreamEvent($eventDataLines, $onChunk, $content, $events, $usage, $reasoningDetails);
        } finally {
            $response->close();
        }

        if ($content === '') {
            /** @var array<string, mixed>|null $payload */
            $payload = json_decode($rawBody, true);

            if (is_array($payload)) {
                return $this->responseFromPayload($payload);
            }

            throw new RuntimeException('Dịch vụ AI không trả về nội dung hợp lệ.');
        }

        return new AiResponse(
            content: trim($content),
            raw: [
                'events' => $events,
            ],
            usage: $usage,
            reasoningDetails: $reasoningDetails,
        );
    }

    /**
     * @param  list<string>  $eventDataLines
     * @param  list<array<string, mixed>>|null  $reasoningDetails
     * @param  array<int, array<string, mixed>>  $events
     */
    protected function consumeStreamEvent(array $eventDataLines, callable $onChunk, string &$content, array &$events, ?array &$usage, ?array &$reasoningDetails): bool
    {
        if ($eventDataLines === []) {
            return false;
        }

        $payload = trim(implode("\n", $eventDataLines));

        if ($payload === '[DONE]') {
            return true;
        }

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            return false;
        }

        $events[] = $decoded;

        $chunk = $this->extractStreamContent($decoded);

        if ($chunk !== '') {
            $content .= $chunk;
            $onChunk($chunk);
        }

        $streamUsage = data_get($decoded, 'usage');

        if (is_array($streamUsage)) {
            $usage = $streamUsage;
        }

        $streamReasoningDetails = data_get($decoded, 'choices.0.delta.reasoning_details');

        if (! is_array($streamReasoningDetails)) {
            $streamReasoningDetails = data_get($decoded, 'choices.0.message.reasoning_details');
        }

        if (is_array($streamReasoningDetails)) {
            $reasoningDetails = $reasoningDetails === null
                ? array_values($streamReasoningDetails)
                : array_values(array_merge($reasoningDetails, $streamReasoningDetails));
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function extractStreamContent(array $payload): string
    {
        $content = data_get($payload, 'choices.0.delta.content');

        if (is_string($content)) {
            return $content;
        }

        if (is_array($content)) {
            return $this->implodeContentParts($content);
        }

        $messageContent = data_get($payload, 'choices.0.message.content');

        if (is_string($messageContent)) {
            return $messageContent;
        }

        if (is_array($messageContent)) {
            return $this->implodeContentParts($messageContent);
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function extractContent(array $payload): string
    {
        $content = data_get($payload, 'choices.0.message.content');

        if (is_string($content)) {
            return trim($content);
        }

        if (! is_array($content)) {
            return '';
        }

        return trim($this->implodeContentParts($content));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function extractGeminiContent(array $payload): string
    {
        $parts = data_get($payload, 'candidates.0.content.parts', []);

        if (! is_array($parts)) {
            return '';
        }

        return trim($this->implodeContentParts($parts));
    }

    protected function messageContentToText(mixed $content): string
    {
        if (is_string($content)) {
            return trim($content);
        }

        if (! is_array($content)) {
            return '';
        }

        return trim($this->implodeContentParts($content));
    }

    /**
     * @param  list<mixed>  $content
     */
    protected function implodeContentParts(array $content): string
    {
        return collect($content)
            ->map(function (mixed $part): string {
                if (! is_array($part)) {
                    return '';
                }

                $text = data_get($part, 'text');

                return is_string($text) ? $text : '';
            })
            ->filter(fn (string $part): bool => $part !== '')
            ->implode("\n")
            ?: '';
    }
}
