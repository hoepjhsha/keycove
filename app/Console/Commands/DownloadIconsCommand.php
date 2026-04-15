<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

#[Signature('app:download-icons')]
#[Description('Download platform and OS icons from CDN and save to storage')]
class DownloadIconsCommand extends Command
{
    private array $platforms = [
        'steam' => 'https://cdn.simpleicons.org/steam/black',
        'epic-games' => 'https://cdn.simpleicons.org/epicgames/black',
        'gog' => 'https://cdn.simpleicons.org/gog.com/black',
        'ea-app' => 'https://cdn.simpleicons.org/ea/black',
        'ubisoft-connect' => 'https://cdn.simpleicons.org/ubisoft/black',
        'battle-net' => 'https://cdn.simpleicons.org/battledotnet/black',
        'xbox' => 'https://cdn-icons-png.flaticon.com/512/1/1321.png',
        'playstation' => 'https://cdn.simpleicons.org/playstation/black',
        'nintendo' => 'https://cdn-icons-png.flaticon.com/128/871/871377.png',
        'humble' => 'https://cdn.simpleicons.org/humblebundle/black',
        'gmg' => 'https://static.rakuten.com/img/store/13501/13501-GreenManGaming-square-fullcolor.png',
        'rockstar' => 'https://cdn.simpleicons.org/rockstargames/black',
        'microsoft' => 'https://cdn-icons-png.flaticon.com/128/732/732221.png',
        'itch' => 'https://cdn.simpleicons.org/itch.io/black',
    ];

    private array $operatingSystems = [
        'windows10' => 'https://cdn-icons-png.flaticon.com/128/732/732225.png',
        'windows11' => 'https://cdn-icons-png.flaticon.com/128/2952/2952245.png',
        'windows7' => 'https://cdn-icons-png.flaticon.com/128/232/232411.png',
        'windows8' => 'https://cdn-icons-png.flaticon.com/128/882/882702.png',
        'macos' => 'https://cdn.simpleicons.org/apple/black',
        'ubuntu' => 'https://cdn.simpleicons.org/ubuntu/black',
        'linux' => 'https://cdn.simpleicons.org/linux/black',
        'steamos' => 'https://cdn.simpleicons.org/steam/black',
        'chromeos' => 'https://cdn.simpleicons.org/googlechrome/black',
    ];

    public function handle(): int
    {
        $disk = config('filesystems.public_disk', 'public');
        $basePath = 'icons';

        $this->info('Downloading platform icons...');
        $this->downloadIcons($this->platforms, "{$basePath}/platforms", $disk);

        $this->info('Downloading OS icons...');
        $this->downloadIcons($this->operatingSystems, "{$basePath}/os", $disk);

        $this->info('All icons downloaded successfully!');

        return Command::SUCCESS;
    }

    private function downloadIcons(array $icons, string $path, string $disk): void
    {
        foreach ($icons as $name => $url) {
            $fullPath = "{$path}/{$name}.svg";

            $result = $this->downloadWithCurl($url);

            if ($result !== false) {
                Storage::disk($disk)->put($fullPath, $result);
                $this->line("  - Downloaded: {$fullPath}");
            } else {
                $this->warn("  - Failed to download {$name}");
                Log::warning("Icon download failed: {$name}", ['url' => $url]);
            }
        }
    }

    private function downloadWithCurl(string $url): string|false
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($content !== false && $httpCode === 200) {
            return $content;
        }

        return false;
    }
}
