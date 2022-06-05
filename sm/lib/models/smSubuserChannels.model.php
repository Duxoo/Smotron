<?php

class smSubuserChannelsModel extends waModel
{
    protected $table = 'sm_subuser_channels';

    public function getSubuserChannels($sub_user_id = 0)
    {
        return $this->getByField('subuser_id',$sub_user_id, true);
    }

    public function getChannels($sub_user_id = null)
    {
        $model = new smChannelModel();
        if(!isset($sub_user_id)){return $model->getAll();}
        return $this->query('SELECT * FROM '.$this->table.' AS ssc 
                  INNER JOIN '.$model->getTableName().' AS sc 
                  ON sc.id = ssc.channel_id 
                  WHERE ssc.subuser_id = i:subuser_id 
                  AND sc.disabled = 0', array('subuser_id' => $sub_user_id))->fetchAll();
    }
}