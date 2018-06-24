<?php

class _7offers {

    public $net = '7offers';
    private $common;
    private $params = array(
        'profit' => '{goal_value}',
        'subid' => '{subid1}',
        'date_add' => '{time_action}',
		'status' => '{action_status}',
		'f1' => '{action_sum}',
        't1' => '{user_ip}',
        't2' => '{user_agent}',
        't3' => '{goal_title}',
        't4' => '{offer_title}',
        't7' => '{source_title}',
        't16' => '{subid2}',
        't17' => '{subid3}',
        't18' => '{subid4}',
        't19' => '{subid5}',
        't20' => '{currency}',
        'i1' => '{goal_id}',
        'i2' => '{offer_id}',
        'i3' => '{action_id}',
        'i7' => '{link_id}',
    );
    private $reg_url = 'http://www.cpatracker.ru/networks/7offers';
    private $net_text = '7offers – крупная сеть партнерских программ с оплатой за целевое действие (Cost Per Action).  Партнёрская программа 7offers начала работу в 2014 году. Работает по CPA модели с любым типом действия. Принцип оплаты за целевые действия различные: есть офферы с фиксированными выплатами, есть и с процентными отчислениями от стоимости заказа. Принимается как мобильный, так и app-трафик.';

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
            'description' => 'Вставьте эту ссылку в PostBack поле URL в инструментах 7offers.ru'
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
            case '1':
                $data['txt_status'] = 'Approved';
                $data['status'] = 1;
                break;
            case '2':
                $data['txt_status'] = 'Declined';
                $data['status'] = 2;
                break;
            case '0':
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

