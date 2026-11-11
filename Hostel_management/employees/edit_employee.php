
<?php
include "../db_connect.php";

$message = "";
$message_type = "";

$employee_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($employee_id <= 0) {
    die("Invalid Employee ID!");
}

// Get employee details
$stmt = $conn->prepare("
    SELECT *
    FROM Employee
    WHERE employee_id = ?
");

$stmt->bind_param("i", $employee_id);
$stmt->execute();

$employee = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$employee) {
    die("Employee not found!");
}

// Update employee
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $employee_name = trim($_POST['employee_name']);
    $job_role = trim($_POST['job_role']);
    $contact_number = trim($_POST['contact_number']);

    if ($employee_name == "") {

        $message = "Employee name is required!";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare("
            UPDATE Employee
            SET employee_name = ?,
                job_role = ?,
                contact_number = ?
            WHERE employee_id = ?
        ");

        $stmt->bind_param(
            "sssi",
            $employee_name,
            $job_role,
            $contact_number,
            $employee_id
        );

        if ($stmt->execute()) {

            $message = "Employee updated successfully!";
            $message_type = "success";

            $employee['employee_name'] = $employee_name;
            $employee['job_role'] = $job_role;
            $employee['contact_number'] = $contact_number;

        } else {

            $message = "Unable to update employee!";
            $message_type = "error";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Employee</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>Edit Employee Details</h2>

    <?php if ($message != ""): ?>

        <p style="color:<?php echo $message_type == 'success' ? 'green' : 'red'; ?>;">
            <?php echo htmlspecialchars($message); ?>
        </p>

    <?php endif; ?>

    <form method="POST">

        <label>Employee Name:</label>

        <input
            type="text"
            name="employee_name"
            value="<?php echo htmlspecialchars($employee['employee_name']); ?>"
            required
        >

        <label>Job Role:</label>

        <input
            type="text"
            name="job_role"
            value="<?php echo htmlspecialchars($employee['job_role'] ?? ''); ?>"
        >

        <label>Contact Number:</label>

        <input
            type="text"
            name="contact_number"
            value="<?php echo htmlspecialchars($employee['contact_number'] ?? ''); ?>"
        >

        <button type="submit">Update Employee</button>

    </form>

    <br>

    <a href="view_employees.php">Back to Employees</a>

</div>

</body>
</html>
