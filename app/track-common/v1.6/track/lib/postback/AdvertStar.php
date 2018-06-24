<?php

class AdvertStar {

    public $net = 'AdvertStar';
    private $common;
    private $params = array(
        'subid' => 'SUB_ID',
        'profit' => 'REV',
        'date_add' => 'START_TIME',
        't6' => 'END_TIME',
        't1' => 'IP',
        't9' => 'GEO',
        'i2' => 'AID',
        'i1' => 'AIM',
        't5' => 'CLICK_ID',
        't6' => 'LEAD_ID',
        't7' => 'SITE_ID',
        'i3' => 'ORDER_ID',
        't16' => 'SUB_ID2',
        't17' => 'SUB_ID3',
        't18' => 'SUB_ID4',
        't19' => 'SUB_ID5',
        't21' => 'ORDER_COMMENT',
        'txt_status' => 'STATUS',
    );
    private $reg_url = 'http://www.cpatracker.ru/networks/advertstar';
    private $net_text = 'Персональное обслуживание крупных партнеров, эксклюзивные условия крупным адвертам с качественным трафиком. Основные тематики сети: браузерные игры, сайты знакомств, интернет-магазины, образовательные офферы. Вас ждут уникальные офферы, собственная партнерская платформа и выплаты до 4 раз в месяц.';

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
            'description' => 'Вставьте эту ссылку в поле PostBack ссылки в настройках оффера AdvertStar.'
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

        unset($data['net']);
        switch ($data['txt_status']) {
            case 1:
                $data['txt_status'] = 'approved';
                $data['status'] = 1;
                break;
            case 2:
                $data['txt_status'] = 'declined';
                $data['status'] = 2;
                break;
            case 0:
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

