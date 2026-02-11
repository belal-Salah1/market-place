<?php

namespace App\Enums;

enum PaymentMethodStatus: string
{
    case CASH = 'cash';
    case CREDIT_CARD = 'credit_card';
    case PAYPAL = 'paypal';

}
