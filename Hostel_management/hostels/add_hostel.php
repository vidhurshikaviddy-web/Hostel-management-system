
<?php
include "../db_connect.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $hostel_name = trim($_POST['hostel_name']);
    $location = trim($_POST['location']);
    $gender = $_POST['gender'];

    if ($hostel_name == "" || $location == "" || $gender == "") {
        $message = "Please fill all the fields!";
    } else {

        $sql = "INSERT INTO Hostel (hostel_name, location, gender)
                VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $hostel_name, $location, $gender);

        if ($stmt->execute()) {
            $message = "Hostel added successfully!";
        } else {
            $message = "Error adding hostel!";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Hostel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>Add New Hostel</h2>

    <p><?php echo htmlspecialchars($message); ?></p>

    <form method="POST" action="">

        <label>Hostel Name:</label>
        <input type="text" name="hostel_name" required>

        <label>Location:</label>
        <input type="text" name="location" required>

        <label>Gender:</label>

        <div class="faculty-options">

            <label>
                <input type="radio" name="gender" value="Male" required>
                Male
            </label>

            <label>
                <input type="radio" name="gender" value="Female">
                Female
            </label>

        </div>

        <button type="submit">Add Hostel</button>

    </form>

    <a href="view_hostels.php">View Hostels</a>

</div>

</body>
</html>
