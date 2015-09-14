<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2011, Oxwall Foundation
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * Update Status Component
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_plugins.newsfeed.components
 * @since 1.0
 */

class ODE_CMP_UpdateStatus extends NEWSFEED_CMP_UpdateStatus
{
    /**
     *
     * @param int $feedAutoId
     * @param string $feedType
     * @param int $feedId
     * @param int $actionVisibility
     * @return Form
     */
    public function createForm( $feedAutoId, $feedType, $feedId, $actionVisibility )
    {
        $form = parent::createForm($feedAutoId, $feedType, $feedId, $actionVisibility);

        $odeButton = new Button('ode_open_dialog');
        $odeButton->setValue(OW::getLanguage()->text('ode', 'add_od'));
        $form->addElement($odeButton);

        $field = new HiddenField('ode_datalet');
        //$field->setValue('linechart-datalet');
        $form->addElement($field);

        $field = new HiddenField('ode_dataset');
        //$field->setValue('http://dati.lazio.it/catalog/api/action/datastore_search?resource_id=722b6cbd-28d3-4151-ac50-9c4261298168&limit=1000');
        $form->addElement($field);

        $field = new HiddenField('ode_query');
        //$field->setValue("'result,records,Capitolo','result,records,Previsione Competenza'");
        $form->addElement($field);

        $field = new HiddenField('ode_forder');
        //$field->setValue("0,1");
        $form->addElement($field);

        $script = "$('#{$odeButton->getId()}').click(function(e){
            //$('#ode_controllet_placeholder').slideToggle('fast');
            previewFloatBox = OW.ajaxFloatBox('ODE_CMP_Preview', {text:'testo'} , {width:'90%', height:'500px', iconClass: 'ow_ic_add', title: 'ODE'});
        });";

        OW::getDocument()->addOnloadScript($script);

        $form->setAction( OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('ODE_CTRL_Ajax', 'statusUpdate')) );

        return $form;
    }

}
