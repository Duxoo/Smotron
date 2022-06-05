<?php

class smChannel
{
    protected $id = null;
    protected $data = null;
    protected $model = null;

    public function __construct($id)
    {
        $this->model = new smChannelModel();
        $default_data = array(
            //'img' => '',
            'fl_channel_name' => '',
            'name' => '',
        );
        $this->data = $this->model->getById($id);
        $this->id = $id;
        /*if($this->data !== null)
        {
            if($this->data['hidden'] == 1) {$this->data = $default_data;}
            else {$this->id = $id; unset($this->data['id']);}
        }
        else {$this->data = $default_data;}*/
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

    /**
     * @param null $image = waFile;
     */
    public function save($image = null)
    {
        if(isset($this->data['id'])) {unset($this->data['id']);}
        if($this->id) {$this->model->updateById($this->id, $this->data);}
        else {$this->id = $this->model->insert($this->data);}
        if ($image)
        {
            try {
                if($image->uploaded()) {
                    $image_path = wa()->getDataPath("channels/channel_{$this->id}/{$this->id}.jpg", true, 'sm');
                    $image->waImage()->save($image_path);
                    $this->data['image'] = wa()->getDataUrl("channels/channel_{$this->id}/{$this->id}.jpg", true, 'sm');
                    $this->data['image_path'] = $image_path;
                    $this->model->updateById($this->id, $this->data);
                }
            } catch (waException $wa) {
                waLog::dump($wa,'post/error/RegionSaveImageError.log');
            }
        }
    }
    public function getByField($field = null, $value = null)
    {
        if($field === null){return null;}
        else{return $this->model->getByField($field, $value, true);}
    }
}