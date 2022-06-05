<?php

class smContactTariffModel extends waModel
{
    protected $table = 'sm_contact_tariff';
    protected $id = 'contact_id';

    public function addTariff($data)
    {
        if($this->getById($data['contact_id'])){return $this->updateById($data['contact_id'],$data);}
        else{return $this->insert($data);}
    }
}