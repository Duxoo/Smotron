<?php
class smFrontendAction extends waViewAction
{
    public function __construct()
    {
        parent::__construct();
        $this->setLayout(new smFrontendLayout());
    }

    public function execute()
    {
        $referral_code = waRequest::get('ref');
        if(isset($referral_code)) {
            setcookie('ref', "", time() - 36000, '/'); //очистка реф куки
            if (!wa()->getUser()->isAuth()) { setcookie('ref', $referral_code, time() + 36000, '/'); }
        }

        $obj = new smFlussonicApi();
        $media = $obj->getMedia();
        $this->view->assign('media', $media);

        $this->setThemeTemplate('home.html');
		wa()->getResponse()->setTitle('Смотрон - ТВ для дома и бизнеса');

        $settings_model = new smSettingsModel();
        $settings = $settings_model->getSettings();
        wa()->getResponse()->setTitle($settings['home_title']);

        if(!empty($settings['home_keywords'])){ wa()->getResponse()->setMeta('keywords', $settings['home_keywords']); }
        if(!empty($settings['home_description'])){ wa()->getResponse()->setMeta('description', $settings['home_description']); }

    }
}
