
<?php
include "../db_connect.php";

$sql = "SELECT * FROM Hostel ORDER BY hostel_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Hostels</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>Registered Hostels</h2>

    <a href="add_hostel.php">Add New Hostel</a>

    <table border="1" cellpadding="10">

        <tr>
            <th>Hostel ID</th>
            <th>Hostel Name</th>
            <th>Location</th>
            <th>Gender</th>
	    <th>Action</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>

        <tr>
            <td><?php echo htmlspecialchars($row['hostel_id']); ?></td>

            <td><?php echo htmlspecialchars($row['hostel_name']); ?></td>

            <td><?php echo htmlspecialchars($row['location'] ?? ''); ?></td>

            <td><?php echo htmlspecialchars($row['gender'] ?? ''); ?></td>
<td>
    <a href="edit_hostel.php?id=<?php echo $row['hostel_id']; ?>">
        Edit
    </a>

    <form action="delete_hostel.php" method="POST">

        <input type="hidden" name="hostel_id"
               value="<?php echo $row['hostel_id']; ?>">

        <button type="submit">Delete</button>

    </form>
</td>
        </tr>

        <?php endwhile; ?>

    </table>

</div>

</body>
</html>
