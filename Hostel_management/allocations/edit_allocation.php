
<?php
include "../db_connect.php";

$message = "";
$message_type = "";

$allocation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($allocation_id <= 0) {
    die("Invalid allocation ID!");
}

// Get existing allocation
$stmt = $conn->prepare("
    SELECT
        ra.allocation_id,
        ra.student_id,
        ra.room_id,
        ra.start_date,
        ra.end_date,
        s.student_name
    FROM Room_Allocation ra
    INNER JOIN Student s ON ra.student_id = s.student_id
    WHERE ra.allocation_id = ?
");

$stmt->bind_param("i", $allocation_id);
$stmt->execute();

$allocation = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$allocation) {
    die("Allocation not found!");
}

if ($allocation['end_date'] !== null) {
    die("This allocation has already ended and cannot be edited!");
}

// Get rooms
$rooms = $conn->query("
    SELECT
        r.room_id,
        r.room_number,
        r.capacity,
        h.hostel_name
    FROM Room r
    INNER JOIN Hostel h ON r.hostel_id = h.hostel_id
    ORDER BY h.hostel_name, r.room_number
");

// Update allocation
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $new_room_id = (int)$_POST['room_id'];
    $new_start_date = $_POST['start_date'];

    if ($new_room_id <= 0 || $new_start_date == "") {

        $message = "Please enter valid details!";
        $message_type = "error";

    } else {

        try {

            $conn->begin_transaction();

            // Lock current allocation
            $stmt = $conn->prepare("
                SELECT student_id, room_id, end_date
                FROM Room_Allocation
                WHERE allocation_id = ?
                FOR UPDATE
            ");

            $stmt->bind_param("i", $allocation_id);
            $stmt->execute();

            $current = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$current || $current['end_date'] !== null) {
                throw new Exception("This allocation is no longer active!");
            }

            // Lock selected room
            $stmt = $conn->prepare("
                SELECT capacity
                FROM Room
                WHERE room_id = ?
                FOR UPDATE
            ");

            $stmt->bind_param("i", $new_room_id);
            $stmt->execute();

            $selected_room = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$selected_room) {
                throw new Exception("Selected room does not exist!");
            }

            // If transferring to a different room
            if ($new_room_id != $current['room_id']) {

                // Count occupied places in new room
                $stmt = $conn->prepare("
                    SELECT COUNT(*) AS occupied
                    FROM Room_Allocation
                    WHERE room_id = ?
                    AND end_date IS NULL
                ");

                $stmt->bind_param("i", $new_room_id);
                $stmt->execute();

                $occupied = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($occupied['occupied'] >= $selected_room['capacity']) {
                    throw new Exception("The selected room is already full!");
                }

                // Close old allocation
                $stmt = $conn->prepare("
                    UPDATE Room_Allocation
                    SET end_date = ?
                    WHERE allocation_id = ?
                ");

                $stmt->bind_param(
                    "si",
                    $new_start_date,
                    $allocation_id
                );

                $stmt->execute();
                $stmt->close();

                // Create new allocation
                $stmt = $conn->prepare("
                    INSERT INTO Room_Allocation
                    (student_id, room_id, start_date, end_date)
                    VALUES (?, ?, ?, NULL)
                ");

                $stmt->bind_param(
                    "iis",
                    $current['student_id'],
                    $new_room_id,
                    $new_start_date
                );

                $stmt->execute();
                $stmt->close();

                $message = "Student transferred successfully!";

            } else {

                // Update start date if room remains the same
                $stmt = $conn->prepare("
                    UPDATE Room_Allocation
                    SET start_date = ?
                    WHERE allocation_id = ?
                ");

                $stmt->bind_param(
                    "si",
                    $new_start_date,
                    $allocation_id
                );

                $stmt->execute();
                $stmt->close();

                $message = "Allocation details updated successfully!";
            }

            $conn->commit();

            $message_type = "success";

            // Reload allocation information
            $stmt = $conn->prepare("
                SELECT
                    ra.allocation_id,
                    ra.student_id,
                    ra.room_id,
                    ra.start_date,
                    ra.end_date,
                    s.student_name
                FROM Room_Allocation ra
                INNER JOIN Student s ON ra.student_id = s.student_id
                WHERE ra.allocation_id = ?
            ");

            $stmt->bind_param("i", $allocation_id);
            $stmt->execute();

            $allocation = $stmt->get_result()->fetch_assoc();
            $stmt->close();

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
    <title>Edit Room Allocation</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>Edit Room Allocation</h2>

    <?php if ($message != ""): ?>

        <p style="color:<?php echo $message_type == 'success' ? 'green' : 'red'; ?>;">
            <?php echo htmlspecialchars($message); ?>
        </p>

    <?php endif; ?>

    <form method="POST">

        <label>Student Name:</label>

        <input
            type="text"
            value="<?php echo htmlspecialchars($allocation['student_name']); ?>"
            disabled
        >

        <label>Select New Room:</label>

        <select name="room_id" required>

            <?php while ($room = $rooms->fetch_assoc()): ?>

                <option
                    value="<?php echo $room['room_id']; ?>"
                    <?php
                    echo $room['room_id'] == $allocation['room_id']
                        ? "selected"
                        : "";
                    ?>
                >

                    <?php
                    echo htmlspecialchars(
                        $room['hostel_name'] . " - " .
                        $room['room_number'] .
                        " (Capacity: " . $room['capacity'] . ")"
                    );
                    ?>

                </option>

            <?php endwhile; ?>

        </select>

        <label>Start Date:</label>

        <input
            type="date"
            name="start_date"
            value="<?php echo htmlspecialchars($allocation['start_date']); ?>"
            required
        >

        <button type="submit">Update Allocation</button>

    </form>

    <br>

    <a href="view_allocations.php">Back to Allocations</a>

</div>

</body>
</html>
