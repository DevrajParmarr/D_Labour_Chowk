<?php
/**
 * Enhanced Form Validation System for D Labour Chowk
 * Provides comprehensive client-side and server-side validation
 */

class FormValidator {
    private $errors = [];
    private $data = [];
    private $rules = [];

    public function __construct($data = []) {
        $this->data = $data;
    }

    /**
     * Set validation data
     */
    public function setData($data) {
        $this->data = $data;
        return $this;
    }

    /**
     * Add validation rule
     */
    public function addRule($field, $rule, $message = '') {
        if (!isset($this->rules[$field])) {
            $this->rules[$field] = [];
        }
        $this->rules[$field][] = ['rule' => $rule, 'message' => $message];
        return $this;
    }

    /**
     * Validate all rules
     */
    public function validate() {
        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                if (!$this->validateRule($field, $value, $rule)) {
                    break; // Stop validating this field on first error
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Validate a single rule
     */
    private function validateRule($field, $value, $rule) {
        $ruleName = $rule['rule'];
        $message = $rule['message'] ?: $this->getDefaultMessage($field, $ruleName);

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0' && $value !== 0) {
                    $this->addError($field, $message);
                    return false;
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, $message);
                    return false;
                }
                break;

            case 'min_length':
                $min = $rule['param'] ?? 0;
                if (!empty($value) && strlen($value) < $min) {
                    $this->addError($field, $message);
                    return false;
                }
                break;

            case 'max_length':
                $max = $rule['param'] ?? PHP_INT_MAX;
                if (!empty($value) && strlen($value) > $max) {
                    $this->addError($field, $message);
                    return false;
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, $message);
                    return false;
                }
                break;

            case 'mobile':
                if (!empty($value) && !preg_match('/^[0-9]{10}$/', $value)) {
                    $this->addError($field, $message);
                    return false;
                }
                break;

            case 'password_strength':
                if (!empty($value) && !$this->isStrongPassword($value)) {
                    $this->addError($field, $message);
                    return false;
                }
                break;

            case 'matches':
                $matchField = $rule['param'] ?? '';
                if ($value !== ($this->data[$matchField] ?? null)) {
                    $this->addError($field, $message);
                    return false;
                }
                break;

            case 'unique':
                $table = $rule['param']['table'] ?? '';
                $column = $rule['param']['column'] ?? $field;
                if (!empty($value) && !$this->isUnique($table, $column, $value)) {
                    $this->addError($field, $message);
                    return false;
                }
                break;
        }

        return true;
    }

    /**
     * Check if password is strong
     */
    private function isStrongPassword($password) {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password);
    }

    /**
     * Check if value is unique in database
     */
    private function isUnique($table, $column, $value) {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM $table WHERE $column = ?");
            $stmt->bind_param('s', $value);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result['count'] == 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Add error message
     */
    private function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * Get default error message
     */
    private function getDefaultMessage($field, $rule) {
        $messages = [
            'required' => ucfirst($field) . ' is required',
            'email' => 'Please enter a valid email address',
            'min_length' => ucfirst($field) . ' must be at least ' . ($this->rules[$field][array_key_last($this->rules[$field])]['param'] ?? 0) . ' characters',
            'max_length' => ucfirst($field) . ' must not exceed ' . ($this->rules[$field][array_key_last($this->rules[$field])]['param'] ?? PHP_INT_MAX) . ' characters',
            'numeric' => ucfirst($field) . ' must be a number',
            'mobile' => 'Please enter a valid 10-digit mobile number',
            'password_strength' => 'Password must be at least 8 characters with uppercase, lowercase, and number',
            'matches' => ucfirst($field) . ' does not match',
            'unique' => ucfirst($field) . ' already exists'
        ];
        return $messages[$rule] ?? 'Invalid ' . $field;
    }

    /**
     * Get all errors
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get errors for specific field
     */
    public function getFieldErrors($field) {
        return $this->errors[$field] ?? [];
    }

    /**
     * Check if field has errors
     */
    public function hasErrors($field = null) {
        if ($field === null) {
            return !empty($this->errors);
        }
        return isset($this->errors[$field]);
    }

    /**
     * Get first error message
     */
    public function getFirstError($field = null) {
        if ($field === null) {
            foreach ($this->errors as $fieldErrors) {
                if (!empty($fieldErrors)) {
                    return $fieldErrors[0];
                }
            }
            return '';
        }
        return $this->errors[$field][0] ?? '';
    }

    /**
     * Get sanitized data
     */
    public function getSanitizedData() {
        $sanitized = [];
        foreach ($this->data as $key => $value) {
            $sanitized[$key] = sanitizeInput($value);
        }
        return $sanitized;
    }

    /**
     * Render validation errors for forms
     */
    public function renderErrors($field = null) {
        if ($field === null) {
            if (empty($this->errors)) return '';

            $html = '<div class="alert alert-danger"><ul class="mb-0">';
            foreach ($this->errors as $fieldErrors) {
                foreach ($fieldErrors as $error) {
                    $html .= '<li>' . htmlspecialchars($error) . '</li>';
                }
            }
            $html .= '</ul></div>';
            return $html;
        }

        if (!$this->hasErrors($field)) return '';

        $html = '<div class="invalid-feedback d-block"><ul class="mb-0">';
        foreach ($this->getFieldErrors($field) as $error) {
            $html .= '<li>' . htmlspecialchars($error) . '</li>';
        }
        $html .= '</ul></div>';
        return $html;
    }
}

// Helper functions for common validations
function validateLoginForm($data) {
    $validator = new FormValidator($data);
    $validator->addRule('mobile_no', 'required')
              ->addRule('mobile_no', 'mobile')
              ->addRule('password', 'required')
              ->addRule('password', 'min_length', ['param' => 6]);

    return $validator;
}

function validateSignupForm($data) {
    $validator = new FormValidator($data);
    $validator->addRule('username', 'required')
              ->addRule('username', 'min_length', ['param' => 3])
              ->addRule('username', 'max_length', ['param' => 50])
              ->addRule('email', 'required')
              ->addRule('email', 'email')
              ->addRule('email', 'unique', ['param' => ['table' => 'user', 'column' => 'email_id']])
              ->addRule('mobile', 'required')
              ->addRule('mobile', 'mobile')
              ->addRule('mobile', 'unique', ['param' => ['table' => 'user', 'column' => 'mobile_no']])
              ->addRule('password', 'required')
              ->addRule('password', 'password_strength')
              ->addRule('confirm_password', 'required')
              ->addRule('confirm_password', 'matches', ['param' => 'password'])
              ->addRule('usertype', 'required');

    return $validator;
}

function validateJobPostForm($data) {
    $validator = new FormValidator($data);
    $validator->addRule('jobTitle', 'required')
              ->addRule('jobTitle', 'min_length', ['param' => 3])
              ->addRule('salary', 'required')
              ->addRule('salary', 'numeric')
              ->addRule('detail', 'required')
              ->addRule('detail', 'min_length', ['param' => 10])
              ->addRule('city', 'required')
              ->addRule('location', 'required');

    return $validator;
}
?>