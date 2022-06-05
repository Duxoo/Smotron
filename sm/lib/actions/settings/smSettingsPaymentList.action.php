<?php
class smSettingsPaymentListAction extends waViewAction
{
    public function execute()
    {
		$model = new smPluginModel();
        $this->view->assign('instances', $model->listPlugins(smPluginModel::TYPE_PAYMENT, array('all' => true, )));
    }
}
