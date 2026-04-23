<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Action\SystemConfig\SystemConfigIndex;
use App\Models\SystemConfig;
use App\Models\User;
use Livewire\Livewire;

it('admin can access the system settings page', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.system_settings.index'));

    $response->assertOk();
    $response->assertSeeLivewire(SystemConfigIndex::class);
});

it('can create a system config from the admin page', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Livewire::actingAs($admin, 'admin')
        ->test(SystemConfigIndex::class)
        ->set('createForm.key', 'test_setting')
        ->set('createForm.value', 'demo-value')
        ->set('createForm.description', 'Demo description')
        ->call('createSystemConfig');

    expect(SystemConfig::where('key', 'test_setting')->value('value'))->toBe('demo-value');
});

it('can update system config inline value', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $config = SystemConfig::create([
        'key'         => 'site_name_test',
        'value'       => 'KeyCove',
        'description' => 'Website name',
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(SystemConfigIndex::class)
        ->set('inlineValues.'.$config->id, 'Updated Name')
        ->call('saveInlineValue', $config->id);

    expect($config->refresh()->value)->toBe('Updated Name');
});

it('can delete a system config', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $config = SystemConfig::create([
        'key'         => 'temp_setting',
        'value'       => 'temp',
        'description' => 'Temporary setting',
    ]);

    Livewire::actingAs($admin, 'admin')
        ->test(SystemConfigIndex::class)
        ->call('performDeleteSystemConfig', $config->id);

    expect(SystemConfig::whereKey($config->id)->exists())->toBeFalse();
});
