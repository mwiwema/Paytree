<?php
require_once("config.php");
require_once("extensions/paytree.php");

if (!isset($config)) 
{
    return var_dump("Can't get config.php");
}

$paytree = new Paytree();

if (!$paytree->classloaded) 
{
    return var_dump("Extensions could not be loaded!");
}

$paytree->setKey($config['username'], $config['password']);

$key = $paytree->getKey();

$methods = $paytree->getPaymentMethods($key);

/*
foreach ($methods as $method) {
    var_dump($method);
    echo "<br>";
}
*/

$amount = "5,00";
$transactionId = $paytree->pay($key, $amount, $methods[0]->methodId);
var_dump($transactionId);

if (isset($transactionId)) {
    var_dump("paymentstatus: $paymentStatus");

    while (true) {
        $paymentStatus = $paytree->getPaymentStatus($key, $transactionId);

        if ($paymentStatus != 0) {
            echo $paymentStatus;
            return;
        }

        sleep(5);
    }
}



?>