<?php

class smEntity
{
    protected $contact_id, $model;
    protected $entity_fields = array(
        'contact_id' => array(
            'name' => '',
            'value' => null,
            'display' => false,
        ),
        'name' => array(
            'name' => 'Наименование организации',
            'value' => null,
            'display' => true,
        ),//'some name',
        'inn' => array(
            'name' => 'ИНН',
            'value' => null,
            'display' => true,
        ),//"11111111111",
        'ogrn' => array(
            'name' => 'ОГРН',
            'value' => null,
            'display' => true,
        ),//"222222222",
        'okpo' => array(
            'name' => 'ОКПО',
            'value' => null,
            'display' => true,
        ),//"3333333333",
        'address' => array(
            'name' => 'Юридический адрес',
            'value' => null,
            'display' => true,
        ),//"some address",
        'bank' => array(
            'name' => 'Банк',
            'value' => null,
            'display' => true,
        ),//"55555555",
        'settlement_account' => array(
            'name' => 'расчетный счет',
            'value' => null,
            'display' => true,
        ),//"666666666",
        'bik' => array(
            'name' => 'БИК',
            'value' => null,
            'display' => true,
        ),//"77777777",
        'kpp' => array(
            'name' => 'КПП',
            'value' => null,
            'display' => true,
        ),//"8888888888",
        //'error' => false,
    );
    public function __construct($contact_id = null)
    {
        $this->model = new smEntityModel();
        if(isset($contact_id)) { $this->contact_id = $contact_id; }
    }

    public function getInfo()
    {
        $data = $this->model->getById($this->contact_id);
        if(!isset($data)){return $this->entity_fields;}
        foreach ($data as $field_name => $value)
        {
            $this->entity_fields[$field_name]['value'] = $value;
        }
        return $this->entity_fields;
    }

    public function setInfo($data)
    {
        return $this->model->insert($data,1);
    }
}