
<?php
include "db_connect.php";

// Function to retrieve dashboard counts
function getCount($conn, $sql) {
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        return (int) array_values($row)[0];
    }

    return 0;
}

// Dashboard statistics
$total_students = getCount($conn, "SELECT COUNT(*) FROM Student");

$total_hostels = getCount($conn, "SELECT COUNT(*) FROM Hostel");

$total_rooms = getCount($conn, "SELECT COUNT(*) FROM Room");

$total_employees = getCount($conn, "SELECT COUNT(*) FROM Employee");

$active_allocations = getCount(
    $conn,
    "SELECT COUNT(*) FROM Room_Allocation WHERE end_date IS NULL"
);

$total_scanners = getCount($conn, "SELECT COUNT(*) FROM Scanner");

$total_capacity = getCount(
    $conn,
    "SELECT COALESCE(SUM(capacity), 0) FROM Room"
);

$available_beds = max(0, $total_capacity - $active_allocations);

$todays_movements = getCount(
    $conn,
    "SELECT COUNT(*) FROM Entry_Exit_Log
     WHERE DATE(swipe_time) = CURDATE()"
);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Hostel Management Dashboard</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f9;
            color: #333;
        }

        .header {
            background: #1e3a5f;
            color: white;
            padding: 25px 40px;
        }

        .header h1 {
            margin-bottom: 8px;
        }

        .header p {
            color: #dbe5f0;
        }

        .dashboard-container {
            padding: 30px 40px;
        }

        .section-title {
            margin-bottom: 20px;
            color: #1e3a5f;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 5px solid #1e3a5f;
        }

        .stat-card h3 {
            color: #666;
            font-size: 15px;
            margin-bottom: 12px;
        }

        .stat-card p {
            font-size: 30px;
            font-weight: bold;
            color: #1e3a5f;
        }

        .modules-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .module-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: 0.2s;
        }

        .module-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.12);
        }

        .module-card h3 {
            color: #1e3a5f;
            margin-bottom: 10px;
        }

        .module-card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 18px;
            line-height: 1.5;
        }

        .module-card a {
            display: inline-block;
            background: #1e3a5f;
            color: white;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 5px;
            font-size: 14px;
        }

        .module-card a:hover {
            background: #315b89;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #777;
            font-size: 13px;
        }

        @media (max-width: 900px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }

            .modules-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 550px) {
            .stats-container,
            .modules-container {
                grid-template-columns: 1fr;
            }

            .dashboard-container {
                padding: 20px;
            }

            .header {
                padding: 20px;
            }
        }

    </style>
</head>

<body>

<div class="header">
    <h1>Hostel Management System</h1>
    <p>University Hostel Administration Dashboard</p>
</div>

<div class="dashboard-container">

    <h2 class="section-title">Dashboard Overview</h2>

    <div class="stats-container">

        <div class="stat-card">
            <h3>Total Students</h3>
            <p><?php echo $total_students; ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Hostels</h3>
            <p><?php echo $total_hostels; ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Rooms</h3>
            <p><?php echo $total_rooms; ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Employees</h3>
            <p><?php echo $total_employees; ?></p>
        </div>

        <div class="stat-card">
            <h3>Active Allocations</h3>
            <p><?php echo $active_allocations; ?></p>
        </div>

        <div class="stat-card">
            <h3>Available Beds</h3>
            <p><?php echo $available_beds; ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Scanners</h3>
            <p><?php echo $total_scanners; ?></p>
        </div>

        <div class="stat-card">
            <h3>Today's Movements</h3>
            <p><?php echo $todays_movements; ?></p>
        </div>

    </div>

    <h2 class="section-title">Management Modules</h2>

    <div class="modules-container">

        <div class="module-card">
            <h3>Student Management</h3>
            <p>Add, view, update and manage student information.</p>
            <a href="students/view_students.php">Open Module</a>
        </div>

        <div class="module-card">
            <h3>Hostel Management</h3>
            <p>Manage hostel details and information.</p>
            <a href="hostels/view_hostels.php">Open Module</a>
        </div>

        <div class="module-card">
            <h3>Room Management</h3>
            <p>Add, view and update hostel room details.</p>
            <a href="rooms/view_rooms.php">Open Module</a>
        </div>

        <div class="module-card">
            <h3>Room Allocation</h3>
            <p>Allocate rooms, transfer students and manage allocation history.</p>
            <a href="allocations/view_allocations.php">Open Module</a>
        </div>

        <div class="module-card">
            <h3>Employee Management</h3>
            <p>Manage employee details and records.</p>
            <a href="employees/view_employees.php">Open Module</a>
        </div>

        <div class="module-card">
            <h3>Employee Assignment</h3>
            <p>Assign employees to hostels and manage transfers.</p>
            <a href="employees/view_assignments.php">Open Module</a>
        </div>

        <div class="module-card">
            <h3>Scanner Management</h3>
            <p>Register and manage hostel entry-exit scanners.</p>
            <a href="scanner/view_scanners.php">Open Module</a>
        </div>

        <div class="module-card">
            <h3>Entry-Exit Monitoring</h3>
            <p>Record student movements and view entry-exit history.</p>
            <a href="scanner/view_logs.php">Open Module</a>
        </div>

    </div>

</div>

<div class="footer">
    <p>Hostel Management System | University Project</p>
</div>

</body>
</html>
