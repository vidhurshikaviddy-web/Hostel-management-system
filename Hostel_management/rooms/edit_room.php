
<?php
include "../db_connect.php";

$message = "";
$message_type = "";

$room_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($room_id <= 0) {
    die("Invalid Room ID!");
}

// Get existing room details
$stmt = $conn->prepare(
    "SELECT 
        Room.room_id,
        Hostel.hostel_name,
        Room.room_number,
        Room.capacity
     FROM Room
     INNER JOIN Hostel
     ON Room.hostel_id = Hostel.hostel_id
     WHERE Room.room_id = ?"
);

$stmt->bind_param("i", $room_id);
$stmt->execute();

$result = $stmt->get_result();
$room = $result->fetch_assoc();

$stmt->close();

if (!$room) {
    die("Room not found!");
}

// Update room details
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $hostel_name = trim($_POST['hostel_name']);
    $room_number = trim($_POST['room_number']);
    $capacity = (int)$_POST['capacity'];

    if ($hostel_name == "" || $room_number == "" || $capacity <= 0) {

        $message = "Please enter valid room details!";
        $message_type = "error";

    } else {

        // Find hostel ID using hostel name
        $stmt = $conn->prepare(
            "SELECT hostel_id FROM Hostel WHERE hostel_name = ?"
        );

        $stmt->bind_param("s", $hostel_name);
        $stmt->execute();

        $hostel_result = $stmt->get_result();
        $hostel = $hostel_result->fetch_assoc();

        $stmt->close();

        if (!$hostel) {

            $message = "Hostel not found! Enter a registered hostel name.";
            $message_type = "error";

        } else {

            $hostel_id = (int)$hostel['hostel_id'];

            try {

                $stmt = $conn->prepare(
                    "UPDATE Room
                     SET hostel_id = ?, room_number = ?, capacity = ?
                     WHERE room_id = ?"
                );

                $stmt->bind_param(
                    "isii",
                    $hostel_id,
                    $room_number,
                    $capacity,
                    $room_id
                );

                $stmt->execute();
                $stmt->close();

                $message = "Room details updated successfully!";
                $message_type = "success";

                // Update displayed values
                $room['hostel_name'] = $hostel_name;
                $room['room_number'] = $room_number;
                $room['capacity'] = $capacity;

            } catch (mysqli_sql_exception $e) {

                if ($e->getCode() == 1062) {
                    $message = "This room number already exists in this hostel!";
                } else {
                    $message = "Unable to update room details!";
                }

                $message_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Room</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>Edit Room Details</h2>

    <?php if ($message != ""): ?>

        <p class="<?php echo $message_type == 'success' ? 'success-message' : ''; ?>"
           style="<?php echo $message_type == 'error' ? 'color:red;' : ''; ?>">

            <?php echo htmlspecialchars($message); ?>

        </p>

    <?php endif; ?>

    <form method="POST" action="">

        <label>Hostel Name:</label>

        <input
            type="text"
            name="hostel_name"
            value="<?php echo htmlspecialchars($room['hostel_name']); ?>"
            required
        >

        <label>Room Number:</label>

        <input
            type="text"
            name="room_number"
            value="<?php echo htmlspecialchars($room['room_number']); ?>"
            required
        >

        <label>Room Capacity:</label>

        <input
            type="number"
            name="capacity"
            min="1"
            value="<?php echo (int)$room['capacity']; ?>"
            required
        >

        <button type="submit">Update Room</button>

    </form>

    <br>

    <a href="view_rooms.php">Back to View Rooms</a>

</div>

</body>
</html>
