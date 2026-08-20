<?php

namespace App\Enums;

enum MetaEventStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';
}
