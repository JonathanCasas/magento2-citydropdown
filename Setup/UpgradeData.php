<?php

namespace Esatic\DropdownCity\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Tests\NamingConvention\true\mixed;

class UpgradeData implements UpgradeDataInterface
{
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setupData = $setup->startSetup();
        $italian_regions = [
            ['country_id' => 'CO', 'code' => 'CO-BOG', 'default_name' => 'Bogotá, D.C.']
        ];
        foreach ($italian_regions as $key => $value) {
            $row = $this->getRow($setup, $value);
            if (!$row) {
                $setup->getConnection()->insertForce(
                    $setup->getTable('directory_country_region'),
                    $value
                );
            } else {
                $rowName = $this->getRowName($setup, $row['region_id'], $value['default_name'], 'es_CO');
                if (!$rowName) {
                    $setup->getConnection()->insertForce(
                        $setup->getTable('directory_country_region_name'),
                        ['locale' => 'es_CO', 'region_id' => $row['region_id'], 'name' => $value['default_name']]
                    );
                }
            }
        }
        $setupData->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param $value
     * @return mixed
     */
    private function getRow(ModuleDataSetupInterface $setup, $value)
    {
        $select = $setup->getConnection()->select()
            ->from(['dcr' => $setup->getTable('directory_country_region')])
            ->where('dcr.country_id = ?', $value['country_id'])
            ->where('dcr.code = ?', $value['code'])
            ->where('dcr.default_name = ?', $value['default_name']);
        return $setup->getConnection()->fetchRow($select);
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param $regionId
     * @param $name
     * @param $locale
     * @return mixed
     */
    private function getRowName(ModuleDataSetupInterface $setup, $regionId, $name, $locale)
    {
        $select = $setup->getConnection()->select()
            ->from(['dcr' => $setup->getTable('directory_country_region_name')])
            ->where('dcr.region_id = ?', $regionId)
            ->where('dcr.name = ?', $name)
            ->where('dcr.locale = ?', $locale);
        return $setup->getConnection()->fetchRow($select);
    }
}
