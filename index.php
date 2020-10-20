<?php


require_once 'XlsExchange.php';


try {



    $exchange = new XlsExchange;
    $exchange
        ->setInputFile(__DIR__ . '/tmp/order.json')
        ->setOutputFile(__DIR__ . '/tmp/items.xlsx')
        ->export();




} catch (Exception $exception) {
    echo $exception->getMessage();
}
