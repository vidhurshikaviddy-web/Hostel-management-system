
<?php
include "../db_connect.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $employee_id = (int)$_POST['employee_id'];

    if ($employee_id <= 0) {

        $message = "Invalid Employee ID!";
        $message_type = "error";

    } else {

        // Check whether employee has assignment history
        $stmt = $conn->prepare("
            SELECT assignment_id
            FROM Employee_Assignment
            WHERE employee_id = ?
            LIMIT 1
        ");

        $stmt->bind_param("i", $employee_id);
        $stmt->execute();

        $assignment = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($assignment) {

            $message = "Cannot delete this employee because assignment history exists!";
            $message_type = "error";

        } else {

            $stmt = $conn->prepare("
                DELETE FROM Employee
                WHERE employee_id = ?
            ");

            $stmt->bind_param("i", $employee_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {

                $message = "Employee deleted successfully!";
                $message_type = "success";

            } else {

                $message = "Employee not found or could not be deleted!";
                $message_type = "error";
            }

            $stmt->close();
        }
    }

} else {

    $message = "Invalid request!";
    $message_type = "error";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Employee</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>Delete Employee</h2>

    <p style="color:<?php echo $message_type == 'success' ? 'green' : 'red'; ?>;">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <br>

    <a href="view_employees.php">Back to Employees</a>

</div>

</body>
</html>
