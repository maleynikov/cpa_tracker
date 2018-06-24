<?php

/**
 * Class DatabaseConnection
 * This is singleton class for work with modern mysqli extension.
 * Create the single database connection instance.
 */
class DatabaseConnection
{
    /**
     * @var DatabaseConnection
     */
    private static $instance;

    /**
     * @var mysqli
     */
    private $_connection;

    /**
     * gets the instance via lazy initialization (created on first usage)
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from DatabaseConnection::getInstance() instead
     */
    private function __construct()
    {
        $settings_file = _TRACK_SETTINGS_PATH . '/settings.php';
        $str = file_get_contents($settings_file);
        $str = str_replace('<?php exit(); ?>', '', $str);
        $db_settings = unserialize($str);

        $this->_connection = new mysqli($db_settings['dbserver'], $db_settings['login'],
            $db_settings['password'], $db_settings['dbname']);
    }

    /**
     * @return mysqli connection
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    private function __wakeup()
    {
    }
}