<?php

class smForgotpasswordAction extends waForgotPasswordAction
{
    public function execute()
    {
        $this->setLayout(new smFrontendLayout());
        $this->setThemeTemplate('forgotpassword.html');
        parent::execute();
    }
}
