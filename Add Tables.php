<!DOCTYPE html>
<html>
	<head>
		<title> GERALD's ADD TABLE PAGE </title>
		<link rel="stylesheet" type="text/css" href="style2.css">
	</head>
	<body>
		<h1 class = "title"> WELCOME TO GERALD BLAKE'S ADD TABLE PAGE </h1>
		<ul id="navbar">
			
			<li><a href = "http://www.cs.okstate.edu/~geralab/">CS HOMEPAGE</a></li>
			<li><a href = "Drop Tables.php">Drop Table Page</a></li>
			<li><a href = "Read File.php">Read File</a></li>
			
		</ul>
		<?php
	// MAIN CODE
	    
	// If there is a fileName, read the file
	if (array_key_exists('query', $_POST) ) 
	{
		$fileText = file_get_contents('/home/geralab/pass.txt', FILE_USE_INCLUDE_PATH);
	    $dbPassword = trim($fileText);
		$dbUser = 'geralab';
		$dbName = $dbUser; 
		$database = new mysqli("cs.okstate.edu", $dbUser, $dbPassword, $dbName);
		if (mysqli_connect_errno()) {
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}
		$q = trim($_POST['query']);
		$queries = explode(';', $q);
		foreach ($queries as $query)
		{
			$query = trim($query) . ';';
			if ($query != ';') 
			{
				$result = $database->query($query);
            }
		}
		if (mysqli_connect_errno()) 
		{
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit();
		}else 
		{
			header('Location:requestProcessed.php');
		}
	}
	else 
	{
	    echo '<div class = "login"><form class = "login" id="queryForm" name="queryForm" action="Add Tables.php" method="POST">', "\n";
		echo '<textarea name="query" form="queryForm">', "\n";
		echo "CREATE TABLE User (userName VARCHAR(100), userId INT, student INT, 
		instructor INT, administrator INT, salt VARCHAR(100), passwordHash VARCHAR(100), PRIMARY KEY(userName,userId));
		\n CREATE TABLE Student (name VARCHAR(100), userId INT, major VARCHAR(100), year INT, PRIMARY KEY(name,userId));
		\n CREATE TABLE Instructor (name VARCHAR(100), userId INT, department VARCHAR(100), tenure INT, PRIMARY KEY(name, userId));
		\n CREATE TABLE Teaches (userId INT, classId INT, PRIMARY KEY(userId,classId));
		\n CREATE TABLE Class (classId INT, className VARCHAR(100), classNum INT, sectionNum INT, semester VARCHAR(100), year INT,
		creditHours INT, maxEnrollment INT, open INT, finished INT, PRIMARY KEY(classId, className));
		\n
		CREATE TABLE Prerequisite (requiringClassNum INT, requiredClassNum INT);
		\n CREATE TABLE Takes (userId INT, classId INT, grade VARCHAR(100), PRIMARY KEY(userId,classId));
		
		\nCREATE TABLE Assignment (classId INT, assignmentName VARCHAR(100), numPoints INT);
		
		\n CREATE TABLE AssignmentGrade (classId INT, assignmentName VARCHAR(100), studentId INT, points INT);


\n</textarea><br/>", "\n";
		echo '<input class = "button" type="submit" value="ENTER QUERY">', "\n";
		echo '</form><br/>', "\n";
		echo '</div>';
	}
	?>
	</body>
</html>
