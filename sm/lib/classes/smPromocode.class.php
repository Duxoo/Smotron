<?php

class smPromocode
{
    protected $id = null;
    protected $data = null;
    protected $model = null;

    public function __construct($id = null)
    {
        $this->model = new smPromocodeModel();
        $default_data = array(
            'name' => '',
            'promocode' => '',
            'count' => '',
            'tariff_id' => '',
            'tariff_days' => '',
        );
        if($id)
        {
            $this->data = $this->model->getById($id);
            if($this->data !== null)
            {
                if($this->data['hidden'] == 1) {$this->data = $default_data;}
                else {$this->id = $id; unset($this->data['id']);}
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

    public function getData()
    {
        return $this->data;
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

        if($this->id) {$this->model->updateById($this->id, $this->data);}
        else {$this->id = $this->model->insert($this->data);}
    }

    public function getByField($field = null, $value = null)
    {
        if($field === null){return null;}
        else{return $this->model->getByField($field, $value, true);}
    }
}