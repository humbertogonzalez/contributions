<?php

declare(strict_types=1);

namespace BalloonGroup\Company\Block\User\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\Locale\OptionInterface;
use Magento\Framework\Registry;
use Magento\User\Block\User\Edit\Tab\Main as MagentoMain;

class Main extends MagentoMain
{
    /** @var string */
    public const CURRENT_USER_SELLER_ID_FIELD ="seller_id";

    /**
     * Main constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Session $authSession
     * @param ListsInterface $localeLists
     * @param array $data
     * @param OptionInterface $deployedLocales
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Session $authSession,
        ListsInterface $localeLists,
        protected OptionInterface $deployedLocales,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $authSession,
            $localeLists,
            $data
        );
    }

    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var $model \Magento\User\Model\User */
        $model = $this->_coreRegistry->registry('permissions_user');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Account Information')]);

        if ($model->getUserId()) {
            $baseFieldset->addField('user_id', 'hidden', ['name' => 'user_id']);
        } else {
            if (!$model->hasData('is_active')) {
                $model->setIsActive(1);
            }
        }

        $baseFieldset->addField(
            'username',
            'text',
            [
                'name' => 'username',
                'label' => __('User Name'),
                'id' => 'username',
                'title' => __('User Name'),
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'firstname',
            'text',
            [
                'name' => 'firstname',
                'label' => __('First Name'),
                'id' => 'firstname',
                'title' => __('First Name'),
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'lastname',
            'text',
            [
                'name' => 'lastname',
                'label' => __('Last Name'),
                'id' => 'lastname',
                'title' => __('Last Name'),
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'email',
            'text',
            [
                'name' => 'email',
                'label' => __('Email'),
                'id' => 'customer_email',
                'title' => __('User Email'),
                'class' => 'required-entry validate-email',
                'required' => true
            ]
        );

        $isNewObject = $model->isObjectNew();
        if ($isNewObject) {
            $passwordLabel = __('Password');
        } else {
            $passwordLabel = __('New Password');
        }
        $confirmationLabel = __('Password Confirmation');
        $this->_addPasswordFields($baseFieldset, $passwordLabel, $confirmationLabel, $isNewObject);

        $baseFieldset->addField(
            'interface_locale',
            'select',
            [
                'name' => 'interface_locale',
                'label' => __('Interface Locale'),
                'title' => __('Interface Locale'),
                'values' => $this->deployedLocales->getOptionLocales(),
                'class' => 'select'
            ]
        );

        if ($this->_authSession->getUser()->getId() != $model->getUserId()) {
            $baseFieldset->addField(
                'is_active',
                'select',
                [
                    'name' => 'is_active',
                    'label' => __('This account is'),
                    'id' => 'is_active',
                    'title' => __('Account Status'),
                    'class' => 'input-select',
                    'options' => ['1' => __('Active'), '0' => __('Inactive')]
                ]
            );
        }

        $baseFieldset->addField('user_roles', 'hidden', ['name' => 'user_roles', 'id' => '_user_roles']);

        $currentUserCompanyFieldset = $form->addFieldset(
            'current_user_company_fieldset',
            ['legend' => __('Current User Company Data')]
        );

        $currentUserCompanyFieldset->addField(
            self::CURRENT_USER_SELLER_ID_FIELD,
            'text',
            [
                'name' => self::CURRENT_USER_SELLER_ID_FIELD,
                'label' => __('Seller ID'),
                'id' => self::CURRENT_USER_SELLER_ID_FIELD,
                'title' => __('Seller ID'),
                'required' => false
            ]
        );

        $currentUserVerificationFieldset = $form->addFieldset(
            'current_user_verification_fieldset',
            ['legend' => __('Current User Identity Verification')]
        );

        $currentUserVerificationFieldset->addField(
            self::CURRENT_USER_PASSWORD_FIELD,
            'password',
            [
                'name' => self::CURRENT_USER_PASSWORD_FIELD,
                'label' => __('Your Password'),
                'id' => self::CURRENT_USER_PASSWORD_FIELD,
                'title' => __('Your Password'),
                'class' => 'validate-current-password required-entry',
                'required' => true
            ]
        );

        $data = $model->getData();
        unset($data['password']);
        unset($data[self::CURRENT_USER_PASSWORD_FIELD]);
        $form->setValues($data);

        $this->setForm($form);

        return $this;
    }
}
