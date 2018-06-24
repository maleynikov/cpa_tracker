<?php

class Cpagetti {

    public $net = 'Cpagetti';
    private $common;
    private $params = array(
        'date' => 'time',
        't1' => 'ip',
        'profit' => 'money',
        'txt_status' => 'status', // wait, accept, decline, invalid
        'i2' => 'offer',
        'i3' => 'conversion_id',
        'i7' => 'landing',
        'i11' => 'layer',
        't5' => 'sub2',
    );
    private $reg_url = 'https://www.cpatracker.ru/networks/cpagetti';
    private $net_text = 'Товарная партнерская сеть с оплатой за подтвержденную заявку, актуальные предложения по самым популярным тематикам на широкую аудиторию. Всегда адекватная техническая поддержка, быстрый обзвон рекламодателями и конкурентные выплаты.';

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

        $return = array('reg_url' => $this->reg_url, 'net_text' => $this->net_text);

        $return = array(
            'id' => 0,
            'url' => $url,
            'description' => 'Вставьте эту ссылку в поле PostBack ссылки в настройках оффера Cpagetti.'
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
        $data['txt_param20'] = 'rub';
        $data['type'] = 'sale';
        unset($data['net']);
        //wait, accept, decline, invalid
        switch ($data['txt_status']) {
            case 'accept':
                $data['txt_status'] = 'Approved';
                $data['status'] = 1;
                break;
            case 'decline':
            case 'invalid':
                $data['txt_status'] = 'Declined';
                $data['status'] = 2;
                break;
            default:
                $data['txt_status'] = 'Unknown';
                $data['status'] = 0;
                break;
        }
        $this->common->process_conversion($data);
    }

}