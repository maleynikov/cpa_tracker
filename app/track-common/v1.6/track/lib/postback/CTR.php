<?php

class CTR {

    public $net = 'CTR';
    private $common;
    private $params = array(
        'subid' => 'sub_id',
        'profit' => 'payment',
        'date_add' => 'time',
        'status' => 'status',
        'txt_status' => 'status_name',
        't1' => 'ip',
        't4' => 'utm_campaign',
        't6' => 'utm_content',
        't7' => 'utm_source',
        't9' => 'country',
        'i2' => 'offer_id',
        'i3' => 'order_id',
        'i12' => 'out_order_id',
    );
    private $reg_url = 'http://www.cpatracker.ru/networks/ctr';
    private $net_text = 'Позволит вам отслеживать эффективные каналы трафика и увеличивать конверсию и заработок';

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
            'description' => 'Вставьте эту ссылку в поле PostBack ссылки в настройках оффера CTR.'
        );

        return array(
            0 => $return,
            'reg_url' => $this->reg_url,
            'net_text' => $this->net_text
        );
    }

    function process_conversion($data_all = array()) {
        $this->common->log($this->net, $data_all['post'], $data_all['get']);
        $data = $this->common->request($data_all);
        $data['network'] = $this->net;
        //$output_data['status'] = 1;
        $this->common->process_conversion($data);
    }

}