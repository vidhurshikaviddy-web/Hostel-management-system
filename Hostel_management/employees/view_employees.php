
<?php
include "../db_connect.php";

$sql = "
    SELECT
        e.employee_id,
        e.employee_name,
        e.job_role,
        e.contact_number,
        h.hostel_name
    FROM Employee e
    LEFT JOIN Employee_Assignment ea
        ON e.employee_id = ea.employee_id
        AND ea.end_date IS NULL
    LEFT JOIN Hostel h
        ON ea.hostel_id = h.hostel_id
    ORDER BY e.employee_id
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Employees</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>Employee Management</h2>

    <a href="add_employee.php">Add New Employee</a>

    <br><br>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">

        <tr>
            <th>Employee ID</th>
            <th>Employee Name</th>
            <th>Job Role</th>
            <th>Contact Number</th>
            <th>Assigned Hostel</th>
            <th>Actions</th>
        </tr>

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($row = $result->fetch_assoc()): ?>

                <tr>

                    <td><?php echo $row['employee_id']; ?></td>

                    <td>
                        <?php echo htmlspecialchars($row['employee_name']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['job_role'] ?? ''); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['contact_number'] ?? ''); ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $row['hostel_name'] ?? 'Not Assigned'
                        );
                        ?>
                    </td>

                    <td>

                        <a href="edit_employee.php?id=<?php echo $row['employee_id']; ?>">
                            Edit
                        </a>

                        <br><br>

                        <form method="POST" action="delete_employee.php">

                            <input
                                type="hidden"
                                name="employee_id"
                                value="<?php echo $row['employee_id']; ?>"
                            >

                            <button type="submit">Delete</button>

                        </form>

                    </td>

                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>
                <td colspan="6">No employees found.</td>
            </tr>

        <?php endif; ?>

    </table>

    <br>

    <a href="assign_employee.php">Assign Employee to Hostel</a>

    <br><br>

    <a href="view_assignments.php">View Assignment History</a>

    <br><br>

    <a href="../index.php">Back to Dashboard</a>

</div>

</body>
</html>
