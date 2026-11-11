
<?php
include "../db_connect.php";

$sql = "
    SELECT
        ea.assignment_id,
        e.employee_id,
        e.employee_name,
        e.job_role,
        h.hostel_name,
        ea.assigned_date,
        ea.end_date
    FROM Employee_Assignment ea
    INNER JOIN Employee e
        ON ea.employee_id = e.employee_id
    INNER JOIN Hostel h
        ON ea.hostel_id = h.hostel_id
    ORDER BY ea.assignment_id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Employee Assignment History</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>Employee Assignment History</h2>

    <a href="assign_employee.php">Assign New Employee</a>

    <br><br>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">

        <tr>
            <th>Assignment ID</th>
            <th>Employee ID</th>
            <th>Employee Name</th>
            <th>Job Role</th>
            <th>Hostel</th>
            <th>Assigned Date</th>
            <th>End Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($row = $result->fetch_assoc()): ?>

                <tr>

                    <td><?php echo $row['assignment_id']; ?></td>

                    <td><?php echo $row['employee_id']; ?></td>

                    <td>
                        <?php echo htmlspecialchars($row['employee_name']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['job_role'] ?? ''); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($row['hostel_name']); ?>
                    </td>

                    <td><?php echo $row['assigned_date']; ?></td>

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

                            <a href="transfer_employee.php?id=<?php echo $row['assignment_id']; ?>">
                                Transfer
                            </a>

                            <br><br>

                            <form method="POST" action="end_assignment.php">

                                <input
                                    type="hidden"
                                    name="assignment_id"
                                    value="<?php echo $row['assignment_id']; ?>"
                                >

                                <label>End Date:</label>

                                <input
                                    type="date"
                                    name="end_date"
                                    required
                                >

                                <button type="submit">
                                    End Assignment
                                </button>

                            </form>

                        <?php else: ?>

                            Completed

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>
                <td colspan="9">No assignment records found.</td>
            </tr>

        <?php endif; ?>

    </table>

    <br>

    <a href="view_employees.php">Back to Employees</a>

    <br><br>

    <a href="../index.php">Back to Dashboard</a>

</div>

</body>
</html>
