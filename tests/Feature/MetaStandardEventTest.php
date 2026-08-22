<?php

use App\Enums\MetaStandardEvent;

it('lists the standard event names', function () {
    expect(MetaStandardEvent::names())->toContain('PageView', 'AddToCart', 'Purchase')
        ->and(MetaStandardEvent::names())->not->toContain('CalculatorUsed');
});

it('orders the funnel from PageView through to Purchase', function () {
    expect(MetaStandardEvent::position('PageView'))
        ->toBeLessThan(MetaStandardEvent::position('AddToCart'))
        ->and(MetaStandardEvent::position('AddToCart'))
        ->toBeLessThan(MetaStandardEvent::position('AddPaymentInfo'))
        ->and(MetaStandardEvent::position('AddPaymentInfo'))
        ->toBeLessThan(MetaStandardEvent::position('Purchase'));
});

it('sorts an unknown event name last rather than throwing', function () {
    expect(MetaStandardEvent::position('SomethingCustom'))
        ->toBeGreaterThan(MetaStandardEvent::position('Contact'));
});
