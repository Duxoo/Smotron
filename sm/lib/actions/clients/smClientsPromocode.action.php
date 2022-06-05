<?php

class smClientsPromocodeAction extends waViewAction
{
    public function execute()
    {
        $this->setLayout(new smBackendLayout());
        $id = waRequest::get('id', 0, 'int');

        $promocode = new smPromocode($id);
        $data = $promocode->getData();

        $tariff_model = new smTariffModel();
        $tariffs = $tariff_model->getTariffs();

        $this->view->assign('tariffs', $tariffs);
        $this->view->assign('data', $data);
        $this->view->assign('id', $promocode->getId());
    }
}