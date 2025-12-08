<?php
/**
 * Input Validator Class
 * Handles validation and sanitization of user inputs
 * 
 * Usage:
 *   $validator = new Validator();
 *   if (!$validator->isEmail($email)) {
 *       return error("Invalid email");
 *   }
 */

class Validator {
    private $errors = [];

    /**
     * Validate and sanitize an email address
     * 
     * @param string $email Email to validate
     * @return bool True if valid
     */
    public function isEmail($email) {
        $email = trim($email);
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate that a string is not empty
     * 
     * @param string $value String to check
     * @param string $fieldName Field name for error message
     * @return bool True if not empty
     */
    public function isRequired($value, $fieldName = 'Field') {
        $value = trim($value);
        if (empty($value)) {
            $this->addError($fieldName . ' is required');
            return false;
        }
        return true;
    }

    /**
     * Validate string length
     * 
     * @param string $value String to check
     * @param int $minLength Minimum length
     * @param int $maxLength Maximum length
     * @param string $fieldName Field name for error message
     * @return bool True if valid length
     */
    public function isLength($value, $minLength = 1, $maxLength = 255, $fieldName = 'Field') {
        $length = strlen(trim($value));
        if ($length < $minLength || $length > $maxLength) {
            $this->addError($fieldName . ' must be between ' . $minLength . ' and ' . $maxLength . ' characters');
            return false;
        }
        return true;
    }

    /**
     * Validate a numeric value (currency, prices, etc.)
     * 
     * @param mixed $value Value to check
     * @param string $fieldName Field name for error message
     * @return bool True if valid number
     */
    public function isNumeric($value, $fieldName = 'Field') {
        if (!is_numeric($value) || $value < 0) {
            $this->addError($fieldName . ' must be a valid number');
            return false;
        }
        return true;
    }

    /**
     * Validate a phone number (basic validation)
     * Accepts formats: 1234567890, (123)456-7890, 123-456-7890, +1-234-567-8900
     * 
     * @param string $phone Phone number to validate
     * @return bool True if valid format
     */
    public function isPhone($phone) {
        $phone = preg_replace('/\D/', '', $phone);
        return strlen($phone) >= 10 && strlen($phone) <= 15;
    }

    /**
     * Sanitize a string for safe HTML output
     * 
     * @param string $input String to sanitize
     * @return string Sanitized string
     */
    public function sanitizeString($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize an email address
     * 
     * @param string $email Email to sanitize
     * @return string Sanitized email (lowercase, trimmed)
     */
    public function sanitizeEmail($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Check if a value is in an array of allowed values
     * Useful for validating dropdown/select values
     * 
     * @param mixed $value Value to check
     * @param array $allowedValues Array of allowed values
     * @param string $fieldName Field name for error message
     * @return bool True if value is allowed
     */
    public function isInArray($value, $allowedValues, $fieldName = 'Field') {
        if (!in_array($value, $allowedValues, true)) {
            $this->addError($fieldName . ' contains an invalid value');
            return false;
        }
        return true;
    }

    /**
     * Add an error message
     * 
     * @param string $error Error message
     */
    public function addError($error) {
        $this->errors[] = $error;
    }

    /**
     * Get all error messages
     * 
     * @return array Array of error messages
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get first error message
     * 
     * @return string|null First error or null if no errors
     */
    public function getFirstError() {
        return !empty($this->errors) ? $this->errors[0] : null;
    }

    /**
     * Check if there are any errors
     * 
     * @return bool True if there are errors
     */
    public function hasErrors() {
        return !empty($this->errors);
    }

    /**
     * Clear all errors
     */
    public function clearErrors() {
        $this->errors = [];
    }
}
?>