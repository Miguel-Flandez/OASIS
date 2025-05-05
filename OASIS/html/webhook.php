<?php
session_start();

// Include database connection
require_once '../includes/db_connection.php';

// Load PayMongo SDK (absolute path based on your vendor location)
require_once 'C:/xampp/htdocs/OASIS/vendor/autoload.php';
use Paymongo\PaymongoClient;

// Configure Paymongo with Test Secret Key
$paymongoClient = null;
try {
    // $paymongoClient = new PaymongoClient('sk_test_Armfvn7Z1iADDVEvpoisocrk'); // Instantiate with API key
} catch (Exception $e) {
    error_log("Failed to initialize Paymongo Client: " . $e->getMessage());
    http_response_code(500);
    exit("Error initializing Paymongo SDK.");
}

// Get the raw POST data
$payload = file_get_contents('php://input');
$signature_header = $_SERVER['HTTP_X_PAYMONGO_SIGNATURE'] ?? '';
$event = json_decode($payload, true);

// Verify webhook payload
if (!$event || !isset($event['type'])) {
    http_response_code(400);
    exit('Invalid payload');
}

// Handle source.chargeable event
if ($event['type'] === 'source.chargeable' && $paymongoClient) {
    $source_id = $event['data']['id'];
    $selected_studentnumber = $_SESSION['studentnumber'] ?? null;
    $parent_id = $_SESSION['parent_id'] ?? null;

    if (!$selected_studentnumber || !$parent_id) {
        http_response_code(400);
        exit('Missing session data');
    }

    try {
        $source = $paymongoClient->sources->retrieve($source_id);
        if ($source['data']['attributes']['status'] === 'chargeable') {
            $payment = $paymongoClient->payments->create([
                'source' => ['id' => $source_id, 'type' => 'source'],
                'amount' => $source['data']['attributes']['amount'],
                'currency' => 'PHP',
                'description' => 'Payment for student ' . $selected_studentnumber
            ]);

            if ($payment['data']['attributes']['status'] === 'paid') {
                // Update fees and store in history
                $selected_fees = $_SESSION['selected_fees'] ?? [];
                $amount_paid = $payment['data']['attributes']['amount'] / 100;
                $receipt_number = 'REC' . time();
                $transaction_date = date('Y-m-d H:i:s');
                $fee_details = $_SESSION['fee_details'] ?? [];

                $conn->begin_transaction();
                // Fetch current tuition and misc fees to update
                $tuition_stmt = $conn->prepare("SELECT id, status FROM tuition WHERE studentnumber = ?");
                $tuition_stmt->bind_param("s", $selected_studentnumber);
                $tuition_stmt->execute();
                $tuition_fees = $tuition_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $tuition_stmt->close();

                $misc_stmt = $conn->prepare("SELECT id, status FROM misc WHERE studentnumber = ?");
                $misc_stmt->bind_param("s", $selected_studentnumber);
                $misc_stmt->execute();
                $misc_fees = $misc_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $misc_stmt->close();

                foreach (array_merge($tuition_fees, $misc_fees) as $fee) {
                    if (in_array($fee['id'], $selected_fees) && $fee['status'] === 'Unpaid') {
                        $update_stmt = $conn->prepare("UPDATE tuition SET status = 'Paid' WHERE id = ? AND studentnumber = ?");
                        if (!$update_stmt) {
                            $update_stmt = $conn->prepare("UPDATE misc SET status = 'Paid' WHERE id = ? AND studentnumber = ?");
                        }
                        $update_stmt->bind_param("is", $fee['id'], $selected_studentnumber);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }
                }

                $history_stmt = $conn->prepare("INSERT INTO history (receiptnumber, transactiondate, amountpaid, fees, parent_id, studentnumber) VALUES (?, ?, ?, ?, ?, ?)");
                $fees_string = implode(', ', $fee_details);
                $history_stmt->bind_param("ssdisi", $receipt_number, $transaction_date, $amount_paid, $fees_string, $parent_id, $selected_studentnumber);
                $history_stmt->execute();
                $conn->commit();

                unset($_SESSION['selected_fees'], $_SESSION['total_amount'], $_SESSION['fee_details']);
                $_SESSION['payment_response'] = ['success' => 'Payment successful! Receipt Number: ' . $receipt_number];
            }
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log('Webhook error: ' . $e->getMessage());
        $_SESSION['payment_response'] = ['error' => 'Payment processing failed: ' . $e->getMessage()];
    }
}

// Acknowledge the webhook
http_response_code(200);
exit;