<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

#[Signature('app:download-game-thumbnails')]
#[Description('Download game thumbnails from Steam CDN and save to storage')]
class DownloadGameThumbnailsCommand extends Command
{
    private array $games = [
        'cyberpunk-2077' => 1091500,
        'red-dead-redemption-2' => 1174180,
        'grand-theft-auto-v' => 271590,
        'elden-ring' => 1245620,
        'baldurs-gate-3' => 1086940,
        'hogwarts-legacy' => 990080,
        'god-of-war' => 1593500,
        'starfield' => 1716740,
        'counter-strike-2' => 730,
        'valorant' => null,
        'rainbow-six-siege' => 359550,
        'ea-sports-fc-24' => 2195250,
        'forza-horizon-5' => 1551360,
        'hades' => 1145360,
        'stardew-valley' => 413150,
        'hollow-knight' => 367520,
        'the-witcher-3-wild-hunt' => 292030,
        'diablo-iv' => 2344520,
        'resident-evil-4-remake' => 2050650,
        'sekiro-shadows-die-twice' => 814380,
        'dead-space-remake' => 1693980,
        'minecraft' => 1358090,
        'street-fighter-6' => 1364780,
        'tekken-8' => 1778820,
    ];

    private array $placeholderImages = [
        'valorant' => 'https://cdn.cloudflare.steamstatic.com/steam/apps/1711412960/library_600x900.jpg',
        'microsoft-365-personal' => null,
        'adobe-creative-cloud-all-apps' => null,
        'spotify-premium' => null,
        'netflix-premium' => null,
    ];

    public function handle(): int
    {
        $disk = config('filesystems.public_disk', 'public');
        $basePath = 'products/thumbnails';

        $this->info('Downloading game thumbnails...');

        $success = 0;
        $failed = 0;

        foreach ($this->games as $slug => $appId) {
            $fullPath = "{$basePath}/{$slug}.jpg";

            if ($appId === null) {
                $this->warn("  - Skipped (no Steam ID): {$slug}");
                $failed++;

                continue;
            }

            $url = "https://cdn.cloudflare.steamstatic.com/steam/apps/{$appId}/library_600x900.jpg";
            $content = $this->downloadWithCurl($url);

            if ($content !== false) {
                Storage::disk($disk)->put($fullPath, $content);
                $this->line("  - Downloaded: {$fullPath}");
                $success++;
            } else {
                $this->warn("  - Failed to download: {$slug}");
                Log::warning("Game thumbnail download failed: {$slug}", ['app_id' => $appId]);
                $failed++;
            }
        }

        $this->info("Downloaded: {$success}, Failed: {$failed}");

        return Command::SUCCESS;
    }

    private function downloadWithCurl(string $url): string|false
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ]);

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($content !== false && $httpCode === 200 && strlen($content) > 1000) {
            return $content;
        }

        return false;
    }
}
