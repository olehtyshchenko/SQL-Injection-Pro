<?php

require __DIR__ . '/main.php';

$address[] = isset($_GET['address']) ? $_GET['address'] : '';
echo json_encode([
    'entities' => Process($address)
]);