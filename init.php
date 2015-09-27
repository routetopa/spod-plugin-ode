<?php

$preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_url');

if(empty($preference))
{
   $ode_deep_url = "http://service.routetopa.eu/DEEalerProvider/DEEP/";
}
else
{
    $ode_deep_url = $preference->defaultValue;
}

define("ODE_DEEP_URL", $ode_deep_url);

$preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_datalet_list');

if(empty($preference))
{
    $ode_deep_url = "http://service.routetopa.eu/DEEalerProvider/DEEP/datalets-list";
}
else
{
    $ode_deep_url = $preference->defaultValue;
}

define("ODE_DEEP_DATALET_LIST", $ode_deep_url);

$preference = BOL_PreferenceService::getInstance()->findPreference('ode_deep_client');

if(empty($preference))
{
    $ode_deep_url = "http://service.routetopa.eu/DEEalerProvider/DEEPCLIENT/js/deepClient.js";
}
else
{
    $ode_deep_url = $preference->defaultValue;
}

define("ODE_DEEP_CLIENT", $ode_deep_url);

$preference = BOL_PreferenceService::getInstance()->findPreference('ode_organization');

if(empty($preference))
{
    $ode_deep_url = 2;
}
else
{
    $ode_deep_url = $preference->defaultValue;
}

define("ODE_ORGANIZATION", $ode_deep_url);

OW::getRouter()->addRoute(new OW_Route('ode-settings', '/ode/settings', 'ODE_CTRL_Admin', 'settings'));

ODE_CLASS_EventHandler::getInstance()->init();
