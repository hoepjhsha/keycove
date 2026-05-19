<?php

declare(strict_types=1);

namespace App\Services\Ai;

final readonly class AiResponse
{
    /**
     * @param  array<string, mixed>  $raw
     * @param  array<string, mixed>|null  $usage
     * @param  list<array<string, mixed>>|null  $reasoningDetails
     */
    public function __construct(
        public string $content,
        public array $raw = [],
        public ?array $usage = null,
        public ?array $reasoningDetails = null,
    ) {}
}
