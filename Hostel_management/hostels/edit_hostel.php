
<?php
include "../db_connect.php";

$message = "";
$error = "";

$hostel_id = isset($_POST['hostel_id'])
    ? (int)$_POST['hostel_id']
    : (int)($_GET['id'] ?? 0);

// Update hostel details
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $hostel_name = trim($_POST['hostel_name']);
    $location = trim($_POST['location']);
    $gender = $_POST['gender'] ?? "";

    if ($hostel_id <= 0 || $hostel_name == "" ||
        $location == "" || !in_array($gender, ["Male", "Female"])) {

        $error = "Please enter valid hostel details!";

    } else {

        $stmt = $conn->prepare(
            "UPDATE Hostel
             SET hostel_name = ?, location = ?, gender = ?
             WHERE hostel_id = ?"
        );

        $stmt->bind_param(
            "sssi",
            $hostel_name,
            $location,
            $gender,
            $hostel_id
        );

        if ($stmt->execute()) {
            $message = "Hostel details updated successfully!";
        } else {
            $error = "Unable to update hostel details!";
        }

        $stmt->close();
    }
}

// Retrieve hostel details
$stmt = $conn->prepare(
    "SELECT * FROM Hostel WHERE hostel_id = ?"
);

$stmt->bind_param("i", $hostel_id);
$stmt->execute();

$result = $stmt->get_result();
$hostel = $result->fetch_assoc();

$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Hostel</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>Edit Hostel Details</h2>

    <?php if ($message): ?>
        <p class="success-message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p style="color:red;">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php endif; ?>

    <?php if ($hostel): ?>

    <form method="POST" action="edit_hostel.php">

        <input type="hidden" name="hostel_id"
               value="<?php echo $hostel['hostel_id']; ?>">

        <label>Hostel Name:</label>
        <input type="text" name="hostel_name"
               value="<?php echo htmlspecialchars($hostel['hostel_name']); ?>"
               required>

        <label>Location:</label>
        <input type="text" name="location"
               value="<?php echo htmlspecialchars($hostel['location'] ?? ''); ?>"
               required>

        <label>Gender:</label>

        <div class="faculty-options">

            <label>
                <input type="radio" name="gender" value="Male"
                <?php echo ($hostel['gender'] == 'Male') ? 'checked' : ''; ?>
                required>
                Male
            </label>

            <label>
                <input type="radio" name="gender" value="Female"
                <?php echo ($hostel['gender'] == 'Female') ? 'checked' : ''; ?>>
                Female
            </label>

        </div>

        <button type="submit">Update Hostel</button>

    </form>

    <?php else: ?>
        <p>Hostel not found!</p>
    <?php endif; ?>

    <a href="view_hostels.php">Back to Hostel List</a>

</div>

</body>
</html>
