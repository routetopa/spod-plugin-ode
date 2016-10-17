<?php

require_once OW::getPluginManager()->getPlugin('ode')->getRootDir() . 'lib/httpful.phar';

use Httpful\Request;
use Httpful\Http;
use Httpful\Mime;


class ODE_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function settings($params)
    {
        $this->setPageTitle(OW::getLanguage()->text('ode', 'settings_title'));
        $this->setPageHeading(OW::getLanguage()->text('ode', 'settings_heading'));

        $form = new Form('settings');
        $this->addForm($form);

        /* DEEP ULR */
        $deepUrl = new TextField('deep_url');
        $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_url');
        $ode_deep_url = empty($preference) ? "http://deep.routetopa.eu/DEEP/" : $preference->defaultValue;
        $deepUrl->setValue($ode_deep_url);
        $deepUrl->setRequired();
        $form->addElement($deepUrl);

        /* DEEP DATALET LIST */
        $deepDataletList = new TextField('deep_datalet_list');
        $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_datalet_list');
        $ode_deep_datalet_list = empty($preference) ? "http://deep.routetopa.eu/DEEP/datalets-list" :  $preference->defaultValue;
        $deepDataletList->setValue($ode_deep_datalet_list);
        $deepDataletList->setRequired();
        $form->addElement($deepDataletList);

        /* DEEP CLIENT */
        $deepClient = new TextField('deep_client');
        $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_client');
        $ode_deep_client = empty($preference) ? "http://deep.routetopa.eu/DEEPCLIENT/js/deepClient.js" : $preference->defaultValue;
        $deepClient->setValue($ode_deep_client);
        $deepClient->setRequired();
        $form->addElement($deepClient);

        /* WEBCOMPONENT JS */
        $webcomponents = new TextField('webcomponents_js');
        $preference = BOL_PreferenceService::getInstance()->findPreference('ode_webcomponents_js');
        $ode_webcomponents_js = empty($preference) ? "http://deep.routetopa.eu/COMPONENTS/bower_components/webcomponentsjs/webcomponents-lite.js" : $preference->defaultValue;
        $webcomponents->setValue($ode_webcomponents_js);
        $webcomponents->setRequired();
        $form->addElement($webcomponents);

        /* OD PROVIDER */
        /*$provider = new TextField('od_provider');
        $preference = BOL_PreferenceService::getInstance()->findPreference('od_provider');
        $odProvider = empty($preference) ? "http://ckan.routetopa.eu" : $preference->defaultValue;
        $provider->setValue($odProvider);
        $provider->setRequired();
        $form->addElement($provider);*/

        /* OD ORGANIZATION */
        /*$organization = new TextField('organization');
        $preference = BOL_PreferenceService::getInstance()->findPreference('ode_organization');
        $orgPref = empty($preference) ? "" : $preference->defaultValue;
        $organization->setValue($orgPref);
        $form->addElement($organization);*/

        $submit = new Submit('add');
        $submit->setValue(OW::getLanguage()->text('ode', 'add_key_submit'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST))
        {
            $data = $form->getValues();

            /* ode_deep_url */
            $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_url');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'ode_deep_url';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['deep_url'];
            $preference->sortOrder = 1;
            BOL_PreferenceService::getInstance()->savePreference($preference);

            /* ode_deep_datalet_list */
            $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_datalet_list');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'ode_deep_datalet_list';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['deep_datalet_list'];
            $preference->sortOrder = 2;
            BOL_PreferenceService::getInstance()->savePreference($preference);

            /* ode_deep_client */
            $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_client');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'ode_deep_client';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['deep_client'];
            $preference->sortOrder = 3;
            BOL_PreferenceService::getInstance()->savePreference($preference);

            /* ode_webcomponents_js */
            $preference = BOL_PreferenceService::getInstance()->findPreference('ode_webcomponents_js');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'ode_webcomponents_js';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['webcomponents_js'];
            $preference->sortOrder = 4;
            BOL_PreferenceService::getInstance()->savePreference($preference);

            /* od_provider */
            /*$preference = BOL_PreferenceService::getInstance()->findPreference('od_provider');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'od_provider';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['od_provider'];
            $preference->sortOrder = 5;
            BOL_PreferenceService::getInstance()->savePreference($preference);*/

            /* ode_organization */
            /*$preference = BOL_PreferenceService::getInstance()->findPreference('ode_organization');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'ode_organization';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['organization'];
            $preference->sortOrder = 6;
            BOL_PreferenceService::getInstance()->savePreference($preference);*/

            /*LOAD DATASET*/

            /*$odProvider = explode(",",$data['od_provider']);
            $odOrganization = explode(",", $data['organization']);
            $odCount = 0;
            $datasetArray = array();

            for($j=0; $j<count($odProvider); $j++)
            {
                $odProvider[$j] = trim($odProvider[$j]);

                if($this->isCkan($odProvider[$j]))
                {
                    if(!empty($odOrganization[$odCount]))
                    {
                        $res = $this->getCKANDatasetList($odProvider[$j], $odOrganization[$odCount]);
                        $odCount++;
                    }
                    else
                    {
                        $res = $this->getCKANDatasetList($odProvider[$j]);
                    }
                }

                if($this->isOpenDataSoft($odProvider[$j]))
                {
                    $res = $this->getISSYDatasetList($odProvider[$j]);
                }

                $datasetArray = array_merge($datasetArray, $res);
            }

            ODE_BOL_Service::getInstance()->saveSetting('ode_dataset_list', json_encode($datasetArray));*/

        }
    }

    protected function getCKANDatasetList($odProvider, $organization="")
    {
        $datasets = Array();

        $odProvider = rtrim($odProvider,"/");
        $response = \Httpful\Request::get($odProvider . '/api/3/action/package_list')->send();

        for($j=0; $j<count($response->body->result); $j++)
        {
            $res = \Httpful\Request::get($odProvider . '/api/3/action/package_search?q=' . $response->body->result[$j])->send();

            for ($i = 0; $i < count($res->body->result->results[0]->resources); $i++)
            {
                if(!empty($organization) && $res->body->result->results[0]->organization->title != $organization) continue;

                //array_push($datasets, array("name" => $res->body->result->results[0]->resources[$i]->name,
                //    "url" => $odProvider . '/api/action/datastore_search?resource_id=' . $res->body->result->results[0]->resources[$i]->id,
                //    "description" => str_replace("'", "", isset($res->body->result->results[0]->resources[$i]->description) ? $res->body->result->results[0]->resources[$i]->description : "")));

                $name = str_replace("'", "", $res->body->result->results[0]->resources[$i]->name);

                array_push($datasets, array("name" => $name,
                    "url" => $odProvider . '/api/action/datastore_search?resource_id=' . $res->body->result->results[0]->resources[$i]->id,
                    "description" => ""));

            }
        }

        return $datasets;
    }

    /*    protected function getCKANDatasetList($odProvider, $organization="")
        {
            $datasets = Array();

            $odProvider = rtrim($odProvider,"/");

            $res = \Httpful\Request::get($odProvider . '/api/3/action/package_search')->send();

            for($j=0; $j<count($res->body->result->results); $j++)
            {
                for ($i = 0; $i < count($res->body->result->results[$j]->resources); $i++)
                {
                    if(!empty($organization) && $res->body->result->results[$j]->resources[$i]->organization->title != $organization) continue;

                    $name = str_replace("'", "", isset($res->body->result->results[$j]->resources[$i]->name) ? $res->body->result->results[$j]->resources[$i]->name : $res->body->result->results[$j]->resources[$i]->description);

                    array_push($datasets, array("name" => $name,
                        "url" => $odProvider . '/api/action/datastore_search?resource_id=' . $res->body->result->results[$j]->resources[$i]->id,
                        "description" => ""));

                }
            }

            return $datasets;
        }*/

    function getISSYDatasetList($odProvider)
    {
        $datasets = Array();

        $odProvider = rtrim($odProvider,"/");
        $response = \Httpful\Request::get($odProvider . '/api/datasets/1.0/search/?rows=10000')->send();

        for($i=0; $i<count($response->body->datasets); $i++)
        {
            // Sanitize dataset title and description
            $response->body->datasets[$i]->metas->title = str_replace("'", "", $response->body->datasets[$i]->metas->title);
            $response->body->datasets[$i]->metas->description = str_replace("'", "", isset($response->body->datasets[$i]->metas->description) ? $response->body->datasets[$i]->metas->description : "");

            array_push($datasets, array("name" => $response->body->datasets[$i]->metas->title,
                "url" => $odProvider . '/api/records/1.0/search/?dataset=' . $response->body->datasets[$i]->datasetid,
                "description" => $response->body->datasets[$i]->metas->description));

        }

        return $datasets;
    }

    protected function isCkan($odProvider)
    {
        try
        {
            $odProvider = rtrim($odProvider,"/");
            $res = \Httpful\Request::get($odProvider . '/api/3/')->followRedirects(true)->expectsJson()->send();
            if(!empty($res->body->version))
                return true;
        }
        catch(Exception $e){}

        return false;
    }

    protected function isOpenDataSoft($odProvider)
    {
        try
        {
            $odProvider = rtrim($odProvider,"/");
            $res = \Httpful\Request::get($odProvider . '/api/records/1.0/search/')->followRedirects(true)->expectsJson()->send();
            if(!empty($res->body->error))
                return true;
        }
        catch(Exception $e){}

        return false;
    }
}