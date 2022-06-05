<?php

class smFrontendMyTariffCustomCreateController extends waJsonController
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth()) {$this->response = array('result' => 0, 'message' => _w("You must log in")); return;}
        $data = waRequest::post('tariff');
        if(!is_array($data)) {$this->response = array('result' => 0, 'message' => _w("System error #NOARR")); return;}
        if (!isset($data['channels']) && $data['channel_custom_count'] == 0) {
            $this->response = array('result' => 0, 'message' => _w("Please select at least one channel"));
            return;
        }
        $data['is_custom'] = 1;
        $contact_id = wa()->getUser()->getId();
        $helper = new smHelper();

        $tariff_model = new smTariffModel();
        $tariff_data = $tariff_model->getByField(array('is_custom'=>1,'user_id'=>$contact_id));
        if(isset($tariff_data['id'])){ $tariff = new smTariff($tariff_data['id'], true); $data['id'] = $tariff->getId(); }
        else{ $tariff = new smTariff(null, true); }

        $data['user_id'] = $contact_id;
        $data['url'] = $helper->translit($data['name']);
        $data['channel_count'] = count($data['channels']);
        $data['summary'] =  $data['name'];
        $data['description'] = $data['name'];

        $data['price'] = $tariff::calculateTariffPrice($data);
        $tariff->setAll($data);
        $tariff->save();

        $tariff_id = $tariff->getId();
        if (isset($tariff_id)) {
            wa()->getResponse()->redirect(wa()->getRouteUrl('sm/frontend/checkout') . '?tariff_id=' . $tariff_id, 302);
            return;
        }
        else
        {
            wa()->getResponse()->redirect(wa()->getRouteUrl('sm/frontend/checkout'), 302);
            return;
        }
    }
}