
<?php
include "../db_connect.php";

if ($_SERVER["REQUEST_METHOD"] != "POST" ||
    !isset($_POST["scanner_id"]) ||
    !is_numeric($_POST["scanner_id"])) {
    die("Invalid request.");
}

$scanner_id = (int) $_POST["scanner_id"];

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM Entry_Exit_Log
     WHERE scanner_id = ?"
);
$stmt->bind_param("i", $scanner_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row["total"] > 0) {
    die("Cannot delete this scanner because entry-exit logs are associated with it.
         <br><a href='view_scanners.php'>Go Back</a>");
}

$stmt = $conn->prepare("DELETE FROM Scanner WHERE scanner_id = ?");
$stmt->bind_param("i", $scanner_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo "Scanner deleted successfully!";
} else {
    echo "Scanner not found or could not be deleted.";
}

$stmt->close();

echo "<br><a href='view_scanners.php'>Back to Scanners</a>";
?>
