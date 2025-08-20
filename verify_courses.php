<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'connection.php';

// Fetch all courses
$sql = "SELECT course_id, course_name, course_description FROM courses";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Available Courses</title>
    <link rel="stylesheet" href="style.css"> <!-- use your existing stylesheet -->
</head>
<body>
    <h2>All Courses</h2>
    <table class="course-table">
        <tr>
            <th>ID</th>
            <th>Course Name</th>
            <th>Description</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['course_id']}</td>
                        <td>{$row['course_name']}</td>
                        <td>{$row['course_description']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No courses available</td></tr>";
        }
        ?>
    </table>
</body>
</html>
<?php
$conn->close();
?>
