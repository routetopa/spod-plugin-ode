<?php

require_once OW::getPluginManager()->getPlugin('ode')->getRootDir() . 'lib/httpful.phar';

use Httpful\Request;
use Httpful\Http;
use Httpful\Mime;

class ODE_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function settings($params)
    {
        $settingsItem = new BASE_MenuItem();
        $settingsItem->setLabel('SETTINGS');
        $settingsItem->setUrl( OW::getRouter()->urlForRoute( 'ode-settings' ) );
        $settingsItem->setKey( 'settings' );
        $settingsItem->setIconClass( 'ow_ic_gear_wheel' );
        $settingsItem->setOrder( 0 );

        $providersItem = new BASE_MenuItem();
        $providersItem->setLabel('PROVIDERS');
        $providersItem->setUrl( OW::getRouter()->urlForRoute( 'ode-providers' ) );
        $providersItem->setKey( 'providers' );
//        $providersItem->setIconClass( 'ow_ic_help' );
        $providersItem->setOrder( 1 );

        $menu = new BASE_CMP_ContentMenu( array( $settingsItem, $providersItem ) );
        $this->addComponent( 'menu', $menu );

        $this->setPageTitle('ODE SETTINGS');
        $this->setPageHeading('ODE SETTINGS');

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

        /* DEEP COMPONENTS */
        $componentsUrl = new TextField('components_url');
        $preference = BOL_PreferenceService::getInstance()->findPreference('spodpr_components_url');
        $spodpr_components_url = empty($preference) ? "http://deep.routetopa.eu/COMPONENTS/" : $preference->defaultValue;
        $componentsUrl->setValue($spodpr_components_url);
        $componentsUrl->setRequired();
        $form->addElement($componentsUrl);

        /* WEBCOMPONENT JS */
        $webcomponents = new TextField('webcomponents_js');
        $preference = BOL_PreferenceService::getInstance()->findPreference('ode_webcomponents_js');
        $ode_webcomponents_js = empty($preference) ? "http://deep.routetopa.eu/COMPONENTS/bower_components/webcomponentsjs/webcomponents-lite.js" : $preference->defaultValue;
        $webcomponents->setValue($ode_webcomponents_js);
        $webcomponents->setRequired();
        $form->addElement($webcomponents);

        $submit = new Submit('add');
        $submit->setValue('SUBMIT');
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

            /* spodpr_components_url */
            $preference = BOL_PreferenceService::getInstance()->findPreference('spodpr_components_url');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'spodpr_components_url';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['components_url'];
            $preference->sortOrder = 1;
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

        }
    }

    public function providers()
    {
        $settingsItem = new BASE_MenuItem();
        $settingsItem->setLabel('SETTINGS');
        $settingsItem->setUrl( OW::getRouter()->urlForRoute( 'ode-settings' ) );
        $settingsItem->setKey( 'settings' );
        $settingsItem->setIconClass( 'ow_ic_gear_wheel' );
        $settingsItem->setOrder( 0 );

        $providersItem = new BASE_MenuItem();
        $providersItem->setLabel('PROVIDERS');
        $providersItem->setUrl( OW::getRouter()->urlForRoute( 'ode-providers' ) );
        $providersItem->setKey( 'providers' );
//        $providersItem->setIconClass( 'ow_ic_help' );
        $providersItem->setOrder( 1 );

        $menu = new BASE_CMP_ContentMenu( array( $settingsItem, $providersItem ) );
        $this->addComponent( 'menu', $menu );

        $this->setPageTitle('ODE PROVIDERS');
        $this->setPageHeading('ODE PROVIDERS');

        $form = new Form('providers');
        $this->addForm($form);

        $name = new TextField('name');
        $name->setRequired();
        $form->addElement($name);

        $url = new TextField('url');
        $url->setRequired();
        $form->addElement($url);

        $submit = new Submit('addProvider');
        $submit->setValue('ADD');
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST))
        {
            $data = $form->getValues();

            ODE_BOL_Service::getInstance()->addProvider($data['name'], $data['url']);

            $this->redirect(OW::getRouter()->urlForRoute('ode-providers'));
        }

        $providersList = array();
        $deleteUrls = array();
        $providers = ODE_BOL_Service::getInstance()->getProviderList();
        foreach ( $providers as $provider )
        {
            /* @var $contact ODE_BOL_Provider */
            $providersList[$provider->id]['name'] = $provider->name;
            $providersList[$provider->id]['url'] = $provider->url;
            $deleteUrls[$provider->id] = OW::getRouter()->urlFor(__CLASS__, 'delete', array('id' => $provider->id));
        }
        $this->assign('providersList', $providersList);
        $this->assign('deleteUrls', $deleteUrls);
        $this->assign('createDatasetCache', OW::getRouter()->urlFor(__CLASS__, 'createDatasetCache'));
    }

    public function delete( $params )
    {
        if ( isset($params['id']) )
        {
            ODE_BOL_Service::getInstance()->deleteProvider((int) $params['id']);
        }
        $this->redirect(OW::getRouter()->urlForRoute('ode-providers'));
    }

    public function createDatasetCache()
    {
        ODE_BOL_Service::getInstance()->saveSetting('ode_datasets_list', $this->datasetsListBuilder());
        $this->redirect(OW::getRouter()->urlForRoute('ode-providers'));
    }

    /**** GET DATASETS LIST ****/

//    public function datasetTree()
//    {
//        header('content-type: application/json');
//        header("Access-Control-Allow-Origin: *");
//        echo $this->datasetTreeBuilder();
//        die();
//    }

    public function datasetsListBuilder()
    {
        $step = 100;
        $maxDatasetPerProvider = isset($_REQUEST['maxDataset']) ? $_REQUEST['maxDataset'] : 1;

        $providersDatasets = [];
        $providers = ODE_BOL_Service::getInstance()->getProviderList();

        foreach ($providers as $p) {

            $providerDatasetCounter = 0;
            $start = 0;

            // Build providers
            $providersDatasets[$p->id] = ['p_name' => $p->name, 'p_url' => $p->url, 'p_datasets' => []];

            // Try CKAN
            while($providerDatasetCounter < $maxDatasetPerProvider) {
                $ch = curl_init($p->url . "/api/3/action/package_search?start=" . $start . "&rows=" . $step);//1000 limit!
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                $res = curl_exec($ch);
                $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if (200 == $retcode)
                {
                    $data = json_decode($res, true);

                    if(count($data["result"]["results"]))
                    {
//                        $providersDatasets[$p->id]['p_datasets'] = array_merge($providersDatasets[$p->id]['p_datasets'], $this->getCkanDatasets($data, $p->id));

                        $a = $this->getCkanDatasets($data, $p->id);
                        $l_a = count($a);
                        for($j = 0; $j < $l_a; $j++) {
                            $providersDatasets[$p->id]['p_datasets'][] = $a[$j];
                        }

                        $start += $step;
                        $providerDatasetCounter += count($data["result"]["results"]);
                    }
                    else
                    {
                        break;
                    }
                }
                else
                {
                    break;
                }
            }

            // Try ODS
            $ch = curl_init($p->url . "/api/datasets/1.0/search/?rows=-1");
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $res = curl_exec($ch);
            $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (200 == $retcode) {
                $data = json_decode( $res, true );
//                $providersDatasets[$p->id]['p_datasets'] = array_merge($providersDatasets[$p->id]['p_datasets'], $this->getOpenDataSoftDatasets($data, $p->id));

                $a = $this->getOpenDataSoftDatasets($data, $p->id);
                $l_a = count($a);
                for($j = 0; $j < $l_a; $j++) {
                    $providersDatasets[$p->id]['p_datasets'][] = $a[$j];
                }
                continue;
            }
        }

        return json_encode($providersDatasets);
    }

//    private function getCkanDatasets($data, $provider_id) {
//        $treemapdata = array();
//        $datasets = $data['result']['results'];
//        $datasetsCnt = count( $datasets );
//        for ($i = 0; $i < $datasetsCnt; $i++) {
//            $ds = $datasets[$i];
//            $resourcesCnt = count($ds['resources']);
//            if($resourcesCnt > 1) {
//                $resources = array();
//                for ($j = 0; $j < $resourcesCnt; $j++)
//                    $resources[] = $ds['resources'][$j]['name'];
//                $treemapdata[] = array(
//                    'name' => $ds['name'],
//                    'id' => $ds['id'],
//                    'p' => 'CKAN_' . $provider_id,
//                    'resources' => $resources
//                );
//            }
//            else
//                $treemapdata[] = array(
//                    'name' => $ds['name'],
//                    'id' => $ds['id'],
//                    'p' => 'CKAN_' . $provider_id
//                );
//        }
//        return $treemapdata;
//    }

    private function getCkanDatasets($data, $provider_id) {
        $treemapdata = array();
        $datasets = $data['result']['results'];
        $datasetsCnt = count( $datasets );
        for ($i = 0; $i < $datasetsCnt; $i++) {
            $ds = $datasets[$i];
            $resourcesCnt = count($ds['resources']);
            $resources = array();
            for ($j = 0; $j < $resourcesCnt; $j++)
                if (strcasecmp($ds['resources'][$j]['format'], 'csv') == 0)
                    $resources[] = $ds['resources'][$j]['name'];

                if (count($resources) == 1)
                    $treemapdata[] = array(
                        'name' => $ds['name'],
                        'id' => $ds['id'],
                        'p' => 'CKAN_' . $provider_id
                    );
                else if(count($resources) > 1)
                    $treemapdata[] = array(
                        'name' => $ds['name'],
                        'id' => $ds['id'],
                        'p' => 'CKAN_' . $provider_id,
                        'resources' => $resources
                    );
        }
        return $treemapdata;
    }

    private function getOpenDataSoftDatasets($data, $provider_id) {
        $treemapdata = array();
        $datasets = $data['datasets'];
        $datasetsCnt = count( $datasets );
        for ($i = 0; $i < $datasetsCnt; $i++) {
            $ds = $datasets[$i];

            @$treemapdata[] = array(
                'name' => $this->sanitizeInput($ds['metas']['title']),
                'id' => $this->sanitizeInput($ds['datasetid']),
                'p' => 'ODS_' . $provider_id
            );
        }
        return $treemapdata;
    }

    protected function sanitizeInput($str)
    {
        return str_replace("'", "&#39;", !empty($str) ? $str : "");
    }

}