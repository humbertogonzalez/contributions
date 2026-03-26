<?php


namespace Redegal\Sepomex\Controller\Adminhtml\Sepomex;

class Delete extends \Redegal\Sepomex\Controller\Adminhtml\Sepomex
{

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('sepomex_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create('Redegal\Sepomex\Model\Sepomex');
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Sepomex.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['sepomex_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Sepomex to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
