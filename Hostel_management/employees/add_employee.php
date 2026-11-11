
<?php
include "../db_connect.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $employee_name = trim($_POST['employee_name']);
    $job_role = trim($_POST['job_role']);
    $contact_number = trim($_POST['contact_number']);

    if ($employee_name == "") {

        $message = "Employee name is required!";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO Employee
            (employee_name, job_role, contact_number)
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param(
            "sss",
            $employee_name,
            $job_role,
            $contact_number
        );

        if ($stmt->execute()) {
            $message = "Employee added successfully!";
            $message_type = "success";
        } else {
            $message = "Unable to add employee!";
            $message_type = "error";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Employee</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>Add New Employee</h2>

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
            placeholder="Enter employee name"
            required
        >

        <label>Job Role:</label>

        <input
            type="text"
            name="job_role"
            placeholder="Example: Warden, Security Officer"
        >

        <label>Contact Number:</label>

        <input
            type="text"
            name="contact_number"
            placeholder="Enter contact number"
        >

        <button type="submit">Add Employee</button>

    </form>

    <br>

    <a href="view_employees.php">View Employees</a>

</div>

</body>
</html>
