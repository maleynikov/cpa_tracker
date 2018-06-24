<?php

class Adinfo {

    public $net = 'Adinfo';
    private $common;
    private $params = array(
        'profit' => 'commission',
        'subid' => 'sub_id',
        'date_add' => 'lead_time',
        'txt_status' => 'status',
        't1' => 'uip',
        'i2' => 'offer_id',
        'i3' => 'order_id',
        'i4' => 'group_id',
    );
    private $reg_url = 'http://www.cpatracker.ru/networks/adinfo';
    private $net_text = 'Надежная партнерская программа с большим количеством эксклюзивных офферов.';

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
            'description' => 'Вставьте эту ссылку в поле PostBack ссылки в настройках оффера Adinfo.'
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
        $data['txt_param20'] = 'rub';
        unset($data['net']);

        switch ($data['txt_status']) {
            case 'confirmed':
            case 'payed':
                $data['txt_status'] = 'approved';
                $data['status'] = 1;
                break;
            case 'cancel':
                $data['txt_status'] = 'declined';
                $data['status'] = 2;
                break;
            case 'new':
            case 'toconfirmed':
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