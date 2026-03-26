<?php

declare(strict_types=1);

namespace BalloonGroup\Configurations\Api;

interface ReindexAllInterface
{
    /**
     * Trigger reindexing for integration process
     *
     * @return bool
     */
    public function execute(): bool;
}
