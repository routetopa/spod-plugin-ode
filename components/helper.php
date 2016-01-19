<?php
class ODE_CMP_Helper extends OW_Component
{
    public function __construct()
    {
        /*OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('ode')->getStaticCssUrl() . 'perfect-scrollbar.min.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('ode')->getStaticJsUrl() . 'perfect-scrollbar.jquery.js');*/

        $this->assign("staticResourcesUrl", OW::getPluginManager()->getPlugin('spodpublic')->getStaticUrl());
        $this->assign('components_url', SPODPR_COMPONENTS_URL);
    }
}