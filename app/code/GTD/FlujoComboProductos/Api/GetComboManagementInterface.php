<?php
/**
 * Copyright © hecho por balloon group juan reyes All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Empresas\FlujoComboProductos\Api;

interface GetComboManagementInterface
{

    /**
     * POST for getById api
     * @param string[] $param
     * @return array
     */
    public function getComboApi($param);
}

