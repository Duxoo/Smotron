<?php

class smFrontendTariffAction extends waViewAction
{
    public function execute()
    {
        $url = waRequest::param('tariff_url');
        $t_model = new smTariffModel();
        $t_data = $t_model->getByUrl($url);

        if(!isset($t_data[0])){wa()->getResponse()->redirect('/'); return;}
        $tariff = new smTariff($t_data[0]['id']);

        $model = new smTariffChannelsModel();
        $data['channels'] = $model->getChannelsDataByTariff($t_data[0]['id']);

        $this->setLayout(new smFrontendLayout());


        $settings_model = new smSettingsModel();
        $settings = $settings_model->getSettings();
        // TITLE
        $meta_title = $settings['tariff_title'];
        $meta_title = str_replace('%tariff_name%', _w($tariff->get('name','Тариф')), _w($meta_title));
        $this->view->assign('post_name',$meta_title);
        // KEYWORDS
        $meta_keywords = $settings['tariff_keywords'];
        $meta_keywords = str_replace('%tariff_name%', $tariff->get('name','Тариф'), $meta_keywords);
        // DESCRIPTION
        $meta_description = $settings['tariff_description'];
        $meta_description = str_replace('%tariff_name%', $tariff->get('name','Тариф'), $meta_description);
        // SET DATA
        wa()->getResponse()->setTitle($meta_title);
        wa()->getResponse()->setMeta('keywords', $meta_keywords);
        wa()->getResponse()->setMeta('description', $meta_description);
        /*wa()->getResponse()->setTitle('Тариф: '.$tariff->get('name','Тариф'));*/
        
        $this->view->assign('tariff',$tariff->getData());
        $this->view->assign('tariff_id',$tariff->getId());
        $this->view->assign('channels',$data['channels']);
        $this->setThemeTemplate('tariff.html');
    }
}