
<?php
include "../db_connect.php";

$sql = "SELECT Scanner.scanner_id, Scanner.scanner_location,
               Hostel.hostel_name
        FROM Scanner
        INNER JOIN Hostel ON Scanner.hostel_id = Hostel.hostel_id
        ORDER BY Scanner.scanner_id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Scanners</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="student-container">
    <h2>Scanner Management</h2>

    <a href="add_scanner.php">Add New Scanner</a>

    <br><br>

    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>Scanner ID</th>
            <th>Hostel</th>
            <th>Scanner Location</th>
            <th>Actions</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['scanner_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['hostel_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['scanner_location']); ?></td>
                    <td>
                        <a href="edit_scanner.php?id=<?php echo $row['scanner_id']; ?>">
                            Edit
                        </a>

                        <form method="POST" action="delete_scanner.php"
                              onsubmit="return confirm('Delete this scanner?');"
                              style="display:inline;">
                            <input type="hidden" name="scanner_id"
                                   value="<?php echo $row['scanner_id']; ?>">
                            <button type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No scanners found.</td>
            </tr>
        <?php endif; ?>
    </table>

    <br>
    <a href="record_movement.php">Record Student Entry / Exit</a>
    <br><br>
    <a href="view_logs.php">View Entry-Exit Logs</a>
</div>

</body>
</html>
