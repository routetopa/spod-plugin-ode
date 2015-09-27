<?php

class ODE_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function settings($params)
    {
        $this->setPageTitle(OW::getLanguage()->text('ode', 'settings_title'));
        $this->setPageHeading(OW::getLanguage()->text('ode', 'settings_heading'));

        $form = new Form('settings');
        $this->addForm($form);

        $deepUrl = new TextField('deep_url');
        $deepUrl->setInvitation(OW::getLanguage()->text('ode', 'deep_url_invitation'));
        $deepUrl->setHasInvitation(true);
        $deepUrl->setRequired();
        $form->addElement($deepUrl);

        $deepDataletList = new TextField('deep_datalet_list');
        $deepDataletList->setInvitation(OW::getLanguage()->text('ode', 'deep_datalet_list_invitation'));
        $deepDataletList->setHasInvitation(true);
        $deepDataletList->setRequired();
        $form->addElement($deepDataletList);

        $deepClient = new TextField('deep_client');
        $deepClient->setInvitation(OW::getLanguage()->text('ode', 'deep_client_invitation'));
        $deepClient->setHasInvitation(true);
        $deepClient->setRequired();
        $form->addElement($deepClient);

        $organization = new TextField('organization');
        $organization->setInvitation(OW::getLanguage()->text('ode', 'organization_invitation'));
        $organization->setHasInvitation(true);
        $organization->setRequired();
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


            $preference = BOL_PreferenceService::getInstance()->findPreference('ode_organization');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'ode_organization';
            $preference->sectionName = 'general';
            $preference->defaultValue = $data['organization'];
            $preference->sortOrder = 4;
            BOL_PreferenceService::getInstance()->savePreference($preference);
        }
    }
}