<?php
$num1 = rand(1, 9);
$num2 = rand(1, 9);
$captcha_hash = md5(($num1 + $num2) . 'LPS_SECRET_SALT');

header('Content-Type: application/json');
echo json_encode([
    'num1' => $num1,
    'num2' => $num2,
    'hash' => $captcha_hash
]);
?>
