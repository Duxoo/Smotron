<?php

class smSettingsPaymentSetupAction extends waViewAction
{
    public function execute()
    {
        $this->view->assign('plugin_id', $plugin_id = waRequest::get('plugin_id'));
        try {

            $this->view->assign('plugin', $info = smPayment::getPluginInfo($plugin_id));

            $plugin = smPayment::getPlugin($info['plugin'], $plugin_id);
            $params = array(
                'namespace' => "payment[settings]",
                'value'     => waRequest::post('shipping[settings]'),
            );
            $this->view->assign('settings_html', $plugin->getSettingsHTML($params));
            $this->view->assign('guide_html', $plugin->getGuide($params));

            $model = new smPluginModel();
            $this->view->assign('shipping', $model->listPlugins(smPluginModel::TYPE_SHIPPING, array('payment' => $plugin_id, 'all' => true)));
        }
		catch (waException $ex) {$this->view->assign('error', $ex->getMessage());}
    }
}
