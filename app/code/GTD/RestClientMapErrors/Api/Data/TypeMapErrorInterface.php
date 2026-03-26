<?php

namespace Balloon\RestClientMapErrors\Api\Data;

interface TypeMapErrorInterface
{
    const TECHNICAL_FEASIBILITY = 0;
    const AVAILABILITY = 1;
    const COMMERCIAL_FEASIBILITY = 2;
    const SELF_HIRING = 3;
    const VALIDATE_RUT = 4;
    const MAP = [
        self::TECHNICAL_FEASIBILITY => 'Factibilidad Técncia',
        self::AVAILABILITY => 'Disponibilidad (Agendamiento)',
        self::COMMERCIAL_FEASIBILITY => 'Factibilidad Comercial',
        self::SELF_HIRING => 'Auto Contratación',
        self::VALIDATE_RUT => 'Validacion rut (Producto Extendido)'
    ];
}
