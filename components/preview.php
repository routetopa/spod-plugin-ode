<?php

class ODE_CMP_Preview extends OW_Component
{
    public function __construct($text)
    {
        $cache = (ODE_BOL_Service::getInstance()->getSettingByKey('openwall_dataset_list') != null) ? ODE_BOL_Service::getInstance()->getSettingByKey('openwall_dataset_list')->value : "";
        $this->assign("datasetCache", str_replace("'", "", $cache));
    }
}