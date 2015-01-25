<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="style2.css">
  <title> LOGIN</title>
</head>
<body>
<h1 class = "title"> WELCOME TO THE PROJECT LOGIN PAGE </h1>
		<ul id="navbar">
		    <li><a href = "http://www.cs.okstate.edu/~geralab/">CS HOME</a></li>
		</ul>
<?php
		session_start();
		$_SESSION['administrator'] = 0;
		$_SESSION['instructor'] = 0;
		$_SESSION['student'] = 0;
		$_SESSION['name'] = "";
		$_SESSION['userId'] = 0;
		$_SESSION['major'] = "";
		$_SESSION['year'] = 0;
		$_SESSION['department'] = "";
		$fileText = file_get_contents('/home/geralab/pass.txt', FILE_USE_INCLUDE_PATH);
	    $dbPassword = trim($fileText);
		$dbUser = 'geralab';
		$dbName = $dbUser; 
		$database = new mysqli("cs.okstate.edu", $dbUser, $dbPassword, $dbName);
		if (mysqli_connect_errno()) 
		{
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}
		if (array_key_exists('user', $_POST) && 
				array_key_exists('password', $_POST) 
				&& array_key_exists('selection', $_POST) )
		{
			$user = $_POST['user'];
			$_SESSION['user'] = $user;
			$password = $_POST['password'];
			$_SESSION['password'] = $password;
			$query = "SELECT * FROM User WHERE userName = '$user';";
			$studentQuery = "SELECT * FROM User NATURAL JOIN Student 
			WHERE userName = '$user';";
	        $instructorQuery = "SELECT * FROM User NATURAL JOIN Instructor 
			WHERE userName = '$user';";
			$salt = getInfo($query,'salt',$database);
			$dbHash = getInfo($query,'passwordHash',$database);
			$administrator = getInfo($query,'administrator',$database);
			$instructor = getInfo($query,'instructor',$database);
			$student = getInfo($query,'student',$database);
		    $passAndSalt = $password . $salt;
			$passHash = hash('sha256', $passAndSalt);
			if($dbHash == $passHash)
			{
				$page = $_POST['selection'];
				switch($page)
				{
					case "instructor":
						if($instructor)
						{
							$_SESSION['name'] = getInfo($instructorQuery,'name',$database);
							$_SESSION['userId'] = getInfo($instructorQuery,'userId',$database);
							$_SESSION['department'] = getInfo($instructorQuery,'department',$database);
							$_SESSION['major'] = "";
							$_SESSION['year'] = 0;
							$_SESSION['instructor'] = 1;
							$_SESSION['administrator'] = 0;
							$_SESSION['student'] = 0;
							header('Location:instructor.php');
						}
						else
						{
							echo '<div class = "out"><h4 id="out"><pre class = "normal">INVALID 
							CREDENTIALS FOR INSTRUCTOR</pre></h4></div>';
						}
					break;
					case "administrator":
						if($administrator)
						{
							$_SESSION['name'] = "";
							$_SESSION['userId'] = 0;
							$_SESSION['major'] = "";
							$_SESSION['year'] = 0;
							$_SESSION['administrator'] = 1;
							$_SESSION['instructor'] = 0;
							$_SESSION['student'] = 0;
							$_SESSION['department'] = "";
							header('Location:admin.php');
						}
						else
						{
							echo '<div class = "out"><h4 id="out"><pre class = "normal">INVALID CREDENTIALS</pre></h4></div>';
						}
					break;
					case "student":
						if($student)
						{
						    $_SESSION['name'] = getInfo($studentQuery,'name',$database);
							$_SESSION['userId'] = getInfo($studentQuery,'userId',$database);
							$_SESSION['major'] = getInfo($studentQuery,'major',$database);
							$_SESSION['year'] = getInfo($studentQuery,'year',$database);
							$_SESSION['student'] = 1;
							$_SESSION['instructor'] = 0;
							$_SESSION['administrator'] = 0;
							$_SESSION['department']="";
							header('Location:student.php');
						}
						else
						{
							echo '<div class = "out"><h4 id="out"><pre class = "normal">INVALID CREDENTIALS</pre></h4></div>';
						}
					break;
				}
			}
			else
			{
				echo '<div class = "out"><h4 id="out"><pre class = "normal">INVALID CREDENTIALS
							</pre></h4></div>';
			}
		}
		echo '<div class = "login"><form class = "login" id="loginForm" name="loginForm" action="Login.php" method="POST">', "\n";
		echo '<label for="user">Username:</label>';
		echo '<input type="textfield" name="user"><br/>', "\n";
		echo '<label for="password">Password:</label>';
		echo '<input type="password" name="password"><br/>', "\n";
		echo '<select name="selection" id="selection">';
	    echo '<option value="administrator">ADMINISTRATOR</option>';
		echo '<option value="instructor">INSTRUCTOR</option>';
		echo '<option value="student">STUDENT</option></select><br/>', "\n";
		echo '<input class = "button" type="submit" value="LOGIN">', "\n";
		echo '</form><br/>', "\n";
		echo '</div>';
		
	function getInfo($query,$col,$database)
	{
		$result = $database->query($query);
		$info='';
		if (is_object($result))
		{
			$row = $result->fetch_array(MYSQLI_ASSOC);
			$info = $row[$col];
		}
			return $info;
	}
?>
</body>
</html>
