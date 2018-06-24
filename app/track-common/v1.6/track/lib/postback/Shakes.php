<?php

class Shakes {

    public $net = 'Shakes';
    private $common;
    private $params = array(
        'profit' => 'cost',
        'subid' => 'sub1',
        'date_add' => 'date', // unix
        'txt_status' => 'status',
        't1' => 'ip',
        't5' => 'sub2',
        'i2' => 'offer',
        'i7' => 'landing',
        'i11' => 'layer',
    );
    private $reg_url = 'http://www.cpatracker.ru/networks/shakes';
    private $net_text = 'Конвертируем ваш трафик в деньги!';

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
            'description' => 'Вставьте эту ссылку в поле PostBack ссылки в настройках оффера Shakes.'
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

        switch ($data['txt_status']) {
            case 'confirm':
                $data['txt_status'] = 'Approved';
                $data['status'] = 1;
                break;
            case 'decline':
            case 'reject':
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