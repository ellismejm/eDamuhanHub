<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the three developer placeholders to visitors', function () {
    $response = $this->get(route('developers.index'));

    $response->assertOk()
        ->assertSee('Developer 1')
        ->assertSee('Developer 2')
        ->assertSee('Developer 3')
        ->assertSee('Replace the three placeholder names');
});

it('links signed-in users to the developers page', function () {
    $role = Role::create(['name' => 'Standard User']);
    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee(route('developers.index'));
});
