<?php

class smMetricsSalesModel extends waModel
{
    protected $table = 'sm_metrics_sales';

    public function change($data, $new)
    {
        waLog::dump($data);
        $sales['date'] = $data['date'];
        $sales = $this->checkPeriodByDate($sales['date']);
        if($data['type'] == 'subscribed')
        {
            $sales['order_count'] = $sales['order_count'] + 1;
            $sales['new_customer_count'] = $sales['new_customer_count'] + $new;
            $sales['sales'] = $sales['sales'] + $data['amount'];
            $sales['cost'] = 0;
            $sales['tax'] = 0;
            $sales['purchase'] = 0;
        }
        elseif ($data['type'] == 'refund')
        {
            //$sales['order_count'] = $sales['order_count'] - 1;
            $sales['ref'] = $sales['ref'] + $data['amount'];
        }

        return $this->updateByField('date',$sales['date'],$sales);
    }

    public function checkPeriodByDate($date)
    {
        $row = $this->getByField('date',$date);
        if(isset($row)){ return $row;}
        else{ $this->insert(array('date'=>$date)); return $this->getByField('date',$date); }
    }

    public function getMinDate()
    {
        static $date_start = null;
        if ($date_start === null) {
            $order_model = new smOrderModel();
            $date_start = $order_model->getMinDate();
        }
        return $date_start;
    }

    public function getPeriodByDate($date_start, $date_end, $options=array())
    {
        // Check parameters
        empty($date_end) && ($date_end = date('Y-m-d 23:59:59'));
        empty($date_start) && ($date_start = $this->getMinDate());

        $date_group = ifset($options['date_group'], 'days');
        $date_col = ($date_group == 'months') ? "DATE_FORMAT(ss.`date`, '%Y-%m-01')" : 'ss.`date`';

        $date_sql = self::getDateSql('ss.`date`', $date_start, $date_end);
        $sql = "SELECT {$date_col} AS `date`,
                    SUM(order_count) AS order_count,
                    SUM(new_customer_count) AS new_customer_count,
                    SUM(sales) AS profit,
                    SUM(sales) AS sales,
                    SUM(purchase) AS purchase,
                    SUM(ref) AS ref
                FROM {$this->table} AS ss
                WHERE {$date_sql}
                GROUP BY {$date_col}
                ORDER BY `date`";

        $sales_by_date = $this->query($sql)->fetchAll('date');

        // Add empty rows
        $empty_row = array(
            'order_count' => 0,
            'new_customer_count' => 0,
            'profit' => 0,
            'sales' => 0,
            'purchase' => 0,
            'ref' => 0,
        );

        $end_ts = strtotime($date_end);
        $start_ts = strtotime($date_start);
        for ($t = $start_ts; $t <= $end_ts; $t = strtotime(date('Y-m-d', $t) . ' +1 day')) {
            $date = date(($date_group == 'months') ? 'Y-m-01' : 'Y-m-d', $t);
            if (empty($sales_by_date[$date])) {
                $sales_by_date[$date] = array(
                        'date' => $date,
                    ) + $empty_row;
            }
            foreach($empty_row as $k => $v) {
                $sales_by_date[$date][$k] = (float) $sales_by_date[$date][$k];
            }
        }
        ksort($sales_by_date);

        return $sales_by_date;

        /*if (empty($type) || !is_string($type)) {
            throw new waException('Type is required');
        }

        $options['hash'] = $hash = self::getHash($type, $options);

        $date_group = ifset($options['date_group'], 'days');
        $date_col = ($date_group == 'months') ? "DATE_FORMAT(ss.`date`, '%Y-%m-01')" : 'ss.`date`';

        $type_sql = '';
        if ($type == 'coupons' || $type == 'campaigns' || $type == 'social') {
            $type_sql = "AND name <> ''";
        }

        // Make sure data is prepared in table
        empty($options['ensured']) && $this->ensurePeriod($type, $date_start, $date_end, $options);

        $date_sql = self::getDateSql('ss.`date`', $date_start, $date_end);
        $sql = "SELECT {$date_col} AS `date`,
                    SUM(order_count) AS order_count,
                    SUM(new_customer_count) AS new_customer_count,
                    SUM(sales - purchase - shipping - tax) AS profit,
                    SUM(sales) AS sales,
                    SUM(purchase) AS purchase,
                    SUM(shipping) AS shipping,
                    SUM(tax) AS tax,
                    SUM(cost) AS cost
                FROM {$this->table} AS ss
                WHERE hash=?
                    AND {$date_sql}
                    {$type_sql}
                GROUP BY {$date_col}
                ORDER BY `date`";

        $sales_by_date = $this->query($sql, $hash)->fetchAll('date');

        // Add empty rows
        $empty_row = array(
            'order_count' => 0,
            'new_customer_count' => 0,
            'profit' => 0,
            'sales' => 0,
            'purchase' => 0,
            'shipping' => 0,
            'tax' => 0,
            'cost' => 0,
        );
        $end_ts = strtotime($date_end);
        $start_ts = strtotime($date_start);
        for ($t = $start_ts; $t <= $end_ts; $t = strtotime(date('Y-m-d', $t) . ' +1 day')) {
            $date = date(($date_group == 'months') ? 'Y-m-01' : 'Y-m-d', $t);
            if (empty($sales_by_date[$date])) {
                $sales_by_date[$date] = array(
                        'date' => $date,
                    ) + $empty_row;
            }
            foreach($empty_row as $k => $v) {
                $sales_by_date[$date][$k] = (float) $sales_by_date[$date][$k];
            }
        }
        ksort($sales_by_date);

        return $sales_by_date;*/
    }

    public static function getDateSql($fld, $start_date, $end_date)
    {
        $paid_date_sql = array();
        if ($start_date) {
            $paid_date_sql[] = $fld." >= DATE('".$start_date."')";
        }
        if ($end_date) {
            $paid_date_sql[] = $fld." <= (DATE('".$end_date."') + INTERVAL ".(24*3600 - 1)." SECOND)";
        }
        if ($paid_date_sql) {
            return implode(' AND ', $paid_date_sql);
        } else {
            return $fld." IS NOT NULL";
        }
    }

    public function getPeriod($date_start, $date_end, &$total_rows=null)
    {
        // Check parameters
        empty($date_end) && ($date_end = date('Y-m-d 23:59:59'));
        empty($date_start) && ($date_start = $this->getMinDate());

        $date_sql = self::getDateSql('`date`', $date_start, $date_end);

        // Using derived query because otherwise
        // ORDER BY would not work for some columns (average_order)
        $sql = "SELECT SQL_CALC_FOUND_ROWS t.* FROM
                    (SELECT
                        name,
                        SUM(order_count) AS order_count,
                        SUM(new_customer_count) AS new_customer_count,
                        SUM(sales) AS profit,
                        SUM(sales) AS sales,
                        SUM(purchase) AS purchase,
                        SUM(ref) AS ref
                    FROM {$this->table}
                    WHERE {$date_sql}                    
                    GROUP BY name) AS t";
        $rows = $this->query($sql);
        $total_rows = $this->query("SELECT FOUND_ROWS()")->fetchField();
        $result = array();
        foreach($rows as $row) {
            // Ignore empty rows
            if ($row['order_count'] == 0 && $row['cost'] == 0) {
                $total_rows--;
                continue;
            }

            /*if ($row['cost'] > 0) {
                $row['roi'] = $row['profit']*100 / $row['cost'];
            } else {
                $row['roi'] = 0;
            }*/
            $result[] = $row;
        }
        return $result;
    }

}