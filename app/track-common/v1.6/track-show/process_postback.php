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

set_time_limit(0);

if (isset($_GET['debug'])) {
    ini_set('display_errors', 1);
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
}

// First run of process_postback, remove marker file
$process_clicks_marker = _CACHE_PATH . '/.crontab_postback';
if (is_file($process_clicks_marker)) {
    unlink($process_clicks_marker);
}

$settings_file = _TRACK_SETTINGS_PATH . '/settings.php';
$str = file_get_contents($settings_file);
$str = str_replace('<?php exit(); ?>', '', $str);
$arr_settings = unserialize($str);

$_DB_LOGIN = $arr_settings['login'];
$_DB_PASSWORD = $arr_settings['password'];
$_DB_NAME = $arr_settings['dbname'];
$_DB_HOST = $arr_settings['dbserver'];

// Connect to DB
mysql_connect($_DB_HOST, $_DB_LOGIN, $_DB_PASSWORD) or die("Could not connect: " . mysql_error());
mysql_select_db($_DB_NAME);
mysql_query('SET NAMES utf8');

include _TRACK_LIB_PATH . "/class/common.php";
include _TRACK_LIB_PATH . "/class/custom.php";
include _TRACK_SHOW_COMMON_PATH . "/functions_general.php";

function net_loader($class) {
    include_once _TRACK_LIB_PATH . '/postback/' . $class . '.php';
}

spl_autoload_register('net_loader');

$arr_files = array();
if ($handle = opendir(_CACHE_PATH . '/postback')) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && $entry != ".empty") {
            if (
            // Check if file starts with dot,  
                    (strpos($entry, '.') === 0) &&
                    // is not processing now
                    (strpos($entry, '+') === false) &&
                    // and was not processed before
                    (strpos($entry, '*') === false)
            ) {
                // Also check that there were at least 2 minutes from creation date
                if ($entry != '.postback_' . date('Y-m-d-H-i', strtotime('-1 minutes')) &&
                        $entry != '.postback_' . date('Y-m-d-H-i')
                ) {
                    $arr_files[] = $entry;
                }
            }
        }
    }
    closedir($handle);
}

if (count($arr_files) == 0) {
    exit();
}

//Если есть что обрабатывать инициализируем класс собственных правил (custom)
$custom = new custom();

foreach ($arr_files as $cur_file) {
    $file_name = _CACHE_PATH . "/postback/{$cur_file}+";
    rename(_CACHE_PATH . "/postback/$cur_file", $file_name);
    $conversions = file($file_name);
    foreach ($conversions as $conv) {
        $data = unserialize($conv);
        $network = isset($data['get']['n']) ? $data['get']['n'] : (isset($data['post']['n']) ? $data['post']['n'] : '');
        if (empty($network) || $network == 'custom' || !file_exists(_TRACK_LIB_PATH . '/postback/' . $network . '.php')) {
            $custom->process_conversion($data);
        } elseif ($network == 'pixel') {
            $custom->process_pixel($data);
        } else {
            $net = new $network();
            $net->process_conversion($data);
        }
    }

    rename($file_name, _CACHE_PATH . "/postback/{$cur_file}*");
}

exit();
?>