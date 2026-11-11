<?php
include "../db_connect.php";

$message = "";
$error = "";
$student = null;

// Get student ID from URL or form submission
$student_id = isset($_POST['student_id'])
    ? (int)$_POST['student_id']
    : (int)($_GET['id'] ?? 0);

// Update student details
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $student_name = trim($_POST['student_name']);
    $faculty = $_POST['faculty'] ?? "";
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);

    if ($student_id <= 0 || $student_name === "") {
        $error = "Please enter valid student details.";
    } else {
        try {
            $sql = "UPDATE Student
                    SET student_name = ?, faculty = ?,
                        email = ?, contact_number = ?
                    WHERE student_id = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssi",
                $student_name,
                $faculty,
                $email,
                $contact_number,
                $student_id
            );

            $stmt->execute();

            if ($stmt->affected_rows >= 0) {
                $message = "Student details updated successfully!";
            }

            $stmt->close();

        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = "This email is already registered!";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}

// Retrieve student details
if ($student_id > 0) {
    $stmt = $conn->prepare(
        "SELECT * FROM Student WHERE student_id = ?"
    );
    $stmt->bind_param("i", $student_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    $stmt->close();
}

if (!$student && $error === "") {
    $error = "Student not found!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>Edit Student Details</h2>

    <?php if ($message): ?>
        <p class="success-message">
            <?php echo htmlspecialchars($message); ?>
        </p>
        <a href="view_students.php">Back to Student List</a>

    <?php elseif ($student): ?>

        <?php if ($error): ?>
            <p style="color:red;">
                <?php echo htmlspecialchars($error); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="edit_students.php">

            <input type="hidden" name="student_id"
                   value="<?php echo $student['student_id']; ?>">

            <label>Student Name:</label>
            <input type="text" name="student_name"
                   value="<?php echo htmlspecialchars($student['student_name']); ?>"
                   required>

            <label>Faculty:</label>

            <div class="faculty-options">
                <?php
                $faculties = ["Business", "IT", "Architecture", "Engineering"];

                foreach ($faculties as $f):
                ?>
                    <label>
                        <input type="radio" name="faculty"
                               value="<?php echo $f; ?>"
                               <?php echo ($student['faculty'] == $f) ? 'checked' : ''; ?>
                               required>
                        <?php echo $f; ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <label>Email:</label>
            <input type="email" name="email"
                   value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>">

            <label>Contact Number:</label>
            <input type="text" name="contact_number"
                   value="<?php echo htmlspecialchars($student['contact_number'] ?? ''); ?>">

            <button type="submit">Update Student</button>

        </form>

        <a href="view_students.php">Back to Student List</a>

    <?php else: ?>

        <p><?php echo htmlspecialchars($error); ?></p>
        <a href="view_students.php">Back to Student List</a>

    <?php endif; ?>

</div>

</body>
</html>