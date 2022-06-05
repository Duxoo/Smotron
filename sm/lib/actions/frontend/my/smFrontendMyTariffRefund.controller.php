<?php

class smFrontendMyTariffRefundController extends waJsonController
{
    public function execute()
    {
		if(!wa()->getCaptcha()->isValid()) {$this->response = array('result' => 0, 'message' => _w("Check the box 'I'm not a robot'")); return;}
		if(!wa()->getUser()->isAuth()) {$this->response = array('result' => 0, 'message' => _w("You must log in")); return;}
		$user_id = wa()->getUser()->getId();
		$user = new smUser($user_id);
		$token_class = new smToken();
		$token_class->updateUserToken($user_id);
        /*$token_class->deleteUserToken($user_id);*/
		$this->response = $user->refundSubscribtion();
    }
}