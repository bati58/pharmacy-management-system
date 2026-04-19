<?php
class ValidationMiddleware
{
    /**
     * Validate required fields
     * @param array $data Input data
     * @param array $requiredFields List of field names that must be present and non-empty
     * @return array|bool Returns array of errors or true if valid
     */
    public static function required($data, $requiredFields)
    {
        $errors = [];
        foreach ($requiredFields as $field) {
            $value = $data[$field] ?? '';
            if (empty($value) && $value !== '0') {
                $errors[] = "Field '$field' is required.";
            }
        }
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit();
        }
        return true;
    }

    /**
     * Validate email format
     */
    public static function email($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
            exit();
        }
        return true;
    }

    /**
     * Validate that a value is numeric and optionally within range
     */
    public static function numeric($value, $min = null, $max = null)
    {
        if (!is_numeric($value)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Value must be numeric.']);
            exit();
        }
        if ($min !== null && $value < $min) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Value must be at least $min."]);
            exit();
        }
        if ($max !== null && $value > $max) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Value must not exceed $max."]);
            exit();
        }
        return true;
    }

    /**
     * Validate that a string length is within limits
     */
    public static function stringLength($value, $min = 1, $max = 255)
    {
        $len = strlen($value);
        if ($len < $min || $len > $max) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "String length must be between $min and $max characters."]);
            exit();
        }
        return true;
    }

    /**
     * Validate date format (YYYY-MM-DD)
     */
    public static function date($date)
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD.']);
            exit();
        }
        return true;
    }

    /**
     * Validate that expiry date is in the future
     */
    public static function futureDate($date)
    {
        self::date($date);
        $today = new DateTime();
        $expiry = new DateTime($date);
        if ($expiry <= $today) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Expiry date must be in the future.']);
            exit();
        }
        return true;
    }

    /**
     * Validate that a value is one of the allowed options
     */
    public static function inArray($value, $allowed)
    {
        if (!in_array($value, $allowed)) {
            $allowedStr = implode(', ', $allowed);
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Value must be one of: $allowedStr"]);
            exit();
        }
        return true;
    }

    /**
     * Sanitize input data (basic XSS prevention)
     */
    public static function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate sale items array
     * Each item must have drug_id and quantity
     */
    public static function saleItems($items)
    {
        if (!is_array($items) || empty($items)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Sale must contain at least one item.']);
            exit();
        }
        foreach ($items as $index => $item) {
            if (!isset($item['drug_id']) || !is_numeric($item['drug_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Item $index: missing or invalid drug_id."]);
                exit();
            }
            if (!isset($item['quantity']) || !is_numeric($item['quantity']) || $item['quantity'] <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Item $index: quantity must be a positive number."]);
                exit();
            }
        }
        return true;
    }
}
