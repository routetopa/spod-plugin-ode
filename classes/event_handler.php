<?php

class ODE_CLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if(self::$classInstance === null)
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    // Handle event and route
    public function init()
    {
        // Remove default topic-default route from Forum plugin and replace with a custom one
        OW::getRouter()->removeRoute('topic-default');
        OW::getRouter()->addRoute(new OW_Route('topic-default', 'forum/topic/:topicId', 'ODE_CTRL_Topic', 'index'));

        // Remove default add-topic route from Forum plugin and replace with a custom one
        OW::getRouter()->removeRoute('add-topic-default');
        OW::getRouter()->addRoute(new OW_Route('add-topic-default', 'forum/addTopic', 'ODE_CTRL_AddTopic', 'index'));

        // Remove default add-topic route from Forum plugin and replace with a custom one
        OW::getRouter()->removeRoute('add-topic');
        OW::getRouter()->addRoute(new OW_Route('add-topic', 'forum/addTopic/:groupId', 'ODE_CTRL_AddTopic', 'index'));

        // Remove default add-post route from Forum plugin and replace with a custom one
        OW::getRouter()->removeRoute('add-post');
        OW::getRouter()->addRoute(new OW_Route('add-post', 'forum/addPost/:topicId/:uid', 'ODE_CTRL_Topic', 'addPost'));

        // Remove default delete-post route from Forum plugin and replace with a custom one
        OW::getRouter()->removeRoute('delete-post');
        OW::getRouter()->addRoute(new OW_Route('delete-post', 'forum/deletePost/:topicId/:postId', 'ODE_CTRL_Topic', 'deletePost'));

        // Remove default delete-topic route from Forum plugin and replace with a custom one
        OW::getRouter()->removeRoute('delete-topic');
        OW::getRouter()->addRoute(new OW_Route('delete-topic', 'forum/deleteTopic/:topicId', 'ODE_CTRL_Topic', 'deleteTopic'));

        // Remove default event.add route from Event plugin and replace with a custom one
        OW::getRouter()->removeRoute('event.add');
        OW::getRouter()->addRoute(new OW_Route('event.add', 'event/add', 'ODE_CTRL_Event', 'add'));

        // Remove default event.view route from Event plugin and replace with a custom one
        OW::getRouter()->removeRoute('event.view');
        OW::getRouter()->addRoute(new OW_Route('event.view', 'event/:eventId', 'ODE_CTRL_Event', 'view'));

        // Remove default event.delete route from Event plugin and replace with a custom one
        OW::getRouter()->removeRoute('event.delete');
        OW::getRouter()->addRoute(new OW_Route('event.delete', 'event/delete/:eventId', 'ODE_CTRL_Event', 'delete'));

        // event triggered when receiving a request, just after the base system initialization
        OW::getEventManager()->bind(OW_EventManager::ON_APPLICATION_INIT, array($this, 'onApplicationInit'));

        // event that allows returning a component to replace the standard status update form
        OW::getEventManager()->bind('feed.get_status_update_cmp', array($this, 'onStatusUpdateCreate'));

        // event raised just before rendering a feed item (= an Action)
        OW::getEventManager()->bind('feed.on_item_render', array($this, 'onItemRender'));
        OW::getEventManager()->bind('feed.on_item_render', array($this, 'onLastReplyForumRender'));

        // event raised just before rendering a comment
        OW::getEventManager()->bind('base.comment_item_process', array($this, 'onCommentItemProcess'), 10000);

        // event raised just before delete a post
        OW::getEventManager()->bind('feed.before_action_delete', array($this, 'onBeforePostDelete'));

        // events raised when adding or deleting a comment
        OW::getEventManager()->bind('base_delete_comment', array($this, 'onDeleteComment'));

        // event raised on top right menu creation
        OW::getEventManager()->bind('console.collect_items', array($this, 'onCollectConsoleItems'));
    }

    // Add ODE Javascript, DEEP-CLIENT and set Javascript constant
    public function onApplicationInit(OW_Event $event)
    {
        // if request is Ajax, we don't need to re-execute the same code again!
        if (!OW::getRequest()->isAjax())
        {
            // TODO try to bind this js inclusion to an event
            // Load polyfill for browser not web-component ready
            // Load in ow_core -> application.php #528
            //OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('ode')->getStaticJsUrl() . '/webcomponentsjs-0.7.12/webcomponents.js', 'text/javascript', (-101));

            //Add ODE.JS script to all the Oxwall pages and set THEME_IMAGES_URL variable with theme image url
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('ode')->getStaticJsUrl() . 'ode.js', 'text/javascript');
            OW::getDocument()->addOnloadScript('ODE.THEME_IMAGES_URL = "' . OW::getThemeManager()->getThemeImagesUrl() . '";');

            //Add deepClient.js to all the Oxwall pages
            OW::getDocument()->addScript(ODE_DEEP_CLIENT, 'text/javascript');

            //Init JS CONSTANTS
            $js = UTIL_JsGenerator::composeJsString('
                ODE.deep_url = {$ode_deep_url}
                ODE.deep_datalet_list = {$ode_deep_datalet_list}
                ODE.ajax_load_item = {$ajax_load_item}
                ODE.ajax_add_comment = {$ajax_add_comment}
                ODE.ajax_private_room_datalet = {$ajax_private_room_datalet}
                ODE.ode_dataset_list = {$ode_dataset_list}
                ODE.ode_deep_client = {$ode_deep_client}
                ODE.ode_webcomponents_js = {$ode_webcomponents_js}
                ODE.is_private_room_active = {$is_private_room_active}
                ODE.user_language = {$user_language}
            ', array(
                'ode_deep_url' => ODE_DEEP_URL,
                'ajax_load_item' => OW::getRouter()->urlFor('ODE_CTRL_Ajax', 'loadItem'),
                'ajax_add_comment' => OW::getRouter()->urlFor('ODE_CTRL_Ajax', 'addComment'),
                'ajax_private_room_datalet' => OW::getRouter()->urlFor('ODE_CTRL_Ajax', 'privateRoomDatalet'),
                'ode_deep_datalet_list' => ODE_DEEP_DATALET_LIST,
                'ode_dataset_list' => ODE_DATASET_LIST,
                'ode_deep_client' => ODE_DEEP_CLIENT,
                'ode_webcomponents_js' => ODE_WEBCOMPONENTS_JS,
                'is_private_room_active' => OW::getPluginManager()->isPluginActive('spodpr'),
                'user_language' => BOL_LanguageService::getInstance()->getCurrent()->tag
            ));

            OW::getDocument()->addOnloadScript($js);
            OW::getDocument()->addOnloadScript('ODE.init();');
        }

        if (OW::getApplication()->isMobile())
        {
            // TODO MOBILE PAGE REQUEST
        }
        else
        {
            // TODO DESKTOP PAGE REQUEST
        }
    }

    // Replace the newsfeed form
    public function onStatusUpdateCreate(OW_Event $event)
    {
        $params = $event->getParams();

        if (OW::getApplication()->isMobile())
        {
            //TODO MOBILE PAGE REQUEST
        }
        else
        {
            $ret = new ODE_CMP_UpdateStatus($params['feedAutoId'], $params['entityType'], $params['entityId'], $params['visibility']);
        }

        $event->setData($ret);
        return $ret;
    }

    // Render last reply on forum
    public function onLastReplyForumRender(OW_Event $event)
    {
        //Get parameter for check pluginKey for this event
        $params = $event->getParams();
        $data = $event->getData();

        if ($params["action"]["pluginKey"] == "forum" && isset($data['content']['vars']['activity']['id']))
        {

            $id = $data['content']['vars']['activity']['id'];
            $datalet = ODE_BOL_Service::getInstance()->getDataletByPostId($id, $params["action"]["pluginKey"]);

            if (!empty($datalet))
            {
                $data['content']['vars']['activity']['description'] .= '<div id="datalet_placeholder_' . $id . '_'.$params["action"]["pluginKey"].'"></div>';

                //CACHE
/*                OW::getDocument()->addOnloadScript('ODE.loadDatalet("'.$datalet["component"].'",
                                                                    '.$datalet["params"].',
                                                                    ['.$datalet["fields"].'],
                                                                    \''.$datalet["data"].'\',
                                                                    "datalet_placeholder_' . $id . '_'.$params["action"]["pluginKey"].'");');*/
                // NO CACHE
                OW::getDocument()->addOnloadScript('ODE.loadDatalet("'.$datalet["component"].'",
                                                                    '.$datalet["params"].',
                                                                    ['.$datalet["fields"].'],
                                                                    undefined,
                                                                    "datalet_placeholder_' . $id . '_'.$params["action"]["pluginKey"].'");');

                $event->setData($data);
            }
        }
    }

    // Render post and topic
    public function onItemRender(OW_Event $event)
    {
        //Get parameter for check pluginKey for this event
        $params = $event->getParams();

        if($params["action"]["pluginKey"] == "newsfeed" ||
            $params["action"]["pluginKey"] == "forum") {

            //if the entity is a post then id = $params['action']['entityId'] otherwise is a topic then get i get the first post of the topic
            $id = $params["action"]["pluginKey"] == "newsfeed" ? $params['action']['entityId'] : FORUM_BOL_ForumService::getInstance()->findTopicFirstPost($params['action']['entityId'])->id;

            $datalet = ODE_BOL_Service::getInstance()->getDataletByPostId($id, $params["action"]["pluginKey"]);

            $data = $event->getData();

            if(!empty($datalet)) {

                switch($params["action"]["pluginKey"])
                {
                    case "newsfeed" : $content =  &$data['content']['vars']['status']; break;
                    case "forum"    : $content =  &$data['content']['vars']['description']; break;
                }

                $content .= '<div id="datalet_placeholder_' . $id . '_'.$params["action"]["pluginKey"].'"></div>';

                 // CACHE
/*                OW::getDocument()->addOnloadScript('ODE.loadDatalet("'.$datalet["component"].'",
                                                                    '.$datalet["params"].',
                                                                    ['.$datalet["fields"].'],
                                                                    \''.$datalet["data"].'\',
                                                                    "datalet_placeholder_' . $id . '_'.$params["action"]["pluginKey"].'");');*/

                // NO CACHE
                OW::getDocument()->addOnloadScript('ODE.loadDatalet("'.$datalet["component"].'",
                                                                    '.$datalet["params"].',
                                                                    ['.$datalet["fields"].'],
                                                                    undefined,
                                                                    "datalet_placeholder_' . $id . '_'.$params["action"]["pluginKey"].'");');

            }

            $event->setData($data);
        }
    }

    // Render comment
    public function onCommentItemProcess(BASE_CLASS_EventProcessCommentItem $event)
    {
        $comment = $event->getItem();
        $id = $comment->getId();

        //$datalet = ODE_BOL_Service::getInstance()->getDataletByPostId($id, "comment");
        $datalet = ODE_BOL_Service::getInstance()->getDataletByPostIdWhereArray($id, array("comment", "public-room"));

        if(!empty($datalet))
        {
            $content = $event->getDataProp('content');
            $content .= '<div id="datalet_placeholder_' . $id . '_comment"></div>';

            // CACHE
/*            OW::getDocument()->addOnloadScript('ODE.loadDatalet("'.$datalet["component"].'",
                                                                    '.$datalet["params"].',
                                                                    ['.$datalet["fields"].'],
                                                                    \''.$datalet["data"].'\',
                                                                    "datalet_placeholder_' . $id . '_comment");');*/
            // NO CACHE
            OW::getDocument()->addOnloadScript('ODE.loadDatalet("'.$datalet["component"].'",
                                                                    '.$datalet["params"].',
                                                                    ['.$datalet["fields"].'],
                                                                    undefined,
                                                                    "datalet_placeholder_' . $id . '_comment");');

            $event->setDataProp('content', $content);
        }

    }

    // Handle post delete
    public function onBeforePostDelete(OW_Event $event)
    {
        //Get parameter for check pluginKey for this event
        $params = $event->getParams();
        ODE_BOL_Service::getInstance()->deleteDataletsById($params['entityId'], 'newsfeed');
    }

    // Handle comment deletion
    public function onDeleteComment(OW_Event $event)
    {
        //Get parameter for check pluginKey for this event
        $params = $event->getParams();
        ODE_BOL_Service::getInstance()->deleteDataletsById($params['commentId'], 'comment');
    }

    // Handle top right menu creation
    public function onCollectConsoleItems( BASE_CLASS_ConsoleItemCollector $event )
    {
        if (OW::getUser()->isAuthenticated())
        {
            $item = new ODE_CMP_Help();
            $event->addItem($item, 0);
        }
    }

}