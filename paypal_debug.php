<?php
include 'auth.php';
$config = require 'config.php';
$paypal_cid = trim($config['paypal_client_id']);
$paypal_mode = $config['paypal_mode'] ?? 'sandbox';

echo "<h1>PayPal Debugger</h1>";
echo "<p>Tryb: <b>$paypal_mode</b></p>";
echo "<p>Client ID (początek): <b>" . substr($paypal_cid, 0, 10) . "...</b></p>";
echo "<hr>";

$sdk_url = "https://www.paypal.com/sdk/js?client-id=$paypal_cid&currency=USD";
echo "<p>Kliknij w poniższy link, aby zobaczyć co odpowiada PayPal:</p>";
echo "<a href='$sdk_url' target='_blank'>Sprawdź bezpośrednio w PayPal SDK</a>";

echo "<hr>";
echo "<p>Jeśli po kliknięciu zobaczysz kod JavaScript, to znaczy że Client ID jest OK.</p>";
echo "<p>Jeśli zobaczysz błąd tekstowy (np. 'Client ID not recognized'), to znaczy że musisz wygenerować nowy klucz w panelu PayPal.</p>";
?>
