<?php
class smSettingsPaymentSaveController extends waJsonController
{
    public function execute()
    {
        if ($plugin = waRequest::post('payment'))
		{
            try
			{
                if (!isset($plugin['settings'])) {$plugin['settings'] = array();}
                smPayment::savePlugin($plugin);
                $this->response = array('result' => 1, 'message' => 'Сохранено');
            }
			catch (waException $ex) {$this->response = array('result' => 0, 'message' => $ex->getMessage());}
        }
    }
}
