<?php

class smSubuser
{
    protected $id = null;
    protected $data = null;
    protected $model = null;

    public function __construct($id = null)
    {
        $this->model = new smSubuserModel();
        $default_data = array(
            'parent_contact_id' => '',
            'name' => '',
            'login' => '',
            'password' => '',
            'channels' => null,
        );
        if($id)
        {
            $this->data = $this->model->getById($id);
            if($this->data !== null)
            {
                if($this->data['dissable'] == 1) {$this->data = $default_data;}
                else
                {
                    $this->id = $id;
                    unset($this->data['id']);
                    $this->data['channels'] = $this->getChannels();
                    $this->data['token'] = $this->getToken();
                }
            }
            else {$this->data = $default_data;}
        }
        else {$this->data = $default_data;}
    }

    public function getId()
    {
        return $this->id;
    }

    public function get($field = null, $default = null)
    {
        if($field === null) {return $this->data;}
        return ifempty($this->data[$field], $default);
    }

    public function set($field, $value)
    {
        $this->data[$field] = $value;
    }

    public function setAll($data)
    {
        $this->data = $data;
    }

    public function save()
    {
        if(isset($this->data['id'])) {unset($this->data['id']);}
        if($this->id) {
            $this->model->updateById($this->id, $this->data);
        }
        else {$this->id = $this->model->insert($this->data);}
    }

    public function getByField($field = null, $value = null)
    {
        if($field === null){return null;}
        else{return $this->model->getByField($field, $value, true);}
    }

    public function getChannels()
    {
        $sub_user_channel_model = new smSubuserChannelsModel();

/*      $subuser_channels = $sub_user_channel_model->getChannels($this->id);
        $user = new smUser($this->data['parent_contact_id']);
        $user_data = $user->getData();
        $tariff_id = $user_data['tariff_id'];
        $tariff = new smTariff($tariff_id);
        $tariff_channels = $tariff->getChannels();

        $result = array();

        foreach($tariff_channels as $i => $t_channel)
        {
            foreach ($subuser_channels as $j => $s_channel)
            {
                if($t_channel['id'] == $s_channel['channel_id'])
                {
                    $result[$i] = $t_channel;
                }
            }
        }*/

        /*return $sub_user_channel_model->getChannels($this->id);*/
        return $sub_user_channel_model->getChannels($this->id);
    }

    public function getToken()
    {
        $rel_model = new smFluserSmuserModel();
        $user_data = $rel_model->getById($this->data['parent_contact_id']);
        return $user_data['fl_token'];
    }
}