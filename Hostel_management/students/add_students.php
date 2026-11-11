
<?php
include "../db_connect.php";

$success = false;
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $student_name = $_POST["student_name"];
    $faculty = $_POST["faculty"];
    $email = $_POST["email"];
    $contact_number = $_POST["contact_number"];

    try {
        $stmt = $conn->prepare(
            "INSERT INTO Student 
            (student_name, faculty, email, contact_number)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssss",
            $student_name,
            $faculty,
            $email,
            $contact_number
        );

        $stmt->execute();

        $success = true;
        $message = "Form submitted successfully!";

        $stmt->close();

    } catch (mysqli_sql_exception $e) {

        if ($e->getCode() == 1062) {
            $message = "This email is already registered!";
        } else {
            $message = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="../css/style.css">
    <title>Add Student</title>
</head>
<body>

<?php if ($success): ?>

    <h2><?php echo $message; ?></h2>
    <p>Student details have been saved successfully.</p>

    <a href="add_students.php">Add Another Student</a>

<?php else: ?>

    <h2>Register a New Student</h2>

    <p><?php echo htmlspecialchars($message); ?></p>

    <div class="student-container">

    <h2>Student Registration</h2>
<div class="faculty-options">
    <form method="POST">

        <label>Student Name:</label><br>
        <input type="text" name="student_name" required>
        <br><br>

        <label>Faculty:</label><br>

        <input type="radio" name="faculty" value="Business" required>
        Business<br>

        <input type="radio" name="faculty" value="IT">
        IT<br>

        <input type="radio" name="faculty" value="Architecture">
        Architecture<br>

        <input type="radio" name="faculty" value="Engineering">
        Engineering<br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required>
        <br><br>

        <label>Contact Number:</label><br>
        <input type="text" name="contact_number">
        <br><br>

        <button type="submit">Add Student</button>

    </form>
</div></div>
<?php endif; ?>

</body>
</html>
