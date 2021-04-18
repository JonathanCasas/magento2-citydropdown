<?php
/**
 * Copyright © 2018 EaDesign by Eco Active S.R.L. All rights reserved.
 * See LICENSE for license details.
 */

namespace Esatic\DropdownCity\Controller\Adminhtml\Index;

use Eadesigndev\RomCity\Helper\Data;
use Eadesigndev\RomCity\Model\ResourceModel\Collection\Collection;
use Eadesigndev\RomCity\Model\ResourceModel\Collection\CollectionFactory;
use Eadesigndev\RomCity\Model\RomCityFactory;
use Eadesigndev\RomCity\Model\RomCityRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\View\Result\PageFactory;

class Upload extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Eadesigndev_RomCity::romcity';

    protected $resultPageFactory;

    private $csvProccesor;

    private $moduleReader;

    private $directoryList;

    private $dataHelper;

    private $collectionFactory;

    private $romCityFactory;

    private $resultRedirect;

    private $romCityRepository;

    public function __construct(
        Context $context,
        Csv $csvProccesor,
        Reader $moduleReader,
        PageFactory $resultPageFactory,
        DirectoryList $directoryList,
        CollectionFactory $collectionFactory,
        ResultFactory $resultRedirect,
        RomCityFactory $romCityFactory,
        RomCityRepository $romCityRepository,
        Data $dataHelper
    ) {
        $this->romCityRepository = $romCityRepository;
        $this->resultRedirect = $resultRedirect;
        $this->romCityFactory = $romCityFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataHelper = $dataHelper;
        $this->directoryList = $directoryList;
        $this->moduleReader = $moduleReader;
        $this->csvProccesor = $csvProccesor;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Index action list city.
     * @return $resultRedirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $this->_redirect->getRefererUrl();
        $resultRedirect->setUrl($url);

        $this->readCsv();

        return $resultRedirect;
    }

    public function readCsv()
    {
        $pubMediaDir = $this->directoryList->getPath(DirectoryList::MEDIA);
        $fieName = $this->dataHelper->getConfigFileName();
        $ds = DIRECTORY_SEPARATOR;
        $dirTest = '/test';

        $file = $pubMediaDir . $dirTest . $ds . $fieName;

        if (!empty($file)) {
            $csvData = $this->csvProccesor->getData($file);

            $csvDataProcessed = [];
            unset($csvData[0]);
            list($collection, $csvDataProcessed) = $this->csvProcessValues($csvData, $csvDataProcessed);

            foreach ($csvDataProcessed as $dataRow) {
                $regionId = $dataRow['region_id'];
                $cityName = $dataRow['city'];
                $entityId = $dataRow['entity_id'];
                $cityCode = $dataRow['city_code'];

                $romCityRepository = $this->romCityFactory->create();
                if (isset($entityId) && is_numeric($entityId)) {
                    $romCityRepository = $this->romCityRepository->getById($entityId);
                    $romCityRepository->setCityName($cityName);
                    $romCityRepository->setCityCode($cityCode);
                    $this->romCityRepository->save($romCityRepository);
                    continue;
                }

                $romCityRepository->setRegionId($regionId);
                $romCityRepository->setCityName($cityName);
                $romCityRepository->setCityCode($cityCode);
                $collection->addItem($romCityRepository);
            }
        }
        $collection->walk('save');
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * @param $csvData
     * @param $csvDataProcessed
     * @return array
     */
    private function csvProcessValues($csvData, $csvDataProcessed)
    {
        /** @var  Collection $collection */
        $collection = $this->collectionFactory->create();

        foreach ($csvData as $csvValue) {
            $csvValueProcessed = [];
            foreach ($csvValue as $key => $value) {
                if ($key == 0) {
                    $csvValueProcessed['entity_id'] = $value;
                }

                if ($key == 1) {
                    $csvValueProcessed['region_id'] = $value;
                }

                if ($key == 2) {
                    $csvValueProcessed['city'] = $value;
                }
                if ($key == 3) {
                    $csvValueProcessed['city_code'] = $value;
                }
            }
            $csvDataProcessed[] = $csvValueProcessed;
        }
        return [$collection, $csvDataProcessed];
    }
}
