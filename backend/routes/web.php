<?php

$routes = [
    'POST /auth/login' => ['AuthController', 'login'],
    'POST /auth/logout' => ['AuthController', 'logout'],
    'POST /users/invite' => ['UserController', 'invite'],
    'GET /auth/logout' => ['AuthController', 'logout'],
    'POST /auth/register' => ['AuthController', 'register'],
    'POST /auth/reset-password' => ['AuthController', 'resetPassword'],
    'POST /auth/activate-invitation' => ['AuthController', 'activateInvitation'],
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
];

function route($requestUri, $requestMethod)
{
    global $routes;

    // Remove query string
    $requestUri = strtok($requestUri, '?');
    $requestUri = str_replace('\\', '/', $requestUri);

    // Strip backend directory prefix (works when app is not at document root)
    $backendDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    if ($backendDir !== '' && strpos($requestUri, $backendDir) === 0) {
        $requestUri = substr($requestUri, strlen($backendDir));
    }

    // Remove leading /index.php or index.php from path-style URLs
    if (strpos($requestUri, '/index.php') === 0) {
        $requestUri = substr($requestUri, strlen('/index.php'));
    } elseif (strpos($requestUri, 'index.php/') === 0) {
        $requestUri = substr($requestUri, strlen('index.php'));
    }

    if ($requestUri === '' || $requestUri[0] !== '/') {
        $requestUri = '/' . ltrim($requestUri, '/');
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

            $controllerClass = $action[0];
            $controllerPath = __DIR__ . '/../controllers/' . $controllerClass . '.php';
            if (!is_file($controllerPath)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Controller not found']);
                return;
            }
            require_once $controllerPath;

            $controller = new $controllerClass();
            call_user_func_array([$controller, $action[1]], $params);
            return;
        }
    }

    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Endpoint not found', 'cleaned_uri' => $requestUri, 'method' => $requestMethod]);
}
