<?php

namespace Redegal\Checkout\Plugin\Block\Checkout;

use Magento\Catalog\Model\Product;

class LayoutProcessor
{

    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array  $jsLayout
    ) {

        $this->changeShippingAddressLabels($jsLayout);
        $this->changeBillingAddressLabels($jsLayout);

        return $jsLayout;
    }

    private function changeShippingAddressLabels(&$jsLayout)
    {
        // Add Validations in fields Checkout

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['postcode']['validation'] = ["required-entry"=> true, "validate-number" => true, "min_text_length" => 5, "max_text_length" => 5];
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['postcode']['sortOrder'] = '80';

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['label'] = __('Telephone');

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['sortOrder'] = '40';

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['postcode']['sortOrder'] = '60';
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['postcode']['component'] = 'Redegal_Sepomex/js/view/element/post-code';
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['postcode']['config']['elementTmpl'] = 'Redegal_Sepomex/element/postcode';

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']
        ['children']['country_id']['config']['disabled'] = 'disabled';

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['validation'] = ['required-entry' => true, "min_text_length" => 10, "max_text_length" => 10, "validate-number" => true];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['street'] = [
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('Street Address'),
            'required' => true,
            'dataScope' => 'shippingAddress.street',
            'provider' => 'checkoutProvider',
            'sortOrder' => 70,
            'type' => 'group',
            'additionalClasses' => 'street',
            'children' => [
                [
                    'label' => __('Type'),
                    'component' => 'Magento_Ui/js/form/element/select',
                    'config' => [
                        'customScope' => 'shippingAddress',
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/select',
                        'options' => [
                            [
                                'value' => '',
                                'label' => 'Seleccione la clasificación de su calle',
                            ],
                            [
                                'value' =>  'Av.',
                                'label' =>  'Av.',
                            ],
                            [
                                'value' =>  'Blvd.',
                                'label' =>  'Blvd.',
                            ],
                            [
                                'value' =>  'Calle',
                                'label' =>  'Calle',
                            ],
                            [
                                'value' =>  'Calz.',
                                'label' =>  'Calz.',
                            ],
                            [
                                'value' =>  'Vía',
                                'label' =>  'Vía',
                            ],
                            [
                                'value' =>  'Cjon.',
                                'label' =>  'Cjón.',
                            ]
                        ]
                    ],
                    'dataScope' => '0',
                    'provider' => 'checkoutProvider',
                    'validation' => ['required-entry' => true, "min_text_length" => 1, "max_text_length" => 255],
                ],
                [
                    'label' => __('Street'),
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        'customScope' => 'shippingAddress',
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input'
                    ],
                    'additionalClasses' => 'custom-validate-length-street-checkout',
                    'dataScope' => '1',
                    'provider' => 'checkoutProvider',
                    'validation' => ['required-entry' => true, "custom-validate-length-street-checkout" => true, "min_text_length" => 1, "max_text_length" => 43],
                ],
                [
                    'label' => __('Outdoor number'),
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        'customScope' => 'shippingAddress',
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input'
                    ],
                    'additionalClasses' => 'custom-validate-length-exterior-number-checkout',
                    'dataScope' => '2',
                    'placeholder' => 'Número Exterior, manzana o lote.',
                    'provider' => 'checkoutProvider',
                    'validation' => ['required-entry' => true, "custom-validate-length-exterior-number-checkout" => true, "min_text_length" => 1, "max_text_length" => 43],
                ],
                [
                    'label' => __('Interior number'),
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        'customScope' => 'shippingAddress',
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input'
                    ],
                    'additionalClasses' => 'custom-validate-length-interior-number-checkout',
                    'dataScope' => '3',
                    'placeholder' => 'Número interior, edificio, torre, unidad, etc.',
                    'provider' => 'checkoutProvider',
                    'validation' => ["custom-validate-length-interior-number-checkout" => true, "min_text_length" => 1, "max_text_length" => 43],
                ],
                [
                    'label' => __('Between streets'),
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        'customScope' => 'shippingAddress',
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input'
                    ],
                    'dataScope' => '4',
                    'provider' => 'checkoutProvider',
                    'validation' => ['required-entry' => true, "min_text_length" => 1, "max_text_length" => 43],
                ],
                [
                    'label' => __('References'),
                    'component' => 'Magento_Ui/js/form/element/textarea',
                    'config' => [
                        'customScope' => 'shippingAddress',
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/textarea'
                    ],
                    'dataScope' => '6',
                    'provider' => 'checkoutProvider',
                    'validation' => ['required-entry' => true, "min_text_length" => 1, "max_text_length" => 43],
                ],
                [
                    'label' => __('Cologne'),
                    'component' => 'Magento_Ui/js/form/element/select',
                    'config' => [
                        'customScope' => 'shippingAddress',
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/select',
                        'options' => [
                            [
                                'value' => '',
                                'label' => 'Colonia, fraccionamiento, etc.',
                                'disabled' => 1
                            ]
                        ]
                    ],
                    'dataScope' => '5',
                    'provider' => 'checkoutProvider',
                    'validation' => ['required-entry' => true, "min_text_length" => 1, "max_text_length" => 255],
                ],
            ]
        ];
    }


    private function changeBillingAddressLabels(&$jsLayout)
    {
        $paymentForms = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'];
        foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {

            $paymentMethodCode = str_replace('-form', '', $paymentMethodForm);

            $billingAddress = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children'];

            if (!isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form'])) {
                continue;
            }

            $billingAddress['country_id']['sortOrder'] = '120';
            $billingAddress['postcode']['sortOrder'] = '80';
            $billingAddress['telephone']['label'] = __('Telephone');
            $billingAddress['telephone']['sortOrder'] = '40';
            $billingAddress['telephone']['validation'] = ['required-entry' => true, "min_text_length" => 1, "max_text_length" => 10, "validate-number" => true];
            $billingAddress['postcode']['sortOrder'] = '60';

            $billingAddress['country_id']['config']['disabled'] = 'disabled';
            $billingAddress['street']['children'] = [
                [
                    'label' => __('Type'),
                    'component' => 'Magento_Ui/js/form/element/select',
                    'config' => [
                        'customScope' => 'billingAddress'.$paymentMethodCode ,
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/select',
                        'options' => [
                            [
                                'value' => '',
                                'label' => 'Seleccione la clasificación de su calle',
                            ],
                            [
                                'value' =>  'Av.',
                                'label' =>  'Av.',
                            ],
                            [
                                'value' =>  'Blvd.',
                                'label' =>  'Blvd.',
                            ],
                            [
                                'value' =>  'Calle',
                                'label' =>  'Calle',
                            ],
                            [
                                'value' =>  'Calz.',
                                'label' =>  'Calz.',
                            ],
                            [
                                'value' =>  'Vía',
                                'label' =>  'Vía',
                            ],
                            [
                                'value' =>  'Cjon.',
                                'label' =>  'Cjón.',
                            ]
                        ]
                    ],
                    'dataScope' => '0',
                    'provider' => 'checkoutProvider',
                    'validation' => ['required-entry' => true, "min_text_len‌​gth" => 1, "max_text_length" => 255],
                ],
                [
                    'label' => __('Street'),
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        'customScope' => 'billingAddress'.$paymentMethodCode ,
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input'
                    ],
                    'dataScope' => '1',
                    'provider' => 'checkoutProvider',
                    'validation' => ['required-entry' => true, "min_text_length" => 1, "max_text_length" => 43, "custom-validate-length-street-checkout" => true],
                ],
                [
                    'label' => __('Outdoor number'),
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        'customScope' => 'billingAddress'.$paymentMethodCode ,
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input'
                    ],
                    'placeholder' => 'No. Ext, Mzn, Lote. No. Int, Depto, Torre, etc.',
                    'dataScope' => '2',
                    'provider' => 'checkoutProvider',
                    'validation' => ['required-entry' => true, "min_text_length" => 1, "max_text_length" => 43, "custom-validate-length-exterior-number-checkout" => true],
                ],
                [
                    'label' => __('Interior number'),
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        'customScope' => 'billingAddress'.$paymentMethodCode ,
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input'
                    ],
                    'dataScope' => '3',
                    'placeholder' => 'Número interior, edificio, torre, unidad, etc.',
                    'provider' => 'checkoutProvider',
                    'validation' => ["custom-validate-length-exterior-number-checkout" => true, "min_text_length" => 1, "max_text_length" => 43],
                ],
                [
                    'label' => __('Between streets'),
                    'component' => 'Magento_Ui/js/form/element/abstract',
                    'config' => [
                        'customScope' => 'billingAddress'.$paymentMethodCode,
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/input'
                    ],
                    'dataScope' => '5',
                    'provider' => 'checkoutProvider',
                    'validation' => ['required-entry' => true, "min_text_length" => 1, "max_text_length" => 43],
                ],
                [
                    'label' => __('References'),
                    'component' => 'Magento_Ui/js/form/element/textarea',
                    'config' => [
                        'customScope' => 'billingAddress'.$paymentMethodCode,
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/textarea'
                    ],
                    'dataScope' => '6',
                    'provider' => 'checkoutProvider',
                    'validation' => ['required-entry' => true, "min_text_length" => 1, "max_text_length" => 43],
                ],
                [
                    'label' => __('Cologne'),
                    'component' => 'Magento_Ui/js/form/element/select',
                    'config' => [
                        'customScope' => 'billingAddress'.$paymentMethodCode ,
                        'template' => 'ui/form/field',
                        'elementTmpl' => 'ui/form/element/select',
                        'options' => [
                            [
                                'value' => '',
                                'label' => 'Colonia, fraccionamiento, etc.',
                                'disabled' => 1
                            ]
                        ]
                    ],
                    'dataScope' => '4',
                    'provider' => 'checkoutProvider',
                    'validation' => ['required-entry' => true, "min_text_length" => 1, "max_text_length" => 255],
                ],
            ];
        }

    }
}
