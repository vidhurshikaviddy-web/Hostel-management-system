
<?php
include "../db_connect.php";

$sql = "
    SELECT
        ra.allocation_id,
        s.student_id,
        s.student_name,
        h.hostel_name,
        r.room_number,
        ra.start_date,
        ra.end_date
    FROM Room_Allocation ra
    INNER JOIN Student s ON ra.student_id = s.student_id
    INNER JOIN Room r ON ra.room_id = r.room_id
    INNER JOIN Hostel h ON r.hostel_id = h.hostel_id
    ORDER BY ra.allocation_id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Allocations</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>Room Allocation Management</h2>

    <a href="allocate_room.php">Allocate New Room</a>

    <br><br>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">

        <tr>
            <th>Allocation ID</th>
            <th>Student</th>
            <th>Hostel</th>
            <th>Room Number</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($row = $result->fetch_assoc()): ?>

                <tr>

                    <td><?php echo $row['allocation_id']; ?></td>

                    <td>
                        <?php
                        echo htmlspecialchars($row['student_name']);
                        ?>
                        (<?php echo $row['student_id']; ?>)
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['hostel_name']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['room_number']); ?>
                    </td>

                    <td><?php echo $row['start_date']; ?></td>

                    <td>
                        <?php echo $row['end_date'] ?? "Not ended"; ?>
                    </td>

                    <td>
                        <?php
                        echo $row['end_date'] === null
                            ? "Active"
                            : "Ended";
                        ?>
                    </td>

                    <td>

                        <?php if ($row['end_date'] === null): ?>

                            <a href="edit_allocation.php?id=<?php echo $row['allocation_id']; ?>">
                                Edit / Transfer
                            </a>

                            <br><br>

                            <form method="POST" action="end_allocation.php">

                                <input
                                    type="hidden"
                                    name="allocation_id"
                                    value="<?php echo $row['allocation_id']; ?>"
                                >

                                <label>End Date:</label>

                                <input
                                    type="date"
                                    name="end_date"
                                    required
                                >

                                <button type="submit">
                                    End Allocation
                                </button>

                            </form>

                        <?php else: ?>

                            <span>Completed</span>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>
                <td colspan="8">No room allocations found.</td>
            </tr>

        <?php endif; ?>

    </table>

    <br>

    <a href="../index.php">Back to Dashboard</a>

</div>

</body>
</html>
