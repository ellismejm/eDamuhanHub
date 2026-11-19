<?php

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates the first administrator and closes one-time setup', function () {
    $response = $this->post(route('setup.store'), [
        'name' => 'Project Administrator',
        'email' => 'admin@example.test',
        'password' => 'StrongPassword!123',
        'password_confirmation' => 'StrongPassword!123',
    ]);

    $user = User::query()->where('email', 'admin@example.test')->firstOrFail();

    $response->assertRedirect(route('dashboard'));
    expect($user->hasRole('Administrator'))->toBeTrue();
    expect(password_verify('StrongPassword!123', $user->password))->toBeTrue();
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'action' => 'first_admin_created',
    ]);

    $this->post(route('logout'));
    $this->get(route('setup.create'))->assertRedirect(route('login'));
});

it('rejects weak passwords during first administrator setup', function () {
    $response = $this->from(route('setup.create'))->post(route('setup.store'), [
        'name' => 'Project Administrator',
        'email' => 'admin@example.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('setup.create'));
    $response->assertSessionHasErrors('password');
    $this->assertDatabaseCount('users', 0);
});

it('authenticates active users and records sign-in activity', function () {
    $role = Role::create(['name' => 'Standard User']);
    $user = User::factory()->create([
        'role_id' => $role->id,
        'password' => 'StrongPassword!123',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'StrongPassword!123',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'action' => 'login',
        'module' => 'authentication',
    ]);
});

it('refuses sign-in for a deactivated account', function () {
    $role = Role::create(['name' => 'Standard User']);
    $user = User::factory()->create([
        'role_id' => $role->id,
        'is_active' => false,
        'password' => 'StrongPassword!123',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'StrongPassword!123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('forbids standard users from administrator pages', function () {
    $role = Role::create(['name' => 'Standard User']);
    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

it('allows standard users to browse the species database', function () {
    $role = Role::create(['name' => 'Standard User']);
    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('species.index'))
        ->assertOk();
});

it('lets administrators create accounts and prevents removing the last active administrator', function () {
    $administratorRole = Role::create(['name' => 'Administrator']);
    $standardRole = Role::create(['name' => 'Standard User']);
    $admin = User::factory()->create(['role_id' => $administratorRole->id]);

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Field Researcher',
        'email' => 'researcher@example.test',
        'role_id' => $standardRole->id,
        'password' => 'StrongPassword!123',
        'password_confirmation' => 'StrongPassword!123',
    ]);

    $response->assertRedirect(route('admin.users.index'));
    $this->assertDatabaseHas('users', [
        'email' => 'researcher@example.test',
        'role_id' => $standardRole->id,
        'is_active' => true,
    ]);

    $response = $this->put(route('admin.users.update', $admin), [
        'name' => $admin->name,
        'email' => $admin->email,
        'role_id' => $standardRole->id,
        'is_active' => 0,
    ]);

    $response->assertSessionHasErrors('role_id');
    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
        'role_id' => $administratorRole->id,
        'is_active' => true,
    ]);
});

it('does not allow audit logs to be updated or deleted', function () {
    $log = AuditLog::create([
        'action' => 'login',
        'module' => 'authentication',
    ]);

    expect(fn () => $log->update(['action' => 'changed']))->toThrow(LogicException::class);
    expect(fn () => $log->delete())->toThrow(LogicException::class);

    $this->assertDatabaseHas('audit_logs', [
        'id' => $log->id,
        'action' => 'login',
    ]);
});

it('allows administrators to view audit logs, imports, and backup controls', function () {
    $role = Role::create(['name' => 'Administrator']);
    $admin = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($admin)->get(route('admin.audit-logs.index'))->assertOk();
    $this->get(route('admin.imports.index'))->assertOk();
    $this->get(route('admin.backups.index'))->assertOk();
    $this->get(route('admin.images.index'))->assertOk();
});

it('explains that database backups require a MySQL connection', function () {
    $role = Role::create(['name' => 'Administrator']);
    $admin = User::factory()->create(['role_id' => $role->id]);

    $response = $this->actingAs($admin)->post(route('admin.backups.store'));

    $response->assertSessionHasErrors('backup');
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'backup_failed',
    ]);
});

it('updates profile details only with the current password for password changes', function () {
    $role = Role::create(['name' => 'Standard User']);
    $user = User::factory()->create([
        'role_id' => $role->id,
        'password' => 'StrongPassword!123',
    ]);

    $response = $this->from(route('profile.edit'))->actingAs($user)->put(route('profile.update'), [
        'name' => 'Updated Researcher',
        'email' => $user->email,
        'current_password' => 'StrongPassword!123',
        'password' => 'AnotherStrong!456',
        'password_confirmation' => 'AnotherStrong!456',
    ]);

    $response->assertRedirect(route('profile.edit'));
    $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Researcher']);
    expect(password_verify('AnotherStrong!456', $user->fresh()->password))->toBeTrue();
});
