<?php

class Himba {

    public $net = 'Himba';
    private $common;
    private $params = array(
        'profit' => 'amount',
        'subid' => 'sub_id',
        'status' => 'status',
        't7' => 'source',
        't16' => 'sub_id2',
        'i1' => 'goal_id',
        'i2' => 'offer_id',
        'i3' => 'adv_sub',
    );
    private $reg_url = 'http://www.cpatracker.ru/networks/himba';
    private $net_text = 'Партнерская сеть, фокусирующаяся на банковских услугах, страховании, кредитовании и образовательных офферах. Чаще всего рекламодатели платят за заполнение анкет, выдачу кредитных карт, оформление страховок или заявок на получение образовательных услуг. Подавляющее большинство трафика принимается со всей территории РФ, но есть офферы, которые принимают посетителей из Москвы и области или отдельных регионов.';

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
            'description' => 'Вставьте эту ссылку в поле PostBack ссылки в настройках оффера Himba.'
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

        $this->common->process_conversion($data);
    }

}

