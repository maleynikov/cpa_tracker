<?php

class Advertise {

    public $net = 'Advertise';
    private $common;
    private $params = array(
        'subid' => 'subid',
        'profit' => 'amount',
        'date_add' => 'action_time',
        'txt_status' => 'status',
        'type' => 'action_type',
        'f1' => 'order_sum',
        'i2' => 'offer_id',
        'i3' => 'order_id',
        'i4' => 'click_time',
        'i5' => 'source_id',
        'i6' => 'conversion_time',
        'i7' => 'action_id',
        'i8' => 'stats_action_id',
        't1' => 'action_ip',
        't2' => 'user_agent',
        't4' => 'offer_name',
        't7' => 'source_name',
        't8' => 'user_referer',
        't9' => 'country',
        't10' => 'city',
        't16' => 'subid1',
        't17' => 'subid2',
        't18' => 'subid3',
        't19' => 'subid4',
        't21' => 'keyword',
        't22' => 'action_name',
    );
    private $reg_url = 'http://www.cpatracker.ru/networks/advertise';
    private $net_text = 'Партнерская сеть с повышенными ставками на все офферы, ежедневные выплаты без комиссии и эксклюзивные промо-материалы. Среди рекламодателей: онлайн-игры, сайты знакомств, wow-товары и финансовые сервисы.';

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
            'description' => 'Вставьте эту ссылку в поле PostBack ссылки в настройках оффера Advertise.'
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

        if (empty($data['type'])) {
            $data['type'] = 'sale';
        }

        unset($data['net']);
        switch ($data['txt_status']) {
            case 'approved':
                $data['txt_status'] = 'approved';
                $data['status'] = 1;
                break;
            case 'rejected':
                $data['txt_status'] = 'declined';
                $data['status'] = 2;
                break;
            case 'processing':
                $data['txt_status'] = 'waiting';
                $data['status'] = 3;
            default:
                $data['txt_status'] = 'Unknown';
                $data['status'] = 0;
                break;
        }

        $this->common->process_conversion($data);
    }

}