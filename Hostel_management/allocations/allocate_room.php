
<?php
include "../db_connect.php";

$message = "";
$message_type = "";

// Get students without an active room allocation
$students = $conn->query("
    SELECT s.student_id, s.student_name
    FROM Student s
    WHERE NOT EXISTS (
        SELECT 1
        FROM Room_Allocation ra
        WHERE ra.student_id = s.student_id
        AND ra.end_date IS NULL
    )
    ORDER BY s.student_name
");

// Get all rooms with hostel names
$rooms = $conn->query("
    SELECT r.room_id, r.room_number, r.capacity,
           h.hostel_name
    FROM Room r
    INNER JOIN Hostel h ON r.hostel_id = h.hostel_id
    ORDER BY h.hostel_name, r.room_number
");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $student_id = (int)$_POST['student_id'];
    $room_id = (int)$_POST['room_id'];
    $start_date = $_POST['start_date'];

    if ($student_id <= 0 || $room_id <= 0 || $start_date == "") {

        $message = "Please enter all allocation details!";
        $message_type = "error";

    } else {

        try {

            $conn->begin_transaction();

            // Lock selected room
            $stmt = $conn->prepare("
                SELECT capacity
                FROM Room
                WHERE room_id = ?
                FOR UPDATE
            ");

            $stmt->bind_param("i", $room_id);
            $stmt->execute();

            $room = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$room) {
                throw new Exception("Selected room does not exist!");
            }

            // Check whether student already has an active allocation
            $stmt = $conn->prepare("
                SELECT allocation_id
                FROM Room_Allocation
                WHERE student_id = ?
                AND end_date IS NULL
                FOR UPDATE
            ");

            $stmt->bind_param("i", $student_id);
            $stmt->execute();

            $active_allocation = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($active_allocation) {
                throw new Exception(
                    "This student already has an active room allocation!"
                );
            }

            // Count currently occupied places
            $stmt = $conn->prepare("
                SELECT COUNT(*) AS occupied
                FROM Room_Allocation
                WHERE room_id = ?
                AND end_date IS NULL
            ");

            $stmt->bind_param("i", $room_id);
            $stmt->execute();

            $occupied = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($occupied['occupied'] >= $room['capacity']) {
                throw new Exception("This room has reached its maximum capacity!");
            }

            // Create allocation
            $stmt = $conn->prepare("
                INSERT INTO Room_Allocation
                (student_id, room_id, start_date, end_date)
                VALUES (?, ?, ?, NULL)
            ");

            $stmt->bind_param(
                "iis",
                $student_id,
                $room_id,
                $start_date
            );

            $stmt->execute();
            $stmt->close();

            $conn->commit();

            $message = "Room allocated successfully!";
            $message_type = "success";

            // Refresh student list
            $students = $conn->query("
                SELECT s.student_id, s.student_name
                FROM Student s
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM Room_Allocation ra
                    WHERE ra.student_id = s.student_id
                    AND ra.end_date IS NULL
                )
                ORDER BY s.student_name
            ");

        } catch (Throwable $e) {

            $conn->rollback();

            $message = $e->getMessage();
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Allocate Room</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>Allocate Room to Student</h2>

    <?php if ($message != ""): ?>
        <p style="color:<?php echo $message_type == 'success' ? 'green' : 'red'; ?>;">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">

        <label>Select Student:</label>

        <select name="student_id" required>
            <option value="">-- Select Student --</option>

            <?php while ($student = $students->fetch_assoc()): ?>
                <option value="<?php echo $student['student_id']; ?>">
                    <?php
                    echo htmlspecialchars(
                        $student['student_name'] .
                        " (ID: " . $student['student_id'] . ")"
                    );
                    ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Select Room:</label>

        <select name="room_id" required>
            <option value="">-- Select Room --</option>

            <?php while ($room = $rooms->fetch_assoc()): ?>
                <option value="<?php echo $room['room_id']; ?>">
                    <?php
                    echo htmlspecialchars(
                        $room['hostel_name'] . " - " .
                        $room['room_number'] . " (Capacity: " .
                        $room['capacity'] . ")"
                    );
                    ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Allocation Start Date:</label>

        <input
            type="date"
            name="start_date"
            required
        >

        <button type="submit">Allocate Room</button>

    </form>

    <br>

    <a href="view_allocations.php">View Allocations</a>

</div>

</body>
</html>
