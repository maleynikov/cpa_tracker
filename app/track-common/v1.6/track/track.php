<?php

ob_start();
/*
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ini_set('log_errors', 1);
 */

require _TRACK_COMMON_PATH . '/functions.php';

$settings_file = _TRACK_SETTINGS_PATH . '/settings.php';

$str = file_get_contents($settings_file);
$str = str_replace('<?php exit(); ?>', '', $str);
$arr_settings = unserialize($str);

$_SERVER_TYPE = $arr_settings['server_type'];
if ($_SERVER_TYPE == '') {
    exit();
}

if (!function_exists('remove_tab')) {

    function remove_tab($str) {
        return str_replace("\t", ' ', $str);
    }

}

if (!function_exists('detector_cp1251')) {

    function detector_cp1251($str) {
        $tmp = str_replace(array('Р', 'ћ', 'Ђ', 'є', 'Ѓ', '°'), '#', $str);
        $l1 = mb_strlen($tmp, 'UTF-8');
        $l2 = mb_strlen(str_replace('#', '', $tmp), 'UTF-8');
        if ($l1 - $l2 > $l1 / 3) {
            return iconv('UTF-8', 'cp1251', $str);
        }
        return $str;
    }

}


if (!function_exists('add_parent_subid')) {

    function add_parent_subid($domain, $subid) {
        $unique = 0;
        if (array_key_exists('cpa_parents', $_COOKIE)) {
            $parents = json_decode($_COOKIE['cpa_parents'], true);
        } else {
            $parents = array();
        }
        $parents[$domain] = $subid;

        // Parent click
        $cookie_time = $_SERVER['REQUEST_TIME'] + 60;

        // Unique user
        $cookie_name = 'cpa_was_here_' . str_replace('.', '_', $domain);
        if (empty($_COOKIE[$cookie_name])) {
            $cookie_time = $_SERVER['REQUEST_TIME'] + (60 * 60 * 24 * 31);
            setcookie($cookie_name, 1, $cookie_time, "/", $_SERVER['HTTP_HOST']);
            $unique = 1;
        }

        setcookie("cpa_parents", json_encode($parents), $cookie_time, "/", $_SERVER['HTTP_HOST']);
        return $unique;
    }

}

$requestingDevice = null;

require_once (_TRACK_LIB_PATH . "/ua-parser/uaparser.php");
if (extension_loaded('xmlreader')) {
    // Init WURFL library for mobile device detection
    $wurflDir = _TRACK_LIB_PATH . '/wurfl/WURFL';
    $resourcesDir = _TRACK_LIB_PATH . '/wurfl/resources';
    require_once $wurflDir . '/Application.php';
    $persistenceDir = _CACHE_COMMON_PATH . '/wurfl-persistence';
    $cacheDir = _CACHE_COMMON_PATH . '/wurfl-cache';
    $wurflConfig = new WURFL_Configuration_InMemoryConfig();
    $wurflConfig->wurflFile(_TRACK_STATIC_PATH . '/wurfl/wurfl_1.5.3.xml');
    $wurflConfig->matchMode('accuracy');
    $wurflConfig->allowReload(true);
    $wurflConfig->persistence('file', array('dir' => $persistenceDir));
    $wurflConfig->cache('file', array('dir' => $cacheDir, 'expiration' => 36000));
    $wurflManagerFactory = new WURFL_WURFLManagerFactory($wurflConfig);
    $wurflManager = $wurflManagerFactory->create();
    $requestingDevice = $wurflManager->getDeviceForUserAgent($_SERVER['HTTP_USER_AGENT']);
}

if (!function_exists('get_geodata')) {

    function get_geodata($ip) {
        require_once (_TRACK_LIB_PATH . "/maxmind/geoip.inc.php");
        require_once (_TRACK_LIB_PATH . "/maxmind/geoipcity.inc.php");
        require_once (_TRACK_LIB_PATH . "/maxmind/geoipregionvars.php");
        $gi = geoip_open(_TRACK_STATIC_PATH . "/maxmind/MaxmindCity.dat", GEOIP_STANDARD);
        $record = geoip_record_by_addr($gi, $ip);
        $giisp = geoip_open(_TRACK_STATIC_PATH."/maxmind-isp/GeoIPISP.dat", GEOIP_STANDARD);
        $isp = geoip_org_by_addr($giisp, $ip);
        geoip_close($gi);

        $cur_country = $record->country_code;

        // Resolve GeoIP extension conflict
        if (function_exists('geoip_country_code_by_name') && ($cur_country == '')) {
            $cur_country = geoip_country_code_by_name($ip);
        }

        return array(
            'country' => $cur_country,
            'region' => $record->region,
            'state' => $GEOIP_REGION_NAME[$record->country_code][$record->region],
            'city' => $record->city,
            'isp' => $isp
        );
    }

}

if (!function_exists('get_rules')) {

    function get_rules($rule_name) {
        $rule_hash = md5($rule_name);

        $rules_path = _CACHE_PATH . "/rules";
        $rule_path = "{$rules_path}/.{$rule_hash}";

        if (is_file($rule_path)) {
            $str_rules = file_get_contents($rule_path);
            $arr_rules = unserialize($str_rules);
            return $arr_rules;
        } else {
            track_error('Rule ' . $rule_name . ' not found');
        }
    }

}

if (!function_exists('get_out_link')) {

    function get_out_link($id) {

        $link = '';
        $id = intval($id);
        if ($id <= 0) {
            return '';
        }

        $outs_path = _CACHE_PATH . "/outs";
        $out_path = "{$outs_path}/.{$id}";

        if (is_file($out_path)) {
            $link = file_get_contents($out_path);
        } else {
            track_error('Out link ' . $id . ' not found');
        }

        return $link;
    }

}

// Remove trailing slash
//$track_request=detector_cp1251(rtrim($_REQUEST['track_request'], '/')); 
$track_request = rtrim($_REQUEST['track_request'], '/');
$track_request = explode('/', $track_request);

$str = '';

// Date
$str.=date("Y-m-d H:i:s") . "\t";

switch ($_SERVER_TYPE) {
    case 'apache':
        $ip = $_SERVER['REMOTE_ADDR'];
        break;

    case 'nginx':
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        break;
}

// Check if we have several ip addresses
if (strpos($ip, ',') !== false) {
    $arr_ips = explode(',', $ip);
    if (trim($arr_ips[0]) != '127.0.0.1') {
        $ip = trim($arr_ips[0]);
    } else {
        $ip = trim($arr_ips[1]);
    }
}

$str.=remove_tab($ip) . "\t";

// Country and city
$geo_data = get_geodata($ip);
$cur_country = $geo_data['country'];
$cur_state = $geo_data['state'];
$cur_city = $geo_data['city'];
$isp = $geo_data['isp'];

// User language
$user_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

// User-agent
$str.=remove_tab($_SERVER['HTTP_USER_AGENT']) . "\t";

// Referer
$str.=remove_tab($_SERVER['HTTP_REFERER']) . "\t";

// Link name
$link_name = $track_request[0];
$str.=$link_name . "\t";

// Link source
$link_source = $track_request[1];
$str.=$link_source . "\t";

// Link ads name
$link_ads_name = $track_request[2];
$str.=$link_ads_name . "\t";

// Subid
$subid = date("YmdHis") . 'x' . sprintf("%05d", rand(0, 99999));
$str.=$subid . "\t";

// Subaccount
$str.=$subid . "\t";

// Apply rules and get out id for current click
$arr_rules = get_rules($link_name);

if (count($arr_rules) == 0) {
    exit('Rule not found');
} else {
    $user_params = array();
    $user_params['agent'] = $_SERVER['HTTP_USER_AGENT'];
    if ($requestingDevice && (($requestingDevice->getCapability('is_wireless_device') == 'true') || ($requestingDevice->getCapability('is_tablet') == 'true'))) {
        $user_params['os'] = $requestingDevice->getCapability('device_os');
        $user_params['device'] = $requestingDevice->getCapability('brand_name') . '; ' . $requestingDevice->getCapability('model_name');
        $user_params['platform'] = $requestingDevice->getCapability('brand_name');
        $user_params['browser'] = $requestingDevice->getCapability('mobile_browser');
    } else {
        $parser = new UAParser;
        $result = $parser->parse($user_params['agent']);
        $user_params['browser'] = $result->ua->family;
        $user_params['os'] = $result->os->family;
        $user_params['device'] = '';
        $user_params['platform'] = '';
    }

    $user_params['ip'] = $ip;
    $user_params['city'] = $cur_city;
    $user_params['region'] = $cur_state;
    $user_params['provider'] = $isp;
    $user_params['lang'] = $user_lang;
    $user_params['referer'] = $_SERVER['HTTP_REFERER'];
    $user_params['geo_country'] = $cur_country;
    $relevant_params = array();

    foreach ($arr_rules['geo_country'] as $key => $value) {
        if ($value['value'] == 'default') {
            $rule_id = $value['rule_id'];
            $out_id = $value['out_id'];
            $rule_order = 0;
            break;
        }
    }

    $flag = false;
    foreach ($arr_rules as $key => $value) {
        $relevant_params = array();
        $relevant_param_order = 0;
        foreach ($value as $internal_key => $internal_value) {
            if ($key == 'get') {
                $get_arr = explode('=', $internal_value['value']);
                $get_name = $get_arr[0];
                $get_val = $get_arr[1];
                if ((isset($_GET[$get_name]) && $_GET[$get_name] == $get_val) || empty($get_val) && empty($_GET[$get_name])) {

                    //if(isset($_GET[$get_name])&&$_GET[$get_name]==$get_val) {
                    $relevant_params[] = $internal_value;
                    if (!$relevant_param_order) {
                        $relevant_param_order = $internal_value['order'];
                    } else {
                        if ($relevant_param_order > $internal_value['order']) {
                            $relevant_param_order = $internal_value['order'];
                        }
                    }
                    $flag = true;
                }
            } elseif ($key == 'referer') {
                $val = strtolower($internal_value['value']);
                // дописываем http:// на случай, если юзер забыл и это не Google Market
                if (substr($val, 0, 7) != 'http://' and substr($val, 0, 9) != 'market://')
                    $val = 'http://' . $val;
                if (trim($user_params[$key]) != '' and strtolower(substr($user_params[$key], 0, strlen($val))) == $val) {
                    $relevant_params[] = $internal_value;
                    if (!$relevant_param_order) {
                        $relevant_param_order = $internal_value['order'];
                    } else {
                        if ($relevant_param_order > $internal_value['order']) {
                            $relevant_param_order = $internal_value['order'];
                        }
                    }
                    $flag = true;
                }
            } elseif ($key == 'os' and substr($internal_value['value'], 0, 8) == 'DEFINED_') {
                if (check_platform($internal_value['value'], $user_params['os'])) {
                    $relevant_params[] = $internal_value;
                    if (!$relevant_param_order) {
                        $relevant_param_order = $internal_value['order'];
                    } else {
                        if ($relevant_param_order > $internal_value['order']) {
                            $relevant_param_order = $internal_value['order'];
                        }
                    }
                    $flag = true;
                }
            } elseif ($key == 'device' and substr($internal_value['value'], 0, 8) == 'DEFINED_') {
                if (check_device($internal_value['value'], $user_params['device'])) {
                    $relevant_params[] = $internal_value;
                    if (!$relevant_param_order) {
                        $relevant_param_order = $internal_value['order'];
                    } else {
                        if ($relevant_param_order > $internal_value['order']) {
                            $relevant_param_order = $internal_value['order'];
                        }
                    }
                    $flag = true;
                }
            } elseif ($key == 'ip') {
                if (check_ip($internal_value['value'], $user_params[$key])) {
                    $relevant_params[] = $internal_value;
                    if (!$relevant_param_order) {
                        $relevant_param_order = $internal_value['order'];
                    } else {
                        if ($relevant_param_order > $internal_value['order']) {
                            $relevant_param_order = $internal_value['order'];
                        }
                    }
                    $flag = true;
                }
            } else {
                if (strripos($internal_value['value'], $user_params[$key]) !== false) {
                    $relevant_params[] = $internal_value;
                    if (!$relevant_param_order) {
                        $relevant_param_order = $internal_value['order'];
                    } else {
                        if ($relevant_param_order > $internal_value['order']) {
                            $relevant_param_order = $internal_value['order'];
                        }
                    }
                    $flag = true;
                }
            }
        }
        $relevant_count = count($relevant_params);
        if ($relevant_count) {
            $relevant_arr_key = rand(0, $relevant_count - 1);
            if (!$rule_order || ($rule_order > $relevant_param_order)) {
                $rule_id = $relevant_params[$relevant_arr_key]['rule_id'];
                $out_id = $relevant_params[$relevant_arr_key]['out_id'];
                $rule_order = $relevant_param_order;
            }
        }
    }
}

$redirect_link = str_ireplace('[SUBID]', $subid, get_out_link($out_id));

// Add rule id
$str.=$rule_id . "\t";

// Add out id
$str.=$out_id . "\t";

// Other link params
// Limit number of params to 5
$track_request = array_slice($track_request, 3, 5);

// Extend array to 5 params exactly
$arr_link_params = array();
for ($i = 0; $i < 5; $i++) {
    if (isset($track_request[$i])) {
        $arr_link_params[] = $track_request[$i];
    } else {
        $arr_link_params[] = '';
    }
}

$link_params = implode("\t", $arr_link_params);

// Additional GET params
$request_params = $_GET;
$get_request = array();
foreach ($request_params as $key => $value) {
    if ($key == 'track_request') {
        continue;
    }
    if (strtoupper(substr($key, 0, 3)) == 'IN_') {
        $var = substr($key, 3);
        $redirect_link = str_ireplace('[' . $var . ']', $value, $redirect_link);
    }
    $get_request[] = "{$key}={$value}";
}

// Cleaning not used []-params
$redirect_link = preg_replace('/\=(\[[a-z\_0-9]+\])/i', '=', $redirect_link);

// Write cookie with parent SubID
$url = parse_url($redirect_link);
$is_unique = add_parent_subid($url['host'], $subid);

// Add unique user
$str.=$is_unique . "\t";

// Possibly last value, don't add \t to the end
$str.=$link_params;

// Last value, don't add \t
$request_string = implode('&', $get_request);
if (strlen($request_string) > 0) {
    $str.="\t" . $request_string;
}

$str.="\n";

// Save click information in file

if (!is_dir(_CACHE_PATH . '/clicks')) {
    mkdir(_CACHE_PATH . '/clicks');
    chmod(_CACHE_PATH . '/clicks', 0777);
}

file_put_contents(_CACHE_PATH . '/clicks/' . '.clicks_' . date('Y-m-d-H-i'), $str, FILE_APPEND | LOCK_EX);

// Redirect
if ($_REQUEST['blind'] === '1') {
    // Meta-refresh
    echo '<!doctype html><html lang=en><head><meta http-equiv="refresh" content="0;URL=' . $redirect_link . '" /><title>Default</title></head><body></body></html>';
} else {
    // Location
    header("Location: " . $redirect_link);
}
exit();
?>