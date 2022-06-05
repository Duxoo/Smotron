<?php
class smSettingsSaveController extends waJsonController
{
    public function execute()
    {
		$settings = waRequest::post('settings', null);
		if(!is_array($settings)) {$this->response = array('result' => 0, 'message' => 'Системная ошибка #NOARR'); return;}
		if(!count($settings)) {$this->response = array('result' => 0, 'message' => 'Системная ошибка #NOARR'); return;}
		
		$settings_model = new smSettingsModel();
		foreach($settings as $key => $value) {$settings_model->setParam($key, $value);}
		
		$this->response = array('result' => 1, 'message' => 'Настройки сохранены');
    }
}
