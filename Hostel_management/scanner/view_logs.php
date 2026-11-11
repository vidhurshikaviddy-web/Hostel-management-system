
<?php
include "../db_connect.php";

$sql = "SELECT Entry_Exit_Log.log_id,
               Student.student_name,
               Hostel.hostel_name,
               Scanner.scanner_location,
               Entry_Exit_Log.swipe_time,
               Entry_Exit_Log.movement_type
        FROM Entry_Exit_Log
        INNER JOIN Student
            ON Entry_Exit_Log.student_id = Student.student_id
        INNER JOIN Scanner
            ON Entry_Exit_Log.scanner_id = Scanner.scanner_id
        INNER JOIN Hostel
            ON Scanner.hostel_id = Hostel.hostel_id
        ORDER BY Entry_Exit_Log.swipe_time DESC,
                 Entry_Exit_Log.log_id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Entry Exit Logs</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="student-container">

    <h2>Student Entry-Exit History</h2>

    <a href="record_movement.php">Record New Entry / Exit</a>

    <br><br>

    <table border="1" cellpadding="10" cellspacing="0">

        <tr>
            <th>Log ID</th>
            <th>Student Name</th>
            <th>Hostel</th>
            <th>Scanner Location</th>
            <th>Date & Time</th>
            <th>Movement</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>

            <?php while ($row = $result->fetch_assoc()): ?>

                <tr>
                    <td><?php echo $row['log_id']; ?></td>

                    <td>
                        <?php echo htmlspecialchars($row['student_name']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['hostel_name']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['scanner_location']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['swipe_time']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['movement_type']); ?>
                    </td>
                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>
                <td colspan="6">No entry-exit records found.</td>
            </tr>

        <?php endif; ?>

    </table>

    <br>
    <a href="view_scanners.php">Back to Scanner Management</a>

</div>

</body>
</html>
