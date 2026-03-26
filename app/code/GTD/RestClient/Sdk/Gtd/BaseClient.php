<?php

namespace Balloon\RestClient\Sdk\Gtd;

use Balloon\Hiring\Api\Data\HiringInterface;
use Balloon\RestClient\Api\Sdk\Gtd\ClientInterface;
use Balloon\RestClient\Model\OrderToReport;
use Balloon\RestClient\Service\CurlClient;
use Balloon\RestClient\Service\Gtd\Client as ServiceClient;
use Balloon\RestClient\Model\Config\ConfigProvider;
use Balloon\RestClientMapErrors\Api\Data\TypeMapErrorInterface;
use Balloon\RestClientMapErrors\Resolve\MapError;
use Psr\Log\LoggerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Balloon\RestClient\Exceptions\ServiceError;
use Magento\Framework\Phrase;
use Balloon\RestClientErrorReport\Model\LogFactory;
use Balloon\RestClientErrorReport\Model\ResourceModel\Logs;
use Balloon\RestClient\Model\Config\Source\StoreCodes;
use Balloon\RestClient\Helper\Data;

class BaseClient implements ClientInterface
{
    const KNOWN_BUGS = [1, 99];
    const SUCCESS_SERVICE_STATUS = 0;
    protected string $urlBase;
    protected string $storeCode;

    public function __construct(
        private readonly ServiceClient       $serviceClient,
        protected ConfigProvider             $configProvider,
        protected LoggerInterface            $logger,
        private readonly SerializerInterface $serializer,
        private readonly LogFactory          $logFactory,
        private readonly Logs                $resourceLogs,
        protected readonly MapError          $mapErrorResolve,
        protected readonly Data              $helper
    )
    {
    }

    protected function sendRequest(string $endpoint, array $params = []): mixed
    {
        return $this->parseResponse($this->sendRawRequest($endpoint, $params));
    }

    protected function sendRawRequest(string $endpoint, array $params = []): mixed
    {
        $url = "{$this->urlBase}{$endpoint}";
        try {
            $bodyRequest = [
                "empresa" => $this->storeCode,
                ...$params
            ];

            $isLogsEnabled = $this->configProvider->getEnabledLogs();
            $isLogsEnabledDB = $this->configProvider->getEnabledLogsOnDB();
            if ($isLogsEnabled) {
                $this->logger->info("New Request");
                $this->logger->info("URL: " . $url);
                $this->logger->info("Payload: " . $this->serializer->serialize($bodyRequest));
            }
            $response = $this->serviceClient->sendRequest(
                $url,
                $bodyRequest
            );
            if ($isLogsEnabled) {
                $this->logger->info("Response");
                $this->logger->info("Status" . $response->getStatus());
                $this->logger->info("Body" . $this->serializer->serialize($response->getBody() ?? ""));
            }
            if ($isLogsEnabledDB) {
                $this->logDBInfo(
                    "Trace Request",
                    "URL: " . $url . ", Payload:" . $this->serializer->serialize($bodyRequest),
                    "Status: " . $response->getStatus() . ", Body:" . $response->getBody() ?? ""
                );
            }
        } catch (\Throwable $e) {
            $this->logError(ServiceError::UNEXPECTED_ERROR, $e->getMessage(), "URL: " . $url . ", Payload:" . $this->serializer->serialize($bodyRequest));
            throw new ServiceError(new Phrase(ServiceError::UNEXPECTED_ERROR));
        }
        return $response;
    }

    protected function logDBInfo(string $title, string $request, string $response)
    {
        try {
            $logModel = $this->logFactory->create();
            $logModel->setTypeId(0);
            $logModel->setMessage($title);
            $logModel->setRequest($request);
            $logModel->setResponse($response);
            $this->resourceLogs->save($logModel);
        } catch (\Throwable $e) {
            $this->logSimpleError(ServiceError::UNEXPECTED_ERROR, $e->getMessage());
        }
    }

    protected function logSimpleError(string $msg, string $data, string $request = null)
    {
        $this->logger->error('Begin: ' . $msg);
        $this->logger->error('Trace: ' . $this->serializer->serialize(debug_backtrace(limit: 5)));
        if ($request) {
            $this->logger->error('Request: ' . $data);
        }
        $this->logger->error('Data: ' . $data);
        $this->logger->error('End: ' . $msg);
    }

    protected function logError(string $msg, string $data, string $request = null)
    {
        $this->logSimpleError($msg, $data, $request);
        try {
            $logModel = $this->logFactory->create();
            $logModel->setTypeId(3);
            $logModel->setMessage($msg);
            $logModel->setRequest($request);
            $logModel->setResponse("Error Message: " . $data);
            $this->resourceLogs->save($logModel);
        } catch (\Throwable $e) {
            $this->logSimpleError(ServiceError::UNEXPECTED_ERROR, $e->getMessage());
        }
    }

    protected function parseResponse(?CurlClient $response)
    {
        try {
            $body = $this->serializer->unserialize($response->getBody() ?? "");
        } catch (\Throwable $e) {
            $this->logError($e->getMessage(), $response->getBody() ?? "", "N/A");
            throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
        }
        if (!isset($body['codigo'])) {
            $this->logError(ServiceError::MALFORMED_MSG, $response->getBody() ?? "", "N/A");
            throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
        }
        $body['codigo'] = (int)$body['codigo'];
        $textoComercial = "<PlaceHolder Comercial>";
        if (isset($body['textoComercial'])) {
            $textoComercial = $body['textoComercial'];
        }
        $body['textoComercial'] = $textoComercial;
        return $body;
    }

    protected function parseResponseTypeList(array $response): array|ServiceError
    {
        if ($response['codigo'] !== self::SUCCESS_SERVICE_STATUS) {
            $this->logError(ServiceError::MALFORMED_MSG, $this->serializer->serialize($response), "N/A");
            if (!isset($body['mensaje'])) {
                throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
            }
            throw new ServiceError(new Phrase($body['mensaje']));
        }
        if (!isset($response['lista']) || !is_array($response['lista'])) {
            $this->logError(ServiceError::MALFORMED_MSG, $this->serializer->serialize($response), "N/A");
            throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
        }
        return $response['lista'];
    }

    protected function parseResponseGeneral(array $response): array|ServiceError
    {
        if ($response['codigo'] !== self::SUCCESS_SERVICE_STATUS) {
            $this->logError(ServiceError::MALFORMED_MSG, $this->serializer->serialize($response), "N/A");
            if (!isset($body['mensaje'])) {
                throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
            }
            throw new ServiceError(new Phrase($body['mensaje']));
        }
        if (!isset($response['lista']) || !is_array($response['lista'])) {
            $this->logError(ServiceError::MALFORMED_MSG, $this->serializer->serialize($response), "N/A");
            throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
        }
        return $response['lista'];
    }

    public function getRegions(): array|ServiceError
    {
        $res = $this->sendRequest(
            $this::ENDPOINT_REGIONS
        );
        $response = $this->parseResponseTypeList($res);
        if ($this->storeCode == StoreCodes::COD_TELSUR) {
            try {
                foreach ($response as &$region) {
                    $region['idRegion'] = $region['id_region'];
                    unset($region['id_region']);
                }
            } catch (\Throwable $e) {
                $this->logError($e->getMessage(), $this->serializer->serialize($response), "N/A");
                throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
            }
        }
        return $response;
    }

    public function getCommunes(int $regionId): array|ServiceError
    {
        $res = $this->sendRequest(
            $this::ENDPOINT_COMMUNES,
            [
                "idRegion" => $regionId
            ]
        );
        return $this->parseResponseTypeList($res);
    }

    public function getStreets(string $communeName, string $regionName): array|ServiceError
    {
        $res = $this->sendRequest(
            $this::ENDPOINT_STREETS,
            [
                "comuna" => $communeName,
                "calleAprox" => $regionName
            ]
        );
        sort($res['lista']);
        return $this->parseResponseTypeList($res);
    }

    public function getSuffixNumbering(string $streetName, string $communeName, string $regionName): array|ServiceError
    {
        $res = $this->sendRequest(
            $this::ENDPOINT_SUFFIX_NUMBERING,
            [
                "calle" => $streetName,
                "comuna" => $communeName,
                "region" => $regionName
            ]
        );
        $response = $this->parseResponseTypeList($res);
        try {
            foreach ($response as &$suffix) {
                $suffix['numero'] = $suffix['numero'] !== null ? strval($suffix['numero']) : "";
                $suffix['numeroSufijo'] = $suffix['numeroSufijo'] !== null ? strval($suffix['numeroSufijo']) : "";
            }
        } catch (\Throwable $e) {
            $this->logError($e->getMessage(), $this->serializer->serialize($response), "N/A");
            throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
        }
        return $response;
    }

    public function getFlatDept(string $number, string $numberSuffix, string $streetName, string $communeName, string $regionName): array|ServiceError
    {
        $params = [
            "calle" => $streetName,
            "comuna" => $communeName,
            "region" => $regionName,
            "numero" => $number
        ];
        if ($this->storeCode == 'CentroNorte') {
            $params['numeroSufijo'] = $numberSuffix;
        } elseif ($this->storeCode == 'Telsur') {
            $params['numeroSufijo'] = $numberSuffix;
        }
        $res = $this->sendRequest(
            $this::ENDPOINT_FLAT_DEPT,
            $params
        );
        $flatDtps = $this->parseResponseTypeList($res);
        $response = [];
        $flatsCodes = [];
        if ($this->storeCode === StoreCodes::COD_TELSUR) {
            try {
                foreach ($flatDtps as $flatDtp) {
                    $flatCode = strval($flatDtp['piso']);
                    $key = array_search($flatCode, $flatsCodes);
                    if ($key === false) {
                        $key = sizeof($flatsCodes);
                        $flatsCodes[] = $flatCode;
                        $response[] = [
                            "piso" => $flatCode,
                            "dptos" => []
                        ];
                    }
                    $flatName = $flatDtp['depto'];
                    if ($flatName) {
                        $response[$key]["dptos"] = $flatName;
                    }

                }
            } catch (\Throwable $e) {
                $this->logError($e->getMessage(), $this->serializer->serialize($response), "N/A");
                throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
            }
        } else {
            foreach ($flatDtps as $flatDtp) {
                $flatDtp['dptos'] = $flatDtp['depto'];
                unset($flatDtp['depto']);
                $response[] = $flatDtp;
            }
        }
        return $response;
    }

    public function getTechnicalFeasibility(?string $flat, ?string $dpto, string $number, string $numberSuffix, string $streetName, string $communeName, string $regionName): array|ServiceError
    {
        $params = [
            "piso" => $flat ?? "",
            "departamento" => $dpto ?? "",
            "numero" => $number,
            "numeroSufijo" => $numberSuffix,
            "calle" => $streetName,
            "comuna" => $communeName,
            "region" => $regionName
        ];
        if ($this->storeCode === StoreCodes::COD_CENTRO_NORTE) {
            $params["tipoServicio"] = [
                "FTTH",
                "INTFTTH"
            ];
        } elseif (($this->storeCode === StoreCodes::COD_TELSUR)) {
            $params["tipoServicio"] = ["FTTX"];
        }
        $response = $this->sendRequest(
            $this::ENDPOINT_TECHNICAL_FEASIBILITY,
            $params
        );
        if (!isset($response['detalle']) || !isset($response['textoComercial'])) {
            throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
        }
        return $this->parseBlockResponse($response, TypeMapErrorInterface::TECHNICAL_FEASIBILITY);
    }

    public function parseBlockResponse(array $response, int $typeRequest): array
    {
        $response['body'] = false;
        if ($response['codigo'] !== 0) {
            $response['body'] = $this->mapErrorResolve->resolveBlock($typeRequest, $response['codigo']);
        }
        return $response;
    }
    public function parseBlockResponseValidate(array $response, int $typeRequest): array
    {
        $response['body'] = false;
        if ($response['codigo'] !== 1) {
            $response['body'] = $this->mapErrorResolve->resolveBlock($typeRequest, $response['codigo']);
        }
        return $response;
    }

    public function getCommercialFeasibility(HiringInterface $hiring): array|ServiceError
    {

        $rut = $hiring->getRut();
        $digito = "0";

        $explode_char = '‑';
        if (str_contains($rut, '-')) {
            $explode_char = '-';
        }

        if (!empty($rut)) {
            $rutExplode = explode($explode_char, $rut);
            if (isset($rutExplode[0]) && isset($rutExplode[1])) {
                $rut = $rutExplode[0];
                $digito = $rutExplode[1];
            } else {
                $digito = substr($rut, -1);
                $rut = rtrim($rut, $digito);
            }
        }

        $params = [
            //            "quote" => $hiring->getQuoteId(),
            "rut" => str_replace(".", "", $rut),
            "region" => $hiring->getRegion(),
            "calle" => $hiring->getStreet(),
            "numero" => $hiring->getSuffixNumber(),
            "numeroSufijo" => $hiring->getSuffixNumberName(),
            "piso" => $hiring->getFlat(),
            "departamento" => $hiring->getDpto(),
            "fecha" => "",
            "segmento" => "",
            "comuna" => $hiring->getCommune(),
            "tipoSolicitud" => "",
            "primerNombre" => $hiring->getFirstname(),
            "segundoNombre" => $hiring->getSecondname(),
            "apellidoPaterno" => $hiring->getLastname() ? $hiring->getLastname() : "FALTA APELLIDO",
            "apellidoMaterno" => $hiring->getMotherLastname()

        ];
        if ($this->storeCode === StoreCodes::COD_CENTRO_NORTE) {
            $params["localidad"] = "";
            $params["digito"] = $digito;
            $params["nroSerie"] = $hiring->getNroSerie();
        } elseif (($this->storeCode === StoreCodes::COD_TELSUR)) {
            $params["comuna"] = "";
            $params["localidad"] = $hiring->getCommune();
            $params["digito"] = $digito;
            $params["nroSerie"] = $hiring->getNroSerie();
        }
        $response = $this->sendRequest(
            $this::ENDPOINT_COMMERCIAL_FEASIBILITY,
            $params
        );

        if (!isset($response['detalle']) || !isset($response['textoComercial'])) {
            throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
        }
        return $this->parseBlockResponse($response, TypeMapErrorInterface::COMMERCIAL_FEASIBILITY);
    }

    public function getAvailability(string $communeName, string $date): array|ServiceError
    {
        $params = [
            "comuna" => $communeName,
            "segmento" => "RES",
            "tipo_solicitud" => "INST",
            "descOferta" => "",
            "fecha" => $date
        ];
        if ($this->storeCode === StoreCodes::COD_TELSUR) {
            $params["segmento"] = "R";
            $params["descOferta"] = "INTERNET";
            $params["tipo_solicitud"] = "";
            $response = $this->sendRawRequest(
                $this::ENDPOINT_AVAILABILITY,
                $params
            );
            $response = $this->parseAvailabilityTelsur($response);
        } else {
            $response = $this->sendRequest(
                $this::ENDPOINT_AVAILABILITY,
                $params
            );
            $response = $this->parseAvailabilityGtd($response);
        }
        if (($response['codigo'] === 0 && count($response['disponibilidad']) === 0) || !isset($response['detalle']) || !isset($response['textoComercial'])) {
            throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
        }

        return $this->parseBlockResponse($response, TypeMapErrorInterface::AVAILABILITY);
    }

    private function parseAvailabilityGtd($responseBody)
    {
        $response = [
            'disponibilidad' => [],
            'detalle' => '', //@ToDo eliminar
            'textoComercial' => '' //@ToDo eliminar
        ];
        try {
            $body = $responseBody ?? "";
            if (isset($body['codigo'])) {
                $response['codigo'] = (int)$body['codigo'];
            }
            $arrayRemplace = [];
            if (isset($body['disponibilidad'])) {
                foreach ($body['disponibilidad'] as $date) {
                    if (isset($date['fecha']) ) {

                        if (!isset($date['timeSlot']) || $date['timeSlot'] == '18:01-21:00' || $date['timeSlot'] == 238 || $date['timeSlot'] == 238 || $date['timeSlot'] == 239) {
                            continue;
                        }

                        $label = explode('-', $date['timeSlot']);
                        if (!isset($response['disponibilidad'][$date['fecha']])) {
                            $response['disponibilidad'][$date['fecha']][$label[0]] = [
                                'fecha' => $date['fecha'],
                                'workSkill' => $date['workSkill'],
                                'tieneCapacity' => $date['tieneCapacity'],
                                'timeSlot' => $date['timeSlot'],
                                'quota' => $date['quota'],
                                'available' => $date['available'],
                                'dia' => $date['dia']
                            ];
                        } else {
                            if (!$this->helper->in_array_r($date['timeSlot'], array_column($response['disponibilidad'][$date['fecha']], 'timeSlot'))) {
                                $response['disponibilidad'][$date['fecha']][$label[0]] = [
                                    'fecha' => $date['fecha'],
                                    'workSkill' => $date['workSkill'],
                                    'tieneCapacity' => $date['tieneCapacity'],
                                    'timeSlot' => $date['timeSlot'],
                                    'quota' => $date['quota'],
                                    'available' => $date['available'],
                                    'dia' => $date['dia']
                                ];
                            }
                        }
                        ksort($response['disponibilidad'][$date['fecha']]);
                    }

                }
            }


            if (isset($response['disponibilidad'])) {
                foreach ($response['disponibilidad'] as $itemDate) {
                    foreach ($itemDate as $item) {
                        $arrayRemplace[] = $item;
                    }
                }
            }
            $response['disponibilidad'] = $arrayRemplace;

            if (count($response['disponibilidad']) === 0) {
                $response['codigo'] = 1;
            }

        } catch (\Throwable $e) {
            $this->logError($e->getMessage(), json_encode($responseBody["disponibilidad"]) ?? "", "N/A");
            throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
        }
        return $response;
    }

    private function parseAvailabilityTelsur($responseBody)
    {
        $response = [
            'disponibilidad' => [],
            'detalle' => '', //@ToDo eliminar
            'textoComercial' => '' //@ToDo eliminar
        ];
        $arrayRemplace = [];
        try {
            $body = $this->serializer->unserialize($responseBody->getBody() ?? "");
            if (isset($body['codigo'])) {
                $response['codigo'] = (int)$body['codigo'];
            }
            $timeSlotsDictionary = [];
            foreach ($body['timeSlotsDictionary'] as $dictionary) {
                if (
                    isset($dictionary['timeFrom']) &&
                    isset($dictionary['timeTo'])
                ) {
                    $timeFrom = substr($dictionary['timeFrom'], 0, -3);
                    $timeTo = substr($dictionary['timeTo'], 0, -3);
                    $timeSlotsDictionary[$dictionary['label']] = "{$timeFrom}-{$timeTo}";
                }
            }

            foreach ($body['dates'] as $date) {
                foreach ($date['areas'] as $area) {
                    if (isset($area['reason'])) {
                        continue;
                    }
                    if (isset($area['timeSlots'])) {
                        foreach ($area['timeSlots'] as $time) {

                            if (isset($time['reason']) || !isset($timeSlotsDictionary[$time['label']]) || $time['label'] == 'am' || $time['label'] == 'pm') {
                                continue;
                            }
                            $currentDate = explode('-', $date['date']);
                            $currentDate = "{$currentDate[2]}/{$currentDate[1]}/{$currentDate[0]}";
                            $label = explode('-', $time['label']);
                            if (!isset($response['disponibilidad'][$currentDate])) {
                                $response['disponibilidad'][$currentDate][$label[0]] = [
                                    'fecha' => $currentDate,
                                    'realLabel' => $time['label'],
                                    'timeSlot' => $timeSlotsDictionary[$time['label']],
                                    'tieneCapacity' => !isset($time['reason'])
                                ];
                            } else {
                                if (!$this->helper->in_array_r($time['label'], array_column($response['disponibilidad'][$currentDate], 'realLabel'))) {
                                    $response['disponibilidad'][$currentDate][$label[0]] = [
                                        'fecha' => $currentDate,
                                        'realLabel' => $time['label'],
                                        'timeSlot' => $timeSlotsDictionary[$time['label']],
                                        'tieneCapacity' => !isset($time['reason'])
                                    ];
                                }
                            }
                            ksort($response['disponibilidad'][$currentDate]);
                        }
                    }
                }
            }

            if (isset($response['disponibilidad'])) {
                foreach ($response['disponibilidad'] as $itemDate) {
                    foreach ($itemDate as $item) {
                        $arrayRemplace[] = $item;
                    }
                }
            }
            $response['disponibilidad'] = $arrayRemplace;

            if (count($response['disponibilidad']) === 0) {
                $response['codigo'] = 1;
            }

        } catch (\Throwable $e) {
            $this->logError($e->getMessage(), $responseBody->getBody() ?? "", "N/A");
            throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
        }
        return $response;
    }

    public function getLegacySistem(array $body): array|ServiceError
    {
        $response = [];
        try {
            $response = $this->sendRequest(
                $this::ENDPOINT_HIRING_LEGACY,
                $body
            );
        } catch (\Throwable $e) {
            $this->logError($e->getMessage(), $this->serializer->serialize($response), "N/A");
            throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
        }
        return $response;
    }

    public function getLegacySystemCron(array $body, string $empresa): array|ServiceError
    {
        $response = [];
        try {
            $response = $this->sendRequestLegacySystemCron(
                $this::ENDPOINT_HIRING_LEGACY,
                $body,
                $empresa
            );
        } catch (\Throwable $e) {
            $this->logError($e->getMessage(), $this->serializer->serialize($response), "N/A");
            throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
        }
        return $response;
    }

    public function sendRequestLegacySystemCron(string $endpoint, array $params, string $empresa): mixed
    {
        $url = "{$this->urlBase}{$endpoint}";
        try {
            $bodyRequest = [
                "empresa" => $empresa,
                ...$params
            ];
            $isLogsEnabled = $this->configProvider->getEnabledLogs();
            $isLogsEnabledDB = $this->configProvider->getEnabledLogsOnDB();
            if ($isLogsEnabled) {
                $this->logger->info("New Request");
                $this->logger->info("URL: " . $url);
                $this->logger->info("Payload: " . $this->serializer->serialize($bodyRequest));
            }
            $response = $this->serviceClient->sendRequest(
                $url,
                $bodyRequest
            );
            if ($isLogsEnabled) {
                $this->logger->info("Response");
                $this->logger->info("Status" . $response->getStatus());
                $this->logger->info("Body" . $this->serializer->serialize($response->getBody() ?? ""));
            }
            if ($isLogsEnabledDB) {
                $this->logDBInfo(
                    "Trace Request",
                    "URL: " . $url . ", Payload:" . $this->serializer->serialize($bodyRequest),
                    "Status: " . $response->getStatus() . ", Body:" . $response->getBody() ?? ""
                );
            }
        } catch (\Throwable $e) {
            $this->logError(ServiceError::UNEXPECTED_ERROR, $e->getMessage(), "URL: " . $url . ", Payload:" . $this->serializer->serialize($bodyRequest));
            throw new ServiceError(new Phrase(ServiceError::UNEXPECTED_ERROR));
        }
        return $this->parseResponse($response);
    }


    public function saveHiring(OrderToReport $order): array|ServiceError
    {
        $params = $order->getAll();
        try {
            $response = $this->sendRequest(
                $this::ENDPOINT_HIRING,
                $params
            );
        } catch (\Throwable $e) {
            $this->logError(ServiceError::UNEXPECTED_ERROR, $e->getMessage(), "URL: " . $this::ENDPOINT_HIRING . ", Payload:" . $this->serializer->serialize($params));
        }

        if (
            $response['codigo'] === 0 && (!isset($response['detalle']) ||
                !isset($response['idCtaFacturacion']) ||
                !isset($response['codigoComercio']) ||
                !isset($response['idInicial'])
            )
        ) {
            throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
        }
        return $this->parseBlockResponse($response, TypeMapErrorInterface::SELF_HIRING);
    }

    public function saveAbandonedRecruitment(array $body): array|ServiceError
    {
        $response = [];
        try {
            $response = $this->sendRequest(
                $this::ENDPOINT_ABANDONED_RECRUITMENT,
                $body
            );

        } catch (\Throwable $e) {
            $this->logError($e->getMessage(), $this->serializer->serialize($response), "N/A");
            throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
        }
        return $response;
    }

    public function ValidateRut(array $body): array|ServiceError
    {
        $response = [];
        try {
            $response = $this->sendRequest(
                $this::ENDPOINT_VALIDATE_RUT,
                $body
            );

        } catch (\Throwable $e) {
            $this->logError($e->getMessage(), $this->serializer->serialize($response), "N/A");
            throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
        }

        if ($response['codigo'] == 1) {
            $response['success'] = true;
        }else{
            $response['success'] = false;
        }

        if (!isset($response['detalle']) || !isset($response['textoComercial'])) {
            throw new ServiceError(new Phrase(ServiceError::MALFORMED_MSG));
        }

        return $this->parseBlockResponseValidate($response, TypeMapErrorInterface::VALIDATE_RUT);
    }


}
