<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_name = $_POST['client_name'];
    $client_email = $_POST['client_email'];
    $client_phone = $_POST['client_phone'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests = $_POST['guests'];
    
    // ✅ ADD VALIDATION FOR EMPTY DATES
    if (empty($check_in) || empty($check_out)) {
        $error = "Please select both check-in and check-out dates.";
    } 
    // ✅ VALIDATE DATE RANGE (check-out must be after check-in)
    else if ($check_in >= $check_out) {
        $error = "Check-out date must be after check-in date.";
    }
    // ✅ VALIDATE FUTURE DATES
    else if ($check_in < date('Y-m-d')) {
        $error = "Check-in date cannot be in the past.";
    }
    else {
        // ✅ ADD DATE CONFLICT VALIDATION (only if dates are valid)
        $conflict_stmt = $pdo->prepare("
            SELECT COUNT(*) as conflict_count 
            FROM bookings 
            WHERE status IN ('pending', 'confirmed') 
            AND (
                (check_in <= ? AND check_out >= ?) OR
                (check_in <= ? AND check_out >= ?) OR
                (check_in >= ? AND check_out <= ?)
            )
        ");
        $conflict_stmt->execute([$check_in, $check_in, $check_out, $check_out, $check_in, $check_out]);
        $conflict = $conflict_stmt->fetch();
        
        if ($conflict['conflict_count'] > 0) {
            $error = "Sorry, the selected dates are not available. Please choose different dates.";
        } else {
            // Insert with pending status
            $stmt = $pdo->prepare("INSERT INTO bookings (client_name, client_email, client_phone, check_in, check_out, guests, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            
            if ($stmt->execute([$client_name, $client_email, $client_phone, $check_in, $check_out, $guests])) {
                $success = "Booking request submitted successfully! It will be confirmed by our admin shortly.";
            } else {
                $error = "Error submitting booking request. Please try again.";
            }
        }
    }
}

// Get next 6 months for calendar
$current_month = date('n');
$current_year = date('Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Stay</title>
    <link rel="stylesheet" href="styles/global.css">
    <script src="script/home.js" defer></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .booking-form {
            max-width: 800px;
            margin: 5rem auto 0 auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .success { 
            color: green; 
            background: #d4edda;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #c3e6cb;
        }
        .error { 
            color: red; 
            background: #f8d7da;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #bee5eb;
        }
        .calendar-section {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .calendar-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }
        .calendar-day {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
        }
        .calendar-day.header {
            background: #007bff;
            color: white;
            font-weight: bold;
        }
        .calendar-day.available {
            background: #d4edda;
            cursor: pointer;
        }
        .calendar-day.booked {
            background: #f8d7da;
            color: #721c24;
        }
        .calendar-day.today {
            border: 2px solid #007bff;
        }
        .calendar-day.selected {
            background: #007bff;
            color: white;
        }
        .calendar-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .availability-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 15px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 3px;
        }
.calendar-day.past {
    background: #f8f9fa;
    color: #6c757d;
    cursor: not-allowed;
}

.calendar-day.available:hover {
    background: #c3e6cb;
    transform: scale(1.05);
}

.calendar-day.selected {
    background: #007bff;
    color: white;
    font-weight: bold;
}

.clear-selection {
    background: #6c757d;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    margin-left: 10px;
    font-size: 14px;
}

.clear-selection:hover {
    background: #545b62;
}

.selected-dates-info {
    text-align: center;
    margin: 10px 0;
    padding: 15px;
    background: #e7f3ff;
    border-radius: 5px;
    border: 1px solid #b8daff;
}

.instructions {
    margin: 15px 0;
    padding: 10px;
    background: #fff3cd;
    border-radius: 5px;
    border: 1px solid #ffeaa7;
}

@media (max-width: 768px) {
    /* Calendar Section Mobile Fix */
    .calendar-section {
        margin: 20px 0;
        padding: 15px;
        overflow-x: hidden;
    }
    
    /* Move month title above calendar */
    .calendar-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: nowrap;
        gap: 8px;
        margin-bottom: 5px; /* Reduced margin since title is above */
    }
    
    /* Month title - moved above calendar */
    .calendar-header {
        text-align: center;
        margin-bottom: 15px;
        order: -1; /* Move to top */
        width: 100%; /* Full width */
    }
    
    #current-month {
        font-size: 18px;
        font-weight: bold;
        margin: 0 0 10px 0;
        text-align: center;
        width: 100%;
        display: block;
    }
    
    /* Navigation buttons container */
    .calendar-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        margin-bottom: 15px;
    }
    
    /* Previous button - left side */
    .calendar-navigation .btn:first-child {
        order: 1;
        margin-right: auto; /* Push to left */
    }
    
    /* Clear All button - center */
    .clear-selection {
        order: 2;
        padding: 8px 12px;
        font-size: 12px;
        background: #6c757d;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin: 0 10px;
    }
    
    /* Next button - right side */
    .calendar-navigation .btn:last-child {
        order: 3;
        margin-left: auto; /* Push to right */
    }
    
    .calendar-navigation .btn {
        width: auto;
        padding: 8px 12px;
        font-size: 14px;
        flex-shrink: 0;
    }
    
    .calendar-grid {
        grid-template-columns: repeat(7, 1fr);
        gap: 3px;
        font-size: 12px;
        min-width: 0;
        margin-top: 10px; /* Space below navigation */
    }
    
    .calendar-day {
        padding: 6px 2px;
        font-size: 11px;
        min-height: 35px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        word-break: break-word;
        overflow: hidden;
    }
    
    .calendar-day.header {
        padding: 8px 2px;
        font-size: 10px;
    }
    
    .calendar-day small {
        font-size: 8px;
        line-height: 1;
        margin-top: 2px;
    }
    
    .selected-dates-info {
        padding: 10px;
        font-size: 12px;
        margin: 8px 0;
    }
    
    .instructions {
        padding: 8px;
        font-size: 12px;
        margin: 10px 0;
    }
    
    .instructions p {
        margin: 5px 0;
    }
    
    .availability-legend {
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
    
    .legend-item {
        font-size: 11px;
    }
    
    .legend-color {
        width: 15px;
        height: 15px;
    }
}

/* Additional fix for very small screens */
@media (max-width: 480px) {
    .calendar-section {
        padding: 10px;
        margin: 15px 0;
    }
    
    .calendar-grid {
        gap: 2px;
    }
    
    .calendar-day {
        padding: 4px 1px;
        font-size: 10px;
        min-height: 30px;
    }
    
    .calendar-day.header {
        padding: 6px 1px;
        font-size: 9px;
    }
    
    .calendar-navigation .btn,
    .clear-selection {
        padding: 6px 10px;
        font-size: 12px;
    }
    
    #current-month {
        font-size: 16px;
    }
}
    </style>
</head>
<body>

    <header class="nav-wrap">
        <nav>
            <!-- Left: Logo -->
            <div class="nav-left">
                <a class="logo" href="index.html">
                    <!-- Using a placeholder logo image -->
                    <img src="images/logo2.png" alt="Cloud Heaven Vagamon Logo" class="logo-img">
                </a>
            </div>

            <!-- Center: Menu -->
            <div class="nav-center">
                <ul class="links">
                    <li><a href="index.html" class="active">Home</a></li>
                    <li><a href="rooms.html">Rooms</a></li>
                    <li>
                      <a href="gallery.html">Gallery</a>
                          <ul class="dropdown">
                            <li><a href="gallery.html#images">Images</a></li>
                            <li><a href="gallery.html#videos">Videos</a></li>
                          </ul>
                    </li>
                    <li><a href="tourist.html">Tourist</a></li>
                    <li><a href="about.html">About</a></li>
                    <li><a href="contact.html">Contact</a></li>
                    <li><a href="booking_form.php" class="active">Book Now</a></li>
                    <li><a href="admin_login.php" class="login-link">Login</a></li>
                </ul>
            </div>

            <!-- Right: Hamburger (only on mobile) -->
            <div class="nav-right">
                <button class="hamburger" id="hambtn">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
            </div>
        </nav>

        <!-- Mobile Dropdown Menu -->
        <div class="mobile-menu" id="mobileMenu">
            <ul>
                <li><a href="index.html" >Home</a></li>
                <li><a href="rooms.html">Rooms</a></li>
                <li>
                  <a href="gallery.html">Gallery</a>
                      <ul class="dropdown">
                        <li><a href="gallery.html#images">Images</a></li>
                        <li><a href="gallery.html#videos">Videos</a></li>
                      </ul>
                </li>
                <li><a href="tourist.html">Tourist</a></li>
                <li><a href="about.html">About</a></li>
                <li><a href="contact.html">Contact</a></li>
                <li><a href="booking_form.php" class="active">Book Now</a></li>
                <li><a href="admin_login.php" class="login-link">Login</a></li>
            </ul>
        </div>
    </header>

    <div class="booking-form">
        <h2>Book Your Resort Stay</h2>
        
        <div class="info">
            <strong>Note:</strong> All bookings require admin confirmation. You'll be notified once your booking is confirmed.
        </div>
        
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Availability Calendar -->
<div class="calendar-section">
    <h3>Select Your Stay Dates</h3>
    <!-- Month Title Above Calendar -->
<div class="calendar-header">
    <h4 id="current-month">Loading...</h4>
</div>

<!-- Navigation Buttons -->
<div class="calendar-navigation">
    <button onclick="changeMonth(-1)" class="btn" style="width: auto;">← Previous</button>
    <button onclick="clearSelection()" class="clear-selection">Clear All</button>
    <button onclick="changeMonth(1)" class="btn" style="width: auto;">Next →</button>
</div>
    
    <div id="selected-dates-info" class="selected-dates-info" style="display: none;">
        <div id="selected-dates-display"></div>
    </div>
    
    <div id="availability-calendar"></div>
    
    <div class="instructions">
        <p><strong>How to book:</strong></p>
        <p>Click on each date you want to stay. Selected dates will turn blue.</p>
        <p><em>Example: For a stay from 15th to 20th, click on 15, 16, 17, 18, 19, and 20</em></p>
    </div>
    
    <div class="availability-legend">
        <div class="legend-item">
            <div class="legend-color" style="background: #d4edda;"></div>
            <span>Available</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: #f8d7da;"></div>
            <span>Booked</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: #007bff;"></div>
            <span>Selected Dates</span>
        </div>
    </div>
</div>

        <form method="POST" id="booking-form">
            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="client_name" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="client_email" required>
            </div>
            <div class="form-group">
                <label>Phone:</label>
                <input type="tel" name="client_phone" required>
            </div>
            <div class="form-group">
                <label>Check-in Date:</label>
                <input type="date" name="check_in" id="check_in" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label>Check-out Date:</label>
                <input type="date" name="check_out" id="check_out" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label>Number of Guests:</label>
                <input type="number" name="guests" min="1" max="10" required>
            </div>
            <button type="submit" class="btn">Submit Booking Request</button>
        </form>
    </div>

    <!-- FOOTER CODE  -->
    <footer>
        <div class="container2">
            <div class="footer-content">
                <div class="footer-logo">
                    <h2>Cloud Heaven Vagamon</h2>
                    <p>Experience luxury amidst the clouds. A serene retreat in the heart of Vagamon's misty hills, where nature meets comfort.</p>
                    <div class="social-links">
                      <a href="#" title="Facebook"><img src="images/facebook.png" alt="Facebook"></a>
                      <a href="#" title="Instagram"><img src="images/instagram.png" alt="Instagram"></a>
                      <a href="#" title="Twitter"><img src="images/twitter.png" alt="Twitter"></a>
                      <a href="#" title="YouTube"><img src="images/youtube.png" alt="YouTube"></a>
                    </div>
                </div>
                
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.html">Home</a></li>
                        <li><a href="rooms.html">Rooms</a></li>
                        <li><a href="gallery.html">Gallery</a></li>
                        <li><a href="about.html">About Us</a></li>
                        <li><a href="contact.html">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-contact">
                    <h3>Contact Info</h3>
                    <p> Vagamon Hills, Idukki District, Kerala, India - 685503</p>
                    <p><a href="tel:+919566667692">mobile no - 9566667692</a></p>
                    <p><a href="mailto:mail1@mail.com">mail id - mail1@mail.com</a></p>
                </div>
                
                
            </div>
            
            <div class="footer-bottom">
                <p><a href="https://eaglevisiontechnology.com/" target="_blank">&copy; 2025 Eagle Vision Technology. All Rights Reserved.</a></p>
            </div>
        </div>
        
    </footer>

<!-- WhatsApp + Call sticky bar (mobile only) -->
<div class="mobile-sticky-actions" aria-hidden="false">
  <a class="action-btn call-btn" href="tel:+919566667692" aria-label="Call us">
    <!-- simple phone SVG -->
    <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 01.95-.27c1.05.24 2.2.37 3.37.37a1 1 0 011 1v3.5a1 1 0 01-1 1C10.07 21.88 2.12 13.93 2.12 4.5A1 1 0 013.12 3.5H6.62a1 1 0 011 1c0 1.17.13 2.32.37 3.37a1 1 0 01-.27.95l-2.2 2.2z"/>
    </svg>
    <span>Call</span>
  </a>

  <a class="action-btn wa-btn" href="https://wa.me/919566667692?text=Hi%20I%20need%20help" target="_blank" rel="noopener" aria-label="Message on WhatsApp">
    <!-- simple WhatsApp SVG -->
    <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <path d="M20.52 3.48A11.91 11.91 0 0012 0C5.37 0 .09 5.28.09 11.91a11.6 11.6 0 001.65 5.9L0 24l6.37-1.66A11.91 11.91 0 0012 24c6.63 0 11.91-5.28 11.91-11.91 0-3.18-1.24-6.17-3.39-8.41zM12 21.5a9.4 9.4 0 01-4.78-1.31l-.34-.2-3.79 1 1.02-3.68-.21-.37A9.4 9.4 0 1121.4 12 9.33 9.33 0 0112 21.5zM17.3 14.2c-.3-.15-1.77-.87-2.05-.97s-.48-.15-.68.15-.78.97-.96 1.17-.35.22-.65.07a6.19 6.19 0 01-1.85-1.14 6.56 6.56 0 01-1.22-1.5c-.13-.23 0-.35.1-.47.1-.1.23-.25.35-.37.12-.12.16-.2.25-.35.08-.15.04-.28-.02-.43-.07-.15-.68-1.64-.93-2.25-.24-.6-.49-.52-.68-.53l-.58-.01c-.2 0-.52.07-.8.37s-1.05 1.03-1.05 2.5 1.08 2.9 1.23 3.1c.15.2 2.12 3.25 5.15 4.55 3.03 1.3 3.03.87 3.57.82.54-.05 1.77-.72 2.02-1.42.24-.7.24-1.3.17-1.42-.07-.12-.27-.2-.57-.35z"/>
    </svg>
    <span>WhatsApp</span>
  </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    let currentDate = new Date();
    let selectedDates = [];

    // Initialize calendar
    function loadCalendar(month, year) {
        fetch(`availability_checker.php?month=${month}&year=${year}`)
            .then(response => response.json())
            .then(bookedDates => {
                displayCalendar(month, year, bookedDates);
            })
            .catch(error => console.error('Error:', error));
    }

    function displayCalendar(month, year, bookedDates) {
        const calendarEl = document.getElementById('availability-calendar');
        const monthNames = ["January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        
        document.getElementById('current-month').textContent = `${monthNames[month-1]} ${year}`;

        const firstDay = new Date(year, month-1, 1);
        const lastDay = new Date(year, month, 0);
        const daysInMonth = lastDay.getDate();
        const startingDay = firstDay.getDay();

        let calendarHTML = '<div class="calendar-grid">';
        
        // Day headers
        const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        dayHeaders.forEach(day => {
            calendarHTML += `<div class="calendar-day header">${day}</div>`;
        });

        // Empty cells for days before the first day of month
        for (let i = 0; i < startingDay; i++) {
            calendarHTML += '<div class="calendar-day"></div>';
        }

        // Days of the month - FIXED TIMEZONE ISSUE
        const today = new Date();
        today.setHours(0,0,0,0);
        
        for (let day = 1; day <= daysInMonth; day++) {
            // Use local date to avoid timezone issues
            const currentDateObj = new Date(year, month-1, day);
            const dateString = formatDateForInput(currentDateObj); // Use local date formatting
            const isToday = currentDateObj.toDateString() === today.toDateString();
            const isBooked = bookedDates[dateString];
            const isPast = currentDateObj < today;
            const isSelected = selectedDates.includes(dateString);
            
            let dayClass = 'calendar-day';
            if (isToday) dayClass += ' today';
            if (isBooked) dayClass += ' booked';
            if (!isBooked && !isPast) dayClass += ' available';
            if (isSelected) dayClass += ' selected';
            if (isPast) dayClass += ' past';

            let clickHandler = '';
            if (!isBooked && !isPast) {
                clickHandler = `onclick="toggleDate('${dateString}')"`;
            }

            calendarHTML += `
                <div class="${dayClass}" 
                     ${clickHandler}
                     data-date="${dateString}">
                    ${day}
                    ${isBooked ? '<br><small>Booked</small>' : ''}
                </div>
            `;
        }

        calendarHTML += '</div>';
        calendarEl.innerHTML = calendarHTML;
        
        // Update form fields and display
        updateSelectedDates();
    }

    // FIXED: Proper date formatting for input fields (YYYY-MM-DD)
    function formatDateForInput(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function toggleDate(dateString) {
        // Parse date in local timezone to avoid UTC issues
        const dateParts = dateString.split('-');
        const date = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
        const today = new Date();
        today.setHours(0,0,0,0);

        if (date < today) {
            alert('Cannot select past dates');
            return;
        }

        const index = selectedDates.indexOf(dateString);
        
        if (index === -1) {
            // Date not selected - add it
            selectedDates.push(dateString);
        } else {
            // Date already selected - remove it
            selectedDates.splice(index, 1);
        }
        
        // Sort dates chronologically
        selectedDates.sort((a, b) => {
            const dateA = new Date(a);
            const dateB = new Date(b);
            return dateA - dateB;
        });
        
        // Update form fields and reload calendar
        updateSelectedDates();
        loadCalendar(currentDate.getMonth() + 1, currentDate.getFullYear());
    }

    function updateSelectedDates() {
        if (selectedDates.length > 0) {
            const checkIn = selectedDates[0];
            const checkOut = selectedDates[selectedDates.length - 1];
            
            // Update form fields
            document.getElementById('check_in').value = checkIn;
            document.getElementById('check_out').value = checkOut;
            
            // Calculate nights (check-out minus check-in)
            const checkInDate = new Date(checkIn);
            const checkOutDate = new Date(checkOut);
            const nights = Math.round((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
            
            // Update display
            document.getElementById('selected-dates-display').innerHTML = `
                <strong>Selected ${selectedDates.length} day(s) - ${nights} night(s):</strong><br>
                Check-in: ${formatDateDisplay(checkIn)}<br>
                Check-out: ${formatDateDisplay(checkOut)}<br>
                <small>Selected: ${selectedDates.map(d => formatDateDisplay(d)).join(', ')}</small>
            `;
            document.getElementById('selected-dates-info').style.display = 'block';
        } else {
            // Clear form fields
            document.getElementById('check_in').value = '';
            document.getElementById('check_out').value = '';
            document.getElementById('selected-dates-info').style.display = 'none';
        }
    }

    function formatDateDisplay(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric'
        });
    }

    function changeMonth(direction) {
        currentDate.setMonth(currentDate.getMonth() + direction);
        loadCalendar(currentDate.getMonth() + 1, currentDate.getFullYear());
    }

    function clearSelection() {
        selectedDates = [];
        document.getElementById('check_in').value = '';
        document.getElementById('check_out').value = '';
        document.getElementById('selected-dates-info').style.display = 'none';
        loadCalendar(currentDate.getMonth() + 1, currentDate.getFullYear());
    }

    // Initialize date pickers (as backup)
    flatpickr("#check_in", {
        minDate: "today",
        onChange: function(selectedDatesArray, dateStr) {
            // If user manually changes check-in, clear calendar selection and use this date
            if (dateStr) {
                selectedDates = [dateStr];
                document.getElementById('check_out').value = '';
                loadCalendar(currentDate.getMonth() + 1, currentDate.getFullYear());
            }
        }
    });

    flatpickr("#check_out", {
        minDate: "today",
        onChange: function(selectedDatesArray, dateStr) {
            // If user manually changes check-out, create range from check-in to check-out
            const checkIn = document.getElementById('check_in').value;
            if (checkIn && dateStr) {
                const start = new Date(checkIn);
                const end = new Date(dateStr);
                selectedDates = [];
                
                // Create continuous range
                for (let dt = new Date(start); dt <= end; dt.setDate(dt.getDate() + 1)) {
                    selectedDates.push(formatDateForInput(dt));
                }
                
                loadCalendar(currentDate.getMonth() + 1, currentDate.getFullYear());
            }
        }
    });

    // Sync form fields with calendar when manually typed
    document.getElementById('check_in').addEventListener('change', function() {
        const checkIn = this.value;
        const checkOut = document.getElementById('check_out').value;
        
        if (checkIn && checkOut) {
            const start = new Date(checkIn);
            const end = new Date(checkOut);
            selectedDates = [];
            
            for (let dt = new Date(start); dt <= end; dt.setDate(dt.getDate() + 1)) {
                selectedDates.push(formatDateForInput(dt));
            }
            
            loadCalendar(currentDate.getMonth() + 1, currentDate.getFullYear());
        } else if (checkIn) {
            selectedDates = [checkIn];
            loadCalendar(currentDate.getMonth() + 1, currentDate.getFullYear());
        }
    });

    document.getElementById('check_out').addEventListener('change', function() {
        const checkIn = document.getElementById('check_in').value;
        const checkOut = this.value;
        
        if (checkIn && checkOut) {
            const start = new Date(checkIn);
            const end = new Date(checkOut);
            selectedDates = [];
            
            for (let dt = new Date(start); dt <= end; dt.setDate(dt.getDate() + 1)) {
                selectedDates.push(formatDateForInput(dt));
            }
            
            loadCalendar(currentDate.getMonth() + 1, currentDate.getFullYear());
        }
    });

    // Add form validation to prevent empty date submission
    document.getElementById('booking-form').addEventListener('submit', function(e) {
        const checkIn = document.getElementById('check_in').value;
        const checkOut = document.getElementById('check_out').value;
        const today = new Date().toISOString().split('T')[0];
        
        // Basic validation
        if (!checkIn || !checkOut) {
            e.preventDefault();
            alert('Please select both check-in and check-out dates.');
            return false;
        }
        
        if (checkIn >= checkOut) {
            e.preventDefault();
            alert('Check-out date must be after check-in date.');
            return false;
        }
        
        if (checkIn < today) {
            e.preventDefault();
            alert('Check-in date cannot be in the past.');
            return false;
        }
        
        return true;
    });

    // Load initial calendar
    loadCalendar(currentDate.getMonth() + 1, currentDate.getFullYear());
</script>
</body>
</html>