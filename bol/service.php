<?php

class ODE_BOL_Service
{
    const ENTITY_TYPE = 'datalet_entity';

    /**
     * Singleton instance.
     *
     * @var ODE_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return ODE_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function getAll()
    {
        return ODE_BOL_DataletDao::getInstance()->findAll();
    }

    public function getById($id)
    {
        $example = new OW_Example();
        $example->andFieldEqual('id', $id);
        $result = ODE_BOL_DataletDao::getInstance()->findObjectByExample($example);
        return $result;
    }

    public function getDataletByPostId($id, $plugin="")
    {
        $dbo = OW::getDbo();

        //TODO FIX TABLE PREFIX NAME
        $query = "SELECT *
                  FROM ow_ode_datalet JOIN ow_ode_datalet_post ON ow_ode_datalet.id = ow_ode_datalet_post.dataletId
                  WHERE ow_ode_datalet_post.postId = " . $id . " AND
                  ow_ode_datalet_post.plugin = '". $plugin ."';";

        return $dbo->queryForRow($query);
    }

    public function getDataletsById($id, $plugin)
    {
        $query = "SELECT * FROM ow_ode_datalet JOIN ow_ode_datalet_post ON ow_ode_datalet.id = dataletId ";

        switch($plugin)
        {
            case "newsfeed" :

                $commentEntityId = " SELECT id FROM ow_base_comment_entity WHERE entityId = ".$id." AND pluginKey = 'newsfeed' ";
                $commentsId = " SELECT id FROM ow_base_comment WHERE commentEntityId = (".$commentEntityId.") ";
                $query .= "WHERE postId = ".$id." AND plugin = 'newsfeed' OR postId IN (".$commentsId.") AND plugin = 'comment'";
                break;

            case "event" :

                $commentEntityId = " SELECT id FROM ow_base_comment_entity WHERE entityId = ".$id." AND pluginKey = 'event' ";
                $commentsId = " SELECT id FROM ow_base_comment WHERE commentEntityId = (".$commentEntityId.") ";
                $query .= "WHERE postId = ".$id." AND plugin = 'event' OR postId IN ($commentsId) AND plugin = 'comment'";
                break;

            case "comment" :

                $query .= "WHERE postId = ".$id." AND plugin = 'comment'";
                break;

            case "topic" :

                $forumsId = " SELECT id FROM ow_forum_post WHERE topicId = ".$id." ";
                $query .= "WHERE postId IN ($forumsId) AND plugin = 'forum'";
                break;

            case "forum" :

                $query .= "WHERE postId = ".$id." AND plugin = 'forum'";
                break;

        }

        //comment dataletId < newsfeed/event dataletId
        $query .= " ORDER BY dataletId DESC;";

        $dbo = OW::getDbo();

        return $dbo->queryForList($query);
    }


    public function addDatalet($datalet, $fields, $ownerId, $params, $postId, $plugin)
    {
        ODE_CLASS_Helper::sanitizeDataletInput($datalet, $dataset, $fields);

        $dt            = new ODE_BOL_Datalet();
        $dt->component = $datalet;
        $dt->fields    = $fields;
        $dt->ownerId   = $ownerId;
        $dt->params    = $params;
        $dt->status    = 'approved';
        $dt->privacy   = 'everybody';
        ODE_BOL_DataletDao::getInstance()->save($dt);

        $dtp            = new ODE_BOL_DataletPost();
        $dtp->postId    = $postId;
        $dtp->dataletId = $dt->id;
        $dtp->plugin    = $plugin;
        ODE_BOL_DataletPostDao::getInstance()->save($dtp);
    }

    public function deleteDataletsById($id, $plugin)
    {
        $datalets = $this->getDataletsById($id, $plugin);

        foreach($datalets as &$dt)
        {
            ODE_BOL_DataletDao::getInstance()->deleteById($dt['id']);

            $ex = new OW_Example();
            $ex->andFieldEqual('dataletId', $dt['id']);
            ODE_BOL_DataletPostDao::getInstance()->deleteByExample($ex);
        }
    }
    public function checkIfAdmin($id){
        /*$admins  =  $this->getAdminList();
        foreach($admins as $admin)
        {
            if($admin->userId == $id){
                return true;
            }
        }
        return false;*/
        return true;
    }
}
