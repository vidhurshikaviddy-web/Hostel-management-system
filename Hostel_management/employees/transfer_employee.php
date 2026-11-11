
<?php
include "../db_connect.php";

$message = "";
$message_type = "";

$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($assignment_id <= 0) {
    die("Invalid Assignment ID!");
}

// Get current assignment
$stmt = $conn->prepare("
    SELECT
        ea.assignment_id,
        ea.employee_id,
        ea.hostel_id,
        ea.assigned_date,
        ea.end_date,
        e.employee_name
    FROM Employee_Assignment ea
    INNER JOIN Employee e
        ON ea.employee_id = e.employee_id
    WHERE ea.assignment_id = ?
");

$stmt->bind_param("i", $assignment_id);
$stmt->execute();

$assignment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$assignment) {
    die("Assignment not found!");
}

if ($assignment['end_date'] !== null) {
    die("This assignment has already ended!");
}

// Get hostels
$hostels = $conn->query("
    SELECT hostel_id, hostel_name
    FROM Hostel
    ORDER BY hostel_name
");

// Transfer employee
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $new_hostel_id = (int)$_POST['hostel_id'];
    $transfer_date = $_POST['transfer_date'];

    $date = DateTime::createFromFormat('Y-m-d', $transfer_date);

    if (
        $new_hostel_id <= 0 ||
        !$date ||
        $date->format('Y-m-d') !== $transfer_date
    ) {

        $message = "Please enter valid transfer details!";
        $message_type = "error";

    } else {

        try {

            $conn->begin_transaction();

            // Lock current assignment
            $stmt = $conn->prepare("
                SELECT employee_id, hostel_id, assigned_date, end_date
                FROM Employee_Assignment
                WHERE assignment_id = ?
                FOR UPDATE
            ");

            $stmt->bind_param("i", $assignment_id);
            $stmt->execute();

            $current = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$current || $current['end_date'] !== null) {
                throw new Exception("This assignment is no longer active!");
            }

            if ($transfer_date < $current['assigned_date']) {
                throw new Exception("Transfer date cannot be earlier than the assigned date!");
            }

            if ($new_hostel_id == $current['hostel_id']) {
                throw new Exception("Select a different hostel for transfer!");
            }

            // Check new hostel exists
            $stmt = $conn->prepare("
                SELECT hostel_id
                FROM Hostel
                WHERE hostel_id = ?
            ");

            $stmt->bind_param("i", $new_hostel_id);
            $stmt->execute();

            $hostel = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$hostel) {
                throw new Exception("Selected hostel does not exist!");
            }

            // End old assignment
            $stmt = $conn->prepare("
                UPDATE Employee_Assignment
                SET end_date = ?
                WHERE assignment_id = ?
            ");

            $stmt->bind_param(
                "si",
                $transfer_date,
                $assignment_id
            );

            $stmt->execute();
            $stmt->close();

            // Create new assignment
            $stmt = $conn->prepare("
                INSERT INTO Employee_Assignment
                (employee_id, hostel_id, assigned_date, end_date)
                VALUES (?, ?, ?, NULL)
            ");

            $stmt->bind_param(
                "iis",
                $current['employee_id'],
                $new_hostel_id,
                $transfer_date
            );

            $stmt->execute();
            $stmt->close();

            $conn->commit();

            $message = "Employee transferred successfully!";
            $message_type = "success";

        } catch (Throwable $e) {

            $conn->rollback();

            $message = $e->getMessage();
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transfer Employee</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>Transfer Employee</h2>

    <?php if ($message != ""): ?>

        <p style="color:<?php echo $message_type == 'success' ? 'green' : 'red'; ?>;">
            <?php echo htmlspecialchars($message); ?>
        </p>

    <?php endif; ?>

    <form method="POST">

        <label>Employee Name:</label>

        <input
            type="text"
            value="<?php echo htmlspecialchars($assignment['employee_name']); ?>"
            disabled
        >

        <label>Select New Hostel:</label>

        <select name="hostel_id" required>

            <option value="">-- Select Hostel --</option>

            <?php while ($hostel = $hostels->fetch_assoc()): ?>

                <?php if ($hostel['hostel_id'] != $assignment['hostel_id']): ?>

                    <option value="<?php echo $hostel['hostel_id']; ?>">

                        <?php echo htmlspecialchars($hostel['hostel_name']); ?>

                    </option>

                <?php endif; ?>

            <?php endwhile; ?>

        </select>

        <label>Transfer Date:</label>

        <input
            type="date"
            name="transfer_date"
            value="<?php echo date('Y-m-d'); ?>"
            required
        >

        <button type="submit">Transfer Employee</button>

    </form>

    <br>

    <a href="view_assignments.php">Back to Assignment History</a>

</div>

</body>
</html>
