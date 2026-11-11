
<?php
include "../db_connect.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid scanner ID.");
}

$scanner_id = (int) $_GET["id"];
$message = "";

$stmt = $conn->prepare(
    "SELECT hostel_id, scanner_location FROM Scanner WHERE scanner_id = ?"
);
$stmt->bind_param("i", $scanner_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Scanner not found.");
}

$scanner = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hostel_id = $_POST["hostel_id"];
    $scanner_location = trim($_POST["scanner_location"]);

    if ($hostel_id == "" || $scanner_location == "") {
        $message = "Please fill all fields.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE Scanner
             SET hostel_id = ?, scanner_location = ?
             WHERE scanner_id = ?"
        );

        $stmt->bind_param("isi", $hostel_id, $scanner_location, $scanner_id);

        if ($stmt->execute()) {
            $message = "Scanner updated successfully!";

            $scanner["hostel_id"] = $hostel_id;
            $scanner["scanner_location"] = $scanner_location;
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
    <title>Edit Scanner</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="student-container">
    <h2>Edit Scanner</h2>

    <?php if ($message != ""): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST">

        <label>Hostel:</label>
        <select name="hostel_id" required>
            <?php while ($hostel = $hostels->fetch_assoc()): ?>
                <option value="<?php echo $hostel['hostel_id']; ?>"
                    <?php if ($hostel['hostel_id'] == $scanner['hostel_id']) echo "selected"; ?>>
                    <?php echo htmlspecialchars($hostel['hostel_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <br><br>

        <label>Scanner Location:</label>
        <input type="text" name="scanner_location"
               value="<?php echo htmlspecialchars($scanner['scanner_location']); ?>"
               required>

        <br><br>

        <button type="submit">Update Scanner</button>
    </form>

    <br>
    <a href="view_scanners.php">Back to Scanners</a>
</div>

</body>
</html>
