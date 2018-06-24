<?php

//if (!isset($_GET['apikey']))
//    exit;

$data['get']    = $_GET;
$data['post']   = $_POST;

if (!isset($data['get']['date_add'])) {
    $data['get']['date_add'] = date('Y-m-d H:i:s');
}
if ($data['get']['n']=='7offers'){$data['get']['n']='_7offers';}

$s_data = serialize($data)."\n";

if (strlen ($s_data)>0)
{
        file_put_contents(_CACHE_PATH.'/postback/.postback_'.date('Y-m-d-H-i'), $s_data, FILE_APPEND | LOCK_EX);		
}

?>