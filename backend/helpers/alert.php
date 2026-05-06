<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Notification.php';

function checkAndNotifyLowStock($drugId, $newStock)
{
    global $pdo;
    $threshold = 10; // low stock threshold
    
    if ($newStock <= $threshold) {
        $drugModel = new Drug($pdo);
        $userModel = new User($pdo);
        $notifModel = new Notification($pdo);
        
        $drug = $drugModel->findById($drugId);
        if (!$drug) return;
        
        $branchId = $drug['branch_id'];
        $drugName = $drug['name'];
        $message = "Low Stock Alert: $drugName has only $newStock units left in branch " . $drug['branch_id'] . ". Please restock.";
        
        // Find all store keepers and managers in this branch
        $users = $userModel->getAll();
        foreach ($users as $user) {
            if ($user['status'] === 'active' && 
                ($user['role'] === 'store_keeper' || $user['role'] === 'manager') && 
                ($user['branch_id'] == $branchId)) {
                $notifModel->create($user['id'], 'alert', $message);
            }
        }
    }
}

function checkAndNotifyExpiringDrugs()
{
    global $pdo;
    $daysThreshold = 30;
    
    $drugModel = new Drug($pdo);
    $userModel = new User($pdo);
    $notifModel = new Notification($pdo);
    
    $expiringDrugs = $drugModel->getExpiringSoon($daysThreshold);
    
    foreach ($expiringDrugs as $drug) {
        $drugId = $drug['id'];
        $drugName = $drug['name'];
        $expiryDate = $drug['expiry_date'];
        $branchId = $drug['branch_id'];
        
        $message = "Expiry Alert: $drugName (Batch: " . $drug['batch'] . ") is expiring on $expiryDate. Please remove or prioritize.";
        
        // Only notify if a similar notification hasn't been sent recently (e.g., today)
        // For simplicity in this project, we'll just send it.
        
        $users = $userModel->getAll();
        foreach ($users as $user) {
            if ($user['status'] === 'active' && 
                ($user['role'] === 'store_keeper' || $user['role'] === 'manager') && 
                ($user['branch_id'] == $branchId)) {
                
                // Check if notification already exists to avoid spamming
                $stmt = $pdo->prepare("SELECT id FROM notifications WHERE user_id = ? AND message = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)");
                $stmt->execute([$user['id'], $message]);
                if (!$stmt->fetch()) {
                    $notifModel->create($user['id'], 'alert', $message);
                }
            }
        }
    }
}

