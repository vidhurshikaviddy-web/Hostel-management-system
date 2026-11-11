
<?php
date_default_timezone_set("Asia/Colombo");

include "../db_connect.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $student_id = $_POST["student_id"];
    $scanner_id = $_POST["scanner_id"];
    $movement_type = $_POST["movement_type"];

    if (!is_numeric($student_id) || !is_numeric($scanner_id) ||
        !in_array($movement_type, ["IN", "OUT"], true)) {

        $message = "Invalid input.";

    } else {

        $student_id = (int) $student_id;
        $scanner_id = (int) $scanner_id;

        // Get student's currently allocated hostel
        $stmt = $conn->prepare(
            "SELECT Room.hostel_id
             FROM Room_Allocation
             INNER JOIN Room
                ON Room_Allocation.room_id = Room.room_id
             WHERE Room_Allocation.student_id = ?
               AND Room_Allocation.end_date IS NULL"
        );

        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $student_result = $stmt->get_result();

        // Get scanner's hostel
        $stmt2 = $conn->prepare(
            "SELECT hostel_id FROM Scanner WHERE scanner_id = ?"
        );

        $stmt2->bind_param("i", $scanner_id);
        $stmt2->execute();
        $scanner_result = $stmt2->get_result();

        if ($student_result->num_rows == 0) {
            $message = "This student does not have an active room allocation.";

        } elseif ($scanner_result->num_rows == 0) {
            $message = "Selected scanner does not exist.";

        } else {

            $student = $student_result->fetch_assoc();
            $scanner = $scanner_result->fetch_assoc();

            if ($student["hostel_id"] != $scanner["hostel_id"]) {

                $message = "This scanner does not belong to the student's allocated hostel.";

            } else {

                // Check the student's latest movement
                $last_stmt = $conn->prepare(
                    "SELECT movement_type
                     FROM Entry_Exit_Log
                     WHERE student_id = ?
                     ORDER BY swipe_time DESC, log_id DESC
                     LIMIT 1"
                );

                $last_stmt->bind_param("i", $student_id);
                $last_stmt->execute();
                $last_result = $last_stmt->get_result();

                $last_movement = null;

                if ($last_result->num_rows > 0) {
                    $last_row = $last_result->fetch_assoc();
                    $last_movement = $last_row["movement_type"];
                }

                if ($last_movement === $movement_type) {

                    $message = "Invalid movement. The student's last movement was already "
                             . $movement_type . ".";

                } else {

                    $swipe_time = date("Y-m-d H:i:s");

                    $insert_stmt = $conn->prepare(
                        "INSERT INTO Entry_Exit_Log
                         (student_id, scanner_id, swipe_time, movement_type)
                         VALUES (?, ?, ?, ?)"
                    );

                    $insert_stmt->bind_param(
                        "iiss",
                        $student_id,
                        $scanner_id,
                        $swipe_time,
                        $movement_type
                    );

                    if ($insert_stmt->execute()) {
                        $message = "Student " . $movement_type .
                                   " recorded successfully at " . $swipe_time . ".";
                    } else {
                        $message = "Error: " . $insert_stmt->error;
                    }

                    $insert_stmt->close();
                }

                $last_stmt->close();
            }
        }

        $stmt->close();
        $stmt2->close();
    }
}

// Only students with active allocations
$students = $conn->query(
    "SELECT Student.student_id, Student.student_name,
            Hostel.hostel_name
     FROM Student
     INNER JOIN Room_Allocation
        ON Student.student_id = Room_Allocation.student_id
     INNER JOIN Room
        ON Room_Allocation.room_id = Room.room_id
     INNER JOIN Hostel
        ON Room.hostel_id = Hostel.hostel_id
     WHERE Room_Allocation.end_date IS NULL
     ORDER BY Student.student_name"
);

// Get all scanners
$scanners = $conn->query(
    "SELECT Scanner.scanner_id, Scanner.scanner_location,
            Hostel.hostel_name
     FROM Scanner
     INNER JOIN Hostel ON Scanner.hostel_id = Hostel.hostel_id
     ORDER BY Hostel.hostel_name"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Record Student Entry / Exit</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="student-container">

    <h2>Student Entry / Exit Monitoring</h2>

    <?php if ($message != ""): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST">

        <label>Select Student:</label>
        <select name="student_id" required>
            <option value="">Select Student</option>

            <?php while ($student = $students->fetch_assoc()): ?>
                <option value="<?php echo $student['student_id']; ?>">
                    <?php
                    echo htmlspecialchars(
                        $student['student_name'] . " - " . $student['hostel_name']
                    );
                    ?>
                </option>
            <?php endwhile; ?>
        </select>

        <br><br>

        <label>Select Scanner:</label>
        <select name="scanner_id" required>
            <option value="">Select Scanner</option>

            <?php while ($scanner = $scanners->fetch_assoc()): ?>
                <option value="<?php echo $scanner['scanner_id']; ?>">
                    <?php
                    echo htmlspecialchars(
                        $scanner['hostel_name'] . " - " .
                        $scanner['scanner_location']
                    );
                    ?>
                </option>
            <?php endwhile; ?>
        </select>

        <br><br>

        <label>Movement Type:</label>
        <select name="movement_type" required>
            <option value="">Select Movement</option>
            <option value="IN">IN (Entry)</option>
            <option value="OUT">OUT (Exit)</option>
        </select>

        <br><br>

        <button type="submit">Record Movement</button>

    </form>

    <br>

    <a href="view_logs.php">View Entry-Exit Logs</a>
    <br><br>
    <a href="view_scanners.php">Back to Scanners</a>

</div>

</body>
</html>
