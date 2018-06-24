<?php
if ((PHP_MAJOR_VERSION > 5) && extension_loaded('mysqli')) {
    if (!function_exists('mysql_query')) {
        function mysql_query($query) {
            return mysqli_query(DatabaseConnection::getInstance()->getConnection(), $query);
        }
    }

    if (!function_exists('mysql_real_escape_string')) {
        function mysql_real_escape_string($string) {
            return mysqli_real_escape_string(DatabaseConnection::getInstance()->getConnection(), $string);
        }
    }

    if (!function_exists('mysql_num_rows')) {
        function mysql_num_rows($result) {
            return mysqli_num_rows($result);
        }
    }

    if (!function_exists('mysql_fetch_assoc')) {
        function mysql_fetch_assoc($result) {
            return mysqli_fetch_assoc($result);
        }
    }

    if (!function_exists('mysql_fetch_assoc')) {
        function mysql_fetch_assoc($result) {
            return mysqli_fetch_assoc($result);
        }
    }

    if (!function_exists('mysql_error')) {
        function mysql_error() {
            return mysqli_error(DatabaseConnection::getInstance()->getConnection());
        }
    }

    if (!function_exists('mysql_insert_id')) {
        function mysql_insert_id() {
            return mysqli_insert_id(DatabaseConnection::getInstance()->getConnection());
        }
    }

    if (!function_exists('mysql_connect')) {
        function mysql_connect($host, $username, $password, $dbname = "") {
            return mysqli_connect($host, $username, $password, $dbname);
        }
    }

    if (!function_exists('mysql_select_db')) {
        function mysql_select_db($dbname) {
            return mysqli_select_db(DatabaseConnection::getInstance()->getConnection(), $dbname);
        }
    }

    if (!function_exists('mysql_free_result')) {
        function mysql_free_result($result) {
            mysqli_free_result($result);
        }
    }
}
