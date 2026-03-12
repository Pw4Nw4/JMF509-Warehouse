<?php
class ErrorHandler {
    public static function handleDatabaseError($e, $userMessage = "An error occurred. Please try again.") {
        if ($e instanceof PDOException) {
            error_log("Database Error: " . $e->getMessage());
        } else {
            error_log("General Error: " . $e->getMessage());
        }
        return $userMessage;
    }

    public static function validateInput($data, $rules) {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = trim($data[$field] ?? '');
            if (!empty($rule['required']) && empty($value)) {
                $errors[$field] = $rule['message'] ?? "$field is required";
                continue;
            }
            if (!empty($value) && isset($rule['type'])) {
                if ($rule['type'] === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "Invalid email format";
                }
                if ($rule['type'] === 'int' && filter_var($value, FILTER_VALIDATE_INT, $rule['options'] ?? []) === false) {
                    $errors[$field] = $rule['message'] ?? "Invalid number";
                }
            }
        }
        return $errors;
    }
}
?>
