<?php

use App\Models\Role;
use App\Models\SpeciesImage;
use App\Models\User;
use App\Models\WeedClassification;
use App\Models\WeedFamily;
use App\Models\WeedSpecies;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows active species and their images in dashboard analytics', function () {
    $administratorRole = Role::create(['name' => 'Administrator']);
    $admin = User::factory()->create(['role_id' => $administratorRole->id]);
    User::factory()->create([
        'role_id' => $administratorRole->id,
        'is_active' => false,
    ]);

    $classification = WeedClassification::create(['name' => 'Grass']);
    $family = WeedFamily::create(['name' => 'Poaceae']);
    $activeSpecies = WeedSpecies::create([
        'scientific_name' => 'Cynodon dactylon',
        'classification_id' => $classification->id,
        'family_id' => $family->id,
    ]);
    $archivedSpecies = WeedSpecies::create([
        'scientific_name' => 'Archived species',
        'classification_id' => $classification->id,
        'family_id' => $family->id,
    ]);

    SpeciesImage::create([
        'weed_species_id' => $activeSpecies->id,
        'image_path' => 'species/active.jpg',
        'original_filename' => 'active.jpg',
    ]);
    SpeciesImage::create([
        'weed_species_id' => $archivedSpecies->id,
        'image_path' => 'species/archived.jpg',
        'original_filename' => 'archived.jpg',
    ]);
    $archivedSpecies->delete();

    $response = $this->actingAs($admin)->get(route('dashboard'));

    $response->assertOk()
        ->assertViewHas('speciesCount', 1)
        ->assertViewHas('imageCount', 1)
        ->assertViewHas('userCount', 1)
        ->assertSee('Cynodon dactylon');
});
