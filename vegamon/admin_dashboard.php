<?php
session_start();
require_once 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle logout FIRST - before any output
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

// Handle add booking form submission - MOVE THIS TO THE VERY TOP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_booking'])) {
    $client_name = $_POST['client_name'];
    $client_email = $_POST['client_email'];
    $client_phone = $_POST['client_phone'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests = $_POST['guests'];
    
    // Validate dates
    if (strtotime($check_in) >= strtotime($check_out)) {
        $error = "Check-out date must be after check-in date.";
    } else {
        // Check for existing bookings for this client on these dates
        $check_stmt = $pdo->prepare("
            SELECT id FROM bookings 
            WHERE client_email = ? 
            AND status IN ('pending', 'confirmed')
            AND (
                (check_in <= ? AND check_out >= ?) OR
                (check_in <= ? AND check_out >= ?) OR
                (check_in >= ? AND check_out <= ?)
            )
        ");
        $check_stmt->execute([
            $client_email, 
            $check_in, $check_in,
            $check_out, $check_out, 
            $check_in, $check_out
        ]);
        
        $existing_booking = $check_stmt->fetch();
        
        if ($existing_booking) {
            $error = "You cannot book, the Dates are not available PLEASE SELECT OTHER DATES that are not booked.";
        } else {
            // Insert the new booking
            $insert_stmt = $pdo->prepare("INSERT INTO bookings (client_name, client_email, client_phone, check_in, check_out, guests, status) VALUES (?, ?, ?, ?, ?, ?, 'confirmed')");
            $insert_stmt->execute([$client_name, $client_email, $client_phone, $check_in, $check_out, $guests]);
            
            // KEEP THIS REDIRECT - only for successful bookings
            $_SESSION['success'] = "Booking successfully added for $client_name!";
            header('Location: admin_dashboard.php');
            exit;
        }
    }
}

// THEN get all bookings and handle other actions
$stmt = $pdo->query("SELECT id, client_name, client_email, client_phone, check_in, check_out, guests, status, created_at FROM bookings ORDER BY created_at DESC");
$bookings = $stmt->fetchAll();

// Handle actions
if (isset($_GET['action'])) {
    $booking_id = $_GET['id'] ?? null;
    
    if ($booking_id) {
        switch ($_GET['action']) {
            case 'confirm':
                $update_stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
                $update_stmt->execute([$booking_id]);
                break;
            case 'cancel':
                $update_stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
                $update_stmt->execute([$booking_id]);
                break;
            case 'delete':
                $delete_stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
                $delete_stmt->execute([$booking_id]);
                break;
        }
        header('Location: admin_dashboard.php');
        exit;
    }
}

// Get booked dates for calendar
$calendar_stmt = $pdo->query("SELECT check_in, check_out, status FROM bookings WHERE status = 'confirmed'");
$booked_dates = $calendar_stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Resort Booking</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .header {
            background-color: #333;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .calendar-section {
            grid-column: 1 / -1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
            font-size: 12px;
        }
        .btn-add {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .btn-confirm {
            background-color: #28a745;
            color: white;
        }
        .btn-cancel {
            background-color: #dc3545;
            color: white;
        }
        .btn-delete {
            background-color: #6c757d;
            color: white;
        }
        .btn-logout {
            background-color: #6c757d;
            color: white;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
        }
        .status-pending { color: #ffc107; font-weight: bold; }
        .status-confirmed { color: #28a745; font-weight: bold; }
        .status-cancelled { color: #dc3545; font-weight: bold; }
        #calendar {
            max-width: 100%;
            margin: 20px 0;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .add-booking-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }
        .add-booking-form h3 {
            margin-top: 0;
            margin-bottom: 15px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .btn-submit {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-submit:hover {
            background-color: #218838;
        }

    @media (max-width: 768px) {
        .header {
            padding: 12px 15px;
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 1.5em;
            margin: 0;
        }
        
        .container {
            margin: 10px;
            padding: 15px;
        }
        
        .stats {
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .stat-card {
            padding: 15px;
        }
        
        .stat-number {
            font-size: 1.5em;
        }
        
        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .form-row {
            grid-template-columns: 1fr;
            gap: 10px;
        }
        
        /* FIXED: Only target the specific bookings table */
        .container > table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
        }
        
        /* Alternative: Even more specific selector */
        /* 
        table:not(.fc):not(.fc-scrollgrid) {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }
        */
        
        th, td {
            padding: 8px;
            font-size: 14px;
        }
        
        .btn {
            padding: 4px 8px;
            font-size: 11px;
            margin: 1px;
        }
        
        #calendar {
            padding: 10px;
            margin: 10px 0;
        }
        
        .add-booking-form {
            padding: 15px;
        }
        
        h2 {
            font-size: 1.4em;
        }
        
        h3 {
            font-size: 1.2em;
        }
        
        /* Action buttons stack on mobile */
        td:last-child {
            min-width: 120px;
        }
        
        .btn-confirm,
        .btn-cancel,
        .btn-delete {
            display: block;
            width: 100%;
            margin: 2px 0;
            text-align: center;
        }
        .fc .fc-button {
            font-size: 0.9rem;
        }
        .fc .fc-button {
            margin-top: 20px;
        }
        .fc .fc-toolbar.fc-header-toolbar{
            margin-bottom: 0;
        }
    }

    /* Additional mobile optimization for very small screens */
    @media (max-width: 480px) {
        .stats {
            grid-template-columns: 1fr;
        }
        
        .header h1 {
            font-size: 1.3em;
        }
        
        th, td {
            padding: 6px;
            font-size: 12px;
        }
        
        .btn {
            font-size: 10px;
            padding: 3px 6px;
        }
    }
 .error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 4px;
    margin: 20px auto;
    border: 1px solid #f5c6cb;
    display: block;
    text-align: center;
    font-size: 16px; /* Normal size */
    max-width: 80%;
}

.success-message {
    background-color: #d4edda;
    color: #155724;
    padding: 10px;
    border-radius: 4px;
    margin: 20px auto;
    border: 1px solid #c3e6cb;
    display: block;
    text-align: center;
    font-size: 16px; /* Normal size */
    max-width: 80%;
}
    </style>
</head>
<body>
    <div class="header">
        <h1>Resort Booking Admin</h1>
        <div>
            <span>Welcome, <?php echo $_SESSION['admin_username']; ?></span>
            <a href="?logout=1" class="btn btn-logout" style="margin-left: 15px;">Logout</a>
        </div>
    </div>

<!-- ERROR MESSAGE RIGHT AFTER HEADER -->
<?php if (isset($error)): ?>
    <div class="error-message">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>
<!-- SUCCESS MESSAGE -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="success-message">
        <?php 
        echo htmlspecialchars($_SESSION['success']); 
        unset($_SESSION['success']); // Clear the message after displaying
        ?>
    </div>
<?php endif; ?>


    <div class="container">
        <h2>Booking Overview</h2>
        
        <!-- Statistics -->
        <div class="stats">
            <?php
            $total_bookings = count($bookings);
            $pending = array_filter($bookings, function($b) { return $b['status'] == 'pending'; });
            $confirmed = array_filter($bookings, function($b) { return $b['status'] == 'confirmed'; });
            $cancelled = array_filter($bookings, function($b) { return $b['status'] == 'cancelled'; });
            ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_bookings; ?></div>
                <div>Total Bookings</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #ffc107;"><?php echo count($pending); ?></div>
                <div>Pending Approval</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #28a745;"><?php echo count($confirmed); ?></div>
                <div>Confirmed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #dc3545;"><?php echo count($cancelled); ?></div>
                <div>Cancelled</div>
            </div>
        </div>

        <!-- Calendar Section -->
        <div class="calendar-section">
            <h3>Booking Calendar</h3>
            <div id="calendar"></div>
        </div>

<!-- Add Booking Form -->
<div class="add-booking-form">
    <h3>Add New Booking</h3>
    
    
    <form method="POST" action="">
        <div class="form-row">
            <div class="form-group">
                <label for="client_name">Client Name *</label>
                <input type="text" id="client_name" name="client_name" value="<?php echo isset($_POST['client_name']) ? htmlspecialchars($_POST['client_name']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="client_email">Client Email *</label>
                <input type="email" id="client_email" name="client_email" value="<?php echo isset($_POST['client_email']) ? htmlspecialchars($_POST['client_email']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="client_phone">Client Phone *</label>
                <input type="tel" id="client_phone" name="client_phone" value="<?php echo isset($_POST['client_phone']) ? htmlspecialchars($_POST['client_phone']) : ''; ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="check_in">Check-in Date *</label>
                <input type="date" id="check_in" name="check_in" value="<?php echo isset($_POST['check_in']) ? htmlspecialchars($_POST['check_in']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="check_out">Check-out Date *</label>
                <input type="date" id="check_out" name="check_out" value="<?php echo isset($_POST['check_out']) ? htmlspecialchars($_POST['check_out']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="guests">Number of Guests *</label>
                <input type="number" id="guests" name="guests" min="1" value="<?php echo isset($_POST['guests']) ? htmlspecialchars($_POST['guests']) : ''; ?>" required>
            </div>
        </div>
        <button type="submit" name="add_booking" class="btn-submit">Add Booking</button>
    </form>
</div>

        <h3>All Bookings</h3>
        <?php if (empty($bookings)): ?>
            <p>No bookings found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Guests</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?php echo $booking['id']; ?></td>
                        <td><?php echo htmlspecialchars($booking['client_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['client_email']); ?></td>
                        <td><?php echo htmlspecialchars($booking['client_phone']); ?></td>
                        <td><?php echo $booking['check_in']; ?></td>
                        <td><?php echo $booking['check_out']; ?></td>
                        <td><?php echo $booking['guests']; ?></td>
                        <td>
                            <span class="status-<?php echo $booking['status']; ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($booking['status'] == 'pending'): ?>
                                <a href="?action=confirm&id=<?php echo $booking['id']; ?>" 
                                   class="btn btn-confirm" 
                                   onclick="return confirm('Confirm this booking?')">
                                    Confirm
                                </a>
                                <a href="?action=cancel&id=<?php echo $booking['id']; ?>" 
                                   class="btn btn-cancel" 
                                   onclick="return confirm('Cancel this booking?')">
                                    Cancel
                                </a>
                            <?php endif; ?>
                            <a href="?action=delete&id=<?php echo $booking['id']; ?>" 
                               class="btn btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this booking?')">
                                Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,dayGridWeek'
                },
                events: [
                    <?php foreach ($booked_dates as $date): ?>
                    {
                        title: 'Booked',
                        start: '<?php echo $date['check_in']; ?>',
                        end: '<?php echo date('Y-m-d', strtotime($date['check_out'] . ' +1 day')); ?>',
                        color: '#dc3545',
                        allDay: true
                    },
                    <?php endforeach; ?>
                ],
                eventDisplay: 'block',
                height: 'auto'
            });
            calendar.render();
        });

        
    </script>
</body>
</html>