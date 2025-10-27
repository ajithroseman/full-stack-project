<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Resort Booking</title>
    <link rel="stylesheet" href="styles/global.css">
    <script src="script/home.js" defer></script>
    <style>
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 300px;
            justify-content: center;
            align-items: center;
            margin: 100px auto;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
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
                    <li><a href="booking_form.php" >Book Now</a></li>
                    <li><a href="admin_login.php" class="login-link" class="active">Login</a></li>
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
                <li><a href="booking_form.php">Book Now</a></li>
                <li><a href="admin_login.php" class="login-link <header class="nav-wrap">
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
                    <li><a href="index.html" >Home</a></li>
                    <li><a href="rooms.html">Rooms</a><li>
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
                    <li><a href="booking_form.php" >Book Now</a></li>
                    <li><a href="admin_login.php" class="login-link active">Login</a></li>
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
                <li><a href="booking_form.php">Book Now</a></li>
                <li><a href="admin_login.php" class="login-link active">Login</a></li>
            </ul>
        </div>
    </header>" >Login</a></li>
            </ul>
        </div>
    </header>


    <div class="login-container">
        <h2>Admin Login</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
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


    
</body>
</html>