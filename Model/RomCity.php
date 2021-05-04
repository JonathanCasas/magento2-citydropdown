<?php


namespace Esatic\DropdownCity\Model;


class RomCity extends \Eadesigndev\RomCity\Model\RomCity
{

    public function getCityCode()
    {
        return $this->getData('city_code');
    }

    public function setCityCode($cityCode)
    {
        $this->setData('city_code', $cityCode);
    }

}
