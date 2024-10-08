<?php

declare(strict_types=1);

namespace App\Service\ImportVirtualGroupStrategy;

use App\Exception\VirtualGroupException;
use Doctrine\DBAL\Exception;

interface ImportShopGroupStrategy
{
    /**
     * Создание ВГ и их связей.
     *
     * @return int[] Действующие ID для ВГ
     *
     * @throws VirtualGroupException
     * @throws Exception
     */
    public function importShopGroups(): array;
}
