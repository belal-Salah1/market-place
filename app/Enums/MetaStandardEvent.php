<?php

namespace App\Enums;

/**
 * The Meta standard events we are willing to record. Declared in funnel order —
 * `position()` is what sorts the dashboard's Events column, so this list is both
 * the beacon allowlist and the report ordering.
 */
enum MetaStandardEvent: string
{
    case PAGE_VIEW = 'PageView';
    case VIEW_CONTENT = 'ViewContent';
    case SEARCH = 'Search';
    case ADD_TO_CART = 'AddToCart';
    case INITIATE_CHECKOUT = 'InitiateCheckout';
    case ADD_PAYMENT_INFO = 'AddPaymentInfo';
    case PURCHASE = 'Purchase';
    case COMPLETE_REGISTRATION = 'CompleteRegistration';
    case LEAD = 'Lead';
    case CONTACT = 'Contact';

    /**
     * @return array<int, string>
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Sort key for the Events column. An event name we do not recognise sorts to
     * the bottom instead of blowing up the dashboard.
     */
    public static function position(string $eventName): int
    {
        $index = array_search($eventName, self::names(), true);

        return $index === false ? PHP_INT_MAX : $index;
    }
}
