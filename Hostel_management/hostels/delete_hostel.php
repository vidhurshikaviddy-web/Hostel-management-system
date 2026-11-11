
<?php
include "../db_connect.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $hostel_id = (int)($_POST['hostel_id'] ?? 0);

    if ($hostel_id <= 0) {

        $message = "Invalid hostel ID!";

    } else {

        // Check whether hostel has rooms
        $stmt = $conn->prepare(
            "SELECT room_id FROM Room
             WHERE hostel_id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $hostel_id);
        $stmt->execute();
        $has_rooms = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        // Check employee assignments
        $stmt = $conn->prepare(
            "SELECT assignment_id FROM Employee_Assignment
             WHERE hostel_id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $hostel_id);
        $stmt->execute();
        $has_assignments = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        // Check scanners
        $stmt = $conn->prepare(
            "SELECT scanner_id FROM Scanner
             WHERE hostel_id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $hostel_id);
        $stmt->execute();
        $has_scanners = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($has_rooms || $has_assignments || $has_scanners) {

            $message = "Cannot delete this hostel because related records exist!";

        } else {

            $stmt = $conn->prepare(
                "DELETE FROM Hostel WHERE hostel_id = ?"
            );

            $stmt->bind_param("i", $hostel_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $message = "Hostel deleted successfully!";
            } else {
                $message = "Hostel not found!";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Hostel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>Delete Hostel</h2>

    <p><?php echo htmlspecialchars($message); ?></p>

    <a href="view_hostels.php">Back to Hostel List</a>

</div>

</body>
</html>
