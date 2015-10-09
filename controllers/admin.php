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

        $deepUrl = new TextField('deep_url');
        //$deepUrl->setInvitation(OW::getLanguage()->text('ode', 'deep_url_invitation'));
        //$deepUrl->setHasInvitation(true);
        $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_url');
        $ode_deep_url = empty($preference) ? "http://service.routetopa.eu/DEEalerProvider/DEEP/" : $preference->defaultValue;
        $deepUrl->setValue($ode_deep_url);
        $deepUrl->setRequired();
        $form->addElement($deepUrl);

        $deepDataletList = new TextField('deep_datalet_list');
        //$deepDataletList->setInvitation(OW::getLanguage()->text('ode', 'deep_datalet_list_invitation'));
        //$deepDataletList->setHasInvitation(true);
        $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_datalet_list');
        $ode_deep_datalet_list = empty($preference) ? "http://service.routetopa.eu/DEEalerProvider/DEEP/datalets-list" :  $preference->defaultValue;
        $deepDataletList->setValue($ode_deep_datalet_list);
        $deepDataletList->setRequired();
        $form->addElement($deepDataletList);

        $deepClient = new TextField('deep_client');
        //$deepClient->setInvitation(OW::getLanguage()->text('ode', 'deep_client_invitation'));
        //$deepClient->setHasInvitation(true);
        $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_client');
        $ode_deep_client = empty($preference) ? "http://service.routetopa.eu/DEEalerProvider/DEEPCLIENT/js/deepClient.js" : $preference->defaultValue;
        $deepClient->setValue($ode_deep_client);
        $deepClient->setRequired();
        $form->addElement($deepClient);

        $provider = new TextField('od_provider');
        //$provider->setInvitation(OW::getLanguage()->text('ode', 'od_provider'));
        //$provider->setHasInvitation(true);
        $preference = BOL_PreferenceService::getInstance()->findPreference('od_provider');
        $odProvider = empty($preference) ? "http://service.routetopa.eu" : $preference->defaultValue;
        $provider->setValue($odProvider);
        $provider->setRequired();
        $form->addElement($provider);

        $organization = new TextField('organization');
        //$organization->setInvitation(OW::getLanguage()->text('ode', 'organization_invitation'));
        //$organization->setHasInvitation(true);
        $preference = BOL_PreferenceService::getInstance()->findPreference('ode_organization');
        $orgPref = empty($preference) ? "" : $preference->defaultValue;
        $organization->setValue($orgPref);
        $form->addElement($organization);

        $submit = new Submit('add');
        $submit->setValue(OW::getLanguage()->text('ode', 'add_key_submit'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST))
        {
            $data = $form->getValues();

            $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_url');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'ode_deep_url';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['deep_url'];
            $preference->sortOrder = 1;
            BOL_PreferenceService::getInstance()->savePreference($preference);


            $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_datalet_list');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'ode_deep_datalet_list';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['deep_datalet_list'];
            $preference->sortOrder = 2;
            BOL_PreferenceService::getInstance()->savePreference($preference);


            $preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_client');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'ode_deep_client';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['deep_client'];
            $preference->sortOrder = 3;
            BOL_PreferenceService::getInstance()->savePreference($preference);


            $preference = BOL_PreferenceService::getInstance()->findPreference('od_provider');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'od_provider';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['od_provider'];
            $preference->sortOrder = 4;
            BOL_PreferenceService::getInstance()->savePreference($preference);

            $preference = BOL_PreferenceService::getInstance()->findPreference('ode_organization');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'ode_organization';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['organization'];
            $preference->sortOrder = 5;
            BOL_PreferenceService::getInstance()->savePreference($preference);


            $datasetListJson = $this->getDatasetList($data['od_provider'], $data['organization']);
            $preference = BOL_PreferenceService::getInstance()->findPreference('ode_dataset_list');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'ode_dataset_list';
            $preference->sectionName = 'general';
            $preference->defaultValue = $datasetListJson;
            $preference->sortOrder = 6;
            BOL_PreferenceService::getInstance()->savePreference($preference);

        }
    }

    protected function getDatasetList($odProvider, $organization="")
    {
        $datasets = Array();

        $response = \Httpful\Request::get($odProvider . '/api/3/action/package_list')->send();

        for($j=0; $j<count($response->body->result); $j++)
        {
            $res = \Httpful\Request::get($odProvider . '/api/3/action/package_search?q=' . $response->body->result[$j])->send();

            for ($i = 0; $i < count($res->body->result->results[0]->resources); $i++)
            {
                if(!empty($organization) && $res->body->result->results[0]->organization->title != $organization) continue;

                array_push($datasets, array("name" => $res->body->result->results[0]->resources[$i]->name,
                    "url" => $odProvider . '/api/action/datastore_search?resource_id=' . $res->body->result->results[0]->resources[$i]->id,
                    "description" => str_replace("'", "", $res->body->result->results[0]->resources[$i]->description)));
            }
        }

        return json_encode($datasets);
    }

    protected function isCkan($odProvider)
    {
        $res = \Httpful\Request::get($odProvider . '/api/3/')->send();
        return false;
    }
}