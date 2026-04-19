<?php

/**
 * Send a JSON success response
 * @param mixed $data The data to return
 * @param string $message Optional success message
 * @param int $statusCode HTTP status code (default 200)
 */
function sendSuccess($data = null, $message = 'Success', $statusCode = 200)
{
    // Clear any previous output (e.g., PHP warnings or notices)
    if (ob_get_length()) ob_clean();

    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

/**
 * Send a JSON error response
 * @param string $message Error message
 * @param int $statusCode HTTP status code (default 400)
 * @param array $errors Optional detailed errors
 */
function sendError($message = 'An error occurred', $statusCode = 400, $errors = [])
{
    // Clear any previous output (e.g., PHP warnings or notices)
    if (ob_get_length()) ob_clean();

    http_response_code($statusCode);
    header('Content-Type: application/json');
    $response = [
        'success' => false,
        'message' => $message
    ];
    if (!empty($errors)) {
        $response['errors'] = $errors;
    }
    echo json_encode($response);
    exit();
}
