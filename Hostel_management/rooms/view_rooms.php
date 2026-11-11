
<?php
include "../db_connect.php";

$sql = "SELECT 
            Room.room_id,
            Hostel.hostel_name,
            Room.room_number,
            Room.capacity
        FROM Room
        INNER JOIN Hostel
        ON Room.hostel_id = Hostel.hostel_id
        ORDER BY Room.room_id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Rooms</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>Room Management</h2>

    <a href="add_room.php">Add New Room</a>

    <br><br>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">

        <tr>
            <th>Room ID</th>
            <th>Hostel Name</th>
            <th>Room Number</th>
            <th>Capacity</th>
            <th>Actions</th>
        </tr>

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($row = $result->fetch_assoc()): ?>

                <tr>
                    <td><?php echo $row['room_id']; ?></td>

                    <td>
                        <?php echo htmlspecialchars($row['hostel_name']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['room_number']); ?>
                    </td>

                    <td><?php echo $row['capacity']; ?></td>

                    <td>
                        <a href="edit_room.php?id=<?php echo $row['room_id']; ?>">
                            Edit
                        </a>
                    </td>
                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>
                <td colspan="5">No rooms found.</td>
            </tr>

        <?php endif; ?>

    </table>

    <br>

    <a href="../index.php">Back to Dashboard</a>

</div>

</body>
</html>
