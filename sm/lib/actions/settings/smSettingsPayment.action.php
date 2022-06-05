<?php
class smSettingsPaymentAction extends waViewAction
{
    public function execute()
    {
		$model = new smPluginModel();
        $this->view->assign('instances', $model->listPlugins(smPluginModel::TYPE_PAYMENT, array('all' => true, )));
        $this->view->assign('plugins', waPayment::enumerate());
        $this->view->assign('installer', $this->getUser()->getRights('installer', 'backend'));
		$this->view->assign('backend_url', wa()->getConfig()->getBackendUrl());
    }
}
