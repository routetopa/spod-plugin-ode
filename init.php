<?php

$preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_url');
$ode_deep_url = empty($preference) ? "http://deep.routetopa.eu/DEEP/" : $preference->defaultValue;
define("ODE_DEEP_URL", $ode_deep_url);

$preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_datalet_list');
$ode_deep_datalet_list = empty($preference) ? "http://deep.routetopa.eu/DEEP/datalets-list" :  $preference->defaultValue;
define("ODE_DEEP_DATALET_LIST", $ode_deep_datalet_list);

$preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_client');
$ode_deep_client = empty($preference) ? "http://deep.routetopa.eu/DEEPCLIENT/js/deepClient.js" : $preference->defaultValue;
define("ODE_DEEP_CLIENT", $ode_deep_client);

$preference =  ODE_BOL_Service::getInstance()->getSettingByKey('ode_dataset_list');
$ode_dataset_list = $preference->value;
define("ODE_DATASET_LIST", $ode_dataset_list);

$preference = BOL_PreferenceService::getInstance()->findPreference('ode_webcomponents_js');
$ode_webcomponents_js = empty($preference) ? "" : $preference->defaultValue;
define("ODE_WEBCOMPONENTS_JS", $ode_webcomponents_js);

OW::getRouter()->addRoute(new OW_Route('ode-settings', '/ode/settings', 'ODE_CTRL_Admin', 'settings'));

ODE_CLASS_EventHandler::getInstance()->init();
