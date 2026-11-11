
<?php
include "../db_connect.php";

$sql = "SELECT * FROM Student ORDER BY student_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="../css/style.css">
    <title>View Students</title>
</head>
<body>

<h2>Registered Students</h2>

<a href="add_students.php">Add New Student</a>

<br><br>

<table border="1" cellpadding="10">
    <tr>
	
        <th>Student ID</th>
        <th>Student Name</th>
        <th>Faculty</th>
        <th>Email</th>
        <th>Contact Number</th>
	<th>Action</th>

    </tr>

    <?php while ($row = $result->fetch_assoc()): ?>

    <tr>
        <td><?php echo htmlspecialchars($row['student_id']); ?></td>
        <td><?php echo htmlspecialchars($row['student_name']); ?></td>
        <td><?php echo htmlspecialchars($row['faculty'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($row['contact_number'] ?? ''); ?></td>
<td>
    <a href="edit_students.php?id=<?php echo $row['student_id']; ?>">
        Edit
    </a>
<br>
<form action="delete_students.php" method="POST"
      onsubmit="return confirm('Are you sure you want to delete this student?');">

    <input type="hidden" name="student_id"
           value="<?php echo $row['student_id']; ?>">

<button type="submit">
    Delete Student's details
</button>

</form>
</td>
    </tr>

    <?php endwhile; ?>

</table>

</body>
</html>
