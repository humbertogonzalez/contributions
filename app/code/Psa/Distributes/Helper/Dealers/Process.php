<?php
namespace Psa\Distributes\Helper\Dealers;

use Psa\Distributes\Helper\Data;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\User\Model\Spi\NotificationExceptionInterface;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Amasty\Storelocator\Model\Location;
use Amasty\Storelocator\Model\LocationFactory;
use Amasty\Storelocator\Model\ResourceModel\Location as LocationResource;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory as LocationCollectionFactory;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\ResourceModel\Role as RoleResource;
use Amasty\Storelocator\Block\Adminhtml\Location\Edit\Form\Status;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\TransportInterfaceFactory;

class Process
{
    private array $dealersProcess = [];
    private array $usersProcess = [];
    private Role $role;

    public function __construct(
        protected readonly LoggerInterface $logger,
        protected readonly Data $dataService,
        protected readonly UserFactory $userFactory,
        protected readonly UserResource $userResource,
        protected readonly UserCollectionFactory $userCollectionFactory,
        protected readonly LocationFactory $locationFactory,
        protected readonly LocationResource $locationResource,
        protected readonly LocationCollectionFactory $locationCollectionFactory,
        protected readonly RoleFactory $roleFactory,
        protected readonly RoleResource $roleResource,
        protected readonly MessageInterfaceFactory $messageInterfaceFactory,
        protected readonly TransportInterfaceFactory $transportInterfaceFactory,
        protected readonly TransportBuilder $transportBuilder,
        protected readonly StateInterface $inlineTranslation,
        protected readonly StoreManagerInterface $storeManager
    ) {
        $role = $this->roleFactory->create();
        $this->roleResource->load($role, 'psa_role_dealer', 'role_name');
        $this->role = $role;
    }

    public function loadAndSaveDealers(): void
    {
        $this->logger->info("===== Process::loadAndSaveDealers =====");
        try {
            $dealers = $this->dataService->getDealearsByBatch();

            if (!$dealers) {
                $this->logger->error($this->dataService->getConfig('distributes/textEdit/withoutDealer'));
                return;
            }
            if (!empty($dealers->error)) {
                $this->logger->error(json_encode($this->dataService->getConfig('distributes/textEdit/withoutDealer')));
                return;
            }
            $totalItems = (int)$dealers->listado->total;
            $this->logger->info("> TotalItems: " . $totalItems);
            $pageSize = (int)$dealers->listado->paginado->renglones;
            $totalPages = (int)($totalItems / $pageSize);
            $totalPages += ($totalItems % $pageSize !== 0) ? 1 : 0;
            $this->updateUsersAndDealers($dealers->listado->elementos);
            for ($currentPage = 2; $currentPage <= $totalPages; $currentPage++) {
                $dealers = $this->dataService->getDealearsByBatch($currentPage, $pageSize);
                if (!$dealers) {
                    $this->logger->error($this->dataService->getConfig('distributes/textEdit/withoutDealer'));
                    continue;
                }
                if (!empty($dealers->error)) {
                    $this->logger->error(json_encode($dealers->error));
                    return;
                }
                $this->updateUsersAndDealers($dealers->listado->elementos);
            }
            $this->disableUsersAndDealersNoProcessed();
            $this->logger->info("> Dealers Sync Suscessfull");
        } catch (\Exception $e) {
            $this->logger->error('-----------loadAndSaveDealers------------');
            $this->logger->error($e->getMessage());
            $this->logger->error('-----------loadAndSaveDealers------------');
        }
    }

    protected function updateUsersAndDealers($dealers): void
    {
        $this->logger->info("===== Process::updateUsersAndDealers =====");
        $codes = array_map(fn ($dealer) => $dealer->codigo, $dealers);
        $dealers = array_combine(
            $codes,
            $dealers
        );
        $dealersCollection = $this->locationCollectionFactory->create();
        $dealersCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter(
                'code',
                ['in' => $codes]
            )
            ->setPageSize(sizeof($codes));
        $dealersProcessed = [];
        $dealersItems = $dealersCollection->getItems();
        foreach ($dealersItems as $dealer) {
            $this->logger->info("> Dealer: " . $dealer->getCode());
            $this->logger->info("> EsTop50: " . $dealers[$dealer->getCode()]->esTop50);
            $dealer->setStatus(Status::ENABLED);
            $dealer->setTop($dealers[$dealer->getCode()]->esTop50);
            $this->dealersProcess[] = $dealer->getCode();
            $dealersProcessed[] = $dealer->getCode();
            $user = $this->processUserByDealer($dealer, $dealers[$dealer->getCode()]);
            if ($user) {
                $this->usersProcess[] = $user;
            }
            $dealer->save();
        }
        $newDealers = array_diff($codes, $dealersProcessed);
        foreach ($newDealers as $dealerCode) {
            $this->dealersProcess[] = $this->processDealer($dealers[$dealerCode]);
        }
    }

    private function getDataByDealerData($dealerData): array
    {
        $pais = substr($dealerData->direccion->localidad->provincia->pais->codigoPais, 0, 2) ;
        return [
            'name'      => strtoupper($dealerData->denominacion),
            'code'      => $dealerData->codigo,
            'country'   => $pais,
            'position' => 0,
            'city'      => strtoupper($dealerData->direccion->localidad->provincia->denominacion),
            'zip'       => empty($dealerData->direccion->codigoPostal)?'':strtoupper($dealerData->direccion->codigoPostal),
            'address'   => strtoupper($dealerData->direccion->calle.' '.$dealerData->direccion->altura.' '
                .$dealerData->direccion->piso.' '.$dealerData->direccion->departamento.' '
                .$dealerData->direccion->barrio),
            'state'     => empty($dealerData->direccion->departamento)?'':strtoupper($dealerData->direccion->departamento),
            'description' => strtoupper($dealerData->condicionImpositiva),
            'phone'     => $dealerData->telefonoCelular.' '.$dealerData->telefonoFijo,
            'email'     => $dealerData->email,
            'lat'       => $dealerData->georeferencia->latitud,
            'lng'       => $dealerData->georeferencia->longitud,
            'top'    => $dealerData->esTop50,
            'code_imp' => strtoupper($dealerData->condicionImpositiva),
            'status' => Status::ENABLED,
            'stores'    => ',1,',
            'url_key'   => 'amlocator'
        ];
    }

    protected function processDealer($dealerData): string
    {
        $data = $this->getDataByDealerData($dealerData);
        $newDealer = $this->locationFactory->create();
        $newDealer->setData($data);
        $this->locationResource->save($newDealer);
        $user = $this->processUserByDealer($newDealer, $dealerData);
        if ($user) {
            $this->usersProcess[] = $user;
        }
        return $newDealer->getCode();
    }

    public function newPassword(int $length): string
    {
        $numbers = '0123456789';
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@#$%&!';
        $charactersLength = strlen($characters);
        $numbersLength = strlen($numbers);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
            $randomString .= $numbers[rand(0, $numbersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @throws NotificationExceptionInterface
     */
    protected function processUserByDealer($dealer, $dealerData): int
    {
        $user = $this->userFactory->create();
        $this->userResource->load($user, $dealer->getId(), 'allowed_store_by_dealer');
        $user->sendNotificationEmailsIfRequired();
        $newUser = false;
        $pass = null;
        if (!$user->getId()) {
            $pass = $this->newPassword(4);
            $newUser = true;
            $user->setData([
                'username'  => $dealerData->email,
                'firstname' => $dealerData->nombres,
                'lastname'  => $dealerData->apellidos,
                'email'     => $dealerData->email,
                'password'  => $pass,
                'interface_locale' => 'es_AR',
                'is_active' => true,
                'is_dealer' => 1,
                'allowed_store_by_dealer' => $dealer->getId()
            ]);
            $user->setRoleId($this->role->getRoleId());
        } else {
            $user->setIsActive(true);
        }
        try {
            $this->userResource->save($user);
        } catch (\Throwable $e) {
            echo $e->getMessage();
            return false;
        }

        if ($newUser) {
            $this->logger->info("> NewUser: ");
            $this->sendMailDealer($user, $pass);
        }
        return $user->getUserId();
    }
    /**
     * @param $data
     * @return void
     */
    public function sendMailDealer($user, $pass): void
    {
        $this->logger->info("===== Process::sendMailDealer =====");
        $toEmail = $user['email'];
        $this->logger->info("> Mail: " . $toEmail);
        try {
            $templateOptions = [
                'area' => Area::AREA_FRONTEND,
                'store' => Store::DEFAULT_STORE_ID
            ];
            $templateVars = [
                'distribute_name' => $user['firstname'] . " " . $user['lastname'],
                'distribute_email' => $toEmail,
                'password' => $pass,
            ];
            $from = [
                'email' => $this->dataService->getConfig('trans_email/ident_general/email'),
                'name' => $this->dataService->getConfig('trans_email/ident_general/name')
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier('distribute_new_dealer')
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFromByScope($from, $this->storeManager->getStore(Store::DEFAULT_STORE_ID))
                ->addTo($toEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->logger->error("> Email sended...");
        } catch (\Exception $e) {
            $this->logger->error("> ERROR sendMailDealer...");
            $this->logger->error($e->getMessage());
        }
    }
    protected function disableUsersAndDealersNoProcessed(): void
    {
        $this->disableUsersNoProcessed();
        $this->disableDealersNoProcessed();
    }

    protected function disableDealersNoProcessed(): void
    {
        $dealersCode = array_unique($this->dealersProcess);
        $collection = $this->locationCollectionFactory->create();
        $collection
            ->addFieldToSelect('*')
            ->addFieldToFilter('code', ['nin' => $dealersCode]);
        foreach ($collection->getItems() as $dealer) {
            $dealer->setStatus(Status::DISABLED);
            $this->locationResource->save($dealer);
        }
    }
    public function disableUsersNoProcessed(): void
    {
        $usersId = array_unique($this->usersProcess);
        $collection = $this->userCollectionFactory->create();
        $collection
            ->addFieldToSelect('*')
            ->addFieldToFilter('user_id', ['nin' => $usersId])
            ->addFieldToFilter('is_dealer', ['eq' => 1])
            ->addFieldToFilter('detail_role.role_name', ['eq' => $this->role->getRoleName()]);
        foreach ($collection->getItems() as $user) {
            $user->setIsActive(false);
            $user->setExtra(null);
            $this->userResource->save($user);
        }
    }
}
