<?php

class smSettingsDiscountSaveController extends waJsonController
{
    public function execute()
    {
        $discount = waRequest::post('discount');
        $referral = waRequest::post('referral');

        if(!is_array($discount)) {$this->response = array('result' => 0, 'message' => 'Системная ошибка #NOARR'); return;}
        if(!count($discount)) {$this->response = array('result' => 0, 'message' => 'Системная ошибка #NOARR'); return;}
        if(!is_array($referral)) {$this->response = array('result' => 0, 'message' => 'Системная ошибка #NOARR'); return;}
        if(!count($referral)) {$this->response = array('result' => 0, 'message' => 'Системная ошибка #NOARR'); return;}

        $settings_model = new smSettingsModel();
        foreach($discount as $key => $value) {$settings_model->setParam($key, $value);}
        foreach($referral as $key => $value) {$settings_model->setParam($key, $value);}

        $this->response = array('result' => 1, 'message' => 'Настройки сохранены');
    }
}