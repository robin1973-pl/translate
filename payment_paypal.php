<?php
require_once 'auth.php';
require_once 'helpers/i18n.php';
$strings = require 'helpers/ui_strings.php';
$ui_lang = get_user_language();
$p_ui = $strings[$ui_lang]['payment'];
$config = require 'config.php';

$amount_pln = $_GET['amount'] ?? 50;
if ($amount_pln == 1) {
    $credits = 1;
} else {
    $credits = ($amount_pln >= 200) ? 50 : 10;
}
?>
<!DOCTYPE html>
<html lang="<?= $ui_lang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $p_ui['title'] ?></title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; background: #f8fafc;">
    <div class="card" style="max-width: 450px; text-align: center; width: 95%; padding: 3rem; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; background: white;">
        <a href="dashboard.php" style="color: #64748b; text-decoration: none; font-size: 0.9rem; margin-bottom: 2rem; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i class="fas fa-arrow-left"></i> <?= $p_ui['back'] ?>
        </a>
        
        <div style="margin-bottom: 2.5rem;">
            <div style="width: 80px; height: 80px; background: #0070ba; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <i class="fa-brands fa-paypal" style="font-size: 2.5rem; color: #ffffff;"></i>
            </div>
            <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 0.5rem;"><?= $p_ui['recharge_title'] ?></h2>
            <p style="color: #64748b; font-size: 1rem;"><?= $p_ui['get_credits'] ?><b style="color: #0070ba;"><?= $credits ?><?= $p_ui['credits_suffix'] ?></b></p>
            <div style="font-size: 2.5rem; font-weight: 900; margin-top: 1rem; color: #1e293b;"><?= $amount_pln ?> <span style="font-size: 1.2rem; font-weight: 600;">PLN</span></div>
        </div>

        <!-- PRZYCISKI PŁATNOŚCI -->
        <div id="paypal-button-container" style="min-height: 250px;"></div>
        
        <div id="error-display" style="display:none; margin-top: 1rem; color: #ef4444; font-size: 0.9rem; font-weight: 600; background: rgba(239, 68, 68, 0.05); padding: 1rem; border-radius: 12px;"></div>
        
        <p style="margin-top: 2rem; font-size: 0.75rem; color: #94a3b8; line-height: 1.5;">
            <?= $p_ui['methods'] ?>
        </p>
    </div>

    <?php $paypal_cid = trim($config['paypal_client_id']); ?>
    <!-- DODAJEMY enable-funding=blik,p24 ABY WYMUSIĆ ICH WIDOCZNOŚĆ -->
    <script src="https://www.paypal.com/sdk/js?client-id=<?= $paypal_cid ?>&currency=PLN&enable-funding=blik,p24"></script>
    
    <script>
        if (typeof paypal !== 'undefined') {
            paypal.Buttons({
                style: {
                    layout: 'vertical',
                    color:  'gold',
                    shape:  'pill',
                    label:  'pay',
                    height: 50
                },
                createOrder: function(data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                            amount: { currency_code: 'PLN', value: '<?= $amount_pln ?>' },
                            description: '<?= addslashes($p_ui['description']) ?> - <?= $credits ?><?= addslashes($p_ui['credits_suffix']) ?>'
                        }]
                    });
                },
                onApprove: function(data, actions) {
                    return actions.order.capture().then(function(details) {
                        return fetch('process_paypal.php', {
                            method: 'POST',
                            headers: { 'content-type': 'application/json' },
                            body: JSON.stringify({
                                orderID: data.orderID,
                                credits: <?= $credits ?>,
                                amount: '<?= $amount_pln ?>'
                            })
                        }).then(() => window.location.href = 'dashboard.php?success=credits_added');
                    });
                }
            }).render('#paypal-button-container');
        }
    </script>
</body>
</html>
