<?php if (!$include_flag) {
    exit();
}

$right_hash = reset_password_hash();

$message = 'Чтобы сменить пароль, пройдите по ссылке<br />http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?page=resetpassword&hash=' . $right_hash['hash'];

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=utf-8\r\n";

//$right_hash['email'] = 'al420@tut.by';

mail($right_hash['email'], 'CPATracker lost password', $message, $headers);

echo '<div class="alert alert-warning">На ваш email была отправлена ссылка для смены пароля</div>';
?>