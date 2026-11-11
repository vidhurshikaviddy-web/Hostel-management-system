
<?php
include "../db_connect.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $assignment_id = (int)$_POST['assignment_id'];
    $end_date = $_POST['end_date'] ?? "";

    $date = DateTime::createFromFormat('Y-m-d', $end_date);

    if (
        $assignment_id <= 0 ||
        !$date ||
        $date->format('Y-m-d') !== $end_date
    ) {

        $message = "Please enter valid details!";
        $message_type = "error";

    } else {

        try {

            $conn->begin_transaction();

            // Get active assignment
            $stmt = $conn->prepare("
                SELECT assigned_date
                FROM Employee_Assignment
                WHERE assignment_id = ?
                AND end_date IS NULL
                FOR UPDATE
            ");

            $stmt->bind_param("i", $assignment_id);
            $stmt->execute();

            $assignment = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$assignment) {
                throw new Exception("Assignment not found or already ended!");
            }

            if ($end_date < $assignment['assigned_date']) {
                throw new Exception("End date cannot be earlier than assigned date!");
            }

            // End assignment
            $stmt = $conn->prepare("
                UPDATE Employee_Assignment
                SET end_date = ?
                WHERE assignment_id = ?
                AND end_date IS NULL
            ");

            $stmt->bind_param(
                "si",
                $end_date,
                $assignment_id
            );

            $stmt->execute();
            $stmt->close();

            $conn->commit();

            $message = "Employee assignment ended successfully!";
            $message_type = "success";

        } catch (Throwable $e) {

            $conn->rollback();

            $message = $e->getMessage();
            $message_type = "error";
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
    <title>End Employee Assignment</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>End Employee Assignment</h2>

    <p style="color:<?php echo $message_type == 'success' ? 'green' : 'red'; ?>;">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <br>

    <a href="view_assignments.php">Back to Assignment History</a>

</div>

</body>
</html>
