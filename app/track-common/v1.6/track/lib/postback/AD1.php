<?php

class AD1 {

    public $net = 'AD1';
    private $common;
    private $params = array(
        'subid' => 'subid',
        'profit' => 'summ_approved',
        'date_add' => 'postback_date',
        'txt_status' => 'status',
        't1' => 'uip',
        't2' => 'uagent',
        't3' => 'goal_title',
        't4' => 'offer_name',
        'f1' => 'summ_total',
        'i1' => 'goal_id',
        'i2' => 'offer_id',
        'i3' => 'order_id',
        'i4' => 'click_time',
        'i5' => 'lead_time',
        'i6' => 'postback_time',
        'i7' => 'rid',
        'd1' => 'click_date',
        'd2' => 'lead_date'
    );
    private $reg_url = 'http://www.cpatracker.ru/networks/ad1';
    private $net_text = 'Одной из самых привлекательных СРА сетей в рунете. С момента запуска в 2011 году, разработчики активно работают над сетью, добавляют новые инструменты и активно привлекают рекламодателей. Сеть работает на собственной платформе Zotto, выплаты по запросу от 30 рублей. Постоянно проходят конкурсы для вебмастеров с крупными призами.';

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
            'description' => 'Вставьте эту ссылку в поле PostBack ссылки в настройках Вашего потока в сети AD1.'
        );

        return array(
            0 => $return,
            'reg_url' => $this->reg_url,
            'net_text' => $this->net_text
        );
    }

    function process_conversion($data_all = array()) {
        $this->common->log($this->net, $data_all['post'], $data_all['get']);
        $data = $this->common->request($data_all);
        $data['network'] = $this->net;
        unset($data['net']);
        $cnt = count($data);
        $i = 0;

        switch ($data['status']) {
            case 'approved':
                $data['txt_status'] = 'Approved';
                $data['status'] = 1;
                break;
            case 'declined':
                $data['txt_status'] = 'Declined';
                $data['status'] = 2;
                break;
            case 'waiting':
                $data['txt_status'] = 'Waiting';
                $data['status'] = 3;
                break;
            default:
                $data['txt_status'] = 'Unknown';
                $data['status'] = 0;
                break;
        }

        $this->common->process_conversion($data);
    }

}

