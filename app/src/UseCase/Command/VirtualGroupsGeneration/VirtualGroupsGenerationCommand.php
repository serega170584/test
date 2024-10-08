<?php

declare(strict_types=1);

namespace App\UseCase\Command\VirtualGroupsGeneration;

use Test\PhpServicesBundle\Bus\CommandBase;

final class VirtualGroupsGenerationCommand implements CommandBase
{
    public function __construct(
        public readonly bool $isImportOnlyVirtualGroups,
    ) {
    }
}
