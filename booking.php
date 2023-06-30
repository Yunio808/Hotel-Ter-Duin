<?php
// MySQL database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'hotel_ter_duin';

// Establish a connection to the database
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Initialize message variables
$message = '';
$status = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $bedroomType = $_POST['bedroomType'];

    // Check if the start date is in the past
    if (strtotime($startDate) < time()) {
        $message = "Invalid start date. Please select a future date.";
        $status = "error";
    } else {
        // Check if the number of bookings for any date within the range exceeds 10
        $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM bookings WHERE start_date <= ? AND end_date >= ?");
        $stmt->execute([$endDate, $startDate]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $bookingCount = $result['count'];

        if ($bookingCount >= 10) {
            // Room limit exceeded
            $message = "Sorry, the maximum number of bookings for the selected dates has been reached. Please pick another date.";
            $status = "error";
        } else {
            // Insert the booking into the database
            $stmt = $pdo->prepare("INSERT INTO bookings (name, email, start_date, end_date, bedroom_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $startDate, $endDate, $bedroomType]);

            // Booking successful
            $message = "Booking successful! Thank you, $name, for choosing Hotel Ter Duin.";
            $status = "success";
        }
    }
}

// Redirect to the appropriate page based on booking status
if ($status === 'success') {
    header("Location: index.html?message=" . urlencode($message) . "&status=" . urlencode($status));
} else {
    header("Location: booking.html?message=" . urlencode($message) . "&status=" . urlencode($status));
}

exit;
?>