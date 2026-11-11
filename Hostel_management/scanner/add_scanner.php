
<?php
include "../db_connect.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hostel_id = $_POST["hostel_id"];
    $scanner_location = trim($_POST["scanner_location"]);

    if ($hostel_id == "" || $scanner_location == "") {
        $message = "Please fill all fields.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO Scanner (hostel_id, scanner_location) VALUES (?, ?)"
        );
        $stmt->bind_param("is", $hostel_id, $scanner_location);

        if ($stmt->execute()) {
            $message = "Scanner added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$hostels = $conn->query("SELECT hostel_id, hostel_name FROM Hostel ORDER BY hostel_name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Scanner</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="student-container">
    <h2>Add Scanner</h2>

    <?php if ($message != ""): ?>
        <p class="success-message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <label>Hostel:</label>
        <select name="hostel_id" required>
            <option value="">Select Hostel</option>
            <?php while ($hostel = $hostels->fetch_assoc()): ?>
                <option value="<?php echo $hostel['hostel_id']; ?>">
                    <?php echo htmlspecialchars($hostel['hostel_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <br><br>

        <label>Scanner Location:</label>
        <input type="text" name="scanner_location"
               placeholder="Enter scanner location" required>

        <br><br>

        <button type="submit">Add Scanner</button>
    </form>

    <br>
    <a href="view_scanners.php">View Scanners</a>
</div>

</body>
</html>
