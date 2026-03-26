<?php

namespace Balloon\RestClient\Model;

class OrderToReport
{
    public function __construct(
        private string $rut,
        private string $digito,
        private string $nro_serie,
        private string $idEcommerce,
        private string $primerNombre,
        private string $segundoNombre,
        private string $apellidoPaterno,
        private string $apellidoMaterno,
        private string $region,
        private string $comuna,
        private string $calle,
        private string $numero,
        private string $numeroSufijo,
        private string $piso,
        private string $departamento,
        private string $telefonoCliente,
        private string $correoCliente,
        private string $fechaInstalacion,
        private string $timeSlot,
        private string $numeroSolicitado,
        private string $boletaElectronica,
        private string $publicidad,
        private string $patpass,
        private string $tipoCliente,
        private string $id_oferta,
        private string $razonSocial,
        private string $tipoDocumento,
        private string $giroProfesional,
        private string $rutFacturacion,
        private string $digitoFacturacion,
        private string $regionFacturacion,
        private string $comunaFacturacion,
        private string $calleFacturacion,
        private string $numeroFacturacion,
        private string $numeroSufijoFacturacion,
        private string $pisoFacturacion,
        private string $departamentoFacturacion,
        private string $tipoPersona,
        private string $rutRepresentanteLegal,
        private string $digitoRepresentanteLegal,
        private string $primerNombreRepLegal,
        private string $segundoNombreRepLegal,
        private string $apellidoPaternoRepLegal,
        private string $apellidoMaternoRepLegal,
        private string $regionLegal,
        private string $comunaLegal,
        private string $calleLegal,
        private string $numeroLegal,
        private string $numeroSufijoLegal,
        private string $pisoLegal,
        private string $departamentoLegal,
        private array  $listaSKU
    )
    {
    }

    public function getAll(): array
    {
        $response = get_object_vars($this);
        $response['listaSKU'] = json_encode($response['listaSKU']);
        return $response;
    }
}
