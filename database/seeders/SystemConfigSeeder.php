<?php

namespace Database\Seeders;

use App\Models\SystemConfig;
use Illuminate\Database\Seeder;

class SystemConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (SystemConfig::MANAGED_CONFIGS as $key => $config) {
            SystemConfig::firstOrCreate(
                ['key' => $key],
                [
                    'value'       => $config['value'],
                    'description' => $config['description'],
                ]
            );
        }
    }
}
