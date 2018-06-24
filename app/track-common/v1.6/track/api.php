<?php
header('Content-Type: text/html; charset=UTF-8'); 
require _TRACK_COMMON_PATH . '/functions.php'; 

$act       = rq('act');
$track_key = rq('key');

if(get_magic_quotes_gpc()) {
	$_REQUEST = stripslashes2($_REQUEST);
}
/*
if($act != 'ping') {
	dmp($_REQUEST);
}*/

$out = array(
	'status' => 1, // Всё хорошо
	'data'   => array(),
);

function api_error($error = '') {
	$out = array(
		'status' => 0,
		'error'  => $error,
	);
	return $out;
}

if($track_key != _SELF_TRACK_KEY) {
	$out = api_error('Invalid track key');
	echo json_encode($out);
	exit;
}

$maxsize = 2000000; // максимальный размер отдаваемых данных

// Получение данных
if($act == 'data_get') {
	$type = rq('type');
	if(!in_array($type, array('clicks', 'postback'))) {
		$out = api_error('Unknown type');
	} else {
		$path = _TRACK_PATH . '/cache/' . $type;
		$files = dir_files($path, $type);
		$size = 0;
		foreach($files as $f) {
			$full_name =  $path . '/' . $f;
			$size += filesize($full_name);
			// Прерываем выполение, если отдаётся больше максимального размера данных
			if(!empty($out['data']) and $size >= $maxsize) break;
			
			$out['data'][$f] = mb_convert_encoding(file_get_contents($full_name), "UTF-8", "UTF-8");
		}
	}

// Данные получены сборщиком, теперь их можно удалять
} elseif($act == 'data_get_confirm') {
	$type  = rq('type');
	$confirm_files = explode(',', rq('file'));
	if(!in_array($type, array('clicks', 'postback'))) {
		$out = api_error('Unknown type');
	} else {
		$path = _TRACK_PATH . '/cache/' . $type;
		$files = dir_files($path, $type);
		
		$cnt = 0;
		foreach($files as $f) {
			if(in_array($f, $confirm_files)) {
				// unlink($path . '/' . $f);
				rename($path . '/' . $f, $path . '/' . $f.'*');
				$cnt++;
			}
		}
		$out['data'] = $cnt;
	}

// Обновление кэша правил
} elseif($act == 'rules_update') {
	$rules_cache = rq('rules');
	$rules_path  = _CACHE_PATH . '/rules';
	$errors = array();
	
	if (!is_dir($rules_path)) {
		mkdir ($rules_path);
		chmod ($rules_path, 0777);
	}
	
	foreach($rules_cache as $rule_name => $str_rules) {
		$path = $rules_path . '/.' . $rule_name;
		if(file_put_contents($path, $str_rules, LOCK_EX)) { 
			chmod ($path, 0777);
		} else {
			$errors[] = 'Ошибка записи в файл ' . $path;
		}
	}
	
	
	// Удаляем неактуальные кэши
	$files = dir_files($rules_path);
	foreach($files as $f) {
		if(!array_key_exists(substr($f, 1), $rules_cache)) {
			unlink($rules_path . '/' . $f);
		}
	}
	
	if(!empty($errors)) {
		$out = api_error(join("\n", $errors));
	} else {
		$out['data'] = 'success';
	}
	
// Обновление кэша ссылок
} elseif($act == 'links_update') {
	$links_cache = rq('links');
	$outs_path = _CACHE_PATH . '/outs';
	$errors = array();
	
	if (!is_dir($outs_path)) {
		mkdir ($outs_path);
		chmod ($outs_path, 0777);
	}
	
	foreach($links_cache as $id => $link) {
		$path = $outs_path . '/.' . $id;
		if(file_put_contents($path, $link, LOCK_EX)) {
			chmod ($path, 0777);
		} else {
			$errors[] = 'Ошибка записи в файл ' . $path;
		}
	}
	
	// Удаляем неактуальные кэши
	$files = dir_files($outs_path);
	foreach($files as $f) {
		if(!array_key_exists(substr($f, 1), $links_cache)) {
			unlink($outs_path . '/' . $f);
		}
	}
	
	if(!empty($errors)) {
		$out = api_error(join("\n", $errors));
	} else {
		$out['data'] = 'success';
	}
}

echo json_encode($out);
?>