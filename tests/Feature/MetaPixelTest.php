<?php

use App\Models\User;

beforeEach(function () {
    config()->set('services.meta.pixel_id', '1410832676690361');
});

it('renders the pixel for a customer', function () {
    $this->actingAs(User::factory()->customer()->create())
        ->get(route('customer.dashboard'))
        ->assertOk()
        ->assertSee("fbq('init', \"1410832676690361\")", false)
        ->assertSee("fbq('track', 'PageView')", false);
});

it('renders the pixel for a guest at the top of the funnel', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee("fbq('init', \"1410832676690361\")", false);
});

it('does not render the pixel for admins or vendors', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('fbevents.js');

    $this->actingAs(User::factory()->vendor()->approved()->create())
        ->get(route('vendor.dashboard'))
        ->assertOk()
        ->assertDontSee('fbevents.js');
});

it('renders nothing when the pixel id is not configured', function () {
    config()->set('services.meta.pixel_id', null);

    $this->actingAs(User::factory()->customer()->create())
        ->get(route('customer.dashboard'))
        ->assertOk()
        ->assertDontSee('fbevents.js');
});
