<?php

namespace Balloon\RestClient\Api\Sdk\Gtd;

use Balloon\Hiring\Api\Data\HiringInterface;
use Balloon\RestClient\Exceptions\ServiceError;
use Balloon\RestClient\Model\OrderToReport;
use Balloon\RestClient\Service\CurlClient;

interface ClientInterface {
    const PARENT_DIRECTIONS_URL = '/factibilidaddireccion';
    const ENDPOINT_REGIONS = self::PARENT_DIRECTIONS_URL . '/regiones';
    const ENDPOINT_COMMUNES = self::PARENT_DIRECTIONS_URL . '/comunas';
    const ENDPOINT_STREETS = self::PARENT_DIRECTIONS_URL . '/calles';
    const ENDPOINT_SUFFIX_NUMBERING = self::PARENT_DIRECTIONS_URL . '/numeracionsufijo';
    const ENDPOINT_FLAT_DEPT = self::PARENT_DIRECTIONS_URL . '/pisodepto';
    const ENDPOINT_AVAILABILITY = '/agendamiento/disponibilidad';
    const ENDPOINT_TECHNICAL_FEASIBILITY = '/factibilidadtecnica/factibilidad';
    const ENDPOINT_COMMERCIAL_FEASIBILITY = '/factibilidadcomercial/factibilidad';
    const ENDPOINT_HIRING_LEGACY = '/contratacion/estadorequerimiento';
    const ENDPOINT_HIRING = '/contratacion/finalizarcontratacion';
    const ENDPOINT_ABANDONED_RECRUITMENT = '/contratacion/contratacionabandonada';
    const ENDPOINT_VALIDATE_RUT = '/ventacruzada/obtenerinformacioncliente';
    public function getRegions(): array | ServiceError;
    public function getCommunes(int $regionId): array | ServiceError;
    public function getStreets(string $communeName, string $regionName): array | ServiceError;
    public function getSuffixNumbering(string $streetName, string $communeName, string $regionName): array | ServiceError;
    public function getFlatDept(string $number, string $numberSuffix, string $streetName, string $communeName, string $regionName): array | ServiceError;
    public function getTechnicalFeasibility(string $flat, string $dpto, string $number, string $numberSuffix, string $streetName, string $communeName, string $regionName): array | ServiceError;
    public function getCommercialFeasibility(HiringInterface $hiring): array | ServiceError;
    public function getAvailability(string $communeName, string $date): array | ServiceError;
    public function getLegacySistem(array $body): array | ServiceError;
    public function getLegacySystemCron(array $body, string $empresa): array | ServiceError;
    public function saveHiring(OrderToReport $order): array | ServiceError;
    public function saveAbandonedRecruitment(array $body): array|ServiceError;
    public function ValidateRut(array $body): array|ServiceError;
}
