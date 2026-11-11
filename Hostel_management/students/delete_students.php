
<?php
include "../db_connect.php";

$message = "";
$student_id = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $student_id = (int)($_POST['student_id'] ?? 0);

    if ($student_id <= 0) {
        $message = "Invalid student ID!";
    } else {

        // Check room allocation history
        $stmt = $conn->prepare(
            "SELECT allocation_id FROM Room_Allocation
             WHERE student_id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $allocation = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        // Check entry/exit history
        $stmt = $conn->prepare(
            "SELECT log_id FROM Entry_Exit_Log
             WHERE student_id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $logs = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($allocation || $logs) {
            $message = "Cannot delete this student because related records exist!";
        } else {

            $stmt = $conn->prepare(
                "DELETE FROM Student WHERE student_id = ?"
            );
            $stmt->bind_param("i", $student_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $message = "Student deleted successfully!";
            } else {
                $message = "Student not found!";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Student</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="student-container">

    <h2>Delete Student</h2>

    <p><?php echo htmlspecialchars($message); ?></p>

    <a href="view_students.php">Back to Student List</a>

</div>

</body>
</html>
