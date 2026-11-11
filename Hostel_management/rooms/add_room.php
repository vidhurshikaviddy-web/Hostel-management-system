
<?php
include "../db_connect.php";

$message = "";
$message_type = "";

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

        $result = $stmt->get_result();
        $hostel = $result->fetch_assoc();

        $stmt->close();

        if (!$hostel) {

            $message = "Hostel not found! Please enter a registered hostel name.";
            $message_type = "error";

        } else {

            $hostel_id = $hostel['hostel_id'];

            try {

                // Insert room details
                $stmt = $conn->prepare(
                    "INSERT INTO Room (hostel_id, room_number, capacity)
                     VALUES (?, ?, ?)"
                );

                $stmt->bind_param(
                    "isi",
                    $hostel_id,
                    $room_number,
                    $capacity
                );

                $stmt->execute();

                $message = "Room added successfully!";
                $message_type = "success";

                $stmt->close();

            } catch (mysqli_sql_exception $e) {

                if ($e->getCode() == 1062) {
                    $message = "This room number already exists in this hostel!";
                } else {
                    $message = "Unable to add room. Please try again.";
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

    <title>Add Room</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

<div class="student-container">

    <h2>Add New Room</h2>

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
            placeholder="Enter registered hostel name"
            required
        >

        <label>Room Number:</label>

        <input
            type="text"
            name="room_number"
            placeholder="Example: R101"
            required
        >

        <label>Room Capacity:</label>

        <input
            type="number"
            name="capacity"
            min="1"
            placeholder="Enter room capacity"
            required
        >

        <button type="submit">Add Room</button>

    </form>

    <a href="view_rooms.php">View Rooms</a>

</div>

</body>
</html>
