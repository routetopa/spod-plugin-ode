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

    public function getDataletByPostID($id, $plugin="")
    {
        $dbo = OW::getDbo();

        //TODO FIX TABLE PREFIX NAME
        $query = "SELECT *
                  FROM ow_ode_datalet JOIN ow_ode_datalet_post ON ow_ode_datalet.id = ow_ode_datalet_post.dataletId
                  WHERE ow_ode_datalet_post.postId = " . $id . " AND
                  ow_ode_datalet_post.plugin = '". $plugin ."';";

        return $dbo->queryForRow($query);
    }

    public function addDatalet($datalet, $dataset, $query, $ownerId, $forder, $postId, $plugin)
    {
        $dt            = new ODE_BOL_Datalet();
        $dt->dataset   = $dataset;
        $dt->component = $datalet;
        $dt->query     = $query;
        $dt->ownerId   = $ownerId;
        $dt->forder    = $forder;
        $dt->status    = 'approved';
        $dt->privacy   = 'everybody';
        ODE_BOL_DataletDao::getInstance()->save($dt);

        $dtp            = new ODE_BOL_DataletPost();
        $dtp->postId    = $postId;
        $dtp->dataletId = $dt->id;
        $dtp->plugin    = $plugin;
        ODE_BOL_DataletPostDao::getInstance()->save($dtp);
    }

    public function deleteDataletByPostId($postId, $plugin)
    {
        $dt = $this->getDataletByPostID($postId, $plugin);

        ODE_BOL_DataletDao::getInstance()->deleteById($dt['id']);

        $ex = new OW_Example();
        $ex->andFieldEqual('postId', $postId);
        ODE_BOL_DataletPostDao::getInstance()->deleteByExample($ex);
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
