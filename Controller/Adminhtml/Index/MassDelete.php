<?php


namespace Esatic\DropdownCity\Controller\Adminhtml\Index;


use Esatic\DropdownCity\Model\RomCity;
use Magento\Backend\App\Action;

class MassDelete extends Action
{

    /**
     * @var \Eadesigndev\RomCity\Model\ResourceModel\RomCityFactory
     */
    private $resourceModel;
    /**
     * @var \Eadesigndev\RomCity\Model\RomCityFactory
     */
    private $romCityFactory;

    public function __construct(
        Action\Context $context,
        \Eadesigndev\RomCity\Model\ResourceModel\RomCityFactory $resourceModel,
        \Eadesigndev\RomCity\Model\RomCityFactory $romCityFactory
    )
    {
        parent::__construct($context);
        $this->resourceModel = $resourceModel;
        $this->romCityFactory = $romCityFactory;
    }


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        $resourceModel = $this->resourceModel->create();
        foreach ($this->getRequest()->getParam('selected', array()) as $item) {
            /** @var RomCity $romCity */
            $romCity = $this->romCityFactory->create();
            $resourceModel->load($romCity, $item);
            $resourceModel->delete($romCity);
        }
        $this->messageManager->addSuccessMessage(__('Selected cities has been deleted'));
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();
        return $resultRedirect;
    }
}
