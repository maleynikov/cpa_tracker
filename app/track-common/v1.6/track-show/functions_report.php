<?php

function get_visitors_flow_data($filter = '', $offset = 0, $limit = 20, $date = 0) {
    if (empty($date) or !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $timezone_shift_simple = get_current_timezone_shift(true);
        $date = date('Y-m-d', time() + $timezone_shift_simple);
    }

    $timezone_shift = get_current_timezone_shift();

    $filter_str = '';
    if ($filter != '') {
        switch ($filter['filter_by']) {
            case 'hour':
                if (empty($filter['source_name'])) {
                    $where = "(`source_name` = '' OR `source_name` = 'source' OR `source_name` = 'SOURCE' OR `source_name` = '{empty}')";
                } else {
                    $where = "`source_name` = '" . mysql_real_escape_string($filter['source_name']) . "'";
                }
                $filter_str .= " and " . $where . " AND CONVERT_TZ(date_add, '+00:00', '" . _str($timezone_shift) . "') BETWEEN STR_TO_DATE('" . _str($filter['date']) . " " . _str(sprintf('%02d', $filter['hour'])) . ":00:00', '%Y-%m-%d %H:%i:%s') AND STR_TO_DATE('" . _str($filter['date']) . " " . _str(sprintf('%02d', $filter['hour'])) . ":59:59', '%Y-%m-%d %H:%i:%s')";
                break;

            //STR_TO_DATE('2014-12-14 00:00:00', '%Y-%m-%d %H:%i:%s')
            // поиск по названию кампании, объявления, рефереру, SubID, источнику, IP адресу 
            case 'search':
                if (is_subid($filter['filter_value'])) {
                    $filter_str .= " and `subid` LIKE '" . mysql_real_escape_string($filter['filter_value']) . "'";
                    $date = false; // ищем за всё время
                } else {
                    $filter_str .= " and (
							`user_ip` LIKE '" . mysql_real_escape_string($filter['filter_value']) . "' OR
							`campaign_name` LIKE '%" . mysql_real_escape_string($filter['filter_value']) . "%' OR
							`source_name` LIKE '%" . mysql_real_escape_string($filter['filter_value']) . "%' OR
							`referer` LIKE '%" . mysql_real_escape_string($filter['filter_value']) . "%'
						)";
                }
                break;
            //sprintf('%02d', $i)

            default:
                $filter_str .= " and " . mysql_real_escape_string($filter['filter_by']) . "='" . mysql_real_escape_string($filter['filter_value']) . "'";
                break;
        }
    }


    //$date2 = date('Y-m-d', strtotime($date) - (12 * 3600));
    //tbl_clicks.date_add > STR_TO_DATE('" . $date2 . " 00:00:00', '%Y-%m-%d %H:%i:%s')
    $sql = "select SQL_CALC_FOUND_ROWS *, date_format(CONVERT_TZ(tbl_clicks.date_add, '+00:00', '" . _str($timezone_shift) . "'), '%d.%m.%Y %H:%i') as dt, timediff(NOW(), tbl_clicks.date_add) as td from tbl_clicks 
		where 1 
		{$filter_str}
		" . ($date ? "and CONVERT_TZ(tbl_clicks.date_add, '+00:00', '" . _str($timezone_shift) . "') between STR_TO_DATE('" . $date . " 00:00:00', '%Y-%m-%d %H:%i:%s') and STR_TO_DATE('" . $date . " 23:59:59', '%Y-%m-%d %H:%i:%s')" : '' ) . "
		order by date_add desc limit $offset, $limit";

    $result = db_query($sql) or die(mysql_error());
    $arr_data = array();

    $q = "SELECT FOUND_ROWS() as `cnt`";
    $total = ap(mysql_fetch_assoc(db_query($q)), 'cnt');

    while ($row = mysql_fetch_assoc($result)) {
        $row['td'] = get_relative_mysql_time($row['td']);
        $arr_data[] = $row;
    }
    //dmp($arr_data);

    return array($total, $arr_data);
}

function sdate($d, $today = true) {
    $d = strtotime($d);
    if ((empty($d) and $today) or date('Y-m-d') == date('Y-m-d', $d)) {
        return 'сегодня';
    } elseif (date('Y-m-d') == date('Y-m-d', $d + 86400)) {
        return 'вчера';
    } else {
        $months = array(
            '01' => "января",
            '02' => "февраля",
            '03' => "марта",
            '04' => "апреля",
            '05' => "мая",
            '06' => "июня",
            '07' => "июля",
            '08' => "августа",
            '09' => "сентября",
            '10' => "октября",
            '11' => "ноября",
            '12' => "декабря",
        );
        return date('j', $d) . ' ' . $months[date('m', $d)] . ' ' . date('Y', $d);
    }
}

function get_clicks_rows($params, $start = 0, $limit = 0, $campaign_params, $click_params) {

    // Смещение часового пояса
    $timezone_shift = get_current_timezone_shift();

    // Применяем фильтры
    if (!empty($params['filter'][0]) or !is_array($params['filter'][0])) {
        $tmp = array();
        foreach ($params['filter'][0] as $k => $v) {
            if ($k == 'referer') {
                if ($v == '{empty}') {
                    $tmp[] = "`" . $k . "` = ''";
                } else {
                    $tmp[] = "`" . $k . "` LIKE '%" . mysql_real_escape_string($v) . "%'";
                }
            } elseif ($k == 'ads_name') {
                list($campaign_name, $ads_name) = explode('-', $v);
                $tmp[] = "`campaign_name` = '" . mysql_real_escape_string($campaign_name) . "'";
                $tmp[] = "`ads_name` = '" . mysql_real_escape_string($ads_name) . "'";
            } elseif ($k == 'source_name' and empty($v)) {
                $tmp[] = "(`source_name` = '' or `source_name` = 'source' or `source_name` = 'SOURCE' or `source_name` = '{empty}')";
            } else {
                if ($v == '{empty}') {
                    $v = '';
                }
                $tmp[] = "`" . $k . "` = '" . mysql_real_escape_string($v) . "'";
            }
        }
        if (!empty($tmp)) {
            $where = ' and (' . join(' and ', $tmp) . ')';
        } else {
            $where = '';
        }
    } else {
        $where = '';
    }

    // Дополнительные поля для режима популярных параметров
    if ($params['mode'] == 'popular' or 1) {
        $select = ', out_id, source_name, ads_name, referer, user_os, user_ip, user_platform, user_browser, country, state, city, isp, campaign_param1, campaign_param2, campaign_param3, campaign_param4, campaign_param5 ';
        for ($i = 1; $i <= 15; $i++) {
            $select .= ', click_param_value' . $i . ' ';
        }
    } else {
        $select = '';
    }

    if ($timezone_shift == '+00:00') {
        $time_add_fld = 'date_add';
    } else {
        $time_add_fld = "CONVERT_TZ(t1.`date_add`, '+00:00', '" . _str($timezone_shift) . "')";
    }

    // Выбираем все переходы за период
    $q = "SELECT SQL_CALC_FOUND_ROWS " . (empty($params['group_by']) ? '' : " " . mysql_real_escape_string($params['group_by']) . " as `name`, ") .
            (($params['group_by'] == $params['subgroup_by'] or empty($params['subgroup_by'])) ? '' : " " . mysql_real_escape_string($params['subgroup_by']) . ", ") .
            "
			1 as `cnt`,
			t1.id,
			t1.source_name,
			UNIX_TIMESTAMP(" . $time_add_fld . ") as `time_add`,
			t1.rule_id,
			t1.out_id,
			t1.parent_id,
			t1.campaign_name,
			t1.click_price,
			t1.is_unique,
			t1.conversion_price_main,
			t1.is_sale,
			t1.is_lead,
			t1.is_parent,
			t1.is_connected " . $select . "
			FROM `tbl_clicks` t1
			WHERE " . $time_add_fld . " BETWEEN STR_TO_DATE('" . $params['from'] . " 00:00:00', '%Y-%m-%d %H:%i:%s') AND STR_TO_DATE('" . $params['to'] . " 23:59:59', '%Y-%m-%d %H:%i:%s')" . $where . (empty($params['where']) ? '' : " and " . $params['where'] ) . "
			ORDER BY t1.id ASC
			LIMIT $start, $limit";

    //echo $q;
    /*
      if($_SERVER['REMOTE_ADDR'] == '178.121.200.233') {
      dmp($params);
      echo $q . '<br /><br />';
      }
     */
    $rs = db_query($q);

    $q = "SELECT FOUND_ROWS() as `cnt`";
    $total = ap(mysql_fetch_assoc(db_query($q)), 'cnt');

    /*
      if($_SERVER['REMOTE_ADDR'] == '178.121.200.233') {
      echo $total . '<br /><br />';
      }
     */
    while ($r = mysql_fetch_assoc($rs)) {
        $rows[$r['id']] = $r;

        // Определяем наличие пользовательских параметров
        for ($i = 1; $i <= 5; $i++) {
            if ($r['campaign_param' . $i] != '') {
                $campaign_params[$i] = 1;
            }
        }

        for ($i = 1; $i <= 15; $i++) {
            if ($r['click_param_value' . $i] != '') {
                $click_params[$i] = 1;
            }
        }
    }
    return array($total, $rows, $campaign_params, $click_params);
}

function php_date_default_timezone_set($GMT) {
    $timezones = array(
        '-12:00' => 'Pacific/Kwajalein',
        '-11:00' => 'Pacific/Samoa',
        '-10:00' => 'Pacific/Honolulu',
        '-09:00' => 'America/Juneau',
        '-08:00' => 'America/Los_Angeles',
        '-07:00' => 'America/Denver',
        '-06:00' => 'America/Mexico_City',
        '-05:00' => 'America/New_York',
        '-04:00' => 'America/Caracas',
        '-03:30' => 'America/St_Johns',
        '-03:00' => 'America/Argentina/Buenos_Aires',
        '-02:00' => 'Atlantic/Azores',
        '-01:00' => 'Atlantic/Azores',
        '+00:00' => 'Europe/London',
        '+01:00' => 'Europe/Paris',
        '+02:00' => 'Europe/Helsinki',
        '+03:00' => 'Europe/Moscow',
        '+03:30' => 'Asia/Tehran',
        '+04:00' => 'Asia/Baku',
        '+04:30' => 'Asia/Kabul',
        '+05:00' => 'Asia/Karachi',
        '+05:30' => 'Asia/Calcutta',
        '+06:00' => 'Asia/Colombo',
        '+07:00' => 'Asia/Bangkok',
        '+08:00' => 'Asia/Singapore',
        '+09:00' => 'Asia/Tokyo',
        '+09:00' => 'Australia/Darwin',
        '+10:00' => 'Pacific/Guam',
        '+11:00' => 'Asia/Magadan',
        '+12:00' => 'Asia/Kamchatka'
    );

    date_default_timezone_set($timezones[$GMT]);

    return date_default_timezone_get();
}

/**
 * Подготовка данных для отчётов:
 * subtype - колонка, по которой группируем данные (то же, что и group_by, если не задан limited_to)
 * limited_to - фильтр по subtype
 * group_by - группировка второго уровня, если задан limited_to
 * type - hourly, daily, monthly с каким шагом собираем статистику
 * from, to - временные рамки, за которые нужна статистика, обязательно в формате Y-m-d H:i:s
 * where - дополнительные условия выборки кликов
 * mode - режим выборки и группировки: offers, landings, lp_offers
 */
function get_clicks_report_grouped2($params) {
    global $group_types;

    // Флаги существующих параметров
    $campaign_params = array(
        1 => 0, 0, 0, 0, 0
    );

    $click_params = array(
        1 => 0, 0, 0, 0, 0,
        0, 0, 0, 0, 0,
        0, 0, 0, 0, 0
    );

    // По временным промежуткам
    $date_formats = array(
        'hour' => 'H', // Y-m-d
        'day' => 'Y-m-d',
        'month' => 'm.Y'
    );

    $groups = array(
        '00' => 'click',
        '01' => 'lead',
        '10' => 'sale',
        '11' => 'sale_lead'
    );

    // Смещение часового пояса
    $timezone_shift = get_current_timezone_shift();
    $timezone_shift_sec = get_current_timezone_shift(true);

    $timezone_backup = date_default_timezone_get();

    //date_default_timezone_set("GMT");
    // Поправка на разницу времени PHP и Базы 
    $timezone_shift_sec += strtotime(mysql_now()) - time();

    $rows = array(); // все клики за период
    $data = array(); // сгруппированные данные
    $data2 = array();
    $arr_dates = array(); // даты для отчёта

    if ($params['part'] == 'month') {
        $arr_dates = getMonthsBetween($params['from'], $params['to']);
    } elseif ($params['part'] == 'day') {
        $arr_dates = getDatesBetween($params['from'], $params['to']);
    } elseif ($params['part'] == 'hour') {
        $arr_dates = getHours24();
    }

    global $pop_sort_by, $pop_sort_order;
    $pop_sort_by = 'cnt';
    $pop_sort_order = 1;

    // Режим показов конвертаций, все, только действия, только продажи, только лиды, без конвертаций.
    // В отчете "популярных параметров" этот фильтр работает ТОЛЬКО как параметр сортировки, в других режимах как условие для WHERE

    if ($params['conv'] != 'all') {
        if ($params['mode'] == 'popular') {
            if ($params['conv'] == 'sale') {
                $pop_sort_by = 'sale';
            } elseif ($params['conv'] == 'lead') {
                $pop_sort_by = 'lead';
            } elseif ($params['conv'] == 'act') {
                $pop_sort_by = 'act';
            } elseif ($params['conv'] == 'none') {
                $pop_sort_by = $params['col'];
                $pop_sort_order = -1;
            }
        } else {
            /*
              Если так сделать - то не посчитаются клики
              if($params['conv'] == 'sale') {
              $params['where'] = '`is_sale` = 1';
              } elseif($params['conv'] == 'lead') {
              $params['where'] = '`is_lead` = 1';
              } elseif($params['conv'] == 'act') {
              $params['where'] = '(`is_sale` = 1 or `is_lead` = 1)';
              } elseif($params['conv'] == 'none') {
              //$params['where'] = '';
              $params['where'] = '`is_sale` = 0 and `is_lead` = 0';
              }
             */
        }
    }

    $parent_clicks = array(); // массив для единичного зачёта дочерних кликов (иначе у нас LP CTR больше 100% может быть)

    $limit = 5000;
    $total = 30000;

    for ($start = 0; $start <= $total; $start += $limit) {
        $rows = array();

        // Получаем порцию данных
        list($total, $rows, $campaign_params, $click_params) = get_clicks_rows($params, $start, $limit, $campaign_params, $click_params);

        // Режим обработки для Landing Page
        // группируем всю информацию с подчинённых переходов на родительские
        if ($params['mode'] == 'lp' or $params['mode'] == '') {
            foreach ($rows as $k => $r) {
                if ($r['parent_id'] > 0) { // ссылка на оффер
                    if (parent_row($r['parent_id'], 'id') == 0) {
                        unset($rows[$k]); // не найден лэндинг, удаляем переход
                        continue;
                    }
                    // не будем считать более одного исходящего с лэндинга
                    $out_calc = isset($parent_clicks[$r['parent_id']]) ? 0 : 1;
                    $parent_clicks[$r['parent_id']] = 1;

                    // исходящие
                    $rows[$r['parent_id']]['out'] += $out_calc;
                }
            }
        }

        if ($params['mode'] == 'lp_offers') {

            foreach ($rows as $k => $r) {
                if ($r['parent_id'] > 0) { // ссылка на оффер
                    // Несём продажи наверх, к лэндингу
                    $rows[$r['parent_id']]['is_sale'] += $r['is_sale'];
                    $rows[$r['parent_id']]['is_lead'] += $r['is_lead'];
                    $rows[$r['parent_id']]['conversion_price_main'] += $r['conversion_price_main'];

                    // А расходы вниз, к офферу
                    $rows[$k]['click_price'] += $rows[$r['parent_id']]['click_price'];

                    // Считаем исходящие для лэндингов
                    $out_calc = isset($parent_clicks[$r['parent_id']]) ? 0 : 1;
                    $parent_clicks[$r['parent_id']] = 1;

                    $rows[$r['parent_id']]['out'] += $out_calc;
                }
            }
        }

        // Фильтры показа
        if (!empty($params['filter'][1])) {
            $parent_clicks2 = array(); // $parent_clicks у нас для исходящих, а тут костыль (
            $rows_new = array(); // сюда будем складывать новые строчки, вместо unset существующих
            foreach ($rows as $k => $v) {

                if ($v['parent_id'] > 0) {
                    if (empty($parent_clicks2[$v['parent_id']])) {
                        $parent_clicks2[$v['parent_id']] = 1;
                    } else {
                        continue;
                    }
                }

                $viz_filter = 1;

                foreach ($params['filter'][1] as $name => $value) {
                    list($cur_val, $parent_val) = explode('|', $value);

                    if ($name == 'referer') {
                        $v[$name] = param_key($v, $name);
                    }

                    if (
                            $params['subgroup_by'] == 'out_id' and (

                            ($parent_val == 0 and ($v[$name] == $cur_val or parent_row($v['parent_id'], $name) == $cur_val))
                            or ($parent_val > 0 and ($v['parent_id'] > 0 and parent_row($v['parent_id'], $name) == $parent_val) and $v[$name] == $cur_val))
                            or ($v[$name] == $cur_val and (empty($parent_val) or $v[$params['group_by']] == $parent_val))
                    ) {
                        /*
                          $lp_offers_valid[$cur_val] = 1;

                          // Сбрасываем parent_id, чтобы оффер у нас был как бы "самостоятельный", без лэндинга. Иначе придётся дорабатывать шаблон отчёта
                          if($parent_val > 0) {
                          $v['parent_id'] = 0;
                          }
                          $rows_new[$k] = $v;
                         */
                        //dmp($v);
                    } else {
                        $viz_filter = 0;
                        break;
                    }
                }

                if ($viz_filter) {
                    //echo '1';
                    $lp_offers_valid[$cur_val] = 1;

                    // Сбрасываем parent_id, чтобы оффер у нас был как бы "самостоятельный", без лэндинга. Иначе придётся дорабатывать шаблон отчёта
                    if ($parent_val > 0) {
                        $v['parent_id'] = 0;
                    }
                    $rows_new[$k] = $v;
                }
            }

            //dmp($rows_new);

            $rows = $rows_new;

            unset($rows_new); // Прибираемся
            unset($parent_clicks2);
        }

        //dmp($rows);
        // Режим популярных значений
        // Вынесен в отдельное условие из-за особой обработки по дням и месяцам
        if ($params['mode'] == 'popular') {

            //$data2 = array();

            foreach ($rows as $r) {
                foreach ($group_types as $k => $v) {
                    $name = param_key($r, $k);

                    $data[$k][$name]['cnt'] += $r['cnt'];
                    $data[$k][$name]['price'] += $r['click_price'];
                    $data[$k][$name]['unique'] += $r['is_unique'];
                    $data[$k][$name]['income'] += $r['conversion_price_main'];
                    $data[$k][$name]['sale'] += $r['is_sale'];
                    $data[$k][$name]['lead'] += $r['is_lead'];
                    $data[$k][$name]['act'] += ($r['is_lead'] + $r['is_sale']);
                    $data[$k][$name]['out'] += $r['out'];

                    // Продажи + Лиды = Действия.
                    $sl = $r['is_sale'] + $r['is_lead'];
                    if ($sl > 2)
                        $sl = 2; // Не более двух на переход

                    $data[$k][$name]['sale_lead'] += $sl;

                    // Если это не общий режим - добавляем информацию о датах
                    if ($params['part'] != 'all') {

                        //$k1 = (trim($r['name']) == '' ? '{empty}' : $r['name']);
                        $k2 = date($date_formats[$params['part']], $r['time_add']);
                        //$k3 = $groups[$r['is_sale'].$r['is_lead']];
                        /*
                          $data2[$k][$name][$k2][$k3]['cnt'] += 1;
                          $data2[$k][$name][$k2][$k3]['cost'] += $r['clicks_price'];
                          $data2[$k][$name][$k2][$k3]['earnings'] += $r['conversions_sum'];
                          $data2[$k][$name][$k2][$k3]['is_parent_cnt'] += $r['is_parent'];
                         */
                        $data2[$k][$name][$k2]['cnt'] += 1;
                        $data2[$k][$name][$k2]['cost'] += $r['clicks_price'];
                        $data2[$k][$name][$k2]['earnings'] += $r['conversions_sum'];
                        $data2[$k][$name][$k2]['is_parent_cnt'] += $r['is_parent'];

                        stat_inc($data2[$k][$name][$k2], $r, $name, $r['name']);
                    }
                }
            }
            // Режим показа группировки офферов и лэндингов
            // Тоже вынесен в отдельное условие из-за особой обработки по дням и месяцам
        } elseif ($params['mode'] == 'lp_offers') {

            $parent_clicks = array(); // массив для единичного зачёта дочерних кликов (иначе у нас LP CTR больше 100% может быть)
            // Вся статистика, без разбиения по времени
            foreach ($rows as $r) {

                $k = param_key($r, $params['group_by']);
                $name = param_val($r, $params['group_by']);

                if (!isset($data[$k])) {
                    $data[$k] = array(
                        'id' => $k,
                        'name' => $name,
                        'price' => 0,
                        'unique' => 0,
                        'income' => 0,
                        'direct' => 0,
                        'sale' => 0,
                        'lead' => 0,
                        'out' => 0,
                        'cnt' => 0,
                        'sale_lead' => 0,
                    );
                }

                // Продажи + Лиды = Действия. 
                $r['sale_lead'] = $r['is_sale'] + $r['is_lead'];
                if ($r['sale_lead'] > 2)
                    $r['sale_lead'] = 2; // Не более одного на переход







                    
// Подчиненные связи будут формироваться не по parent_id перехода,
                // а через другие параметры этого перехода (например через источники, с которых пришли)
                // Лэндинг 1
                // ├ Источник 1
                // └ Источник 2

                if ($params['subgroup_by'] != $params['group_by']) {

                    if ($r['parent_id'] == 0) {
                        $k1 = param_key($r, $params['subgroup_by']);
                        $r['name'] = param_val($r, $params['subgroup_by']);

                        // Общая часть статистики
                        stat_inc($data[$k]['sub'][$k1], $r, $k1, $r['name']);

                        // Выдаём офферу разрешение на показ (тут ведь у нас лэндинги, просто так не покажем)
                        $lp_offers_valid[$k] = 1;

                        // Информация о датах 
                        if ($params['part'] != 'all') {
                            $timekey = date($date_formats[$params['part']], $r['time_add']);

                            stat_inc($data[$k]['sub'][$k1][$timekey], $r, $k1, $r['name']);
                        }
                    } else {
                        // Будем считать исходящий только если у этого родителя его ещё нет
                        $r['cnt'] = isset($parent_clicks[$r['parent_id']]) ? 0 : 1;
                        $parent_clicks[$r['parent_id']] = 1;

                        // Отмечаем исходящий для лэндинга
                        if ($r['cnt']) {
                            $parent_row = parent_row($r['parent_id']);
                            $k0 = param_key($parent_row, $params['group_by']);

                            $data[$k0]['out'] += 1;
                        }

                        continue;
                    }
                }

                // Подчиненные связи будут формироваться по parent_id перехода
                // Лэндинг 1
                // ├ Оффер 1
                // └ Оффер 2

                if ($r['parent_id'] > 0) {
                    // Будем считать исходящий только если у этого родителя его ещё нет
                    $r['cnt'] = isset($parent_clicks[$r['parent_id']]) ? 0 : 1;
                    $parent_clicks[$r['parent_id']] = 1;

                    $parent_row = parent_row($r['parent_id']);
                    $k0 = param_key($parent_row, $params['group_by']);

                    $k1 = param_key($r, $params['subgroup_by']);
                    $name = param_val($r, $params['subgroup_by']);

                    stat_inc($data[$k0]['sub'][$k1], $r, $k1, $name);

                    // Отмечаем исходящий для лэндинга
                    if ($r['cnt']) {
                        $data[$k0]['out'] += 1;
                    }

                    $data[$k]['order'] = 1;

                    // Выдаём офферу разрешение на показ
                    $lp_offers_valid[$k0] = 1;
                    $lp_offers_valid[$k1] = 1;

                    // Запрошена информация по дням
                    if ($params['part'] != 'all') {

                        $k2 = date($date_formats[$params['part']], $r['time_add']);

                        $id = param_key($r, $params['subgroup_by']);
                        $name = param_val($r, $params['subgroup_by']);

                        stat_inc($data[$k0]['sub'][$k1][$k2], $r, $id, $name);
                    }

                    // Обычный инкремент статистики
                } else {
                    stat_inc($data[$k], $r, $k, $name);

                    // Информация о датах
                    if ($params['part'] != 'all') {
                        $timekey = date($date_formats[$params['part']], $r['time_add']);
                        stat_inc($data[$k][$timekey], $r, $k, $name);
                    }
                }
            }

            //dmp($data);
            /*             * ********** */
        } else {
            // Данные выбраны, начинаем группировку
            // Статистика за весь период
            if ($params['part'] == 'all') {

                $parent_clicks = array(); // массив для единичного зачёта дочерних кликов (иначе у нас LP CTR больше 100% может быть)
                // Вся статистика, без разбиения по времени

                foreach ($rows as $r) {
                    $k = param_key($r, $params['group_by']);
                    $name = param_val($r, $params['group_by']);

                    // Продажи + Лиды = Действия. 
                    $r['sale_lead'] = $r['is_sale'] + $r['is_lead'];
                    if ($r['sale_lead'] > 2)
                        $r['sale_lead'] = 2; // Не более одного на переход

                    stat_inc($data[$k], $r, $k, $name);
                }
                /*
                  if($_SERVER['REMOTE_ADDR'] == '178.121.200.233') {
                  // echo $total . '<br /><br />';
                  dmp($rows);
                  } */

                // Статистика по дням
            } else {
                foreach ($rows as $r) {
                    $k1 = param_key($r, $params['group_by']);

                    $timekey = date($date_formats[$params['part']], $r['time_add']);

                    stat_inc($data[$k1], $r, $k1, $r['name']);
                    stat_inc($data[$k1][$timekey], $r, $k1, $r['name']);
                }
            }
        } // Стандартный режим
    } // Цикличный сбор данных из БД
    // ----------------------------------------
    // Постобработка, когда ВСЕ данные получены
    // ----------------------------------------
    //if($params['part'] == 'all') {
    if ($params['mode'] == 'popular') {

        if ($params['group_by'] != '') {

            foreach ($data as $k => $v) {
                if ($k != $params['group_by']) {
                    unset($data[$k]);
                } else {
                    $total = sum_arr($v, 'cnt');
                    foreach ($data[$k] as $k1 => $v1) {
                        $data[$k][$k1]['total'] = $total;
                    }
                }
            }
        } else {
            //dmp($data);
            foreach ($data as $k => $v) {
                uasort($v, 'params_order');

                $data[$k] = current($v);

                // Для этого режима нам нужны ТОЛЬКО нулевые конвертации
                if ($params['conv'] == 'none' and $data[$k][$params['col']] != 0) {
                    unset($data[$k]);
                    continue;
                }

                $data[$k]['total'] = sum_arr($v, 'cnt');
                $data[$k]['name'] = $k;
                $data[$k]['popular'] = current(array_keys($v));
            }
        }
        /*
          if($_SERVER['REMOTE_ADDR'] == '178.121.255.182') {
          dmp($data);
          dmp($data2);
          //echo $k .' - '. $name . '<br />';
          }
         */
        // Убираем из популярных "не определено", отфильрованные значения и если 100%

        foreach ($data as $k => $r) {
            if ($r['popular'] == $group_types[$r['name']][1]
                    or $r['popular'] == ''
                    or !empty($params['filter'][0][$r['name']])
                    or ($r['cnt'] == $r['total'] or round($r['cnt'] / $r['total'] * 100) == 100)
            ) {
                unset($data[$k]);
            }
        }

        if ($params['part'] != 'all') {
            $data3 = array();
            foreach ($data as $k => $v) {

                //$name = $group_types[$v['name']][1];
                $name = $v['name'];

                $data3[$name] = $data2[$k][$v['popular']];
                $data3[$name]['popular'] = $v['popular'];
            }
            unset($data2);
            $data = $data3;
        }
    } else {
        // Убираем строчки с конверсиями
        $data = conv_filter($data, $params['conv']);

        // "Один источник" - если группировка по источнику и он у нас один, то берём его именованные параметры
        if ($params['group_by'] == 'source_name' and count($data) == 1) { //
            global $one_source;
            $one_source = current(array_keys($data));
        }
    }
    //}

    if ($part != 'all') {
        // Оставляем только те даты, за которые есть данные
        $arr_dates = strip_empty_dates($arr_dates, $data);
    }

    // Особая сортировка для режима lp_offers, офферы с прямыми переходами в конце
    if ($params['mode'] == 'lp_offers') { //and $params['part'] == 'all'
        uasort($data, 'lp_order');

        //dmp($data); //111
        $lp_offers_valid = array_keys($lp_offers_valid);
        $ln = 0; // номер лэндинга - условное значение, необходимое для группировки при сортировке таблицы с подчиненными офферами. У лэндинга и его офферов должен быть один номер, уникальный для этой группы
        foreach ($data as $k => $v) {
            if ((!in_array($k, $lp_offers_valid) and $v['direct'] == 0) or $v['cnt'] == 0) {
                unset($data[$k]);
            } else {
                $data[$k]['ln'] = $ln;
                if (!empty($data[$k]['sub'])) {
                    foreach ($data[$k]['sub'] as $k0 => $v0) {
                        $data[$k]['sub'][$k0]['ln'] = $ln;
                    }
                }
                $ln++;
            }
        }
    }

    // Удаляем страницы, у которых нет исходящих (Это не Лэндинги)
    if (($params['mode'] == 'lp' and $params['part'] == 'all') and empty($parent_val)) {
        foreach ($data as $k => $v) {
            if (empty($v['out']) and empty($v['direct'])) {
                unset($data[$k]);
            }
        }
    }

    // cсылка "Другие", для Площадки, параметров ссылки и перехода 
    // если не выбран какой-то определенный лэндинг.
    //
		
		global $pop_sort_by, $pop_sort_order;
    $max_sub = 50; // После скольки объектов начинаем сворачивать

    if ($params['no_other'] == 0
            and !isset($params['filter'][1]['out_id'])
            and (

            (($params['subgroup_by'] == 'referer' and $params['mode'] == 'lp_offers') or ($params['group_by'] == 'referer' and $params['mode'] == ''))
            or strstr($params['subgroup_by'], 'click_param_value') !== false)
    ) {


        if ($params['mode'] == 'lp_offers') {
            foreach ($data as $k => &$v) {
                if (isset($v['sub']) and count($v['sub']) > $max_sub) {
                    uasort($v['sub'], 'sub_order');

                    $sub = array_slice($v['sub'], $max_sub);
                    $v['sub'] = array_slice($v['sub'], 0, $max_sub);

                    $other = array(); // Сюда мы соберём всю статистику "других"
                    foreach ($sub as $sub_row) {
                        stat_inc($other, $sub_row, -1, 'Другие');
                    }
                    $v['sub'][-1] = $other;

                    //dmp($other);
                }
            }
        } elseif (($params['mode'] == '' or $params['mode'] == 'lp') and count($data) > $max_sub) {


            $pop_sort_by = 'cnt';
            $pop_sort_order = 1;

            uasort($data, 'params_order');

            $other_arr = array_slice($data, $max_sub);
            foreach ($other_arr as $row) {
                if (($params['mode'] == '' and empty($row['out']))
                        or ($params['mode'] == 'lp' and !empty($row['out']))
                ) {
                    foreach ($row as $k => $v) {
                        if (is_array($v)) {
                            foreach ($v as $d => $vd) {
                                $other[$k][$d] += $vd;
                            }
                        } else {
                            $other[$k] += $v;
                        }
                    }
                }
            }

            $data = array_slice($data, 0, $max_sub);

            $other['id'] = -1;
            $other['name'] = 'Другие';
            $data[-1] = $other;
        }
    }

    //date_default_timezone_set($timezone_backup);

    return array(
        'data' => $data,
        'dates' => $arr_dates,
        'click_params' => $click_params,
        'campaign_params' => $campaign_params
    );
}

// Сортировка по кликам 
function sub_order($a, $b) {
    if ($a['cnt'] == $b['cnt']) {
        return 0;
    }
    return ($a['cnt'] < $b['cnt']) ? 1 : -1;
}

// Суммирует значения из двухмерного массива
function sum_arr($arr, $param = 'cnt') {
    $summ = 0;
    foreach ($arr as $v) {
        $summ += $v[$param];
    }
    return $summ;
}

// Сортировка лэндингов
function lp_order($a, $b) {
    if ($a['order'] == $b['order']) {
        return 0;
    }
    return ($a['order'] < $b['order']) ? -1 : 1;
}

// Сортировка по конверсии
function params_order($a, $b) {
    global $pop_sort_by, $pop_sort_order;

    $k1 = $a[$pop_sort_by];
    $k2 = $b[$pop_sort_by];
    if ($k1 == $k2) {
        // Вторичная сортировка по переходам
        if ($pop_sort_by != 'cnt') {
            $k1 = $a['cnt'];
            $k2 = $b['cnt'];
            if ($k1 == $k2) {
                return 0;
            }
            return ($k1 < $k2) ? 1 : -1;
        } else {
            return 0;
        }
    }
    return ($k1 < $k2) ? $pop_sort_order * 1 : $pop_sort_order * -1;
}

/* Генерируем данные с возможностью переключения колонок (дневной режим) 
 * emp  - показывать пустую ячейку, если значение равно 0
 * sub  - данные иерархически организованы (отчёт "целевые страницы")
 * cols - предустановленный набор колонок для загрузки, двухмерный массив вида:
 * $cols = array(
 * 	'act'   => array('cnt', 'conversion_a', 'roi', 'epc', 'profit'),
 * 	'sale'  => array('cnt', 'conversion',   'roi', 'epc', 'profit'),
 * 	'lead'  => array('cnt', 'conversion_l', 'cpl')
 * );
 */

function get_clicks_report_element2($data, $emp = true, $sub = true, $cols = false) {
    global $report_cols;
    $out = array();

    // Используем только пользовательские колонки, если они определены
    if ($cols and is_array($cols)) {
        $data_cols = array();
        foreach ($cols as $type => $type_cols) {
            foreach ($type_cols as $col) {
                if (!isset($data_cols[$col])) {
                    $data_cols[$col] = $report_cols[$col];
                }
            }
        }
    } else {
        $data_cols = $report_cols; // все доступные колонки
    }

    foreach ($data_cols as $col => $options) {

        // С иерархически организованными данными используется функция sortdata для корректной сортировки по всем уровням
        if ($sub) {
            $out[] = '<span class="timetab sdata ' . $col . '">' . sortdata($col, $data, $emp) . '</span>';
        } else {
            $func = 't_' . $col;
            $out[] = '<span class="timetab sdata ' . $col . '">' . $func($data, true, $emp) . '</span>';
        }
    }
    return join('', $out);
}

/* /v2 */

function get_sales($from, $to, $days, $month) {
    $timezone_shift = get_current_timezone_shift();
    $q = "SELECT *, `cnv`.`date_add` as `date` 
        FROM `tbl_conversions` `cnv` 
        LEFT JOIN `tbl_clicks` `clc` ON `cnv`.`subid` = `clc`.`subid`  
        WHERE (`cnv`.`status` = 0 or `cnv`.`status` = 1)
            AND CONVERT_TZ(`cnv`.`date_add`, '+00:00', '" . _str($timezone_shift) . "') BETWEEN STR_TO_DATE('" . _str($from) . " 00:00:00', '%Y-%m-%d %H:%i:%s') 
            AND STR_TO_DATE('" . _str($to) . " 23:59:59', '%Y-%m-%d %H:%i:%s') 
        ORDER BY `cnv`.`date_add` ASC";
    $rs = db_query($q);

    if (mysql_num_rows($rs) == 0) {
        return false;
    }

    $data = array();
    $return = array();

    while ($f = mysql_fetch_assoc($rs)) {
        $data[] = $f;
    }

    foreach ($data as $row) {
        if ($row['source_name'] == '') {
            $row['source_name'] = '_';
        }
        foreach ($days as $day) {
            $d = (!$month) ? date('d.m', strtotime($day)) : $day;
            if ($d == date((!$month) ? 'd.m' : 'm.Y', strtotime($row['date']))) {
                $return[$row['source_name']][$d]++;
            }
        }
    }

    return $return;
}

/*
 * Убираем даты, за которые нет данных
 */

function strip_empty_dates($arr_dates, $arr_report_data, $mode = 'date') {
    $dates = array();
    $begin = false;

    if ($mode == 'group') {
        $arr_report_data = current($arr_report_data);
    }

    foreach ($arr_report_data as $source_name => $data) {
        foreach ($data as $k => $v) {
            if ($mode == 'month')
                $k = date('m.Y', strtotime($k));
            $dates[$k] = 1;
        }
    }

    foreach ($arr_dates as $k => $v) {
        if (!isset($dates[$v]) and !$begin)
            unset($arr_dates[$k]);
        else
            $begin = true;
    }
    return $arr_dates;
}

/*
 * Готовит к выводу параметры перехода
 */

function params_list($row, $name, $source_name = '') {
    global $source_config;

    // Если есть фильтр по источнику - считаем именованные параметры
    if (!empty($source_config[$source_name]['params'])) {
        $named_params = $source_config[$source_name]['params'];
        $named_params_cnt = count($named_params);
        $named_params_keys = array_keys($named_params);
    } else {
        $named_params_cnt = 0;
    }

    $out = array();
    for ($i = 1; $i <= 15; $i++) {
        if (empty($row[$name . $i]))
            continue;

        list($param_name, $param_val) = click_param($i, $row[$name . $i], $source_name);
        /*
          if($i <= $named_params_cnt) {
          $param_name = $named_params[$named_params_keys[$i]]['name'];
          } else {
          $param_name = $i - $named_params_cnt;
          }
         */
        $out[] = $param_name . ': ' . $param_val;
    }
    /*
      $i = 1;

      while(isset($row[$name.$i])) {
      if($row[$name.$i] != '') {
      $out[] = $i.': '.$row[$name.$i] . '<br />';
      }
      $i++;
      } */
    return $out;
}

/*
 * Функция вывода кнопок статистики в интерфейс
 */

function type_subpanel() {
    global $type;

    // Кнопки типов статистики
    $type_buttons = array(
        'all_stats' => 'Все',
        'daily_stats' => 'По дням',
        'monthly_stats' => 'По месяцам',
    );

    $out = '<div class="btn-group">';
    foreach ($type_buttons as $k => $v) {
        $out .= '<a href="?act=reports&type=' . $k . '&subtype=' . $_GET['subtype'] . '" type="button" class="btn btn-default ' . ($type == $k ? 'active' : '') . '">' . $v . '</a>';
    }
    $out .= '</div>';
    return $out;
}

// Литералы для группировок
$group_types = array(
    'out_id' => array('Оффер', 'Без оффера', 'Офферы'),
    'rule_id' => array('Ссылка', 'Без ссылки', 'Ссылки'),
    'source_name' => array('Источник', 'Не определён', 'Источники'),
    'campaign_name' => array('Кампания', 'Не определена', 'Кампании'),
    'ads_name' => array('Объявление', 'Не определено', 'Объявления'),
    'referer' => array('Площадка', 'Не определена', 'Площадки'),
    'user_os' => array('ОС', 'Не определена', 'ОС'),
    'user_platform' => array('Платформа', 'Не определена', 'Платформы'),
    'user_browser' => array('Браузер', 'Не определен', 'Браузеры'),
    'country' => array('Страна', 'Не определена', 'Страны'),
    'state' => array('Регион', 'Не определен', 'Регионы'),
    'city' => array('Город', 'Не определен', 'Города'),
    'user_ip' => array('IP адрес', 'Не определен', 'IP адреса'),
    'isp' => array('Провайдер', 'Не определен', 'Провайдеры'),
    'campaign_param1' => array('Параметр ссылки #1', 'Не определен', 'Параметр ссылки #1'),
    'campaign_param2' => array('Параметр ссылки #2', 'Не определен', 'Параметр ссылки #2'),
    'campaign_param3' => array('Параметр ссылки #3', 'Не определен', 'Параметр ссылки #3'),
    'campaign_param4' => array('Параметр ссылки #4', 'Не определен', 'Параметр ссылки #4'),
    'campaign_param5' => array('Параметр ссылки #5', 'Не определен', 'Параметр ссылки #5'),
    'click_param_value1' => array('Параметр перехода #1', 'Не определен', 'Параметр перехода #1'),
    'click_param_value2' => array('Параметр перехода #2', 'Не определен', 'Параметр перехода #2'),
    'click_param_value3' => array('Параметр перехода #3', 'Не определен', 'Параметр перехода #3'),
    'click_param_value4' => array('Параметр перехода #4', 'Не определен', 'Параметр перехода #4'),
    'click_param_value5' => array('Параметр перехода #5', 'Не определен', 'параметр перехода #5'),
    'click_param_value6' => array('Параметр перехода #6', 'Не определен', 'Параметр перехода #6'),
    'click_param_value7' => array('Параметр перехода #7', 'Не определен', 'Параметр перехода #7'),
    'click_param_value8' => array('Параметр перехода #8', 'Не определен', 'Параметр перехода #8'),
    'click_param_value9' => array('Параметр перехода #9', 'Не определен', 'Параметр перехода #9'),
    'click_param_value10' => array('Параметр перехода #10', 'Не определен', 'Параметр перехода #10'),
    'click_param_value11' => array('Параметр перехода #11', 'Не определен', 'Параметр перехода #11'),
    'click_param_value12' => array('Параметр перехода #12', 'Не определен', 'Параметр перехода #12'),
    'click_param_value13' => array('Параметр перехода #13', 'Не определен', 'Параметр перехода #13'),
    'click_param_value14' => array('Параметр перехода #14', 'Не определен', 'Параметр перехода #14'),
    'click_param_value15' => array('Параметр перехода #15', 'Не определен', 'Параметр перехода #15')
);

/*
 * Ссылка согласно параметрам отчёта
 */

function report_lnk($params, $set = false) {
    if ($set and is_array($set)) {
        foreach ($set as $k => $v) {
            if ($k == 'filter') {
                $k = 'filter_str';
            }
            $params[$k] = $v;
        }
    }


    $tmp = array();

    foreach ($params['filter_str'] as $k => $v) {
        $tmp[] = $k . ':' . $v;
    }
    $vars = array(
        'act' => 'reports',
        'filter' => join(';', $tmp),
        'type' => $params['type'],
        'part' => $params['part'],
        'group_by' => $params['group_by'],
        'subgroup_by' => $params['subgroup_by'],
        'conv' => $params['conv'],
        'mode' => $params['mode'],
        'col' => $params['col'],
        'from' => $params['from'],
        'to' => $params['to'],
        'no_other' => $params['no_other']
    );

    return '?' . http_build_query($vars);
}

/*
 * Формируем параметры отчёта из REQUEST-переменных
 */

function report_options() {
    global $group_types;
    // Дешифруем фильтры
    $tmp_filters = rq('filter');
    $filter = array(0 => array(), 1 => array());
    $filter_str = array();

    if (!empty($tmp_filters)) {
        $tmp_filters = explode(';', $tmp_filters);
        foreach ($tmp_filters as $tmp_filter) {
            list($k, $v, $type) = explode(':', $tmp_filter);
            $type = intval($type);
            if (array_key_exists($k, $group_types)) {
                $filter[$type][$k] = $v;
                $filter_str[$k] = $v . ':' . $type;
            }
        }
    }

    $part = rq('part', 0, 'day');

    // Устанавливаем даты по умолчанию
    switch ($part) {
        case 'month':
            $from = date('Y-m-01', strtotime(get_current_day('-6 months')));
            $to = date('Y-m-t', strtotime(get_current_day()));
            break;
        default:
            $from = get_current_day('-6 days');
            $to = get_current_day();
            break;
    }

    $group_by = rq('group_by', 0, 'out_id');
    $subgroup_by = rq('subgroup_by', 0, $group_by);
    $conv = rq('conv', 0, 'all');
    $mode = rq('mode', 0, '');
    $col = rq('col', 0, 'act');

    // Если эта группировка уже затронута фильтром - выбираем следующую по приоритету
    // Примечание: в отчёте по целевым можно не выбирать
    if ($mode != 'lp') {
        $i = 0;
        $group_types_keys = array_keys($group_types);
        while (!empty($filter) and array_key_exists($group_by, $filter)) {
            $group_by = $group_types_keys[$i];
            $i++;
        }
    }
    /*
      for($i = 0; empty($filter) or array_key_exists($group_by, $filter); $i++) {
      $group_by = $group_types_keys[$i];
      } */

    // Готовим параметры для отдачи
    $v = array(
        'type' => rq('type', 0, 'basic'),
        'part' => rq('part', 0, 'all'),
        'filter' => $filter,
        'filter_str' => $filter_str,
        'group_by' => $group_by,
        'subgroup_by' => $subgroup_by,
        'conv' => $conv,
        'mode' => $mode,
        'col' => $col,
        'from' => rq('from', 4, $from),
        'to' => rq('to', 4, $to),
        'no_other' => rq('no_other', 2)
    );
    return $v;
}

// Набор функций для вычисления и форматирования показателей в отчётах
function t_price($r, $wrap = true, $emp = true) {
    $r['price'] = round($r['price'], 2);
    return currencies_span($r['price'], $wrap);
}

function t_lpctr($r, $wrap = true, $emp = true) {
    if (!empty($r['cnt'])) {
        $out = round($r['out'] / $r['cnt'] * 100, 1);
        return $wrap ? $out . '%' : $out;
    } else {
        return '';
    }
}

function t_income($r, $wrap = true, $emp = true) {
    return currencies_span($r['income'], $wrap);
}

function t_epc($r, $wrap = true, $emp = true) {
    return currencies_span(round2($r['income'] / $r['cnt']), $wrap);
}

function t_profit($r, $wrap = true, $emp = true) {
    return currencies_span(round2($r['income'] - $r['price']), $wrap);
}

function t_roi($r, $wrap = true, $emp = true) {
    $out = round(($r['income'] - $r['price']) / $r['price'] * 100, 1);
    return $wrap ? $out . '%' : $out;
}

function t_conversion($r, $wrap = true, $emp = true) {
    if ($r['sale'] == 0)
        return $wrap ? ($emp ? '' : '0') : 0;
    $out = round2($r['sale'] / $r['cnt'] * 100);
    return $wrap ? $out . '%' : $out;
}

function t_conversion_l($r, $wrap = true, $emp = true) {
    if ($r['lead'] == 0)
        return $wrap ? ($emp ? '' : '0') : 0;
    $out = round2($r['lead'] / $r['cnt'] * 100);
    return $wrap ? $out . '%' : $out;
}

function t_conversion_a($r, $wrap = true, $emp = true) {
    if ($r['act'] == 0)
        return $wrap ? ($emp ? '' : '0') : 0;
    $out = round2($r['act'] / $r['cnt'] * 100);
    return $wrap ? $out . '%' : $out;
}

function t_follow($r, $wrap = true, $emp = true) {
    $out = round($r['out'] / $r['cnt'] * 100, 1);
    return $wrap ? $out . '%' : $out;
}

function t_cps($r, $wrap = true, $emp = true) {
    return currencies_span(round2($r['price'] / $r['sale']), $wrap);
}

function t_cpa($r, $wrap = true, $emp = true) {
    //return currencies_span($r['price'] / $r['act'], $wrap);
    return currencies_span(round2($r['price'] / $r['act']), $wrap);
}

function t_cpl($r, $wrap = true, $emp = true) {
    return currencies_span(round2($r['price'] / $r['lead']), $wrap);
}

function t_repeated($r, $wrap = true, $emp = true) {

    $repeated = $r['cnt'] - $r['unique'];
    //if($repeated < 0 or $repeated == 0) return $wrap ? '' : 0;
    if ($repeated < 0)
        $repeated = 0;

    $repeated = round($repeated / $r['cnt'] * 100, 1);
    return $wrap ? (($emp && $repeated <= 0) ? '' : $repeated . '%') : $repeated;
}

function t_cnt($r, $wrap = true, $emp = true) {
    return empty($r['cnt']) ? ($emp ? '' : '0') : $r['cnt'];
}

function t_sale($r, $wrap = true, $emp = true) {
    if ($r['sale'] == 0)
        return $wrap ? '' : 0;
    return $r['sale'];
}

function t_lead($r, $wrap = true, $emp = true) {
    if ($r['lead'] == 0)
        return $wrap ? '' : 0;
    return $r['lead'];
}

function t_act($r, $wrap = true, $emp = true) {
    if ($r['act'] == 0)
        return ($wrap && $emp) ? '' : 0;
    return $r['act'];
}

function t_cnt_sale($r, $wrap = true, $emp = true) {
    if ($r['sale'] == 0)
        return t_cnt($r, $wrap, $emp);
    return $wrap ? '<b>' . $r['cnt'] . ':' . $r['sale'] . '</b>' : $r['sale'] * 10000000 + $r['cnt'];
}

function t_cnt_lead($r, $wrap = true, $emp = true) {
    if ($r['lead'] == 0)
        return t_cnt($r, $wrap, $emp);
    return $wrap ? '<b>' . $r['cnt'] . ':' . $r['lead'] . '</b>' : $r['lead'] * 10000000 + $r['cnt'];
}

function t_cnt_act($r, $wrap = true, $emp = true) {
    if ($r['act'] == 0)
        return t_cnt($r, $wrap, $emp);
    return $wrap ? '<b>' . $r['cnt'] . ':' . $r['act'] . '</b>' : $r['act'] * 10000000 + $r['cnt'];
}

function t_sale_lead($r, $wrap = true, $emp = true) {
    if ($r['sale_lead'] == 0)
        return $wrap ? '' : 0;
    return $r['sale_lead'];
}

function cur_conv($n, $currency = 'RUB') {
    global $currencies;
    $curr_rates = array(
        'RUB' => $currencies['rub'],
    );
    // Нет такой валюты

    if (array_key_exists($currency, $curr_rates)) {
        return 0;
    }
    return $n * $curr_rates[$currency];
}

function currencies_span($v, $wrap = true) {
    if (!$wrap)
        return $v;
    global $currencies;
    $rub_rate = $currencies['rub'];
    $style = '';
    if (empty($v)) {
        $style = 'style="color:lightgray;font-weight:normal;"';
    } elseif ($v < 0) {
        $style = 'style="color:red;"';
    }
    return '<b><span class="sdata usd" ' . $style . '>' . ($v < 0 ? '-' : '') . '$' . abs($v) . '</span><span class="sdata rub" ' . $style . '>' . round($v * $rub_rate) . 'р.</span></b>';
}

function click_param($n, $val, $source_name) {
    global $source_config;
    if (!empty($source_config[$source_name]['params'])) {
        $named_params = $source_config[$source_name]['params'];
        $named_params_cnt = count($named_params);
        $named_params_keys = array_keys($named_params);
    } else {
        $named_params_cnt = 0;
    }

    if ($n <= $named_params_cnt) {
        $param_name = $named_params[$named_params_keys[$n - 1]]['name'];
        if (!empty($named_params[$named_params_keys[$n - 1]]['list']) and
                !empty($named_params[$named_params_keys[$n - 1]]['list'][$val])) {
            $val = $named_params[$named_params_keys[$n - 1]]['list'][$val];
        }
    } else {
        $param_name = '#' . ($n - $named_params_cnt);
    }
    return array($param_name, $val);
}

// Значение поля для рассчётов, например площадка
// http://site.ru/topic1/page1.html станет site.ru

function param_key($row, $type) {

    if (!is_array($row)) {
        $row = array($type => $row);
    }

    if (trim($row[$type]) != '') {
        // Обрезаем реферер до домена
        if ($type == 'referer') {
            $url = parse_url($row[$type]);
            $out = $url['host'];

            // Для объявления добавляем кампанию
        } elseif ($type == 'ads_name') {
            if ($row[$type] != '' and ($row[$type] != 'ads' or $row['campaign_name'] != 'campaign')) {
                $out = ($row['campaign_name'] . '-' . $row[$type]);
            } else {
                $out = '';
            }
        } elseif ($type == 'campaign_name') {
            if ($row[$type] != 'campaign') {
                $out = $row[$type];
            } else {
                $out = '';
            }
        } elseif ($type == 'out_id') {
            if ($row[$type] == '{empty}') {
                $out = '';
            } else {
                $out = $row[$type];
            }
        } elseif ($type == 'source_name') {
            if ($row[$type] == 'source' or $row[$type] == 'SOURCE') {
                $out = '';
            } else {
                $out = $row[$type];
            }
        } else {
            $out = $row[$type];
        }
    } else {
        $out = '';
    }

    return $out;
}

// Вливаем информацию о переходе в массив статистики 

function stat_inc(&$arr, $r, $id, $name) {
    if (!isset($arr)) {
        $arr = array(
            'id' => $id,
            'name' => $name,
            'price' => 0,
            'unique' => 0,
            'income' => 0,
            'direct' => 0,
            'sale' => 0,
            'lead' => 0,
            'act' => 0,
            'out' => 0,
            'cnt' => 0,
            'sale_lead' => 0,
        );
    }
    $arr['id'] = $id;
    $arr['name'] = $name;
    $arr['sale'] += $r['is_sale'];
    $arr['lead'] += $r['is_lead'];
    $arr['act'] += ($r['is_lead'] + $r['is_sale']);
    $arr['cnt'] += $r['cnt'];
    $arr['price'] += $r['click_price'];
    $arr['unique'] += $r['is_unique'];
    $arr['income'] += $r['conversion_price_main'];
    $arr['direct'] += ($r['rule_id'] == 0 and $r['time_add'] > 1419463566) ? 1 : 0;
    $arr['out'] += $r['is_connected'];
    $arr['sale_lead'] += $r['sale_lead'];
}

// Складываем дневную статистику для подведения итогов по строкам и колонкам
function stat_inc_total($cur_date, $row) {
    global $row_total_data, $column_total_data, $table_total_data;
    foreach ($row as $k => $v) {
        if (is_array($v))
            continue;

        // Служебные колонки ln (landing number) и order не должны суммироваться, но переносятся в итоговую статистику
        if ($k == 'order' or $k == 'ln') {
            $row_total_data[$k] = $v;
            continue;
        }
        $row_total_data[$k] += $v;
        $column_total_data[$cur_date][$k] += $v;
        $table_total_data[$k] += $v;
    }
}

// Значение поля для отображения пользователю, например
// out_id "10" становится названием ссылки "Ссылка 1",
// а источник popunder станет Popunder.ru
// нам нужно обрабатывать рефереров, имена объявлений, специальные параметры

function param_val($row, $type, $source_name = '') {
    global $group_types, $source_config;
    static $outs = array();
    static $links = array();

    $name = '';
    if (is_array($row)) {
        $v = $row[$type];
        $source_name = $row['source_name'];
    } else {
        $v = $row;
    }

    // Ссылка "Другие" для площадок и пользовательских параметров
    if (is_other_link($v, $type)) {
        $name = 'Другие';
    } else {
        if ($type == 'referer') {
            if (substr($v, 0, 4) == 'http' or strstr($v, '/') !== false) {
                $name = parse_url($v);
                $name = $name['host'];
            } else {
                $name = $v;
            }
        } elseif ($type == 'source_name') {
            if ($v == 'source' or $v == 'SOURCE') { // значение по умолчанию
                $name = '';
            } else {
                $name = empty($source_config[$v]['name']) ? $v : $source_config[$v]['name'];
            }
        } elseif ($type == 'ads_name') {
            if ($v != '') {
                $name = is_array($row) ? ($row['campaign_name'] . '-' . $row['ads_name']) : $row;
            }
        } elseif ($type == 'out_id') {
            if (isset($outs[$v])) {
                $name = $outs[$v];
            } else {
                $name = current(get_out_description($v));
                $outs[$v] = $name;
            }
        } elseif ($type == 'rule_id') {
            if (isset($links[$v])) {
                $name = $links[$v];
            } else {
                $name = get_rule_description($v);
                $links[$v] = $name;
            }
        } else {
            // Специальные поля, определённые для источника в виде списка
            if (!empty($source_config[$source_name]['params'])
                    and strstr($type, 'click_param_value') !== false) {
                $n = intval(str_replace('click_param_value', '', $type));
                $i = 1;
                foreach ($source_config[$source_name]['params'] as $param) {
                    if ($i == $n and !empty($param['list'][$v])) {
                        $name = str_replace(' ', '&nbsp;', $param['list'][$v]);
                        return $name;
                    }
                    $i++;
                }
                $name = $v;
            } else {
                $name = $v;
            }
        }
    }

    if (trim($name) == ''
            or $name == '{empty}'
            or ($type == 'campaign_name' and $name == 'campaign')
            or ($type == 'ads_name' and $name == 'campaign-ads'))
        $name = $group_types[$type][1];

    return $name;
}

/*
 * Название параметра (если пользовательский (click_param_value1-15) - зависит от источника)
 */

function param_name($type, $source = '', $only_name = false) {
    global $source_config, $group_types;

    $n = intval(str_replace('click_param_value', '', $type));

    // Если есть фильтр по источнику - считаем именованные параметры
    if (!empty($source) and !empty($source_config[$source]['params'])) {
        $named_params_cnt = count($source_config[$source]['params']);
    } else {
        $named_params_cnt = 0;
    }

    if (strstr($type, 'click_param_value') !== false and $named_params_cnt > 0) {
        $i = 1;
        foreach ($source_config[$source]['params'] as $v) {
            if ($i == $n) {
                $name = str_replace(' ', '&nbsp;', $v['name']);
                if ($only_name) {
                    return $name;
                }
                return $name;
            }
            $i++;
        }
    }

    if ($only_name) {
        if (strstr($type, 'click_param_value') !== false) {
            return 'Параметр #' . ($n - $named_params_cnt);
        } else {
            return $group_types[$type][2];
        }
    }

    $name = $group_types[$type][0];
    $name = str_replace('Параметр перехода', 'ПП', $name);
    $name = str_replace('Параметр ссылки', 'ПС', $name);
    $name = str_replace('#' . $n, '#' . ($n - $named_params_cnt), $name);
    return $name;
}

/**
 * Название ведущей колонки в отчёте (для специальных настроек источников)
 */
function col_name($params, $only_name = false) {

    // Для режима подчинённых страниц нужно брать второй уровень (subgroup_by)
    $group_by = ($params['mode'] == 'lp_offers') ? $params['subgroup_by'] : $params['group_by'];
    return param_name($group_by, $params['filter'][0]['source_name'], $only_name);
}

/*
 * фрагмент данных для сортировки подчинённых офферов (режим lp_offers)
 * Вид: order|val|ln|offer|val_offer
 * order - 1 для офферов с прямыми переходами и 0 для всех остальных, офферы всегда внизу
 * val   - значение ячейки лэндинга (у оффера - значение родительской ячейки)
 * ln    - номер группы, одинаковый у лэндинга и всех его подчиненённых офферов
 * offer - флаг оффера, 0 для лэндинга, 1 для оффера, сортируется всегда так чтобы лэндинг был вверху
 * val_offer - значение ячейки оффера (для лэндинга пустое)
 */

function sortdata($col_name, $data, $emp = false) {
    //dmp($data);
    //print_r($data);
    //static $l; // счётчик лэндингов
    $r = $data['r'];
    $parent = $data['parent'];
    $func = 't_' . $col_name;
    $tmp = array(
        intval($data['r']['order'])
            //empty($data['r']['sub']) ? 0 : 1 // есть ли подчинённые
    );

    $val0 = intval($func($r, false, false));
    $val = $func($r, true, $emp);

    if ($col_name == 'cnt' and $r['sale_lead'] > 0 and $data['part'] != 'all') {
        $val = $val . ':' . $r['sale_lead'];
        $val0 += ($r['sale_lead'] * 10000000);
    }

    if (!empty($parent)) {
        //dmp($parent);
        if ($col_name == 'cnt' and $data['part'] != 'all') {
            // В дневном режиме особый режим переноса родительских переходов
            $tmp[] = intval($func($parent, false) + ($parent['sale_lead'] * 10000000)); // значение лэндинга
        } else {
            $tmp[] = intval($func($parent, false)); // значение лэндинга
        }
        $tmp[] = $data['r']['ln']; // номер лэндинга
        $tmp[] = 1; // это оффер
        $tmp[] = $val0;
    } else {
        //$l[$col_name]++;
        $tmp[] = $val0;
        $tmp[] = $data['r']['ln']; // номер лэндинга
        $tmp[] = 0; // это лэндинг
    }

    return '<span class="sortdata">' . join('|', $tmp) . '|</span>' . $val;
}

/*
 * Мы загружаем данные частями и иногда получается так, что родительский клик мы загрузили, а подчиненный - нет, или наоборот. Лезем прямо в базу и проверяем наличие клика
 */

function parent_row($id, $name = '') {
    global $rows;
    if (empty($id))
        return 0;

    if (!isset($rows[$id])) {
        $q = "select * from `tbl_clicks` where `id` = '" . intval($id) . "' limit 1";
        //echo $q. '<br >';
        if ($rs = db_query($q) and mysql_num_rows($rs) > 0) {
            $row = mysql_fetch_assoc($rs);
        } else {
            return 0;
        }
    } else {
        $row = $rows[$id];
    }
    return empty($name) ? $row : $row[$name];
}

/*
 * Получаем самую первую ссылку из правила
 */

function get_first_rule_link($rule_id) {
    $q = "select `tbl_offers`.`id`, `offer_tracking_url` 
				from `tbl_rules_items`
				left join `tbl_offers` on value = tbl_offers.id
				where `rule_id` = '" . intval($rule_id) . "'
					and `type` = 'redirect'
				order by `tbl_rules_items`.`id`
				limit 1";
    $rs = db_query($q);
    $r = mysql_fetch_assoc($rs);
    return array($r['id'], $r['offer_tracking_url']);
}

/*
 * Фильтруем конверсии
 */

function conv_filter($data, $conv = 'none') {
    switch ($conv) {
        case 'none':
            foreach ($data as $k => $v) {
                if ($v['sale_lead'] > 0)
                    unset($data[$k]);
                if (isset($v['sub']))
                    $data[$k]['sub'] = conv_filter($v['sub'], $conv);
            }
            break;
        case 'act':
            foreach ($data as $k => $v) {
                if ($v['act'] == 0 and $v['sale'] == 0 and $v['lead'] == 0)
                    unset($data[$k]);
                if (isset($v['sub']))
                    $data[$k]['sub'] = conv_filter($v['sub'], $conv);
            }
            break;
        case 'sale':
            foreach ($data as $k => $v) {
                if ($v['sale'] == 0)
                    unset($data[$k]);
                if (isset($v['sub']))
                    $data[$k]['sub'] = conv_filter($v['sub'], $conv);
            }
            break;
        case 'lead':
            foreach ($data as $k => $v) {
                if ($v['lead'] == 0)
                    unset($data[$k]);
                if (isset($v['sub']))
                    $data[$k]['sub'] = conv_filter($v['sub'], $conv);
            }
            break;
    }
    return $data;
}

/*
 * Массив из 24 часов
 */

function getHours24() {
    $hours = array(
        '00', '01', '02', '03', '04', '05',
        '06', '07', '08', '09', '10', '11',
        '12', '13', '14', '15', '16', '17',
        '18', '19', '20', '21', '22', '23',
    );
    return $hours;
}

/*
 * Ба! Да это же у нас ссылка "Другие"!
 */

function is_other_link($val, $type) {
    return ($val == -1 and ($type == 'referer' or strstr($type, 'click_param_value') !== false));
}

/**
 * Числовые формы (трекер, трекера, трекеров)
 * @param int число
 * @param array значения для 1, 3 и 12
 * @return string
 */
function numform($n, $expr) {
    if (empty($expr[2]))
        $expr[2] = $expr[1];
    //$i=preg_replace('/[^0-9]+/s','',$digit)%100; //intval не всегда корректно работает
    $i = intval($n) % 100; //intval всегда корректно работает
    if ($i >= 5 and $i <= 20)
        return $expr[2];
    else {
        $i%=10;
        if ($i == 1)
            $res = $expr[0];
        elseif ($i >= 2 && $i <= 4)
            $res = $expr[1];
        else
            $res = $expr[2];
    }
    return trim($res);
}