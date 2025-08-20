<?php
// ===============================
// DB CONNECTION (adjust if needed)
// ===============================
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'learned';

$dbc = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($dbc->connect_error) {
    http_response_code(500);
    die('Database connection failed.');
}
$dbc->set_charset('utf8mb4');

// =====================================
// SEARCH (prepared) or LIST ALL courses
// =====================================
$courses_rs = null;
$searchTerm = '';

if (isset($_POST['search'])) {
    $searchTerm = trim($_POST['valueToSearch'] ?? '');
    $like = "%{$searchTerm}%";
    $stmt = $dbc->prepare("SELECT cname, cdescription, clink, creator, verified FROM courses WHERE cname LIKE ? ORDER BY created_at DESC");
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $courses_rs = $stmt->get_result();
    $stmt->close();
} else {
    $courses_rs = $dbc->query("SELECT cname, cdescription, clink, creator, verified FROM courses ORDER BY created_at DESC");
}

// =====================================
// VERIFIED COURSES (for tiles below)
// =====================================
$verified_rs = $dbc->query("SELECT cname FROM courses WHERE verified='yes' ORDER BY created_at DESC");

// Helper to safely render link HREF (allow http/https only)
function safe_href(?string $url): string {
    $url = trim((string)$url);
    if ($url === '') return '#';
    // basic allowlist
    if (stripos($url, 'http://') === 0 || stripos($url, 'https://') === 0) {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
    return '#';
}
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="shortcut icon" type="png" href="images/icon/favicon.png">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Comaptible" content="IE=edge">
	<title>Courses</title>
	<meta name="desciption" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" type="text/css" href="style01.css">
	<script type="text/javascript" src="script.js"></script>
	<script src="https://code.jquery.com/jquery-3.2.1.js"></script>
	<script>
		$(window).on('scroll', function(){
  			if($(window).scrollTop()){
  			  $('nav').addClass('black');
 			 }else {
 		   $('nav').removeClass('black');
 		 }
		})
	</script>

	<style type="text/css">
		#searchbar {
			border: none;
			width: 220%;
			height: 45px;
			margin-left: -245%;
			margin-top: 30%;
			border: 2px solid #DF2771;
			padding: 2%;
			padding-left: 10%;
			border-radius: 25px;
			font-size: 120%;
			margin-bottom: 10%;
			margin-right: -10%;
		}
		.srch { height: 40px; width: 50%; border: 2px solid yellow; }
		#title { text-align: center; margin-top: -5%; margin-left: 40%; margin-bottom: 1.5%; }
		#content { margin-top: 5%; }
		table { margin-right: 10%; margin-left: 10%; }
		#filter_button { height: 50px; width: 50px; margin-top: 30%; margin-left: -25%; border: none; background-color: white; }
		#search_img { height: 35px; width: 35px; margin-top: -2%; }
		tr { width: 70%; }
		th, td { width: 15%; padding: 1.5%; }
		th { text-align: left; background-image: linear-gradient(to right, #FA4B37, #DF2771); color:#fff; }
		td { background-color: #dddddd; }
		td:hover { background-color: #bbbbbb; }
		.head-container { margin-top: 5%; }
		#email_head { background-color: yellow; }
		.title { margin-top: 50%; }
		#verify_courses {
			width: 250px; padding: 28px; border-radius: 10px; border: 2px solid #55AF55;
		    font-size: 15px; color: #272529; font-family: cursive; margin-left: 42%;
		    box-shadow: 2px 2px 5px grey;
		}
		#verify_courses:hover {
			background-image: linear-gradient(to right, #FA4B37, #DF2771);
			box-shadow: 5px 5px 10px black; border: none;
		}
		#tick { height: 40px; width: 40px; padding-left: 2%; }
	</style>
</head>
<body>

<!-- Navigation Bar -->
	<header id="header">
		<nav>
			<div class="logo"><img src="images/icon/logo.png" alt="logo"></div>
			<ul>
				<li><a class="active" href="index.php">Home</a></li>
				<li><a href="#popular_subjects">Popular subjects</a></li>
				<li><a href="teach.html">Add course</a></li>
				<li><a href="students.php">View Students</a></li>
			</ul>
			<a class="get-started" href="index.php">Logout</a>
			<img src="images/icon/menu.png" class="menu" onclick="sideMenu(0)" alt="menu">
		</nav>

		<div class="head-container">
			<h2 id="title" class="text-center pt-4" style="font-weight:bold; color:black;">Courses</h2><br>

			<form action="courses.php" method="post">
	            <input id="searchbar" type="text" name="valueToSearch" placeholder="Search Courses"
					value="<?= htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8') ?>">
	            <button id="filter_button" type="submit" name="search" value="Filter">
					<img id="search_img" src="images/icon/search.png" alt="search" onclick="slide()">
				</button>
			</form>
			<br><br>

	        <div>
				<a id="verify_courses" href="verify_courses.php">Verify Courses</a>
			</div>
			<br><br><br><br><br>

			<div class="table">
				<table class="content-table" style="margin-bottom:auto;">
					<thead>
						<tr>
							<th class="text-center">COURSE NAME</th>
							<th class="text-center">DESCRIPTION</th>
							<th class="text-center">LINK</th>
							<th class="text-center">CREATOR</th>
							<th class="text-center">VERIFIED</th>
						</tr>
					</thead>
					<tbody>
						<?php if ($courses_rs && $courses_rs->num_rows): ?>
							<?php while($rows = $courses_rs->fetch_assoc()): ?>
								<tr>
									<td class="text-center py-2"><?= htmlspecialchars($rows['cname'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
									<td class="text-center py-2"><?= nl2br(htmlspecialchars($rows['cdescription'] ?? '', ENT_QUOTES, 'UTF-8')) ?></td>
									<td class="text-center py-2">
										<?php $href = safe_href($rows['clink'] ?? ''); ?>
										<?php if ($href === '#'): ?>
											<span>-</span>
										<?php else: ?>
											<a href="<?= $href ?>" target="_blank" rel="noopener">Link to the course</a>
										<?php endif; ?>
									</td>
									<td class="text-center py-2"><?= htmlspecialchars($rows['creator'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
									<td class="text-center py-2"><?= htmlspecialchars($rows['verified'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
								</tr>
							<?php endwhile; ?>
						<?php else: ?>
							<tr><td colspan="5" class="text-center py-2">No courses found.</td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

		</div>

		<div class="side-menu" id="side-menu">
			<div class="close" onclick="sideMenu(1)"><img src="images/icon/close.png" alt=""></div>
			<ul>
				<li><a href="#about_section">About</a></li>
				<li><a href="#portfolio_section">Portfolio</a></li>
				<li><a href="#team_section">Team</a></li>
				<li><a href="#services_section">Services</a></li>
				<li><a href="#contactus_section">Contact</a></li>
				<li><a href="#feedBACK">Feedback</a></li>
			</ul>
		</div>
	</header>

<!-- Some Popular Subjects -->
	<div class="title" id="popular_subjects">
		<span>Popular Subjects on LearnEd</span>
	</div>
	<br><br>
	<div class="course">
		<center><div class="cbox">
			<div class="det"><a href="loginNew.html"><img src="images/courses/book.png">JEE Preparation</a></div>
			<div class="det"><a href="loginNew.html"><img src="images/courses/d1.png">GATE Preparation</a></div>
			<div class="det"><a href="loginNew.html"><img src="images/courses/paper.png">Sample Papers</a></div>
			<div class="det"><a href="loginNew.html"><img src="images/courses/d1.png">Daily Quiz</a></div>
		</div></center>
		<div class="cbox">
			<div class="det"><a href="loginNew.html"><img src="images/courses/computer.png">Computer Courses</a></div>
			<div class="det"><a href="loginNew.html"><img src="images/courses/data.png">Data Structures</a></div>
			<div class="det"><a href="loginNew.html"><img src="images/courses/algo.png">Algorithm</a></div>
			<div class="det det-last"><a href="loginNew.html"><img src="images/courses/projects.png">Projects</a></div>
		</div>
	</div>

	<?php if ($verified_rs && $verified_rs->num_rows > 0): ?>
		<div class="cbox">
			<?php while($row = $verified_rs->fetch_assoc()): ?>
				<div class="det">
					<a href="subjects/trial.php"><img src="images/courses/book.png">
						<?= htmlspecialchars($row["cname"], ENT_QUOTES, 'UTF-8'); ?>
					</a>
				</div>
			<?php endwhile; ?>
		</div>
	<?php else: ?>
		<div class="cbox"><div class="det">No verified courses yet.</div></div>
	<?php endif; ?>

<!-- Sliding Information -->
	<marquee style="background: linear-gradient(to right, #FA4B37, #DF2771); margin-top: 50px;" direction="left" onmouseover="this.stop()" onmouseout="this.start()" scrollamount="20"><div class="marqu">“Education is the passport to the future, for tomorrow belongs to those who prepare for it today.” “Your attitude, not your aptitude, will determine your altitude.” “If you think education is expensive, try ignorance.” “The only person who is educated is the one who has learned how to learn …and change.”</div></marquee>

<!-- FOOTER -->
	<footer>
		<div class="footer-container">
			<div class="left-col">
				<img src="images/icon/logo - Copy.png" style="width: 200px;">
				<div class="logo"></div>
				<div class="social-media">
					<a href="#"><img src="images/icon/fb.png"></a>
					<a href="#"><img src="images/icon/insta.png"></a>
					<a href="#"><img src="images/icon/tt.png"></a>
					<a href="#"><img src="images/icon/ytube.png"></a>
					<a href="#"><img src="images/icon/linkedin.png"></a>
				</div><br><br>
				<p class="rights-text">Copyright © 2021 Created By Rincy Fernandes, Marilyn Almeida, Aryan Patil All Rights Reserved.</p>
				<br><p><img src="images/icon/location.png"> Lovely Professional University<br>Phagwara, Punjab-144401</p><br>
				<p><img src="images/icon/phone.png"> +91-1234-567-890<br><img src="images/icon/mail.png">&nbsp; learnedonline9419@gmail.com</p>
			</div>
			<div class="right-col">
				<h1 style="color: #fff">Our Newsletter</h1>
				<div class="border"></div><br>
				<p>Enter Your Email to get our News and updates.</p>
				<form class="newsletter-form">
					<input class="txtb" type="email" placeholder="Enter Your Email">
					<input class="btn" type="submit" value="Submit">
				</form>
			</div>
		</div>
	</footer>

<?php
// Cleanup
$verified_rs && $verified_rs->free();
$courses_rs  && $courses_rs->free();
$dbc->close();
?>
</body>
</html>
