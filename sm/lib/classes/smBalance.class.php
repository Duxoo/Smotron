<?php

class smBalance
{
    protected $model;
    protected $log_model;
    protected $contact_id;
    protected $balance = array(
        'total' => null,
        //'currency' => '',
        'log' => null,
    );

    protected $payment;
    protected $operation_model;
    protected $operation;

    public function __construct($contact_id = null)
    {
        $this->model = new smBalanceModel();
        $this->log_model = new smBalanceLogModel();
        $this->operation_model = new smBalanceOperationTypeModel();

        if(isset($contact_id)){ $this->contact_id = $contact_id; }
        else{ return null; }
        $this->balance['total'] = $this->checkBalance();
        $this->balance['currency'] = waSystem::getInstance('shop')->getConfig()->getCurrency();

        $this->balance['log'] = $this->log_model->getLog($contact_id,false);

    }

    public function checkBalance()
    {
        $balance_info = $this->model->getById($this->contact_id);
        if(!isset($balance_info))
        {
            $this->model->insert(array('contact_id' => $this->contact_id));
            $balance_info = $this->model->getById($this->contact_id);
        }
        return $balance_info['total'];
    }

    public function getTotal()
    {
        return $this->balance['total'];
    }

    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param $pay
     * @param $type - operation type 1 for +, 2 for -, 3 for refund to balance, 4 for refund to bank
     */
    public function setPayment($pay, $type_id)
    {
        $this->payment = intval($pay);
        $this->operation = $this->operation_model->getById($type_id);
        /*if($this->operation['type'] == 1){ $this->operation['description'] = 'Пополнение баланса';}
        if($this->operation['type'] == 2){ $this->operation['description'] = 'Оплата услуг';}*/
    }

    public function savePay()
    {
        $total_old = $this->model->getById($this->contact_id);
        $total_old['total'] = intval($total_old['total']);

        if(($this->operation['id'] == '1') || ($this->operation['id'] == '3'))
        {
            $total = $total_old['total'] + intval($this->payment);
        }
        elseif (($this->operation['id'] == '2') || ($this->operation['id'] == '4'))
        {
            $total = $total_old - intval($this->payment);
        }
        else
        {
            return 'ERROR undifined operation type';
        }

        $this->model->updateById($this->contact_id, array('total' => $total));
        $log = array(
            'contact_id' => $this->contact_id,
            'operation_type' => $this->operation['id'],
            'amount' => $this->payment,
            'description' => $this->operation['description'],
            'datetime' => date('Y-m-d  H:i:s'),
        );
        $this->log_model->insert($log);
    }

    public function getPaymentLog()
    {
        if($this->contact_id == null){return $this->log_model->getAll('id');}
        else{return $this->model->getByField('contact_id',$this->contact_id);}
    }
}