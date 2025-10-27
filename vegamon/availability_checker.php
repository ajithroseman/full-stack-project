<?php
require_once 'db.php';

header('Content-Type: application/json');

if (isset($_GET['month']) && isset($_GET['year'])) {
    $month = (int)$_GET['month'];
    $year = (int)$_GET['year'];
    
    // Get both pending and confirmed bookings
    $stmt = $pdo->prepare("
        SELECT check_in, check_out
        FROM bookings 
        WHERE status IN ('pending', 'confirmed') 
        AND (
            (YEAR(check_in) = ? AND MONTH(check_in) = ?) 
            OR (YEAR(check_out) = ? AND MONTH(check_out) = ?)
            OR (check_in <= ? AND check_out >= ?)
        )
    ");
    
    $start_date = "$year-$month-01";
    $end_date = "$year-$month-" . date('t', strtotime($start_date));
    
    $stmt->execute([$year, $month, $year, $month, $end_date, $start_date]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate booked dates - FIXED VERSION
    $booked_dates = [];
    foreach ($bookings as $booking) {
        $check_in = new DateTime($booking['check_in']);
        $check_out = new DateTime($booking['check_out']);
        
        // ✅ FIX: Include the check-out date in the range
        // Loop through each day of the booking INCLUDING check-out
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($check_in, $interval, $check_out->modify('+1 day'));
        
        foreach ($period as $dt) {
            $date_str = $dt->format('Y-m-d');
            $booked_dates[$date_str] = true; // Just mark as booked, no counting needed
        }
    }
    
    echo json_encode($booked_dates);
    exit;
}
?>