<?php

class smEndSubscriptionChangeTokenCli extends waCliController {
    public function execute() {
        $userModel = new smUserModel();
        $users = $userModel->getUsersWithEndedSubscription();
        $token_class = new smToken();
        foreach ($users as $key => $value) {
            $token_class->updateUserToken($value['id']);
        }
    }
}