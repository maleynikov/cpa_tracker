<?php

class Clickdealer {

    public $net = 'Clickdealer';
    private $common;
    private $params = array(
        'profit' => 'price',
        'subid' => 's2',
        'i2' => 'oid',
        'i3' => 'tid',
        'i10' => 'cid',
        'i11' => 'affid',
        't4' => 'campid',
        't6' => 'leadid',
        't16' => 's1',
        't17' => 's3',
        't18' => 's4',
        't19' => 's5',
    );
    private $reg_url = 'http://www.cpatracker.ru/networks/clickdealer';
    private $net_text = 'Международная партнерская сеть с оплатой за результат. Более 5 тысяч офферов, большой выбор мобильных приложений с оплатой за установку, сервисы знакомств, интернет-магазины, дейтинг и путешествия.';

    function __construct() {
        $this->common = new common($this->params);
    }

    function get_links() {
        $url = tracklink() . '/p.php?n=' . $this->net;

        foreach ($this->params as $name => $value) {
            $url .= '&' . $name . '=#' . $value . '#';
        }

        $code = $this->common->get_code();
        $url .= '&ak=' . $code;

        $return = array(
            'id' => 0,
            'url' => $url,
            'description' => 'Вставьте эту ссылку в поле PostBack ссылки в настройках оффера Clickdealer.'
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
        $data['type'] = 'sale';
        $data['txt_param20'] = 'usd';
        $data['txt_status'] = 'Approved';
        $data['status'] = 1;
        unset($data['net']);

        $this->common->process_conversion($data);
    }

}