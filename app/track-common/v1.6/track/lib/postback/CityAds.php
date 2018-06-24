<?php

class CityAds {

    public $net = 'CityAds';
    private $common;
    private $params = array(
        'subid' => 'subaccount',
        'profit' => 'payout',
        'date_add' => 'conversion_date',
        't1' => 'ip',
        't2' => 'ua',
        't3' => 'target_name',
        't4' => 'offer_name',
        't5' => 'click_id',
        't6' => 'wp_name',
        't7' => 'site',
        't8' => 'action_type',
        't9' => 'country',
        't10' => 'city',
        't11' => 'user_browser',
        't12' => 'user_os',
        't13' => 'user_device',
        't20' => 'payout_currency',
        'i1' => 'target_id',
        'i2' => 'offer_id',
        'i3' => 'cpl_id',
        'i4' => 'click_time',
        'i5' => 'event_time',
        'i6' => 'conversion_time',
        'i7' => 'wp_id',
        'i9' => 'payout_id',
    );
    private $reg_url = 'http://www.cpatracker.ru/networks/cityads';
    private $net_text = 'Крупнейшая сеть по работе с онлайн-играми с оплатой за регистрацию. Вы также можете получать процент от платежей привлеченных игроков, что позволяет построить стабильный источник дохода даже при ограниченных ресурсах. Вас ждут более 300 предложений в 26 различных категориях, среди которых информационные продукты, купонные сервисы, товары для детей, банковские услуги и онлайн-кинотеатры.';

    function __construct() {
        $this->common = new common($this->params);
    }

    function get_links() {
        $url = tracklink() . '/p.php?n=' . $this->net;

        $code = $this->common->get_code();
        $url .= '&ak=' . $code;

        $return = array('reg_url' => $this->reg_url, 'net_text' => $this->net_text);

        array_push($return, array(
            'id' => 0,
            'description' => '1. Вставьте эту ссылку в поле <b>Postback URL</b> в CityAds.<br>'
            . '2. Выберите Тип запроса <b>POST</b><br>'
            . '3. Поставьте галочки напротив ВСЕХ переменных',
            'url' => $url . '&status=created'
        ));

        return $return;
    }

    function process_conversion($data_all = array()) {
        $this->common->log($this->net, $data_all['post'], $data_all['get']);
        $input_data = $this->common->request($data_all);
        $output_data = array();
        foreach ($input_data as $name => $value) {
            if ($key = array_search($name, $this->params)) {
                $output_data[$key] = $value;
            }
        }
        $output_data['network'] = $this->net;
        $output_data['status'] = 1;
        $this->common->process_conversion($output_data);
    }

}
