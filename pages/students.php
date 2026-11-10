<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Management</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="sidebar">

    <h2>HOSTEL MS</h2>

    <a href="../index.php">Dashboard</a>
    <a href="students.php">Students</a>
    <a href="#">Hostels</a>
    <a href="#">Rooms</a>
    <a href="#">Room Allocation</a>
    <a href="#">Employees</a>
    <a href="#">Employee Assignments</a>
    <a href="#">Entry / Exit</a>
    <a href="#">Reports</a>

</div>


<div class="main">

    <h1>Student Management</h1>

    <p>Register and manage university hostel students.</p>

    <br>

    <div class="card">

        <h2>Student Registration</h2>

        <br>

        <form method="POST">

            <label>Student ID</label>
            <input type="text" name="student_id"
            placeholder="Enter Student ID" required>

            <label>Student Name</label>
            <input type="text" name="student_name"
            placeholder="Enter Student Name" required>

            <label>Gender</label>
            <select name="gender" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>

            <label>Faculty</label>
            <input type="text" name="faculty"
            placeholder="Enter Faculty">

            <label>Contact Number</label>
            <input type="text" name="contact_number"
            placeholder="Enter Contact Number">

            <label>Email</label>
            <input type="email" name="email"
            placeholder="Enter Email">

            <br><br>

            <button type="submit">Add Student</button>

            <button type="reset">Clear</button>

        </form>

    </div>

</div>

</body>
</html>
