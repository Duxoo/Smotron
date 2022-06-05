<?php

class smFrontendMyBalanceReplenishController extends waJsonController
{
    public function execute()
    {
        $contact = wa()->getUser();
        // сюда добавить оплату
        $pay = 500;

        if($contact->isAuth())
        {
            $balance = new smBalance($contact->getId());
            $balance->setPayment($pay,1);
            $balance->savePay();
            $this->response = array('result' => 1, 'message' => _w("Saved"));
        }
    }
}