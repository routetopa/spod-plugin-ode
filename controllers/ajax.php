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
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package ow_plugins.newsfeed.controllers
 * @since 1.0
 */
class ODE_CTRL_Ajax extends NEWSFEED_CTRL_Ajax
{

    public function statusUpdate()
    {
        if ( empty($_POST['status']) && empty($_POST['attachment']) )
        {
            echo json_encode(array(
                "error" => OW::getLanguage()->text('base', 'form_validate_common_error_message')
            ));
            exit;
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            echo json_encode(false);
            exit;
        }

        $oembed = null;
        $attachId = null;
        $status = empty($_POST['status']) ? '' : strip_tags($_POST['status']);
        $content = array();

        if ( !empty($_POST['attachment']) )
        {
            $content = json_decode($_POST['attachment'], true);

            if ( !empty($content) )
            {
                if( $content['type'] == 'photo' && !empty($content['uid']) )
                {
                    $attachmentData = OW::getEventManager()->call('base.attachment_save_image', array(
                        "pluginKey" => "newsfeed",
                        'uid' => $content['uid']
                    ));

                    $content['url'] = $content['href'] = $attachmentData["url"];
                    $attachId = $content['uid'];
                }

                if( $content['type'] == 'video' )
                {
                    $content['html'] = BOL_TextFormatService::getInstance()->validateVideoCode($content['html']);
                }
            }
        }

        $userId = OW::getUser()->getId();

        $event = new OW_Event("feed.before_content_add", array(
            "feedType" => $_POST['feedType'],
            "feedId" => $_POST['feedId'],
            "visibility" => $_POST['visibility'],
            "userId" => $userId,
            "status" => $status,
            "type" => empty($content["type"]) ? "text" : $content["type"],
            "data" => $content
        ));

        OW::getEventManager()->trigger($event);

        $data = $event->getData();

        if ( !empty($data) )
        {
            if ( !empty($attachId) )
            {
                BOL_AttachmentService::getInstance()->deleteAttachmentByBundle("newsfeed", $attachId);
            }

            $item = empty($data["entityType"]) || empty($data["entityId"])
                ? null
                : array(
                    "entityType" => $data["entityType"],
                    "entityId" => $data["entityId"]
                );

            echo json_encode(array(
                "item" => $item,
                "message" => empty($data["message"]) ? null : $data["message"],
                "error" => empty($data["error"]) ? null : $data["error"]
            ));
            exit;
        }

        $status = UTIL_HtmlTag::autoLink($status);
        $out = NEWSFEED_BOL_Service::getInstance()
            ->addStatus(OW::getUser()->getId(), $_POST['feedType'], $_POST['feedId'], $_POST['visibility'], $status, array(
                "content" => $content,
                "attachmentId" => $attachId
            ));

        /* ODE */
        if( ODE_CLASS_Helper::validateDatalet($_REQUEST['ode_datalet'], $_REQUEST['ode_params'], $_REQUEST['ode_fields']) )
        {
            ODE_BOL_Service::getInstance()->addDatalet($_REQUEST['ode_datalet'], $_REQUEST['ode_fields'],
                OW::getUser()->getId(), $_REQUEST['ode_params'], $out['entityId'], 'newsfeed');
        }
        /* ODE */

        echo json_encode(array(
            "item" => $out
        ));
        exit;
    }

    public function addComment()
    {
        $errorMessage = false;
        $isMobile = !empty($_POST['isMobile']) && (bool) $_POST['isMobile'];
        $params = $this->getParamsObject();

        if ( empty($_POST['commentText']) && empty($_POST['attachmentInfo']) && empty($_POST['oembedInfo']) )
        {
            $errorMessage = OW::getLanguage()->text('base', 'comment_required_validator_message');
        }
        else if ( !OW::getUser()->isAuthorized($params->getPluginKey(), 'add_comment') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus($params->getPluginKey(), 'add_comment');
            $errorMessage = $status['msg'];
        }
        else if ( BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $params->getOwnerId()) )
        {
            $errorMessage = OW::getLanguage()->text('base', 'user_block_message');
        }

        if ( $errorMessage )
        {
            exit(json_encode(array('error' => $errorMessage)));
        }

        $commentText = empty($_POST['commentText']) ? '' : trim($_POST['commentText']);
        $attachment = null;

        if ( BOL_TextFormatService::getInstance()->isCommentsRichMediaAllowed() && !$isMobile )
        {
            if ( !empty($_POST['attachmentInfo']) )
            {
                $tempArr = json_decode($_POST['attachmentInfo'], true);
                OW::getEventManager()->call('base.attachment_save_image', array('uid' => $tempArr['uid'], 'pluginKey' => $tempArr['pluginKey']));
                $tempArr['href'] = $tempArr['url'];
                $tempArr['type'] = 'photo';
                $attachment = json_encode($tempArr);
            }
            else if ( !empty($_POST['oembedInfo']) )
            {
                $tempArr = json_decode($_POST['oembedInfo'], true);
                // add some actions
                $attachment = json_encode($tempArr);
            }
        }

        $comment = BOL_CommentService::getInstance()->addComment($params->getEntityType(), $params->getEntityId(), $params->getPluginKey(), OW::getUser()->getId(), $commentText, $attachment);

        /* ODE */
        if( ODE_CLASS_Helper::validateDatalet($_REQUEST['datalet']['component'], $_REQUEST['datalet']['params'], $_REQUEST['datalet']['fields']) )
        {
            ODE_BOL_Service::getInstance()->addDatalet(
                $_REQUEST['datalet']['component'],
                $_REQUEST['datalet']['fields'],
                OW::getUser()->getId(),
                $_REQUEST['datalet']['params'],
                $comment->getId(),
                'comment');
        }
        /* ODE */

        // trigger event comment add
        $event = new OW_Event('base_add_comment', array(
            'entityType' => $params->getEntityType(),
            'entityId' => $params->getEntityId(),
            'userId' => OW::getUser()->getId(),
            'commentId' => $comment->getId(),
            'pluginKey' => $params->getPluginKey(),
            'attachment' => json_decode($attachment, true)
        ));

        OW::getEventManager()->trigger($event);

        BOL_AuthorizationService::getInstance()->trackAction($params->getPluginKey(), 'add_comment');

        if ( $isMobile )
        {
            $commentListCmp = new BASE_MCMP_CommentsList($params, $_POST['cid']);
        }
        else
        {
            $commentListCmp = new BASE_CMP_CommentsList($params, $_POST['cid']);
        }

        exit(json_encode(array(
                'newAttachUid' => BOL_CommentService::getInstance()->generateAttachmentUid($params->getEntityType(), $params->getEntityId()),
                'entityType' => $params->getEntityType(),
                'entityId' => $params->getEntityId(),
                'commentList' => $commentListCmp->render(),
                'onloadScript' => OW::getDocument()->getOnloadScript(),
                'commentCount' => BOL_CommentService::getInstance()->findCommentCount($params->getEntityType(), $params->getEntityId())
            )
        )
        );
    }

    private function getParamsObject()
    {
        $errorMessage = false;

        $entityType = !isset($_POST['entityType']) ? null : trim($_POST['entityType']);
        $entityId = !isset($_POST['entityId']) ? null : (int) $_POST['entityId'];
        $pluginKey = !isset($_POST['pluginKey']) ? null : trim($_POST['pluginKey']);

        if ( !$entityType || !$entityId || !$pluginKey )
        {
            $errorMessage = OW::getLanguage()->text('base', 'comment_ajax_error');
        }

        $params = new BASE_CommentsParams($pluginKey, $entityType);
        $params->setEntityId($entityId);

        if ( isset($_POST['ownerId']) )
        {
            $params->setOwnerId((int) $_POST['ownerId']);
        }

        if ( isset($_POST['commentCountOnPage']) )
        {
            $params->setCommentCountOnPage((int) $_POST['commentCountOnPage']);
        }

        if ( isset($_POST['displayType']) )
        {
            $params->setDisplayType($_POST['displayType']);
        }

        if ( isset($_POST['initialCount']) )
        {
            $params->setInitialCommentsCount((int) $_POST['initialCount']);
        }

        if ( isset($_POST['loadMoreCount']) )
        {
            $params->setLoadMoreCount((int) $_POST['loadMoreCount']);
        }

        if ( $errorMessage )
        {
            echo json_encode(array(
                'error' => $errorMessage
            ));

            exit();
        }

        return $params;
    }

}