<?php

class Biznip {

    public $net = 'Biznip';
    private $common;
    private $params = array(
        'profit' => 'payout',
        'subid' => 'aff_sub',
        'txt_status' => 'status',
        't5' => 'click_id',
        't16' => 'aff_sub2',
        't17' => 'aff_sub3',
        't18' => 'aff_sub4',
        't19' => 'aff_sub5',
        'i1' => 'goal_id',
        'i2' => 'offer_id',
        'i3' => 'conversion_id',
    );
    private $reg_url = 'http://www.cpatracker.ru/networks/biznip';
    private $net_text = 'Лучшая в рунете партнерская программа по инфотоварам. Собственные продукты высочайшего качества по работе в интернете, похудению, построению отношений. Привлеченным клиентам постоянно продолжают продавать услуги и товары с помощью почтовых рассылок, партнеры получают комиссию по повторным продажам, что позволяет стабильно зарабатывать даже после остановки трафика.';

    function __construct() {
        $this->common = new common($this->params);
    }

    function get_links() {
        $url = tracklink() . '/p.php?n=' . $this->net;

        foreach ($this->params as $name => $value) {
            $url .= '&' . $name . '={' . $value . '}';
        }

        $code = $this->common->get_code();
        $url .= '&ak=' . $code;

        $return = array(
            'id' => 0,
            'url' => $url,
            'description' => 'Вставьте эту ссылку в поле PostBack ссылки в настройках Biznip.'
        );

        return array(
            0 => $return,
            'reg_url' => $this->reg_url,
            'net_text' => $this->net_text
        );
    }

    function process_conversion($data_all) {
        $this->common->log($this->net, $data_all['post'], $data_all['get']);
        $data = $this->common->request($data_all);
        $data['network'] = $this->net;

        unset($data['net']);

        $data['date_add'] = date('Y-m-d H:i:s');

        switch ($data['txt_status']) {
            case 'pending':
                $data['status'] = 3;
                break;
            case 'approved':
                $data['status'] = 1;
                break;
            case 'rejected':
                $data['status'] = 2;
                break;
        }


        $this->common->process_conversion($data);
    }

}

