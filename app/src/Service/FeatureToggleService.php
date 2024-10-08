<?php

namespace App\Service;

use Flagception\Manager\FeatureManagerInterface;

class FeatureToggleService
{
    /** Новая автоагрегация с учетом дистрибьютора Пульс */
    public const IS_ENABLED_GENERATE_VIRTUAL_GROUP_FOR_DISTR = 'is_enabled_generate_virtual_group_for_distr';

    public function __construct(
        private FeatureManagerInterface $featureManager
    ) {
    }

    public function isEnabledGenerateVirtualGroupForDistr(): bool
    {
        return $this->featureManager->isActive(self::IS_ENABLED_GENERATE_VIRTUAL_GROUP_FOR_DISTR);
    }
}
