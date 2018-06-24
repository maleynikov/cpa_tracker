<?php

class ActionPay {

    public $net = 'ActionPay';
    private $common;
    private $params = array(
        'subid' => 'subaccount',
        'profit' => 'payment',
        'i1' => 'aim',
        'i2' => 'offer',
        'i3' => 'apid',
        'i5' => 'time',
        'i7' => 'landing',
        'i8' => 'source',
        't9' => 'uniqueid'
    );
    private $reg_url = 'http://www.cpatracker.ru/networks/actionpay';
    private $net_text = 'Одна из старейших партнерских сетей рунета. Быстрые выплаты, удобный интерфейс пользователя, отзывчивый саппорт. Основные тематики: магазины одежды, банки и кредиты, инфопродукты, онлайн-игры. Офферы из России, Украины, Казахстана и Молдовы, есть предложения для зарубежного трафика. Прекрасная сеть для долгосрочного сотрудничества.';

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
            'description' => 'Вставьте эту ссылку в поле "Постбэк - Создание"',
            'url' => $url . '&status=created'
        ));
        array_push($return, array(
            'id' => 1,
            'description' => 'Вставьте эту ссылку в поле "Постбэк - Принятие"',
            'url' => $url . '&status=approved'
        ));
        array_push($return, array(
            'id' => 2,
            'description' => 'Вставьте эту ссылку в поле "Постбэк - Отклонение"',
            'url' => $url . '&status=declined'
        ));


        $return = $return + array('reg_url' => $this->reg_url, 'net_text' => $this->net_text);


        return $return;
    }

    function process_conversion($data_all = array()) {
        $this->common->log($this->net, $data_all['post'], $data_all['get']);
        $data = $this->common->request($data_all);
        $data['network'] = $this->net;
        if (!isset($data['date_add'])) {
            $data['date_add'] = date('Y-m-d H:i:s');
        }
        unset($data['net']);


        switch ($data['status']) {
            case 'approved':
                $data['txt_status'] = 'Approved';
                $data['status'] = 1;
                break;
            case 'declined':
                $data['txt_status'] = 'Declined';
                $data['status'] = 2;
                break;
            case 'created':
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
