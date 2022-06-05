<?php

class smTariffListAction extends waViewAction
{
    public function execute()
    {
        $tariff_model = new smTariffModel();
        $result = $tariff_model->getTariffs();
        $this->view->assign('tariffs',$result);
    }
}
