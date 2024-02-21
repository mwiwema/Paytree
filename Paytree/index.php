<!DOCTYPE html>
<?php 
require_once("config.php");
require_once("extensions/paytree.php");
?>
<html lang="en">
    <head>
        <title>Paytree payments</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <?php include "includes/header.php"; ?>
        <?php 
        // Present error when there is no config data found.
        if (!isset($config)) 
        {
            echo '<div id="error-dialog" class="error-container">';
            echo '<span class="close">&times;</span>';
            echo '<h1 style="text-align: center">ERROR</h1>';
            echo '<p>Fout met ophalen config.</p>';
            echo '</div>';

            echo '<script src="js/script.js"></script>';
            return;
        }
        ?>
        <div class="form-container">
            <h1 id="payment-form-title">Payment Form</h1>
            <form class="payment-form" method="POST">
                    <label for="amount">Amount: </label>
                    <input type="number" name="amount" id="amount" step="0.01" min="0" required>
                    <label for="method-dropdown">Payment methods:</label>
                    <select name="method-dropdown" id="method-dropdown" required>
                        <option value="none">Select payment method</option>
                        <?php 
                            $paytree = new Paytree();

                            // Checks if the class is not loaded, and stops the process.
                            if (!$paytree->classloaded) { return; }

                            // Set the API key.
                            $paytree->setKey($config['username'], $config['password']);

                            // Get the base64 API key.
                            $key = $paytree->getKey();

                            $methods = $paytree->getPaymentMethods($key);

                            // Creates new dropdown items from the methods retrieved by the API call.
                            foreach ($methods as $method) {
                                echo '<option value="' . $method->methodId . '">' . $method->methodName . '</option>';
                            }
                        ?>
                    </select>
                    <input type="submit" id="pay-button" value="Pay">      
            </form>
        </div>
        <?php 
        if (isset($_POST['amount']) && isset($_POST['method-dropdown'])) {
            $amountWithDecimal = $_POST['amount'];
            $amountInCents = intval($amountWithDecimal * 100);
            $methodId = $_POST['method-dropdown'];

            $paytree = new Paytree();

            if (!$paytree->classloaded) { return; }

            $paytree->setKey($config['username'], $config['password']);

            $key = $paytree->getKey();

            $transactionId = $paytree->pay($key, $amountInCents, $methodId);

            // Continues the proces if there is a transaction ID (UUID).
            if (isset($transactionId)) {
                while (true) {
                    $paymentStatus = $paytree->getPaymentStatus($key, $transactionId);

                    // Checks if payment status is ZERO, if so it will wait 5 seconds and retry the request.
                    if ($paymentStatus == 0) {
                        sleep(5);
                        continue;
                    } 
                    
                    // Checks on the error-codes and represents an error dialog.
                    if ($paymentStatus == -100 || $paymentStatus == -200) {
                        echo '<div id="error-dialog" class="error-container">';
                        echo '<span class="close">&times;</span>';
                        echo '<h1 style="text-align: center">ERROR</h1>';
                        echo '<p>Fout met de betaling.</p>';
                        echo '</div>';

                        echo '<script src="js/script.js"></script>';
                        return;
                    } else {
                        // Succesfull transaction and redirect to paytree
                        header("Location: https://paytree.nl");
                        return;
                    }
                }
            }
        }
        ?>
        <?php include "includes/footer.php"; ?>
    </body>
</html>