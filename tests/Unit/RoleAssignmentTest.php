<?php
use \App\Enums\RoleStatus;
use App\Services\RoleAssignmentService;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;


uses(TestCase::class, RefreshDatabase::class);



test('should return the admin role', function () {

$adminEmail = 'admin@gmail.com';

$request = Request::create('/login','POST', ['email' => $adminEmail]);

$role = (new RoleAssignmentService())->determineRoleForRegistration($request);

expect($role)->toBe(RoleStatus::ADMIN);

});
test('should return the vendor role', function () {

$vendorEmail = 'vendor@gmail.com';

$request = Request::create('/register','POST', ['email' => $vendorEmail , 'isVendor' => true]);

$role = (new RoleAssignmentService())->determineRoleForRegistration($request);

expect($role)->toBe(RoleStatus::VENDOR);

});
test('should return the customer role', function () {

$customerEmail = 'customer@gmail.com';

$request = Request::create('/register','POST', ['email' => $customerEmail]);

$role = (new RoleAssignmentService())->determineRoleForRegistration($request);

expect($role)->toBe(RoleStatus::CUSTOMER);

});

test('should return the role model for the given role status', function () {
$adminRole = \App\Models\Role::factory()->create(['name' => RoleStatus::ADMIN->value]);
$role = (new RoleAssignmentService())->getRoleByStatus(RoleStatus::ADMIN);
expect($role->id)->toBe($adminRole->id);
});
