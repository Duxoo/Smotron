<?php

class smReferralHelper
{
    protected function getRefDiscount()
    {
        $settings_model = new smSettingsModel();
        $settings = $settings_model->getSettings();

        return array(
            '0' => $settings['1discount'],
            '1' => $settings['2discount'],
            '2' => $settings['3discount'],
        );
    }

    public static function addPayout($user_id, $price_total, $order_id)
    {
        $referral = new smReferral($user_id);
        $order_model = new smOrderModel();

        $order_data = $order_model->getById($order_id);
        $parents = $referral->getParents();

        $discounts = self::getRefDiscount();

        waLog::dump($parents);

        if(!is_array($parents)){return null;}
        if(isset($parents[0])){
            $first = array_slice($parents,0,1);
            self::setPayment($first['0'], self::calculatePrice($price_total, $discounts['0']), $order_id, $user_id);
            unset($parents['0']);
        }
        if(isset($parents[1])) {
            $second = array_slice($parents,0,1);
            self::setPayment($second['0'], self::calculatePrice($price_total, $discounts['1']), $order_id, $user_id);
            unset($parents['1']);
        }
        if(is_array($parents)){
            foreach ($parents as $key => $contact)
            {
                self::setPayment($contact, self::calculatePrice($price_total, $discounts['2']), $order_id, $user_id);
            }
        }
    }

    protected static function calculatePrice($price, $percent)
    {
        return intval($price * ($percent / 100));
    }

    protected static function setPayment($contact, $price, $order_id, $emit_id)
    {
        $event_model = new smUserEventModel();
        $user = new smUser($contact['contact_id']);
        $event_model = new smUserEventModel();

        $user->transaction('referal', $price, $order_id, 'Бонус за реферальную программу', $emit_id, false);
        $event_model->addEvent($contact['contact_id'], 'promo', 'Бонус за реферальную программу в размере'.$price, $emit_id);
    }












    public static function processRefund($user_id, $left_amount)
    {
        $user = new smUser($user_id);
        $user_data = $user->getData();
        $t_datetime = $user_data['subscribtion_activated'];
        $referral = new smReferral($user_id);
        $discounts = self::getRefDiscount();

        $parents = $referral->getParents();
        if(!is_array($parents)){return null;}

        if(isset($parents[0])){
            $first = array_slice($parents,0,1);
            $data = self::prepareData($first[0], $t_datetime);
            self::runRefund($data, $left_amount, $user_id);
            unset($parents['0']);
        }
        if(isset($parents[1])) {
            $second = array_slice($parents,0,1);
            $data = self::prepareData($second[0], $t_datetime);
            self::runRefund($data, $left_amount, $user_id);
            unset($parents['1']);
        }
        if(is_array($parents)){
            foreach ($parents as $key => $contact)
            {
                $data = self::prepareData($contact, $t_datetime);
                self::runRefund($data, $left_amount, $user_id);
            }
        }

    }

    protected static function runRefund($data, $amount, $user_id)
    {
        self::updateBalance($user_id, $data, $amount);
        self::updateBalanceHistory($data, $amount);
        self::updateEvent($data, $user_id);
    }

    protected static function prepareData($contact, $transaction_date)
    {
        $user = new smUser($contact['contact_id']);
        $data = array(
            'user_id' => $user->getId(),
            'type' => 'referal',
            'emit_id' => $contact['contact_id'],
            'transaction_datetime' => $transaction_date,
        );
        return $data;
    }

    protected static function updateBalance($user_id, $data, $amount)
    {
        $user = new smUser($user_id);
        $history_model = new smUserBalanceHistoryModel();
        $old_data = $history_model->getByfield($data);
        $new_amount = $old_data['amount_before'] + $amount;
        $user->updateBalance($new_amount);
    }

    // param $id - id записи в таблице balancehistory
    protected static function updateBalanceHistory($data, $amount)
    {
        $history_model = new smUserBalanceHistoryModel();
        $old_data = $history_model->getByfield($data);
        $data = array(
            'amount' => $amount,
            'amount_after' => $old_data['amount_before'] + $amount,
            'comment' => 'Бонус за реферальную программу в размере'.$amount,
        );
        return $history_model->updateById($old_data['id'], $data);
    }

    protected static function updateEvent($pdata, $amount)
    {
        $data = array(
            'user_id' => $pdata['user_id'],
            'type' => 'promo',
            'emit_id' => $pdata['emit_id'],
            'register_datetime' => $pdata['transaction_datetime'],
        );
        $event_model = new smUserEventModel();
        $event = $event_model->getByField($data);
        $event_model->updateEventComment($event['id'], 'Бонус за реферальную программу в размере'.$amount);
    }
}