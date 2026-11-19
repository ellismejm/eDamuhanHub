<?php

use App\Models\Role;
use App\Models\User;
use App\Models\WeedClassification;
use App\Models\WeedFamily;
use App\Models\WeedSpecies;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createSpeciesAdministrator(): User
{
    $role = Role::firstOrCreate(['name' => 'Administrator']);

    return User::factory()->create(['role_id' => $role->id]);
}

it('creates species and records the database activity', function () {
    $admin = createSpeciesAdministrator();
    $classification = WeedClassification::create(['name' => 'Grass']);
    $family = WeedFamily::create(['name' => 'Poaceae']);

    $response = $this->actingAs($admin)->post(route('species.store'), [
        'scientific_name' => 'Cynodon dactylon',
        'taxonomic_author' => '(L.) Pers.',
        'eppo_code' => 'CYNDA',
        'classification_id' => $classification->id,
        'family_id' => $family->id,
        'common_names_source' => 'Bakbaka, buku-buku',
        'characteristics' => 'Spreading grass.',
    ]);

    $species = WeedSpecies::query()->where('eppo_code', 'CYNDA')->firstOrFail();

    $response->assertRedirect(route('species.show', $species));
    $this->assertModelExists($species);
    $this->assertDatabaseHas('local_names', ['name' => 'Bakbaka']);
    $this->assertDatabaseHas('local_names', ['name' => 'buku-buku']);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'created',
        'module' => 'species',
        'subject_id' => $species->id,
    ]);
});

it('rejects a duplicate EPPO code without creating a second species', function () {
    $admin = createSpeciesAdministrator();
    $classification = WeedClassification::create(['name' => 'Grass']);
    $family = WeedFamily::create(['name' => 'Poaceae']);
    WeedSpecies::create([
        'scientific_name' => 'Existing species',
        'eppo_code' => 'CYNDA',
        'classification_id' => $classification->id,
        'family_id' => $family->id,
    ]);

    $response = $this->actingAs($admin)->from(route('species.create'))->post(route('species.store'), [
        'scientific_name' => 'Another species',
        'eppo_code' => 'CYNDA',
        'classification_id' => $classification->id,
        'family_id' => $family->id,
    ]);

    $response->assertRedirect(route('species.create'));
    $response->assertSessionHasErrors('eppo_code');
    $this->assertDatabaseCount('weed_species', 1);
});

it('prevents standard users from changing species records', function () {
    $role = Role::create(['name' => 'Standard User']);
    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('species.create'))
        ->assertForbidden();
});

it('archives a species and keeps its audit record', function () {
    $admin = createSpeciesAdministrator();
    $classification = WeedClassification::create(['name' => 'Grass']);
    $family = WeedFamily::create(['name' => 'Poaceae']);
    $species = WeedSpecies::create([
        'scientific_name' => 'Cynodon dactylon',
        'eppo_code' => 'CYNDA',
        'classification_id' => $classification->id,
        'family_id' => $family->id,
    ]);

    $response = $this->actingAs($admin)->delete(route('species.destroy', $species));

    $response->assertRedirect(route('species.index'));
    $this->assertSoftDeleted($species);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'deleted',
        'module' => 'species',
        'subject_id' => $species->id,
    ]);
});

it('stores an uploaded species photograph as the main image', function () {
    Storage::fake('public');

    $admin = createSpeciesAdministrator();
    $classification = WeedClassification::create(['name' => 'Grass']);
    $family = WeedFamily::create(['name' => 'Poaceae']);
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lN8AAAAASUVORK5CYII=');

    $response = $this->actingAs($admin)->post(route('species.store'), [
        'scientific_name' => 'Cynodon dactylon',
        'classification_id' => $classification->id,
        'family_id' => $family->id,
        'images' => [UploadedFile::fake()->createWithContent('cynodon.png', $png)],
    ]);

    $species = WeedSpecies::query()->where('scientific_name', 'Cynodon dactylon')->firstOrFail();
    $image = $species->images()->firstOrFail();

    $response->assertRedirect(route('species.show', $species));
    expect($image->is_primary)->toBeTrue();
    Storage::disk('public')->assertExists($image->image_path);
});

it('prevents deletion of reference data that is used by a species', function () {
    $admin = createSpeciesAdministrator();
    $classification = WeedClassification::create(['name' => 'Grass']);
    $family = WeedFamily::create(['name' => 'Poaceae']);
    $species = WeedSpecies::create([
        'scientific_name' => 'Cynodon dactylon',
        'classification_id' => $classification->id,
        'family_id' => $family->id,
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.references.destroy', [
        'type' => 'classifications',
        'reference' => $classification->id,
    ]));

    $response->assertSessionHasErrors('reference');
    $this->assertModelExists($classification);

    $species->delete();

    $response = $this->delete(route('admin.references.destroy', [
        'type' => 'classifications',
        'reference' => $classification->id,
    ]));

    $response->assertSessionHasErrors('reference');
    $this->assertModelExists($classification);
});
