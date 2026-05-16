<?php
// Import controllers
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/BranchController.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../controllers/DrugController.php';
require_once __DIR__ . '/../controllers/InventoryController.php';
require_once __DIR__ . '/../controllers/TransferController.php';
require_once __DIR__ . '/../controllers/SaleController.php';
require_once __DIR__ . '/../controllers/ReportController.php';
require_once __DIR__ . '/../controllers/NotificationController.php';
require_once __DIR__ . '/../controllers/BackupController.php';

$routes = [
    'POST /auth/login' => ['AuthController', 'login'],
    'POST /auth/logout' => ['AuthController', 'logout'],
    'POST /users/invite' => ['UserController', 'invite'],
    'GET /auth/logout' => ['AuthController', 'logout'],
    'POST /auth/register' => ['AuthController', 'register'],
    'POST /auth/reset-password' => ['AuthController', 'resetPassword'],
    'POST /auth/reset-password-confirm' => ['AuthController', 'resetPasswordConfirm'],
    'POST /auth/activate-invitation' => ['AuthController', 'activateInvitation'],
    'GET /auth/validate-invitation' => ['AuthController', 'validateInvitation'],
    'POST /auth/update-profile' => ['AuthController', 'updateProfile'],
    'POST /auth/change-password' => ['AuthController', 'changePassword'],
    'GET /branches' => ['BranchController', 'index'],
    'GET /branches/{id}' => ['BranchController', 'show'],
    'POST /branches' => ['BranchController', 'create'],
    'PUT /branches/{id}' => ['BranchController', 'update'],
    'DELETE /branches/{id}' => ['BranchController', 'delete'],
    'GET /users' => ['UserController', 'index'],
    'GET /users/{id}' => ['UserController', 'show'],
    'POST /users' => ['UserController', 'create'],
    'PUT /users/{id}' => ['UserController', 'update'],
    'DELETE /users/{id}' => ['UserController', 'delete'],
    'PUT /users/{id}/activate' => ['UserController', 'activate'],
    'PUT /users/{id}/deactivate' => ['UserController', 'deactivate'],
    'GET /drugs' => ['DrugController', 'index'],
    'GET /drugs/{id}' => ['DrugController', 'show'],
    'POST /drugs' => ['DrugController', 'create'],
    'PUT /drugs/{id}' => ['DrugController', 'update'],
    'DELETE /drugs/{id}' => ['DrugController', 'delete'],
    'PUT /inventory/{id}/stock' => ['InventoryController', 'updateStock'],
    'GET /inventory/low-stock' => ['InventoryController', 'lowStockAlerts'],
    'GET /inventory/expiring-soon' => ['InventoryController', 'expiringSoon'],
    'GET /transfers' => ['TransferController', 'index'],
    'POST /transfers' => ['TransferController', 'create'],
    'PUT /transfers/{id}/status' => ['TransferController', 'updateStatus'],
    'GET /sales' => ['SaleController', 'index'],
    'GET /sales/{id}' => ['SaleController', 'show'],
    'POST /sales' => ['SaleController', 'create'],
    'GET /reports/sales' => ['ReportController', 'salesReport'],
    'GET /reports/revenue-by-branch' => ['ReportController', 'revenueByBranch'],
    'GET /reports/revenue-by-pharmacist' => ['ReportController', 'revenueByPharmacist'],
    'GET /reports/top-drugs' => ['ReportController', 'topDrugs'],
    'GET /reports/slow-moving-drugs' => ['ReportController', 'slowMovingDrugs'],
    'GET /notifications' => ['NotificationController', 'index'],
    'PUT /notifications/{id}/read' => ['NotificationController', 'markAsRead'],
    'PUT /notifications/read-all' => ['NotificationController', 'markAllRead'],
    'GET /system/backup' => ['BackupController', 'download'],
];

function route($requestUri, $requestMethod)
{
    global $routes;

    // Remove query string
    $requestUri = strtok($requestUri, '?');
    $requestUri = urldecode($requestUri);

    // Dynamically determine the base path from the script name
    // e.g., if SCRIPT_NAME is /pharmacy system/backend/index.php
    // then the base path to strip is /pharmacy system/backend/
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $backendPath = dirname($scriptName); // /pharmacy system/backend
    
    // Normalize paths to use forward slashes and ensure trailing slash for comparison
    $backendPath = str_replace('\\', '/', $backendPath);
    if (substr($backendPath, -1) !== '/') {
        $backendPath .= '/';
    }

    if (strpos($requestUri, $backendPath) === 0) {
        $requestUri = substr($requestUri, strlen($backendPath));
    }

    // Also handle if the URI includes index.php explicitly
    $scriptPath = str_replace('\\', '/', $scriptName);
    if (strpos($requestUri, $scriptPath) === 0) {
        $requestUri = substr($requestUri, strlen($scriptPath));
    } elseif (strpos($requestUri, 'index.php/') === 0) {
        $requestUri = substr($requestUri, strlen('index.php/'));
    }

    // Ensure the URI starts with a slash for matching
    if (substr($requestUri, 0, 1) !== '/') {
        $requestUri = '/' . $requestUri;
    }

    foreach ($routes as $routePattern => $action) {
        list($method, $path) = explode(' ', $routePattern, 2);
        if ($method !== $requestMethod) continue;

        // Convert route path to regex
        $regex = preg_replace('/\{([a-z]+)\}/', '(?P<$1>[^/]+)', $path);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $requestUri, $matches)) {
            $params = array_filter($matches, function ($key) {
                return !is_numeric($key);
            }, ARRAY_FILTER_USE_KEY);

            $controller = new $action[0]();
            call_user_func_array([$controller, $action[1]], $params);
            return;
        }
    }

    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Endpoint not found', 'cleaned_uri' => $requestUri, 'method' => $requestMethod]);
}
