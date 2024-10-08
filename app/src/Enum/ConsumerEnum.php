<?php

declare(strict_types=1);

namespace App\Enum;

enum ConsumerEnum: string
{
    case PHARMA_CLIENTS = 'pharma_clients';

    case WEB = 'web';

    case ANDROID = 'android';

    case ANDROID_DISTR = 'android_distr';

    case IOS = 'ios';

    case IOS_DISTR = 'ios_distr';

    case DISTRIBUTOR = 'pharma_distributor';
}
