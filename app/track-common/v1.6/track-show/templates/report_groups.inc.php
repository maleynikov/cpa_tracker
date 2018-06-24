<?php
if (!$include_flag) {
    exit();
}
// Меню фильтров для отчёта

extract($var);

global $params, $source_config, $one_source;

// Параметры отчёта нужны для формирования ссылок
$params = $var['report_params'];

// Формируем ссылку на группировку
function glink($v, $li = false, $name = '') {
    global $group_types, $params;

    // Если параметр уже есть в фильтре - не показываем этот тип группировки
    // Для группиров ads_name убираем campaign_name тоже
    if (array_key_exists($v, $params['filter'][0]) or ($v == 'campaign_name' and array_key_exists('ads_name', $params['filter'][0])))
        return '';

    if ($li) {
        $class = '';
    } else {
        $class = ' class="btn btn-default' . (($v == $params['group_by'] and $params['mode'] != 'popular') ? ' active' : '') . '"';
    }
    $out = '<a href="' . report_lnk($params, array('group_by' => $v, 'mode' => ($params['mode'] == 'lp_offers' ? $params['mode'] : ''))) . '"' . $class . '>' . ($name == '' ? $group_types[$v][0] : $name) . '</a>';
    if ($li)
        $out = '<li>' . $out . '</li>';
    return $out;
}
?><div class='row report_grouped_menu'>
    <div class='col-md-12'>
        <div class="btn-group">
            <?php
            if (count($params['filter'][0]) > 0) { //or $params['mode'] == 'popular'
                echo '<a class="btn btn-default ' . ($params['mode'] == 'popular' ? 'active' : '') . '" href="' . report_lnk($params, array('mode' => 'popular', 'group_by' => '')) . '"' . $class . '>Популярные</a>';
            }

            echo
            glink('out_id') .
            glink('source_name') .
            glink('campaign_name') .
            glink('ads_name') .
            glink('referer') .
            glink('rule_id');
            ?>

<?php if ($group_by == 'out_id') {
    $class = "active";
} else {
    $class = '';
} ?>
                <!--<a href="?act=reports&type=<?php echo _e($type); ?>&subtype=<?php echo $subtype; ?>&group_by=out_id&limited_to=<?php echo _e($limited_to); ?>&from=<?php echo $from ?>&to=<?php echo $to ?>" class="btn btn-default <?php echo $class; ?>">Ссылка</a>-->

            <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    Гео
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <?php
                    echo
                    glink('country', true) .
                    glink('state', true) .
                    glink('city', true) .
                    glink('user_ip', true);
                    echo '<li class="divider"></li>';
                    echo glink('isp', true);
                    ?>
                </ul>
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    Устройство
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
<?php
echo
glink('user_os', true) .
 glink('user_platform', true) .
 glink('user_browser', true);
?>
                </ul>
            </div>


            <?php
            $campaign_params_html = '';
            $click_params_html = '';

            for ($i = 1; $i <= 5; $i++) {
                if (!$var['campaign_params'][$i])
                    continue; // есть ли в наших данных переходы с таким параметром
                $campaign_params_html .= glink('campaign_param' . $i, true);
            }

            $click_params_cnt = 0;

            if (array_key_exists('source_name', $params['filter'][0]) and !empty($source_config[$params['filter'][0]['source_name']]['params'])) {
                $click_params = $source_config[$params['filter'][0]['source_name']]['params'];
                $click_params_cnt = count($click_params);
                $click_params_keys = array_keys($click_params);
            } elseif (!empty($one_source) and !empty($source_config[$one_source]['params'])) {
                $click_params = $source_config[$one_source]['params'];
                $click_params_cnt = count($click_params);
                $click_params_keys = array_keys($click_params);
            }

            for ($i = 1; $i <= 15; $i++) {
                if (!$var['click_params'][$i])
                    continue; // есть ли в наших данных переходы с таким параметром
                if ($i <= $click_params_cnt) { // именованный параметр
                    $click_params_html .= glink('click_param_value' . $i, true, $click_params[$click_params_keys[$i - 1]]['name']);
                } else { // пользовательский параметр
                    $click_params_html .= glink('click_param_value' . $i, true, 'Параметр перехода #' . ($i - $click_params_cnt));
                }
            }

            if ($campaign_params_html != '' or $click_params_html != '') {
                ?><div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        Другие параметры
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">

                        <?php
                        echo $campaign_params_html;

                        // Разделитель, если присутствуют оба раздела
                        if ($campaign_params_html != '' and $click_params_html != '')
                            echo '<li class="divider"></li>';

                        echo $click_params_html;

                        echo '</ul></div>';
                    }
                    ?>

            </div>

        </div> <!-- ./col-md-12 -->
    </div> <!-- ./row -->
    <div class="row">&nbsp;</div>