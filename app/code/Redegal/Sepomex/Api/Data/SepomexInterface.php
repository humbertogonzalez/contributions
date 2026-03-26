<?php


namespace Redegal\Sepomex\Api\Data;

interface SepomexInterface
{

    const D_TIPO_ASENTA = 'd_tipo_asenta';
    const D_CIUDAD = 'd_ciudad';
    const D_MNPIO = 'D_mnpio';
    const D_CP = 'd_CP';
    const C_ESTADO = 'c_estado';
    const D_ESTADO = 'd_estado';
    const D_CODIGO = 'd_codigo';
    const SEPOMEX_ID = 'sepomex_id';
    const D_ASENTA = 'd_asenta';


    /**
     * Get sepomex_id
     * @return string|null
     */
    public function getSepomexId();

    /**
     * Set sepomex_id
     * @param string $sepomexId
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setSepomexId($sepomexId);

    /**
     * Get d_codigo
     * @return string|null
     */
    public function getDCodigo();

    /**
     * Set d_codigo
     * @param string $dCodigo
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setDCodigo($dCodigo);

    /**
     * Get d_asenta
     * @return string|null
     */
    public function getDAsenta();

    /**
     * Set d_asenta
     * @param string $dAsenta
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setDAsenta($dAsenta);

    /**
     * Get d_tipo_asenta
     * @return string|null
     */
    public function getDTipoAsenta();

    /**
     * Set d_tipo_asenta
     * @param string $dTipoAsenta
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setDTipoAsenta($dTipoAsenta);

    /**
     * Get D_mnpio
     * @return string|null
     */
    public function getDMnpio();

    /**
     * Set D_mnpio
     * @param string $dMnpio
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setDMnpio($dMnpio);

    /**
     * Get d_estado
     * @return string|null
     */
    public function getDEstado();

    /**
     * Set d_estado
     * @param string $dEstado
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setDEstado($dEstado);

    /**
     * Get d_ciudad
     * @return string|null
     */
    public function getDCiudad();

    /**
     * Set d_ciudad
     * @param string $dCiudad
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setDCiudad($dCiudad);

    /**
     * Get d_CP
     * @return string|null
     */
    public function getDCP();

    /**
     * Set d_CP
     * @param string $dCP
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setDCP($dCP);

    /**
     * Get c_estado
     * @return string|null
     */
    public function getCEstado();

    /**
     * Set c_estado
     * @param string $cEstado
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setCEstado($cEstado);
}
