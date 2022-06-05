<?php

class smSubscribtionHelper
{
    public static function getUpgradeOptions($prev_t_id, $cost, $valid_from, $valid_till)
    {
        $user = new smUser();
        $user_id = $user->getId();
        $tariffs = smTariff::getUserTariffs($user_id);

        $days_total = max(ceil((strtotime($valid_till) - strtotime($valid_from))/86400),1);
        $days_used = max(ceil((time() - strtotime($valid_from))/86400), 1);

        $usage_percent = $days_used*100/$days_total;
        $amount = $cost - $cost*$usage_percent/100;

        $result = array();
        foreach ($tariffs as $key => $tariff){
            $result[$key] = $tariff;
            /*$result[$key]['refund_data'] = smTariff::calculateRefundAmount($tariff['price'], $valid_from, $valid_till);*/
            $result[$key]['cost'] = ($tariff['price'] - $tariff['price']*$usage_percent/100) - $amount;
            /*if($result[$key]['cost'] < 0) { unset($result[$key]); }*/
            if($key == $prev_t_id){ unset($result[$key]); }
        }
        return $result;
    }


}