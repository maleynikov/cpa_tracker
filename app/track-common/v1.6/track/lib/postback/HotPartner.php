<?php

class HotPartner {

    public $net = 'HotPartner';
    private $common;
    private $params = array(
        'profit' => 'payout',
        'subid' => 'pl_name',
        'date_add' => 'time',
        'txt_status' => 'status',
        't1' => 'ip',
        't4' => 'offer_name',
        't7' => 'referer',
        'i7' => 'shop_id',
        'i10' => 'teaser_id',
        'i11' => 'partner_id',
        'i12' => 'order_id',
        'i13' => 'partner_id',
        'i14' => 'pl_id',
        't15' => 'gate',
        't16' => 'shop_name',
    );
    private $reg_url = 'http://www.cpatracker.ru/networks/hotpartner';
    private $net_text = 'CPA сеть работает с 2010 года на рынках России, Беларуси и Казахстана. Владеет собственным круглосуточным call-центром, платят вебмастерам по запросу 5 дней в неделю. Сеть специализируется на wow-товарах.';

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
            'description' => 'Вставьте эту ссылку в поле PostBack ссылки в настройках оффера HotPartner.'
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
                $data['txt_status'] = 'Approved';
                $data['status'] = 1;
                break;
            case 'cancel':
                $data['txt_status'] = 'Declined';
                $data['status'] = 2;
                break;
            case 'new':
            case 'toconfirmed':
                $data['txt_status'] = 'Waiting';
                $data['status'] = 3;
            default:
                $data['txt_status'] = 'Unknown';
                $data['status'] = 0;
                break;
        }

        $this->common->process_conversion($data);
    }

}