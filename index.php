<?php

if (true === file_exists( __DIR__ . '/vendor/autoload.php' )) {
    require_once __DIR__ . '/vendor/autoload.php';
}

require_once 'XlsExchange.php';


try {

    $exchange = new XlsExchange;
    $exchange
        ->setInputFile(__DIR__ . '/tmp/order.json')
        ->setOutputFile(__DIR__ . '/tmp/items.xlsx')
        ->setFtpConnectionData('bone018.timeweb.ru', 'cu91437', 'Y4UdLnehZSjL', '/tmp/items.xlsx')
        ->export();

} catch (Exception $exception) {

    echo $exception->getMessage();

}
