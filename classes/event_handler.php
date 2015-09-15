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
        // event triggered when receiving a request, just after the base system initialization
        OW::getEventManager()->bind(OW_EventManager::ON_APPLICATION_INIT, array($this, 'onApplicationInit'));

        // event that allows returning a component to replace the standard status update form
        OW::getEventManager()->bind('feed.get_status_update_cmp', array($this, 'onStatusUpdateCreate'));

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

        // event raised just before rendering a feed item (= an Action)
        OW::getEventManager()->bind('feed.on_item_render', array($this, 'onItemRender'));
        OW::getEventManager()->bind('feed.on_item_render', array($this, 'onLastReplyForumRender'));

        // event raised just before rendering a comment
        OW::getEventManager()->bind('base.comment_item_process', array($this, 'onCommentItemProcess'), 10000);

        // event raised just before delete a post
        OW::getEventManager()->bind('feed.before_action_delete', array($this, 'onBeforePostDelete'));

        // events raised when adding or deleting a comment
        OW::getEventManager()->bind('base_add_comment', array($this, 'onAddComment'));
        OW::getEventManager()->bind('base_delete_comment', array($this, 'onDeleteComment'));
    }

    public function onApplicationInit(OW_Event $event)
    {
        // if request is Ajax, we don't need to re-execute the same code again!
        if (!OW::getRequest()->isAjax())
        {
            //Load polyfill for browser not web-component ready
            //OW::getDocument()->addScript('https://cdnjs.cloudflare.com/ajax/libs/webcomponentsjs/0.7.12/webcomponents.min.js', 'text/javascript');

            //Add ODE.JS script to all the Oxwall pages and set THEME_IMAGES_URL variable with theme image url
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('ode')->getStaticJsUrl() . 'ode.js', 'text/javascript');
            OW::getDocument()->addOnloadScript('ODE.THEME_IMAGES_URL = "' . OW::getThemeManager()->getThemeImagesUrl() . '";');

            //Add deepClient.js to all the Oxwall pages
            //TODO set reference to deepClient.js to a GIT version
            /*OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('ode')->getStaticJsUrl() . 'deepClient.js', 'text/javascript');*/
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('ode')->getStaticJsUrl() . 'JQueryDeepClient.js', 'text/javascript');

            //Init JS CONSTANTS
            $js = UTIL_JsGenerator::composeJsString('
                ComponentService.deep_url = {$ode_deep_url}
                ComponentService.deep_datalet_list = {$ode_deep_datalet_list}
                ODE.ajax_load_item = {$ajax_load_item}
                ODE.ajax_add_comment = {$ajax_add_comment}
            ', array(
                'ode_deep_url' => ODE_DEEP_URL,
                'ajax_load_item' => OW::getRouter()->urlFor('ODE_CTRL_Ajax', 'loadItem'),
                'ajax_add_comment' => OW::getRouter()->urlFor('ODE_CTRL_Ajax', 'addComment'),
                'ode_deep_datalet_list' => ODE_DEEP_DATALET_LIST
            ));

            OW::getDocument()->addOnloadScript($js);
            OW::getDocument()->addOnloadScript('ODE.init();');
        }

        if (OW::getApplication()->isMobile())
        {
            //TODO MOBILE PAGE REQUEST
        }
        else
        {
            //TODO DESKTOP PAGE REQUEST
        }
    }

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
            $datalet = ODE_BOL_Service::getInstance()->getDataletByPostID($id, $params["action"]["pluginKey"]);

            if (!empty($datalet))
            {
                $data['content']['vars']['activity']['description'] .= '<div id="datalet_placeholder_' . $id . '_'.$params["action"]["pluginKey"].'"></div>';

                //TODO remove fields-order
                //TODO check if data or dataset
                OW::getDocument()->addOnloadScript('ComponentService.getComponent({
	                                             component   : "' . $datalet["component"] . '",
                                                 params      :{
                                                    \'data-url\' : \'' . $datalet["dataset"] . '\',
                                                    \'fields-order\' : \'' . $datalet["forder"] . '\'
                                                 },
		                                         fields      :  Array(' . $datalet["query"] . '),
		                                         placeHolder : "datalet_placeholder_' . $id . '_'.$params["action"]["pluginKey"].'"
	                                            });');

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

            $datalet = ODE_BOL_Service::getInstance()->getDataletByPostID($id, $params["action"]["pluginKey"]);

            $data = $event->getData();

            //echo $params["action"]["pluginKey"];
            //echo var_dump($data['content']);
            //echo $id;

            if (!empty($datalet)) {

                switch($params["action"]["pluginKey"])
                {
                    case "newsfeed" : $content =  &$data['content']['vars']['status']; break;
                    case "forum"    : $content =  &$data['content']['vars']['description']; break;
                }

                $content .= '<div id="datalet_placeholder_' . $id . '_'.$params["action"]["pluginKey"].'"></div>';

                //TODO remove fields-order
                //TODO check if data or dataset
                OW::getDocument()->addOnloadScript('ComponentService.getComponent({
	                                             component   : "' . $datalet["component"] . '",
                                                 params      :{
                                                    \'data-url\' : \'' . $datalet["dataset"] . '\',
                                                    \'fields-order\' : \'' . $datalet["forder"] . '\'
                                                 },
		                                         fields      :  Array(' . $datalet["query"] . '),
		                                         placeHolder : "datalet_placeholder_' . $id . '_'.$params["action"]["pluginKey"].'"
	                                            });');
            }

            $event->setData($data);
        }
    }

    // Render comment
    public function onCommentItemProcess(BASE_CLASS_EventProcessCommentItem $event)
    {
        $comment = $event->getItem();
        $id = $comment->getId();

        $datalet = ODE_BOL_Service::getInstance()->getDataletByPostID($id, "comment");

        if(!empty($datalet))
        {
            $content = $event->getDataProp('content');
            $content .= '<div id="datalet_placeholder_' . $id . '_comment"></div>';

            //TODO remove fields-order
            //TODO check if data or dataset
            OW::getDocument()->addOnloadScript('ComponentService.getComponent({
	                                             component   : "' . $datalet["component"] . '",
                                                 params      :{
                                                    \'data-url\' : \'' . $datalet["dataset"] . '\',
                                                    \'fields-order\' : \'' . $datalet["forder"] . '\'
                                                 },
		                                         fields      :  Array(' . $datalet["query"] . '),
		                                         placeHolder : "datalet_placeholder_' . $id . '_comment"
	                                            });');

            $event->setDataProp('content', $content);
        }

    }

    // Handle post delete
    public function onBeforePostDelete(OW_Event $event)
    {
        //Get parameter for check pluginKey for this event
        $params = $event->getParams();
        ODE_BOL_Service::getInstance()->deleteDataletByPostId($params['entityId'], 'newsfeed');

        /*$commentEntity = BOL_CommentService::getInstance()->findCommentEntity($params['entityType'], $params['entityId']);
        ODE_BOL_Service::getInstance()->deleteDataletByPostId($commentEntity->id, 'comment');*/

    }

    // TODO REMOVE this method
    public function onAddComment(OW_Event $event)
    {
        $params = $event->getParams();

        /* ODE */
        //ODE_BOL_Service::getInstance()->addDatalet('comment', 'comment',
          //  'comment', OW::getUser()->getId(), '0,1', $params['commentId'], 'comment');
        /* ODE */
    }

    public function onDeleteComment(OW_Event $event)
    {
        //Get parameter for check pluginKey for this event
        $params = $event->getParams();
        ODE_BOL_Service::getInstance()->deleteDataletByPostId($params['commentId'], 'comment');
    }

}