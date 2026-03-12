<?php
class PaymentGateway {
    public static function processPayment($amount, $cardData, $customerEmail) {
        if (empty($cardData['number']) || empty($cardData['expiry']) || empty($cardData['cvv'])) {
            return ['success' => false, 'error' => 'Invalid card data'];
        }
        if (!self::validateCardNumber($cardData['number'])) {
            return ['success' => false, 'error' => 'Invalid card number'];
        }
        $transactionId = 'txn_' . bin2hex(random_bytes(8));
        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'message' => 'Payment processed successfully'
        ];
    }

    private static function validateCardNumber($number) {
        $number = preg_replace('/\D/', '', $number);
        $sum = 0; $alt = false;
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $n = (int)substr($number, $i, 1);
            if ($alt) { $n *= 2; if ($n > 9) $n = ($n % 10) + 1; }
            $sum += $n;
            $alt = !$alt;
        }
        return ($sum % 10 == 0);
    }
}
?>
