
<?php
include "../db_connect.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $allocation_id = (int)$_POST['allocation_id'];
    $end_date = $_POST['end_date'] ?? "";

    // Validate date format
    $date = DateTime::createFromFormat('Y-m-d', $end_date);

    if (
        $allocation_id <= 0 ||
        !$date ||
        $date->format('Y-m-d') !== $end_date
    ) {

        $message = "Please enter valid allocation details!";
        $message_type = "error";

    } else {

        try {

            $conn->begin_transaction();

            // Get active allocation
            $stmt = $conn->prepare("
                SELECT start_date
                FROM Room_Allocation
                WHERE allocation_id = ?
                AND end_date IS NULL
                FOR UPDATE
            ");

            $stmt->bind_param("i", $allocation_id);
            $stmt->execute();

            $allocation = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$allocation) {

                throw new Exception(
                    "Allocation not found or already ended!"
                );

            }

            if ($end_date < $allocation['start_date']) {

                throw new Exception(
                    "End date cannot be earlier than the start date!"
                );

            }

            // Update end date
            $stmt = $conn->prepare("
                UPDATE Room_Allocation
                SET end_date = ?
                WHERE allocation_id = ?
                AND end_date IS NULL
            ");

            $stmt->bind_param(
                "si",
                $end_date,
                $allocation_id
            );

            $stmt->execute();
            $stmt->close();

            $conn->commit();

            $message = "Room allocation ended successfully!";
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
    <title>End Room Allocation</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="student-container">

    <h2>End Room Allocation</h2>

    <p style="color:<?php echo $message_type == 'success' ? 'green' : 'red'; ?>;">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <br>

    <a href="view_allocations.php">Back to Room Allocations</a>

</div>

</body>
</html>
