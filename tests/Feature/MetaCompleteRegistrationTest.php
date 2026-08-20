<?php

use App\Enums\MetaEventStatus;
use App\Jobs\SendMetaCapiEvent;
use App\Models\MetaEvent;
use App\Models\Role;
use App\Models\User;
use App\Services\Meta\MetaEventService;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config()->set('services.meta.pixel_id', '1562473862273045');
    config()->set('services.meta.capi_token', 'test-token');
    config()->set('services.meta.api_version', 'v21.0');
    config()->set('services.meta.currency', 'USD');
    config()->set('services.meta.test_event_code', null);

    Role::factory()->create(['name' => 'customer']);
    Role::factory()->create(['name' => 'vendor']);
});

function register(array $overrides = [])
{
    return test()->post(route('register'), [
        'name' => 'Belal',
        'email' => 'belal@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        ...$overrides,
    ]);
}

it('flashes a CompleteRegistration payload for the pixel', function () {
    Queue::fake();

    register()->assertRedirect();

    $user = User::sole();

    expect(session('meta_event'))->toBe([
        'name' => 'CompleteRegistration',
        'event_id' => "registration_{$user->id}",
        'params' => [
            'content_name' => 'customer',
            'status' => true,
        ],
    ]);
});

it('queues a CAPI CompleteRegistration under the same event_id', function () {
    Queue::fake();

    register();

    $user = User::sole();
    $record = MetaEvent::sole();

    expect($record->event_name)->toBe('CompleteRegistration')
        ->and($record->event_id)->toBe("registration_{$user->id}")
        ->and($record->order_id)->toBeNull()
        ->and($record->status)->toBe(MetaEventStatus::PENDING)
        ->and($record->payload['action_source'])->toBe('website')
        ->and($record->payload['custom_data']['content_name'])->toBe('customer');

    Queue::assertPushed(SendMetaCapiEvent::class, fn ($job) => $job->event->is($record));
});

it('sends the hashed email and external id as matching signals', function () {
    Queue::fake();

    register(['email' => 'belal@example.com']);

    $user = User::sole();
    $userData = MetaEvent::sole()->payload['user_data'];

    expect($userData['em'])->toBe([hash('sha256', 'belal@example.com')])
        ->and($userData['external_id'])->toBe([hash('sha256', (string) $user->id)])
        ->and(json_encode($userData))->not->toContain('belal@example.com');
});

it('reports the role the account signed up as', function () {
    Queue::fake();

    register(['isVendor' => true]);

    expect(session('meta_event')['params']['content_name'])->toBe('vendor');
});

it('never reports the same signup twice', function () {
    Queue::fake();

    $user = User::factory()->customer()->create();
    $service = app(MetaEventService::class);

    $service->completeRegistration(request(), $user);
    $service->completeRegistration(request(), $user);

    expect(MetaEvent::where('event_id', "registration_{$user->id}")->count())->toBe(1);

    Queue::assertPushed(SendMetaCapiEvent::class, 1);
});

it('still fires the pixel but skips CAPI when no token is configured', function () {
    Queue::fake();
    config()->set('services.meta.capi_token', null);

    register();

    expect(session('meta_event')['name'])->toBe('CompleteRegistration')
        ->and(MetaEvent::count())->toBe(0);

    Queue::assertNothingPushed();
});

it('tracks nothing at all when the pixel is not configured', function () {
    Queue::fake();
    config()->set('services.meta.pixel_id', null);

    register();

    expect(session('meta_event'))->toBeNull()
        ->and(MetaEvent::count())->toBe(0);
});
