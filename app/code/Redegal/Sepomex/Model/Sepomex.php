<?php


namespace Redegal\Sepomex\Model;

use Redegal\Sepomex\Api\Data\SepomexInterface;

class Sepomex extends \Magento\Framework\Model\AbstractModel implements SepomexInterface
{

    protected $_eventPrefix = 'redegal_sepomex_sepomex';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Redegal\Sepomex\Model\ResourceModel\Sepomex');
    }

    /**
     * Get sepomex_id
     * @return string
     */
    public function getSepomexId()
    {
        return $this->getData(self::SEPOMEX_ID);
    }

    /**
     * Set sepomex_id
     * @param string $sepomexId
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setSepomexId($sepomexId)
    {
        return $this->setData(self::SEPOMEX_ID, $sepomexId);
    }

    /**
     * Get d_codigo
     * @return string
     */
    public function getDCodigo()
    {
        return $this->getData(self::D_CODIGO);
    }

    /**
     * Set d_codigo
     * @param string $dCodigo
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setDCodigo($dCodigo)
    {
        return $this->setData(self::D_CODIGO, $dCodigo);
    }

    /**
     * Get d_asenta
     * @return string
     */
    public function getDAsenta()
    {
        return $this->getData(self::D_ASENTA);
    }

    /**
     * Set d_asenta
     * @param string $dAsenta
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setDAsenta($dAsenta)
    {
        return $this->setData(self::D_ASENTA, $dAsenta);
    }

    /**
     * Get d_tipo_asenta
     * @return string
     */
    public function getDTipoAsenta()
    {
        return $this->getData(self::D_TIPO_ASENTA);
    }

    /**
     * Set d_tipo_asenta
     * @param string $dTipoAsenta
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setDTipoAsenta($dTipoAsenta)
    {
        return $this->setData(self::D_TIPO_ASENTA, $dTipoAsenta);
    }

    /**
     * Get D_mnpio
     * @return string
     */
    public function getDMnpio()
    {
        return $this->getData(self::D_MNPIO);
    }

    /**
     * Set D_mnpio
     * @param string $dMnpio
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setDMnpio($dMnpio)
    {
        return $this->setData(self::D_MNPIO, $dMnpio);
    }

    /**
     * Get d_estado
     * @return string
     */
    public function getDEstado()
    {
        return $this->getData(self::D_ESTADO);
    }

    /**
     * Set d_estado
     * @param string $dEstado
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setDEstado($dEstado)
    {
        return $this->setData(self::D_ESTADO, $dEstado);
    }

    /**
     * Get d_ciudad
     * @return string
     */
    public function getDCiudad()
    {
        return $this->getData(self::D_CIUDAD);
    }

    /**
     * Set d_ciudad
     * @param string $dCiudad
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setDCiudad($dCiudad)
    {
        return $this->setData(self::D_CIUDAD, $dCiudad);
    }

    /**
     * Get d_CP
     * @return string
     */
    public function getDCP()
    {
        return $this->getData(self::D_CP);
    }

    /**
     * Set d_CP
     * @param string $dCP
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setDCP($dCP)
    {
        return $this->setData(self::D_CP, $dCP);
    }

    /**
     * Get c_estado
     * @return string
     */
    public function getCEstado()
    {
        return $this->getData(self::C_ESTADO);
    }

    /**
     * Set c_estado
     * @param string $cEstado
     * @return \Redegal\Sepomex\Api\Data\SepomexInterface
     */
    public function setCEstado($cEstado)
    {
        return $this->setData(self::C_ESTADO, $cEstado);
    }
}
