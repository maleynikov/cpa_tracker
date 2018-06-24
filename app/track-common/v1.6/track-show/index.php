<?php

if (!defined('PHP_VERSION_ID'))
{
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

if (PHP_VERSION_ID < 50207)
{
    define('PHP_MAJOR_VERSION',   $version[0]);
    define('PHP_MINOR_VERSION',   $version[1]);
    define('PHP_RELEASE_VERSION', $version[2]);
}

if (defined('PHP_MAJOR_VERSION') && PHP_MAJOR_VERSION > 5)
{
    require_once _TRACK_SHOW_COMMON_PATH . "/DatabaseConnection.php";
    require_once _TRACK_LIB_PATH . '/mysql-backport/mysql.php';
    require_once _TRACK_LIB_PATH . '/php5-backport/string.php';
}

// Turn on output buffering to be able to send headers anytime
ob_start();

header('Content-Type: text/html; charset=utf-8');

// Increase execution time, useful for the slow DB queries
set_time_limit(0);

// Create token to preven сross site request forgery attacks
define("CSRF_KEY", md5(session_id()));

// Define flag to allow templates inclusion, security measure
$include_flag = true;

// Debug
ini_set('display_errors', true);
if (_ENABLE_DEBUG && isset($_GET['debug'])) {
    ini_set('display_startup_errors', true);
    error_reporting(E_ALL & ~E_NOTICE);
} else {
    // Disable PHP warnings
    $_GET['debug'] = 0;
    error_reporting(E_ERROR | E_PARSE);
}

// Set allowed for inclusion files list, security measure
$page_sidebar_allowed = array('sidebar-left-links.inc.php', 'sidebar-left-reports.inc.php');
$page_content_allowed = array('reports.php', 'sales.php', 'stats-flow.php', 'links_page.inc.php', 'rules_page.inc.php', 'import_page.inc.php', 'support_page.inc.php', 'costs_page.inc.php', 'import_page_postback.inc.php', 'timezone_settings_page.inc.php', 'login.php', 'salesreport.php', 'pixel_page.inc.php', 'register.php', 'system-first-run.php', 'system-message-cache.php', 'notifications_page.inc.php', 'targetreport.php', 'landing_page.inc.php', 'reset_password.inc.php', 'lost_password.inc.php', 'adnets_page.inc.php');

// Include main functions
require _TRACK_SHOW_COMMON_PATH . "/functions_general.php";

// Disable excess quoting for unusual hosting environments
disable_magic_quotes();


// Check file with db and server settings
$settings = check_settings();

if ($_REQUEST['ajax_act'] == 'create_database') {
    if ($settings[0] == true) {
        echo json_encode(array(false, 'config_found', $settings[2]));
        exit();
    }

    switch ($settings[1]) {
        case 'cache_not_writable':
            echo json_encode(array(false, 'cache_not_writable', $settings[2]));
            exit();
            break;

        case 'first_run':

            // Check file system
            // We have .htaccess files

            if (!file_exists(dirname(__FILE__) . '/.htaccess')
                    or !file_exists(dirname(__FILE__) . '/../track/.htaccess')
            ) {
                echo json_encode(array(false, 'htaccess_not_found'));
                exit();
            }

            if (!extension_loaded('mysqli'))
            {
                echo json_encode(array(false, 'mysqli_not_installed', 'mysqli extension for PHP is not installed'));
                exit();
            }

            // tmp file for WURFL

            $temp_dir = ini_get('upload_tmp_dir');
            if (!$temp_dir)
                $temp_dir = '/tmp';
            $temp_dir = realpath($temp_dir);

            // tmp dir is writable

            $tmp_file = 'cpa_tmp.test';
            $tmp_rand = date('Y-m-d H:i') . mt_rand(11111, 99999);

            file_put_contents($temp_dir . '/' . $tmp_file, $tmp_rand);
            if (!(file_get_contents($temp_dir . '/' . $tmp_file) == $tmp_rand and unlink($temp_dir . '/' . $tmp_file))) {
                echo json_encode(array(false, 'cache_not_writable', $temp_dir));
                exit();
            }

            // tmp file for WURFL

            $wurfl_tmp_files = array('wurfl.xml', 'wurfl_builder.lock');

            foreach ($wurfl_tmp_files as $tmp_file) {
                $wbase = $temp_dir . '/' . $tmp_file;
                if (file_exists($wbase)) {
                    unlink($wbase); // Попытка удалить
                    if (file_exists($wbase)) {
                        rename($wbase, $wbase . '.old'); // Попытка переименовать
                        if (file_exists($wbase)) {
                            echo json_encode(array(false, 'wurfl_not_writable', $wbase));
                            exit();
                        }
                    }
                }
            }

            // Check datababase

            $login = $_REQUEST['login'];
            $password = $_REQUEST['password'];
            $dbname = $_REQUEST['dbname'];
            $dbserver = $_REQUEST['dbserver'];
            $server_type = $_REQUEST['server_type'];

            // Trying to connect
            if (!defined('PHP_MAJOR_VERSION') || PHP_MAJOR_VERSION == 5)
            {
                $connection = mysql_connect($dbserver, $login, $password);
                if (!$connection)
                {
                    echo json_encode(array(false, 'db_error', mysql_error()));
                    exit();
                }

                // Switch to database
                $db_selected = mysql_select_db($dbname, $connection);
                if (!$db_selected)
                {
                    echo json_encode(array(false, 'db_not_found', $dbname));
                    exit();
                }
            }
            else
            {
                $connection = mysqli_connect($dbserver, $login, $password);
                if (mysqli_connect_errno())
                {
                    echo json_encode(array(false, 'db_error', mysqli_connect_error()));
                    exit();
                }

                // Switch to database
                if (!mysqli_select_db($connection, $dbname))
                {
                    echo json_encode(array(false, 'db_not_found', $dbname));
                    exit();
                }
            }

            // Try create table
            $access_ok = false;

            $q = "CREATE TABLE IF NOT EXISTS `tbl_users_test` ("
                    . "`id` int(11) NOT NULL AUTO_INCREMENT,"
                    . "`email` varchar(255) CHARACTER SET utf8 NOT NULL,"
                    . "`password` varchar(255) CHARACTER SET utf8 NOT NULL,"
                    . "`salt` varchar(255) NOT NULL,"
                    . "PRIMARY KEY (`id`)"
                    . ") ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

            if (!defined('PHP_MAJOR_VERSION') || PHP_MAJOR_VERSION == 5)
            {
                if (mysql_query($q))
                {
                    $q = "INSERT INTO `tbl_users_test` (`email`, `password`, `salt`) VALUES ('email', 'password', 'salt')";
                    if (mysql_query($q))
                    {
                        $id = mysql_insert_id();
                        if ($id > 0)
                        {
                            $q = "DELETE FROM `tbl_users_test` WHERE `id` = '" . $id . "'";
                            if (mysql_query($q))
                            {
                                $ar = mysql_affected_rows();
                                if ($ar > 0)
                                {
                                    $q = "DROP TABLE `tbl_users_test`";
                                    if (mysql_query($q))
                                    {
                                        $access_ok = true;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            else
            {
                if (mysqli_query($connection, $q))
                {
                    $q = "INSERT INTO `tbl_users_test` (`email`, `password`, `salt`) VALUES ('email', 'password', 'salt')";
                    if (mysqli_query($connection, $q))
                    {
                        $id = mysqli_insert_id($connection);
                        if ($id > 0)
                        {
                            $q = "DELETE FROM `tbl_users_test` WHERE `id` = '" . $id . "'";
                            if (mysqli_query($connection, $q))
                            {
                                $ar = mysqli_affected_rows($connection);
                                if ($ar > 0)
                                {
                                    $q = "DROP TABLE `tbl_users_test`";
                                    if (mysqli_query($connection, $q))
                                    {
                                        $access_ok = true;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (!$access_ok) {
                echo json_encode(array(false, 'table_not_create', $dbname));
                exit();
            }

            // Create tables
            if (!is_file(_TRACK_SHOW_COMMON_PATH . '/database.php')) {
                echo json_encode(array(false, 'schema_not_found', $dbname));
                exit();
            }

            // Save settings in file
            $settings_file = $settings[2];
            file_put_contents($settings_file, '<?php exit(); ?>' . serialize(array('login' => $login, 'password' => $password, 'dbname' => $dbname, 'dbserver' => $dbserver, 'server_type' => $server_type)));
            chmod($settings_file, 0777);

            // Create tables and run mysql updates
            require_once (_TRACK_SHOW_COMMON_PATH . '/database.php');
            foreach ($arr_sql as $sql)
            {
                if (!defined('PHP_MAJOR_VERSION') || PHP_MAJOR_VERSION == 5)
                {
                    mysql_query($sql);
                }
                else
                {
                    mysqli_query($connection, $sql);
                }
            }

            // Create first run marker for crontab
            create_crontab_markers();

            // Installation successful
            echo json_encode(array(true, _HTML_ROOT_PATH));

            exit();
            break;
    }
}

// DB settings found
if ($settings[0] == true) {
    $arr_settings = $settings[1];
    $_DB_LOGIN = $arr_settings['login'];
    $_DB_PASSWORD = $arr_settings['password'];
    $_DB_NAME = $arr_settings['dbname'];
    $_DB_HOST = $arr_settings['dbserver'];
} else {
    switch ($settings[1]) {
        case 'cache_not_writable':
            $cache_folder = $settings[2];
            $bHideLeftSidebar = true;
            $bHideTopMenu = true;
            $bHideBottomMenu = true;
            $page_content = "system-message-cache.php";
            include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
            exit();
            break;

        case 'first_run':
            $bHideLeftSidebar = true;
            $bHideTopMenu = true;
            $bHideBottomMenu = true;
            $page_content = "system-first-run.php";
            include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
            exit();
            break;
    }
    exit();
}

include _TRACK_SHOW_COMMON_PATH . "/functions_report.php";

// Connect to DB
mysql_connect($_DB_HOST, $_DB_LOGIN, $_DB_PASSWORD) or die("Could not connect: " . mysql_error());
mysql_select_db($_DB_NAME);
mysql_query('SET NAMES utf8');
//mysql_query('SET TIME_ZONE=\'+04:00\'');

// Mass import offers from XLSX file
if ($_REQUEST['ajax_upload_offers']!='')
{
    // Check CSRF key
    $arr_request_headers=getallheaders();
    if ($arr_request_headers['Authorization']!=CSRF_KEY){echo "Wrong access key"; exit();}
    
    require_once (_TRACK_SHOW_COMMON_PATH.'/lib/uploader/uploader.php');
    $upload_dir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
    $valid_extensions = array('xlsx');
    $Upload = new FileUpload('ajax_upload_offers');
    $Upload->newFileName = 'offers.xlsx';
    $result = $Upload->handleUpload($upload_dir, $valid_extensions);
    if (!$result) 
    {
        echo json_encode(array('success' => false, 'msg' => $Upload->getErrorMsg()));   
    } 
    else 
    {
        require_once (_TRACK_SHOW_COMMON_PATH.'/lib/excel-reader/excel_reader.php');
        require_once (_TRACK_SHOW_COMMON_PATH.'/lib/excel-reader/SpreadsheetReader.php');
        
        $reader = new SpreadsheetReader($upload_dir.'/'.$Upload->getFileName());
        $i=0;
        
        foreach ($reader as $xls_row)
        {
            // Skip row with column names
            if ($i++==0){continue;}

            $category_id=0;

            // Category is set
            if (isset($xls_row[3]))
            {
                $category_name=trim(str_replace(array("\r\n", "\r", "\n", "\t"), '', $xls_row[3]));
                if ($category_name!='')
                {
                    // Check if we already have this category
                    $sql="select id, status from tbl_links_categories_list where category_caption='"._str($category_name)."'";
                    $result=mysql_query($sql);
                    $row=mysql_fetch_assoc($result);
                    if ($row['id']>0)
                    {
                        $category_id=$row['id'];
                        if ($row['status']!=0)
                        {
                            // Make this category visible
                            $sql="update tbl_links_categories_list set status=0 where id='"._str($category_id)."'";
                            mysql_query($sql);
                        }
                    }
                    else
                    {
                        // Add new category
                        $sql = "insert into tbl_links_categories_list (category_caption, category_type, status) values ('" . _str($category_name) . "', '', 0)";
                        mysql_query($sql);
                        $category_id = mysql_insert_id();
                    }
                }
            }

            // Add offer
            $offer_id=edit_offer($category_id, $xls_row[0], $xls_row[1]);

            if (isset($xls_row[2]) && trim($xls_row[2])!='')
            {
                // Add link for this offer
                $rule_name=trim(str_replace(array("\r\n", "\r", "\n", "\t"), '', $xls_row[2]));
                $out_id=$offer_id;

                // ********************
                // Check if we already have rule with this name
                $sql = "select id from tbl_rules where link_name='" . mysql_real_escape_string($rule_name) . "' and status=0";
                $rs = mysql_query($sql);
                $row = mysql_fetch_assoc($rs);

                if ($row['id'] > 0) {
                    ;
                } else {
                    $sql = "insert into tbl_rules (link_name, date_add) values ('" . mysql_real_escape_string($rule_name) . "', NOW())";
                    mysql_query($sql);
                    $rule_id = mysql_insert_id();

                    $sql = "insert into tbl_rules_items (rule_id, parent_id, type, value) values ('" . mysql_real_escape_string($rule_id) . "', '0', 'geo_country', 'default')";
                    mysql_query($sql);
                    $parent_id = mysql_insert_id();

                    $sql = "insert into tbl_rules_items (rule_id, parent_id, type, value) values ('" . mysql_real_escape_string($rule_id) . "', '" . mysql_real_escape_string($parent_id) . "', 'redirect', '" . mysql_real_escape_string($out_id) . "')";
                    mysql_query($sql);

                    // Remove rule from tracker cache
                    cache_remove_rule($rule_name);
                }

                cache_rules_update();
                // ********************                
            }
        }
    }

    exit();
}

if ($_REQUEST['ajax_act'] == 'a_load_flow') {
    $filter = '';
    if ($_REQUEST['filter_by'] != '') {
        switch ($_REQUEST['filter_by']) {
            case 'hour':
                $filter = array(
                    'filter_by' => $_REQUEST['filter_by'],
                    'source_name' => $_REQUEST['source_name'],
                    'date' => $_REQUEST['date'],
                    'hour' => $_REQUEST['hour']
                );
                break;

            default:
                $filter = array(
                    'filter_by' => $_REQUEST['filter_by'],
                    'filter_value' => $_REQUEST['value']
                );
                break;
        }
    }

    list($total, $arr_data) = get_visitors_flow_data($filter, rq('offset', 2), 100, $_REQUEST['date']);

    foreach ($arr_data as $row) {
        include _TRACK_SHOW_COMMON_PATH . '/pages/stats-flow-row.php';
    }
    exit();
}

if ($_REQUEST['ajax_act'] == 'get_source_link') {
    $source = rq('source');

    //if($source == 'source')
    $name = rq('name');
    $rule_id = rq('id', 1);
    $direct = rq('direct', 1);

    /*
      if($source == 'landing')
      $source = 'source';
     */

    // Прямая ссылка без редиректа!
    //$source == 'landing' or 
    if ($direct) {
        list($out_id, $lnk) = get_first_rule_link($rule_id);
    } else {
        $lnk = tracklink() . '/' . $name . '/';
    }

    if (array_key_exists($source, $source_config)) {
        if ($source != 'landing' and !$direct) {
            $lnk .= $source . '/campaign-ads/';
        }

        if ($direct) {
            $source_text = ($source == 'landing') ? 'source' : $source;

            $lnk .= (strstr($lnk, '?') === false ? '?' : '&') . 'utm_source=' . $source_text . '&rule_name=' . onlyword($name); // это безопасно потому что мы проверили наличие $source в нашем $source_config
            //. '&out_id=' . intval($out_id) 
        }

        if ($source != 'source' and !empty($source_config[$source]['params'])) {
            $tmp = array();
            foreach ($source_config[$source]['params'] as $param_name => $param_value) {
                if (empty($param_value['url']) or strstr($lnk, $param_value['url']) !== false)
                    continue;
                $tmp[] = $param_name . '=' . $param_value['url'];
            }



            if (count($tmp) > 0) {
                $lnk .= (strstr($lnk, '?') === false ? '?' : '&') . join('&', $tmp);
            }
        }

        if ($direct and strstr($lnk, 'utm_campaign=') === false) {
            $lnk .= '&utm_campaign=campaign-ads';
        }
    } elseif (!$direct) {
        $lnk .= 'source/campaign-ads';
    }

    echo $lnk;
    exit();
}

if ($_REQUEST['ajax_act'] == 'sync_slaves') {
    dmp(cache_rules_update());
    dmp(cache_links_update());
    exit();
}

// Страницы, на которые можно войти без авторизации
$open_pages = array('login', 'lostpassword', 'resetpassword');

// Authentification
if (!in_array($_REQUEST['page'], $open_pages)) {
    $auth_info = is_auth();

    if ($auth_info[0] == false) {
        switch ($auth_info[1]) {
            case 'register_new':
                if ($_REQUEST['page'] != 'register') {
                    header('Location: ' . _HTML_ROOT_PATH . "/?page=register");
                }
                break;

            default:
                header('Location: ' . _HTML_ROOT_PATH . "/?page=login");
                break;
        }
    }
}

if (isset($_REQUEST['csrfkey']) && ($_REQUEST['csrfkey'] == CSRF_KEY)) {
    switch ($_REQUEST['ajax_act']) {
        case 'get_rules_json':

            $arr_offers = get_rules_offers();
            $condition_types = array('geo_country' => 'Страна', 'lang' => 'Язык', 'referer' => 'Реферер', 'city' => 'Город', 'region' => 'Регион', 'provider' => 'Провайдер', 'ip' => 'IP адрес', 'os' => 'ОС', 'platform' => 'Платформа', 'browser' => 'Браузер', 'agent' => 'User-agent', 'get' => 'GET');
            $arr_rules = get_rules_list($arr_offers);
            $arr = array();
            $i = 0;

            foreach ($arr_rules as $cur) {
                $arr['rules'][$i] = array('id' => $cur['id'], 'name' => $cur['name'], 'url' => tracklink() . "/{$cur['name']}/source/campaign-ads");

                $arr_destinations = array();
                $default_destination_id = '';
                foreach ($cur['items'] as $cur_item_val) {
                    if ($cur_item_val['inner'][0]['value'] != '') {
                        $arr_destinations[$cur_item_val['inner'][0]['value']]++;
                    }

                    // Set default out for this link, separate section
                    if ($cur_item_val['root']['type'] == 'geo_country' && $cur_item_val['root']['value'] == 'default') {
                        $default_destination_id = $cur_item_val['inner'][0]['value'];
                        continue;
                    }

                    // Add item to conditions section
                    $arr['rules'][$i]['conditions'][] = array('textinput' => inputtype($cur_item_val['root']['type']), 'getinput' => ($cur_item_val['root']['type'] == 'get'), 'type' => $condition_types[$cur_item_val['root']['type']], 'select_type' => $cur_item_val['root']['type'], 'value' => $cur_item_val['root']['value'], 'destination_id' => $cur_item_val['inner'][0]['value']);
                }

                $arr_destinations = array_keys($arr_destinations);
                $destinations_count = count($arr_destinations);
                switch ($destinations_count) {
                    case 0:
                        break;

                    case 1:
                        $str = current($arr_destinations);
                        $arr['rules'][$i]['destination'] = $arr_offers[$str]['offer_name'];
                        $arr['rules'][$i]['destination_id'] = $arr_offers[$str]['id'];
                        break;

                    default:
                        $arr['rules'][$i]['destination_multi'] = declination($destinations_count, array(' оффер', ' оффера', ' офферов'));
                        break;
                }
                $arr['rules'][$i]['default_destination_id'] = $default_destination_id;
                $arr['rules'][$i]['other_users'] = count($cur['items']) > 1 ? 'Остальные посетители' : 'Все посетители';

                $i++;
            }
            echo json_encode($arr);

            exit();
            break;

        case 'excel_export':
            include(_TRACK_SHOW_COMMON_PATH . '/lib/excel_writer/ExcelWriterXML.php');
            $xml = new ExcelWriterXML('report.xls');

            $sheet = $xml->addSheet('Report');

            // Get data for report
            $arr_data = get_excel_report($_REQUEST['date']);
            $iRow = 1;
            foreach ($arr_data as $cur) {
                $iCol = 1;
                foreach ($cur as $val) {
                    $sheet->writeString($iRow, $iCol, $val);
                    $iCol++;
                }

                $iRow++;
            }

            $xml->sendHeaders();
            $xml->writeData();
            exit();
            break;

        case 'tsv_export':
            $filename = 'report.txt';
            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment; filename=$filename");
            header("Pragma: no-cache");
            header("Expires: 0");

            // Get data for report
            $arr_data = get_excel_report($_REQUEST['date']);
            $iRow = 1;
            foreach ($arr_data as $cur) {
                foreach ($cur as $val) {
                    echo $val . "\t";
                }

                echo "\n";
            }

            exit();
            break;

        case 'import_hasoffers_offers':
            $network_id = $_REQUEST['id'];
            $result = import_hasoffers_links($network_id);

            if ($result[0] == true) {
                echo $result[1];
            } else {
                echo "Произошла ошибка {$result[1]} при импорте офферов";
            }
            exit();
            break;

        case 'import_sales':
            // Convert currency
            $amount = convert_to_usd($_REQUEST['currency_code'], $_REQUEST['amount_value']);

            $leadsType = $_REQUEST['leadsType'];
            $str_subids = $_REQUEST['subids'];

            $pattern = '/\d{14}x\d{5}/';
            preg_match_all($pattern, $str_subids, $subids);
            foreach ($subids[0] as $key => $subid) {
                import_sale_info($leadsType, $amount, $subid);
            }
            break;

        case 'delete_link':
            $id = rq('id', 2);
            delete_offer($id);
            cache_links_update();
            exit();
            break;

        case 'restore_link':
            $id = rq('id', 2);
            delete_offer($id, 0);
            cache_links_update();
            exit();
            break;

        case 'delete_sale':
            $type = $_REQUEST['type'];
            $click_id = rq('click_id', 2);
            $conversion_id = rq('conversion_id', 2);
            delete_sale($click_id, $conversion_id, $type);
            exit();
            break;

        case 'get_sales':
            $sales = get_sales($_POST['sType'], $_POST['sStart'], $_POST['sEnd']);
            echo json_encode($sales);
            exit;
            break;

        case 'delete_rule':
            $rule_id = $_REQUEST['id'];
            delete_rule($rule_id);
            exit();
            break;

        case 'restore_rule':
            $rule_id = intval($_POST['id']);
            restore_rule($rule_id);
            exit;
            break;

        case 'move_link_to_category':
            $category_id = $_REQUEST['category_id'];
            $offer_id = $_REQUEST['offer_id'];
            if ($category_id == 0) {
                $sql = "delete from tbl_links_categories where offer_id='" . mysql_real_escape_string($offer_id) . "'";
                mysql_query($sql);
            } else {
                // Remove old category
                $sql = "delete from tbl_links_categories where offer_id='" . mysql_real_escape_string($offer_id) . "'";
                mysql_query($sql);

                $sql = "insert into tbl_links_categories (category_id, offer_id) values ('" . mysql_real_escape_string($category_id) . "', '" . mysql_real_escape_string($offer_id) . "')";
                mysql_query($sql);
            }
            exit();
            break;

        case 'category_edit':
            $category_id = $_REQUEST['category_id'];
            $category_name = $_REQUEST['category_name'];
            if ($_REQUEST['is_delete'] == 1) {
                $sql = "delete from tbl_links_categories_list where id='" . mysql_real_escape_string($category_id) . "'";
                mysql_query($sql);

                $sql = "delete from tbl_links_categories where category_id='" . mysql_real_escape_string($category_id) . "'";
                mysql_query($sql);
                header('Location: ' . _HTML_ROOT_PATH . "/?page=links");
            } else {
                $sql = "update tbl_links_categories_list set category_caption='" . mysql_real_escape_string($category_name) . "' where id='" . mysql_real_escape_string($category_id) . "'";
                mysql_query($sql);

                header('Location: ' . _HTML_ROOT_PATH . "/?page=links&category_id={$category_id}");
            }
            exit();
            break;

        case 'add_rule':
            ob_start();
            $rule_name = trim($_REQUEST['rule_name']);
            $out_id = trim($_REQUEST['out_id']);

            // Check if we already have rule with this name
            $sql = "select id from tbl_rules where link_name='" . mysql_real_escape_string($rule_name) . "' and status=0";
            $rs = mysql_query($sql);
            $row = mysql_fetch_assoc($rs);

            if ($row['id'] > 0) {
                ;
            } else {
                $sql = "insert into tbl_rules (link_name, date_add) values ('" . mysql_real_escape_string($rule_name) . "', NOW())";
                mysql_query($sql);
                $rule_id = mysql_insert_id();

                $sql = "insert into tbl_rules_items (rule_id, parent_id, type, value) values ('" . mysql_real_escape_string($rule_id) . "', '0', 'geo_country', 'default')";
                mysql_query($sql);
                $parent_id = mysql_insert_id();

                $sql = "insert into tbl_rules_items (rule_id, parent_id, type, value) values ('" . mysql_real_escape_string($rule_id) . "', '" . mysql_real_escape_string($parent_id) . "', 'redirect', '" . mysql_real_escape_string($out_id) . "')";
                mysql_query($sql);

                // Remove rule from tracker cache
                cache_remove_rule($rule_name);
            }

            cache_rules_update();
            header('Location: ' . full_url() . "?page=rules");
            exit();
            break;

        case 'update_rule_name':
            $rule_id = rq('rule_id', 2);
            $rule_name = trim(rq('rule_name'));
            $old_rule_name = trim(rq('old_rule_name'));

            if ($rule_id == 0 || $rule_id == '' || $rule_name == '' || $old_rule_name == '' || $old_rule_name == $rule_name) {
                exit();
            }

            // Update rule name
            $sql = "update tbl_rules set link_name='" . mysql_real_escape_string($rule_name) . "' where id='" . mysql_real_escape_string($rule_id) . "'";
            mysql_query($sql);
            cache_remove_rule($old_rule_name);
            cache_rules_update();

            exit();
        break;

        case 'sync_slaves':
            cache_rules_update();
            cache_links_update();
        break;

        case 'update_rule':
            $rule_id = $_REQUEST['rule_id'];
            $rule_name = $_REQUEST['rule_name'];
            $rules_item = $_REQUEST['rules_item'];
            $rule_values = $_REQUEST['rule_value'];

            //$pattern = '/(^[a-z0-9_]+$)/';
            $pattern = '/^[ЎўІіёa-zA-Zа-яА-Я0-9_-]*$/u';
            foreach ($rules_item as $key => $rull) {
                if ($rull['type'] == 'get') {
                    $get_arr = explode('=', $rull['val']);
                    $get_name = $get_arr[0];
                    $get_val = $get_arr[1];
                    if (!preg_match($pattern, $get_name) || !preg_match($pattern, $get_val)) {
                        exit;
                    }
                }
            }

            if ($rule_id == 0 || $rule_id == '' || $rule_name == '') {
                exit();
            }

            // Update rule name
            $sql = "update tbl_rules set link_name='" . mysql_real_escape_string($rule_name) . "' where id='" . mysql_real_escape_string($rule_id) . "'";
            mysql_query($sql);

            // Remove old rules
            $sql = "delete from tbl_rules_items where rule_id='" . mysql_real_escape_string($rule_id) . "'";
            mysql_query($sql);

            // Remove rule from tracker cache
            cache_remove_rule($rule_name);

            // Add new rules
            $i = 0;
            foreach ($rules_item as $cur_item) {
                $item = $rules_item[$i];
                $out_id = $rule_values[$i];
                if ($item['val'] != '') {
                    $sql = "insert into tbl_rules_items (rule_id, parent_id, type, value) values ('" . mysql_real_escape_string($rule_id) . "', '0', '" . mysql_real_escape_string($item['type']) . "', '" . mysql_real_escape_string($item['val']) . "')";
                    mysql_query($sql);
                    $parent_id = mysql_insert_id();

                    $sql = "insert into tbl_rules_items (rule_id, parent_id, type, value) values ('" . mysql_real_escape_string($rule_id) . "', '" . mysql_real_escape_string($parent_id) . "', 'redirect', '" . mysql_real_escape_string($out_id) . "')";
                    mysql_query($sql);
                }
                $i++;
            }

            $out = cache_rules_update();
            echo json_encode($out);

            // Create rule in tracker cache
            //cache_set_rule ($rule_name);
            exit();
            break;

        case 'add_offer':
            $category_id = $_REQUEST['category_id'];
            $link_name = $_REQUEST['link_name'];
            $link_url = $_REQUEST['link_url'];

            edit_offer($category_id, $link_name, $link_url);

            // Redirect to links page with category_id
            if ($category_id != '') {
                header("Location: " . _HTML_ROOT_PATH . "/?page=links&category_id={$category_id}");
            } else {
                header("Location: " . _HTML_ROOT_PATH . "/?page=links");
            }
            exit();
            break;

        case 'add_category':
            $category_name = $_REQUEST['category_name'];

            $sql = "insert into tbl_links_categories_list (category_caption, category_name, category_type, status) values ('" . mysql_real_escape_string($category_name) . "', '', '', 0)";
            mysql_query($sql);

            $id = mysql_insert_id();
            $sql = "update tbl_links_categories_list set category_name='category_" . mysql_real_escape_string($id) . "' where id='" . mysql_real_escape_string($id) . "'";
            mysql_query($sql);

            echo $id;
            exit();
            break;

        case 'add_costs':
            $timezone_shift = get_current_timezone_shift();

            $date_range = explode(' - ', trim($_REQUEST['date_range']));
            $date_start = $date_range[0];
            $date_end = $date_range[1];

            $source_name = $_REQUEST['source_name'];
            $campaign_name = $_REQUEST['campaign_name'];
            $ads_name = $_REQUEST['ads_name'];
            $costs_value = trim(str_replace(',', '.', $_REQUEST['costs_value']));
            $costs_value = convert_to_usd($_REQUEST['currency_code'], $costs_value);

            if ($date_start == '' || $date_end == '' || $source_name == '' || $costs_value == '') {
                exit();
            }

            $date_start = date2mysql($date_start);
            $date_end = date2mysql($date_end);
            $where = '';
            if ($campaign_name != '') {
                $where.=" and campaign_name='" . mysql_real_escape_string($campaign_name) . "'";
            }
            if ($ads_name != '') {
                $where.=" and ads_name='" . mysql_real_escape_string($ads_name) . "'";
            }

            $sql = "select count(id) as cnt from tbl_clicks where CONVERT_TZ(date_add, '+00:00', '" . _str($timezone_shift) . "') BETWEEN '" . mysql_real_escape_string($date_start) . " 00:00:00' AND '" . mysql_real_escape_string($date_end) . " 23:59:59' and source_name='" . mysql_real_escape_string($source_name) . "' {$where}";

            $rs = mysql_query($sql);
            $row = mysql_fetch_assoc($rs);
            if ($row['cnt'] > 0) {
                $click_price = $costs_value / $row['cnt'];
                $click_price = number_format($click_price, 5);
                $sql = "update tbl_clicks set click_price='" . mysql_real_escape_string($click_price) . "' where CONVERT_TZ(date_add, '+00:00', '" . _str($timezone_shift) . "') BETWEEN '" . mysql_real_escape_string($date_start) . " 00:00:00' AND '" . mysql_real_escape_string($date_end) . " 23:59:59' and source_name='" . mysql_real_escape_string($source_name) . "' {$where}";
                mysql_query($sql);
            }
            exit();
            break;

        case 'change_current_timezone':
            change_current_timezone($_REQUEST['id']);
            exit();
            break;

        case 'add_adnet':
            $id = rq('adnet_id', 2);
            add_adnet($id, $_REQUEST['name'], $_REQUEST['url']);
            exit();
            break;

        case 'add_timezone':
            add_timezone($_REQUEST['timezone_name'], $_REQUEST['timezone_offset_h']);
            exit();
            break;

        case 'edit_timezone':
            update_timezone($_REQUEST['timezone_name'], $_REQUEST['timezone_offset_h'], $_REQUEST['timezone_id']);
            exit();
            break;

        case 'delete_adnet':
            delete_adnet($_REQUEST['id']);
            exit();
            break;

        case 'delete_timezone':
            delete_timezone($_REQUEST['id']);
            exit();
            break;

        case 'send_support_message':
            $installation_guid = md5($_SERVER['HTTP_HOST']) . md5(_TRACK_SHOW_COMMON_PATH);
            $url = 'https://www.cpatracker.ru/system/tickets.php';
            $data = array('act' => 'send_support_message', 'message' => $_REQUEST['message'], 'email' => $_REQUEST['user_email'], 'installation_guid' => $installation_guid);

            $result = send_post_request($url, $data);
            if ($result[0] === false) {
                echo '0|' . $result[1];
            } else {
                echo '1|' . $result[1];
            }
            exit();
            break;

        case 'postback_info':
            require(_TRACK_LIB_PATH . '/class/common.php');
            $net = $_POST['net'];
            $result = array();
            if (!is_file(_TRACK_LIB_PATH . '/postback/' . $net . '.php')) {
                $result['status'] = 'ERR';
                echo json_encode($result);
                exit;
            }

            require(_TRACK_LIB_PATH . '/postback/' . $net . '.php');
            $result['status'] = 'OK';
            $network = new $net();
            $result['links'] = $network->get_links();
            $result['reg_url'] = $result['links']['reg_url'];
            $result['net_text'] = $result['links']['net_text'];
            unset($result['links']['reg_url']);
            unset($result['links']['net_text']);
            echo json_encode($result);
            exit;
            break;
    }
} // End CSRF check
// Check if crontab is installed
$result = check_crontab_markers();
if ($result['error']) {
    if ($result['crontab_clicks']) {
        $global_notifications[] = 'CRONTAB_CLICKS_NOT_INSTALLED';
    }
    if ($result['crontab_postback']) {
        $global_notifications[] = 'CRONTAB_POSTBACK_NOT_INSTALLED';
    }
}

header('Content-Type: text/html; charset=utf-8');
header('X-Frame-Options: DENY');

$page = rq('page');

switch ($page) {
    case 'import':
    case 'costs':
    case 'postback':
    case 'adnets':
    case 'pixel':
    case 'landing':
        $arr_left_menu = array(
            'import' => array('link' => 'index.php?page=import', 'icon' => 'icon-shopping-cart', 'caption' => 'Добавление продаж'),
            'costs' => array('link' => 'index.php?page=costs', 'icon' => 'icon-credit-card', 'caption' => 'Добавление затрат'),
            'postback' => array('link' => 'index.php?page=postback', 'icon' => 'icon-cogs', 'caption' => 'Интеграция с CPA сетями'),
            'adnets' => array('link' => 'index.php?page=adnets', 'icon' => 'icon-cogs', 'caption' => 'Интеграция с рекламными сетями'),
            //'pixel'    => array('link'=>'index.php?page=pixel', 'icon'=>'icon-cogs', 'caption'=>'Пиксель отслеживания'),
            'landing' => array('link' => 'index.php?page=landing', 'icon' => 'icon-cogs', 'caption' => 'Целевые страницы'),
        );

        foreach ($arr_left_menu as $k => $v) {
            if ($page == $k) {
                $arr_left_menu[$k]['is_active'] = 1;
                break;
            }
        }
        break;
}


switch ($_REQUEST['page']) {
    case 'landing':

        $page_content = 'landing_page.inc.php';
        include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
        exit();
        break;
    case 'links':
        $page_sidebar = 'sidebar-left-links.inc.php';
        $page_content = "links_page.inc.php";
        include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
        exit();
        break;

    case 'rules':
        $arr_offers = get_rules_offers();
        list ($js_last_offer_id, $js_offers_data) = get_offers_data_js($arr_offers);
        $js_sources_data = get_sources_data_js();

        $js_countries_data = get_countries_data_js();
        $js_langs_data = get_langs_data_js();
        $page_content = 'rules_page.inc.php';
        include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
        exit();
        break;

    case 'costs':
        $arr_sources = get_sources();
        $arr_campaigns = get_campaigns();
        $arr_ads = get_ads();

        $page_content = 'costs_page.inc.php';
        include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
        exit();
        break;

    case 'import':
        $page_content = 'import_page.inc.php';
        include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
        exit();
        break;

    case 'adnets':
        $page_content = 'adnets_page.inc.php';
        include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
        exit();
        break;

    case 'postback':
        $page_content = 'import_page_postback.inc.php';
        include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
        exit();
        break;

    case 'pixel':
        $page_content = 'pixel_page.inc.php';
        include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
        exit();
        break;

    case 'lostpassword':
        $page_content = 'lost_password.inc.php';
        include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
        exit();
        break;

    case 'resetpassword':
        $page_content = 'reset_password.inc.php';
        include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
        exit();
        break;

    case 'support':
        $page_content = 'support_page.inc.php';
        include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
        exit();
        break;

    case 'notifications':
        $page_content = 'notifications_page.inc.php';
        include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
        exit();
        break;

    case 'settings':
        switch ($_REQUEST['type']) {
            case 'timezone':
                $page_content = 'timezone_settings_page.inc.php';
                break;
        }
        include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
        exit();
        break;

    case 'login':
        switch ($_REQUEST['act']) {
            case 'login':
                $email = $_REQUEST['email'];
                $password = $_REQUEST['password'];
                list ($is_valid, $email, $salted_password) = check_user_credentials($email, $password);
                if ($is_valid) {
                    setcookie("cpatracker_auth_email", $email, time() + 3600 * 24 * 365, "/");
                    setcookie("cpatracker_auth_password", $salted_password, time() + 3600 * 24 * 365, "/");
                    header('Location: ' . full_url());
                } else {
                    header('Location: ' . full_url() . '?page=login');
                }
                exit();
                break;

            default:
                $bHideLeftSidebar = true;
                $bHideTopMenu = true;
                $bHideBottomMenu = true;
                $page_content = "login.php";
                include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
                exit();
                break;
        }
        break;

    case 'logout':
        setcookie("cpatracker_auth_email", $email, time() - 3600 * 24 * 365, "/");
        setcookie("cpatracker_auth_password", $salted_password, time() - 3600 * 24 * 365, "/");
        header('Location: ' . full_url());
        exit();
        break;

    case 'register':
        switch ($_REQUEST['act']) {
            case 'register_admin':
                if ($auth_info[1] != 'register_new') {
                    exit();
                }
                $email = $_REQUEST['email'];
                $password = $_REQUEST['password'];

                $salted_password = register_admin($email, $password);

                if (!empty($_REQUEST['subscribe'])) {
                    file_get_contents('http://www.cpatracker.ru/system/subscription/subscribe_updates.php?email=' . $email);
                }

                setcookie("cpatracker_auth_email", $email, time() + 3600 * 24 * 365, "/");
                setcookie("cpatracker_auth_password", $salted_password, time() + 3600 * 24 * 365, "/");

                header('Location: ' . full_url());

                exit();
                break;

            default:
                $bHideLeftSidebar = true;
                $bHideTopMenu = true;
                $bHideBottomMenu = true;
                $page_content = "register.php";
                include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
                exit();
                break;
        }
        break;

    default:
        $page_top_menu = "top_menu.php";
        $sidebar_inc = "left-sidebar.php";
        switch ($_REQUEST['act']) {
            case 'reports':
                switch ($_REQUEST['type']) {
                    case 'sales':
                        $page_content = 'sales.php';
                        break;

                    case 'salesreport':
                        $page_content = 'salesreport.php';
                        break;
                    default:
                        $page_content = "reports.php";
                        break;
                }

                $page_sidebar = 'sidebar-left-reports.inc.php';
                include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
                exit();
                break;

            case 'register_admin':
                $page_top_menu = "top_menu_empty.php";
                $sidebar_inc = "left-sidebar-empty.php";
                $page_content = "register.php";
                exit();
                break;

            default:
                $search = $_REQUEST['search'];
                $filter = '';
                if ($_REQUEST['filter_by'] != '') {
                    switch ($_REQUEST['filter_by']) {
                        case 'search':
                            $filter = array(
                                'filter_by' => $_REQUEST['filter_by'],
                                'filter_value' => $_REQUEST['search'],
                                'date' => $_REQUEST['date'],
                            );
                            break;

                        case 'hour':
                            $filter = array(
                                'filter_by' => $_REQUEST['filter_by'],
                                'source_name' => $_REQUEST['source_name'],
                                'date' => $_REQUEST['date'],
                                'hour' => $_REQUEST['hour']
                            );
                            break;

                        default:
                            $filter = array(filter_by => $_REQUEST['filter_by'], filter_value => $_REQUEST['value']);
                            break;
                    }
                }

                list($total, $arr_data) = get_visitors_flow_data($filter, 0, 20, $_REQUEST['date']);

                $page_sidebar = 'sidebar-left-reports.inc.php';
                $page_content = "stats-flow.php";

                include _TRACK_SHOW_COMMON_PATH . "/templates/main.inc.php";
                exit();
                break;
        }
        break;
}
?>