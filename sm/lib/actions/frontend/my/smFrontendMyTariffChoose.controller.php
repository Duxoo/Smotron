<?php

class smFrontendMyTariffChooseController extends waJsonController
{
    public function execute()
    {
        $tariff_id = waRequest::post('tariff-id');
        $data = array(
            'contact_id' => $this->getUserId(),
            'tariff_type' => 0,
            'tariff_id' => $tariff_id,
        );
        $data['endtime'] = date("Y-m-d H:i:s", strtotime("+1 month"));
        //сюда добавить оплату
        $model = new smContactTariffModel();
        $result = $model->addTariff($data);

        if($result){$this->response = array('result' => 1, 'message' => 'success');}
        else{$this->response = array('result' => 0, 'message' => 'fail');}
    }
}