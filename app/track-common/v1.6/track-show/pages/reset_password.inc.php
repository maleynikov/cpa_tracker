<?php

if (!$include_flag) {
    exit();
}

// Хеш сброса пароля
// 1 	tracker@copeac.in 	08b02bc6f6297b2ee45b52ea9057fa49 	0738a83
$reset_hash = rq('hash');

if (!empty($reset_hash)) {
    $right_hash = reset_password_hash();

    if ($right_hash['hash'] == $reset_hash) {
        //echo $right_hash['email'];

        $new_password = generate_random_string(10);
        change_password($right_hash['email'], $new_password);

        $message = 'Ваш новый пароль: ' . $new_password;

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";

        mail($right_hash['email'], 'CPATracker new password', $message, $headers);

        echo '<div class="alert alert-success">Вам было отправлено письмо с новым паролем</div>';
    } else {
        echo '<div class="alert alert-danger">Неправильная ссылка для смены пароля</div>';
    }
}
