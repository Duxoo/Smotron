<?php

class smFrontendMyProfileAction extends waMyProfileAction
{
    public function execute()
    {
        if(!wa()->getUser()->isAuth())
		{
			$this->setThemeTemplate('blank.html');
			wa()->getResponse()->redirect(wa()->getRouteUrl('sm/frontend').'?ar=1', 302);
			return;
		}

        $this->setThemeTemplate('my.profile.html');
		$this->setLayout(new smFrontendLayout());
		$this->getResponse()->setTitle(_w("Profile - Smotron"));

		$contact = wa()->getUser();
		$contact_id = $contact->getId();

		$referral = new smReferral($contact_id);

		$user = new smUser($contact_id);
		$promocode_id = $user->getData('promo');
		if(!empty($promocode_id)) {
		    $promocode = new smPromocode($promocode_id);
		    $promocode_data = $promocode->getData();
		    //waLog::dump($promocode_data);
            $tariff = new smTariff($promocode_data['tariff_id']);
		    $this->view->assign('promocode', $promocode_data);
            $this->view->assign('tariff', $tariff->getData());
        }

		$photo_url = waContact::getPhotoUrl($contact->get('id'), $contact->get('photo'), 200, 200);
		$this->view->assign('contact', $contact);
        $this->view->assign('referral_code', $referral->getCode());
		$this->view->assign('photo_url', $photo_url);
		$this->view->assign('jfields', smBillHelper::getFilledBillFields());

		$settings_model = new smSettingsModel();
		$this->view->assign('settings', $settings_model->getSettings());
    }
}