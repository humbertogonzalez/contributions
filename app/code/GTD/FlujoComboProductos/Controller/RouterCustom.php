<?php

namespace Empresas\FlujoComboProductos\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ResponseInterface;
use Empresas\FlujoComboProductos\Helper\Data;

class RouterCustom implements RouterInterface
{
    protected $actionFactory;
    protected $response;
    protected $helper;

    public function __construct(
        ActionFactory     $actionFactory,
        ResponseInterface $response,
        Data              $helper
    )
    {
        $this->actionFactory = $actionFactory;
        $this->response = $response;
        $this->helper = $helper;
    }

    public function match(RequestInterface $request)
    {
        $urls_params = $this->helper->getConfig('segmento_empresas_configuracion/url_combos/value');

        if ($urls_params && $this->helper->isEnable() == 1) {
            $urls_paramsArray = unserialize($urls_params);
            $path = trim($request->getPathInfo(), '/');
            foreach ($urls_paramsArray as $key => $value) {
                if ($path == $value['request_path']) {
                    $request->setModuleName('segmento_empresas')
                        ->setControllerName('index')
                        ->setActionName('index')
                        ->setParams([
                            'combo_id' => $value['combos'],
                            'tipo_producto' => isset($value['tipo_producto']) ? $value['tipo_producto'] : '',
                            'request_path' => isset($value['request_path']) ? $value['request_path'] : '',
                        ]);
                    return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
                }

            }

        }

        return null;
    }
}
