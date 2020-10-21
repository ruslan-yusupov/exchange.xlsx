<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\XlsExchange;

try {

    /**
     * Доступы для тестирования по FTP:
     * host: bone018.timeweb.ru
     * login: cu91437
     * password: Y4UdLnehZSjL
     * path: /tmp/items.xlsx
     */

    $exchange = new XlsExchange();
    $exchange
        ->setInputFile(__DIR__ . '/tmp/order.json')
        ->setOutputFile(__DIR__ . '/tmp/items.xlsx')
        ->export();

} catch (Exception $exception) {

    echo $exception->getMessage();

}
