<?php

class SalesDoubler {

    public $net = 'SalesDoubler';
    private $common;
    private $params = array(
        'subid' => 'TRANS_ID',
        'profit' => 'AFF_REV',
        'status' => 'status',
        'f1' => 'SALE_AMOUNT',
        't4' => 'CAMPAIGN',
        't7' => 'SOURCE',
        't8' => 'PROMO',
        't9' => 'TID1',
        't10' => 'TID2',
        'i3' => 'CONVERSION_ID',
    );
    private $reg_url = 'http://www.cpatracker.ru/networks/salesdoubler';
    private $net_text = 'Украинская CPA-сеть с проверенными офферам, которые тщательно отбираются и тестируются представителями сети. Собственная платформа, отзывчивая техническая поддержка, много эксклюзивных рекламодателей с хорошей конверсией. Основные тематики: интернет-магазины, образовательные услуги, потребительские кредиты.';

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

        $return = array();

        array_push($return, array(
            'id' => 0,
            'description' => 'Вставьте эту ссылку в поле "Постбэк - В ожидании"',
            'url' => $url . '&status=pending'
        ));
        array_push($return, array(
            'id' => 1,
            'description' => 'Вставьте эту ссылку в поле "Постбэк - Принято"',
            'url' => $url . '&status=approved'
        ));
        array_push($return, array(
            'id' => 2,
            'description' => 'Вставьте эту ссылку в поле "Постбэк - Отклонено"',
            'url' => $url . '&status=rejected'
        ));


        $return = $return + array('reg_url' => $this->reg_url, 'net_text' => $this->net_text);


        return $return;
    }

    function process_conversion($data_all = array()) {
        $this->common->log($this->net, $data_all['post'], $data_all['get']);
        $data = $this->common->request($data_all);
        $data['network'] = $this->net;
        $data['txt_param20'] = 'uah';

        if (!isset($data['date_add'])) {
            $data['date_add'] = date('Y-m-d H:i:s');
        }
        unset($data['net']);

        switch ($data['status']) {
            case 'approved':
                $data['txt_status'] = 'Approved';
                $data['status'] = 1;
                break;
            case 'rejected':
                $data['txt_status'] = 'Declined';
                $data['status'] = 2;
                break;
            case 'pending':
                $data['txt_status'] = 'Created';
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
