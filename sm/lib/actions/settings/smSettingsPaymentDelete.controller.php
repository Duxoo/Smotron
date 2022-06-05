<?php
class smSettingsPaymentDeleteController extends waJsonController
{
    public function execute()
    {
        if ($plugin_id = waRequest::post('plugin_id'))
		{
            $model = new smPluginModel();
            if ($plugin = $model->getByField(array('id' => $plugin_id, 'type' => 'payment')))
			{
                $settings_model = new smPluginSettingsModel();
                $settings_model->del($plugin['id'], null);
                $model->deleteById($plugin['id']);
				$this->response = array('result' => 1, 'message' => 'Готово');
            }
			else
			{
				$this->response = array('result' => 0, 'message' => 'Плагин не найден');
            }
        }
    }
}
