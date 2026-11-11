
<?php
include "../db_connect.php";

$message = "";
$message_type = "";

// Get employees without active assignments
$employees = $conn->query("
    SELECT e.employee_id, e.employee_name
    FROM Employee e
    WHERE NOT EXISTS (
        SELECT 1
        FROM Employee_Assignment ea
        WHERE ea.employee_id = e.employee_id
        AND ea.end_date IS NULL
    )
    ORDER BY e.employee_name
");

// Get hostels
$hostels = $conn->query("
    SELECT hostel_id, hostel_name
    FROM Hostel
    ORDER BY hostel_name
");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $employee_id = (int)$_POST['employee_id'];
    $hostel_id = (int)$_POST['hostel_id'];
    $assigned_date = $_POST['assigned_date'];

    if ($employee_id <= 0 || $hostel_id <= 0 || $assigned_date == "") {

        $message = "Please enter all assignment details!";
        $message_type = "error";

    } else {

        try {

            $conn->begin_transaction();

            // Lock employee record
            $stmt = $conn->prepare("
                SELECT employee_id
                FROM Employee
                WHERE employee_id = ?
                FOR UPDATE
            ");

            $stmt->bind_param("i", $employee_id);
            $stmt->execute();

            $employee = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$employee) {
                throw new Exception("Employee not found!");
            }

            // Check active assignment
            $stmt = $conn->prepare("
                SELECT assignment_id
                FROM Employee_Assignment
                WHERE employee_id = ?
                AND end_date IS NULL
                FOR UPDATE
            ");

            $stmt->bind_param("i", $employee_id);
            $stmt->execute();

            $active = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($active) {
                throw new Exception("Employee already has an active hostel assignment!");
            }

            // Check hostel
            $stmt = $conn->prepare("
                SELECT hostel_id
                FROM Hostel
                WHERE hostel_id = ?
            ");

            $stmt->bind_param("i", $hostel_id);
            $stmt->execute();

            $hostel = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$hostel) {
                throw new Exception("Selected hostel does not exist!");
            }

            // Create assignment
            $stmt = $conn->prepare("
                INSERT INTO Employee_Assignment
                (employee_id, hostel_id, assigned_date, end_date)
                VALUES (?, ?, ?, NULL)
            ");

            $stmt->bind_param(
                "iis",
                $employee_id,
                $hostel_id,
                $assigned_date
            );

            $stmt->execute();
            $stmt->close();

            $conn->commit();

            $message = "Employee assigned to hostel successfully!";
            $message_type = "success";

            // Refresh employee list
            $employees = $conn->query("
                SELECT e.employee_id, e.employee_name
                FROM Employee e
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM Employee_Assignment ea
                    WHERE ea.employee_id = e.employee_id
                    AND ea.end_date IS NULL
                )
                ORDER BY e.employee_name
            ");

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
    <title>Assign Employee</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>Assign Employee to Hostel</h2>

    <?php if ($message != ""): ?>

        <p style="color:<?php echo $message_type == 'success' ? 'green' : 'red'; ?>;">
            <?php echo htmlspecialchars($message); ?>
        </p>

    <?php endif; ?>

    <form method="POST">

        <label>Select Employee:</label>

        <select name="employee_id" required>

            <option value="">-- Select Employee --</option>

            <?php while ($employee = $employees->fetch_assoc()): ?>

                <option value="<?php echo $employee['employee_id']; ?>">

                    <?php
                    echo htmlspecialchars(
                        $employee['employee_name'] .
                        " (ID: " . $employee['employee_id'] . ")"
                    );
                    ?>

                </option>

            <?php endwhile; ?>

        </select>

        <label>Select Hostel:</label>

        <select name="hostel_id" required>

            <option value="">-- Select Hostel --</option>

            <?php while ($hostel = $hostels->fetch_assoc()): ?>

                <option value="<?php echo $hostel['hostel_id']; ?>">

                    <?php echo htmlspecialchars($hostel['hostel_name']); ?>

                </option>

            <?php endwhile; ?>

        </select>

        <label>Assigned Date:</label>

        <input
            type="date"
            name="assigned_date"
            required
        >

        <button type="submit">Assign Employee</button>

    </form>

    <br>

    <a href="view_assignments.php">View Assignment History</a>

</div>

</body>
</html>
