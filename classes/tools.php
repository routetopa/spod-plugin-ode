<?php

class ODE_CLASS_Tools
{
    private static $classInstance;

    public static function getInstance()
    {
        if(self::$classInstance === null)
            self::$classInstance = new self();

        return self::$classInstance;
    }

    public function get_all_datalet_definitions()
    {
        $definitions = '';

        $ch = curl_init($preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_datalet_list')->defaultValue);//1000 limit!
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (200 == $retcode) {
            $data = json_decode($res, true);
            foreach ($data as $datalet)
            {
                $definitions .= "<link rel='import' href='{$datalet['url']}{$datalet['name']}.html' />";
            }
        }

        return $definitions;
    }

}