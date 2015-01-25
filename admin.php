<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" type="text/css" href="style2.css">
  <title> ADMINISTRATOR</title>
</head>
<body>
<h1 class = "title"> ADMINISTRATOR </h1>
		<ul id="navbar">
		    <li><a href = "http://www.cs.okstate.edu/~geralab/">CS HOME</a></li>
			<li><a href = "http://www.cs.okstate.edu/~geralab/Project/Login.php">LOGIN</a></li>
		</ul>
</body>

<div id = "adminQ" name = "adminQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="admin.php" method="POST">
        <select name="selection" id="selection" onchange = "changeOptions()">
		<option value = "Z">PLEASE SELECT AN OPTION</option>
		<option value="general">GENERAL QUERY</option>
	    <option value="Add">ADD TABLES</option>
		<option value="Drop">DROP TABLES</option>
		<option value="Read"> READ INFORMATION FROM FILE</option>
		<option value="User"> ADD USER</option>
		<option value="Delete"> DELETE USER</option>
		<option value="Change"> CHANGE PASSWORD</option>
		<option value="Look"> LOOK UP USER</option>
		<option value="Instructor">INSTRUCTOR OPTIONS</option>
		<option value="Student">STUDENT OPTIONS</option>
		<option value="Class">CLASS OPTIONS</option>
		</select><br/>
		</form>
<?php
        session_start();
		if($_SESSION['administrator'] != 1)
		{
			header('Location:denied');
		}
		else
		{
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
			//GENERAL QUERIES
			if(array_key_exists('query', $_POST) ) 
			{
				$q = trim($_POST['query']);
				$queries = explode(';', $q);
				foreach ($queries as $query)
				{
					$query = trim($query) . ';';
					if ($query != ';') 
					{
						$result = $database->query($query);
						printTable2($result,$database);

					}
				}
			}
			//ADD USER
			else if(array_key_exists('userName', $_POST) 
			&& array_key_exists('userId', $_POST)
			&& array_key_exists('administrator', $_POST)
			&& array_key_exists('student', $_POST)
			&& array_key_exists('instructor', $_POST)
				 && array_key_exists('password', $_POST))
			{
				$userName = $_POST['userName'];
				$userId = $_POST['userId'];
				$student = $_POST['student'];
				$instructor = $_POST['instructor'];
				$administrator = $_POST['administrator'];
				$password = $_POST['password'];
				$salt = openssl_random_pseudo_bytes(16, $cstrong);
				$salt = bin2hex($salt);
				$passAndSalt = $password . $salt;
				$passHash = hash('sha256', $passAndSalt);
				    $newUserQuery = "INSERT INTO User (userName, userId, student, 
					instructor, administrator, salt, passwordHash) VALUES
					('$userName','$userId', $student, $instructor
					, $administrator, '$salt','$passHash');";
				$result = $database->query($newUserQuery);
				confirm($result);
			}
			else if(array_key_exists('selection',$_POST))
			{				
				$selection = $_POST['selection'];
				//DELETE USER
				 if(array_key_exists('userId', $_POST) 
				&& $selection =="Delete" ) 
				{
						$userId = $_POST['userId'];
						$query = "DELETE FROM AssignmentGrade WHERE studentId =
						'$userId';";
						$result = $database->query($query);
						confirm($result);
						$query = "DELETE FROM Takes WHERE userId =
						'$userId';";
						$result = $database->query($query);
						confirm($result);
						$query = "DELETE FROM Teaches WHERE userId =
						'$userId';";
						$database->query($query);
						$query = "DELETE FROM Instructor WHERE userId =
						'$userId';";
						$result = $database->query($query);
						confirm($result);
						$query = "DELETE FROM Student WHERE userId =
						'$userId';";
						$result = $database->query($query);
						confirm($result);
						$query = "DELETE FROM User WHERE userId =
						'$userId';";
						$result = $database->query($query);
						confirm($result);
				//SEARCH
				 } 
				 else if(array_key_exists('userName', $_POST) 
				 && $selection =="Look" )
				{
					$userName = $_POST['userName'];
					$searchQuery = 
					"SELECT * FROM User WHERE
					userName LIKE '$userName%' OR userName = '$userName';";
					printTable($searchQuery,$database);
				}
				//change password
				else if(array_key_exists('userId', $_POST)
				&&	array_key_exists('userName', $_POST)	
					&& array_key_exists('password', $_POST)
				 &&$selection =="Change" )
				{
					$userId = $_POST['userId'];
					$password = $_POST['password'];
					$salt = openssl_random_pseudo_bytes(16, $cstrong);
					$salt = bin2hex($salt);
					$passAndSalt = $password . $salt;
					$passHash = hash('sha256', $passAndSalt);
					$query = "UPDATE User SET salt = '$salt',passwordHash = '$passHash' WHERE userId =
						'$userId';";
						$result = $database->query($query);
						confirm($result);
				}
				else if(array_key_exists('studentOptions', $_POST)
				 &&$selection =="Student" )
				{
					$studentOptions = $_POST['studentOptions'];
					if($studentOptions =="ZZ" || $studentOptions == "class")
					{
					}
					else if($studentOptions == "directory")
					{
						$query = "SELECT * FROM Student;";
						printTable($query,$database);
					}
					else 
					{
						$query = "SELECT className FROM Takes NATURAL JOIN Class WHERE userId = $studentOptions;";
						printTable($query,$database);
					
					}
				}
				else if(array_key_exists('instructorOptions', $_POST)
				 &&$selection =="Instructor" )
				{
					$instructorOptions = $_POST['instructorOptions'];
					if($instructorOptions =="ZZ" || $instructorOptions == "class")
					{
					}
					else if($instructorOptions == "directory")
					{
						$query = "SELECT * FROM Instructor;";
						printTable($query,$database);
					}
					else 
					{
						$query = "SELECT className FROM Teaches NATURAL JOIN Class WHERE userId = $instructorOptions;";
						printTable($query,$database);
					}
				}
				else if(array_key_exists('instructorList', $_POST)
				 &&array_key_exists('classList', $_POST))
				{
					$instructorList = $_POST['instructorList'];
					$classList = $_POST['classList'];
					$query = "INSERT INTO Teaches (userId, classId) VALUES
					($instructorList, $classList)";
					$result = $database->query($query);
					confirm($result);
				}
				else if(array_key_exists('instructorRemove', $_POST))
				{
					$instructorRemove = $_POST['instructorRemove'];
					$query = "DELETE FROM Teaches WHERE classId = $instructorRemove;";
					$result = $database->query($query);
					confirm($result);
				}
				else if(array_key_exists('classOptions', $_POST))
				{
					$classOptions = $_POST['classOptions'];
					if($classOptions == 'directory')
					{
						$query = "SELECT * FROM Class;";
						printTable($query,$database);
					}
				}
			}
			 if(array_key_exists('classId', $_POST)
				&&array_key_exists('className', $_POST)
				&&array_key_exists('classNum', $_POST)
				&&array_key_exists('sectionNum', $_POST)
				&&array_key_exists('semester', $_POST)
				&&array_key_exists('year', $_POST)
				&&array_key_exists('creditHours', $_POST)
				&&array_key_exists('maxEnrollment', $_POST))
				{
					$classId = $_POST['classId'];
					$className = $_POST['className'];
					$classNum = $_POST['classNum'];
					$sectionNum = $_POST['sectionNum'];
					$semester = $_POST['semester'];
					$year= $_POST['year'];
					$creditHours = $_POST['creditHours'];
					$maxEnrollment = $_POST['maxEnrollment'];
					$open = $_POST['open'];
					$finished = $_POST['finished'];
					$query = "INSERT INTO Class (classId, className, 
					classNum, sectionNum, semester, year, creditHours,
					maxEnrollment,open,finished) VALUES
					($classId,'$className','$classNum',$sectionNum ,'$semester',
					$year,$creditHours,$maxEnrollment, $open, $finished);";
					$result = $database->query($query);
					confirm($result);
				}
				
				if(array_key_exists('changePrereq', $_POST))
				{
					$changePrereq = $_POST['changePrereq'];
					$query = "SELECT DISTINCT requiredClassNum AS PREREQUISITES 
					FROM Prerequisite WHERE requiringClassNum = '$changePrereq';";
					printTable($query,$database);
				}
				if(array_key_exists('addPrereq2',$_POST)
				&&array_key_exists('requiredClassNum', $_POST))
				{
					$addPrereq2 = $_POST['addPrereq2'];
					$requiredClassNum = $_POST['requiredClassNum'];
					$query = "INSERT INTO Prerequisite (requiringClassNum,
					requiredClassNum) VALUES('$addPrereq2', '$requiredClassNum');";
					$result = $database->query($query);
					confirm($result);
				}
				if(array_key_exists('delPrereq2',$_POST)
				&&array_key_exists('theReq', $_POST))
				{
					$delPrereq2 = $_POST['delPrereq2'];
					$theReq = $_POST['theReq'];
					$query = "DELETE FROM Prerequisite WHERE requiringClassNum 
					= '$delPrereq2' AND requiredClassNum = '$theReq';";
					$result = $database->query($query);
					confirm($result);
				}
		} 		
?>
</div>
<script>
function changeOptions()
{
	if(document.getElementById("selection").value == "Z")
	{
		document.getElementById("adminQ").innerHTML = '<div id = "adminQ" name = "adminQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="admin.php" method="POST">'+
				'<select name="selection" id="selection" onchange = "changeOptions()">'+
				'<option value="Z">PLEASE SELECT AN OPTION</option>'+
				'<option value="general">GENERAL QUERY</option>'+
				'<option value="Add">ADD TABLES</option>'+
				'<option value="Drop">DROP TABLES</option>'+
				'<option value="Read"> READ INFORMATION FROM FILE</option>'+
				'<option value="User"> ADD USER</option>'+
			'<option value="Delete"> DELETE USER</option>'+
			'<option value="Change"> CHANGE PASSWORD</option>'+
			'<option value="Look"> LOOK UP USER</option>'+
			'<option value="Instructor">INSTRUCTOR OPTIONS</option>'+
			'<option value="Student">STUDENT OPTIONS</option>' +
			'<option value="Class">CLASS OPTIONS</option>'+
			'</select></br>'+
			"</div>";
			document.getElementById("selection").value = 'Z';
	}
	else if(document.getElementById("selection").value == "general")
	{
		document.getElementById("adminQ").innerHTML = '<div id = "adminQ" name = "adminQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="admin.php" method="POST">'+
				'<select name="selection" id="selection" onchange = "changeOptions()">'+
				'<option value="Z">PLEASE SELECT AN OPTION</option>'+
				'<option value="general">GENERAL QUERY</option>'+
				'<option value="Add">ADD TABLES</option>'+
				'<option value="Drop">DROP TABLES</option>'+
				'<option value="Read"> READ INFORMATION FROM FILE</option>'+
				'<option value="User"> ADD USER</option>'+
			    '<option value="Delete"> DELETE USER</option>'+
				'<option value="Change"> CHANGE PASSWORD</option>'+
				'<option value="Look"> LOOK UP USER</option>'+
				'<option value="Instructor">INSTRUCTOR OPTIONS</option>'+
				'<option value="Student">STUDENT OPTIONS</option>' +
				'<option value="Class">CLASS OPTIONS</option>'+
			'</select></br>'+
				'\n<textarea name = "query"></textarea><br/>'+
			"<input class = \"button\" type=\"submit\" value=\"ENTER QUERY\">"+
			"</form><br/>"+
			"</div>";
			document.getElementById("selection").value = 'general';
	}
	else if(document.getElementById("selection").value == "Add")
	{
		document.getElementById("adminQ").innerHTML = ('<div id = "adminQ" name = "adminQ"'+
		' class = "login"><form class = "login" id="queryForm" name="queryForm" action="admin.php" method="POST">'+
			'<select name="selection" id="selection" onchange = "changeOptions()">'+
			'<option value="Z">PLEASE SELECT AN OPTION</option>'+
			'<option value="general">GENERAL QUERY</option>'+
			'<option value="Add">ADD TABLES</option>'+
			'<option value="Drop">DROP TABLES</option>'+
			'<option value="Read"> READ INFORMATION FROM FILE</option>'+
			'<option value="User"> ADD USER</option>'+
			'<option value="Delete"> DELETE USER</option>'+
			'<option value="Change"> CHANGE PASSWORD</option>'+
			'<option value="Look"> LOOK UP USER</option>'+
			'<option value="Instructor">INSTRUCTOR OPTIONS</option>'+
			'<option value="Student">STUDENT OPTIONS</option>' +
			'<option value="Class">CLASS OPTIONS</option>'+
			'</select></br>'+
			"<textarea name=\"query\" form=\"queryForm\">"+
			"CREATE TABLE User (userName VARCHAR(100), userId INT , student INT,"+
			"instructor INT, administrator INT, salt VARCHAR(100), passwordHash VARCHAR(100), PRIMARY KEY(userName,userId));"+
			"\n CREATE TABLE Student (name VARCHAR(100), userId INT REFERENCES User(userId),"+
			"major VARCHAR(100), year INT, PRIMARY KEY(name,userId));"+
			"\n CREATE TABLE Instructor (name VARCHAR(100), userId INT REFERENCES User(userId) ,"+
			"department VARCHAR(100), tenure INT, PRIMARY KEY(name, userId));"+
			"\n CREATE TABLE Teaches (userId INT REFERENCES User(userId) "+
			", classId INT, PRIMARY KEY(userId,classId));"+
			"\n CREATE TABLE Class (classId INT, className VARCHAR(100), classNum VARCHAR(150), sectionNum INT, semester VARCHAR(100), year INT,"+
			"creditHours INT, maxEnrollment INT, open INT, finished INT, PRIMARY KEY(classId, className));"+
			"\nCREATE TABLE Prerequisite (requiringClassNum VARCHAR(100), requiredClassNum VARCHAR(100));"+
			"\n CREATE TABLE Takes (userId INT REFERENCES User(userId) ,"+
			"classId INT, grade VARCHAR(100), PRIMARY KEY(userId,classId));"+
			"\nCREATE TABLE Assignment (classId INT, assignmentName VARCHAR(100), numPoints INT);"+
			"\n CREATE TABLE AssignmentGrade (classId INT, assignmentName VARCHAR(100), studentId INT REFERENCES User(userId) , points INT, PRIMARY KEY(classId, assignmentName, studentId));"+
	"\n</textarea><br/>"+
			"<input class = \"button\" type=\"submit\" value=\"ENTER QUERY\">"+
			"</form><br/>"+
			"</div>");
			document.getElementById("selection").value = 'Add';
	}
	else if(document.getElementById("selection").value == "Drop")
	{
		document.getElementById("adminQ").innerHTML = '<div id = "adminQ" name = "adminQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="admin.php" method="POST">'+
			'<select name="selection" id="selection" onchange = "changeOptions()">'+
			'<option value="Z">PLEASE SELECT AN OPTION</option>'+
			'<option value="general">GENERAL QUERY</option>'+
			'<option value="Add">ADD TABLES</option>'+
			'<option value="Drop">DROP TABLES</option>'+
			'<option value="Read"> READ INFORMATION FROM FILE</option>'+
			'<option value="User"> ADD USER</option>'+
			'<option value="Delete"> DELETE USER</option>'+
			'<option value="Change"> CHANGE PASSWORD</option>'+
			'<option value="Look"> LOOK UP USER</option>'+
			'<option value="Instructor">INSTRUCTOR OPTIONS</option>'+
			'<option value="Student">STUDENT OPTIONS</option>' +
			'<option value="Class">CLASS OPTIONS</option>'+
			'</select></br>'+
			"<textarea name=\"query\" form=\"queryForm\">"+

				  "DROP TABLE User; \n"+
				  "DROP TABLE Student;\n"+
				  "DROP TABLE Instructor; \n"+
				  "DROP TABLE Teaches; \n"+
				  "DROP TABLE Class; \n"+
				  "DROP TABLE Prerequisite; \n"+
				  "DROP TABLE Takes; \n"+
				  "DROP TABLE Assignment; \n"+
				  "DROP TABLE AssignmentGrade; \n"+
				 " </textarea><br/>\n"+

			"<input class = \"button\" type=\"submit\" value=\"ENTER QUERY\">\n"+

			"</form><br/>\n</div>"
			document.getElementById("selection").value = 'Drop';
	}
	else if(document.getElementById("selection").value == "Read")
	{
		document.getElementById("adminQ").innerHTML = '<div id = "adminQ" name = "adminQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="admin.php" method="POST">'+
			'<select name="selection" id="selection" onchange = "changeOptions()">'+
			'<option value="Z">PLEASE SELECT AN OPTION</option>'+
			'<option value="general">GENERAL QUERY</option>'+
			'<option value="Add">ADD TABLES</option>'+
			'<option value="Drop">DROP TABLES</option>'+
			'<option value="Read"> READ INFORMATION FROM FILE</option>'+
			'<option value="User"> ADD USER</option>'+
			'<option value="Delete"> DELETE USER</option>'+
			'<option value="Change"> CHANGE PASSWORD</option>'+
			'<option value="Look"> LOOK UP USER</option>'+
			'<option value="Instructor">INSTRUCTOR OPTIONS</option>'+
			'<option value="Student">STUDENT OPTIONS</option>' +
			'<option value="Class">CLASS OPTIONS</option>'+
			'</select></br>'+
			"<label for=\"fileName\">Filename:</label>"+

			"<input type=\"textfield\" id = \"fileName\" name=\"fileName\"><br/>"+

			"<textarea id = \"query\" name=\"query\" form=\"queryForm\">"+
			

			"</textarea><br/>"+

			"<input class = \"button\" type=\"submit\" value=\"QUERY FILE CONTENTS\">"+
			'<input class = \"button\" onclick = "getFileContent()" name = "GFILE" value="GET FILE CONTENTS">'+
			"</form><br/></div>";
		document.getElementById("selection").value = 'Read';
	}
	else if(document.getElementById("selection").value == "User")
	{
		document.getElementById("adminQ").innerHTML = '<div id = "adminQ" name = "adminQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="admin.php" method="POST">'+
			'<select name="selection" id="selection" onchange = "changeOptions()">'+
			'<option value="Z">PLEASE SELECT AN OPTION</option>'+
			'<option value="general">GENERAL QUERY</option>'+
			'<option value="Add">ADD TABLES</option>'+
			'<option value="Drop">DROP TABLES</option>'+
			'<option value="Read"> READ INFORMATION FROM FILE</option>'+
			'<option value="User"> ADD USER</option>'+
			'<option value="Delete"> DELETE USER</option>'+
			'<option value="Change"> CHANGE PASSWORD</option>'+
			'<option value="Look"> LOOK UP USER</option>'+
			'<option value="Instructor">INSTRUCTOR OPTIONS</option>'+
			'<option value="Student">STUDENT OPTIONS</option>' +
			'<option value="Class">CLASS OPTIONS</option>'+
			'</select></br>'+
			"<label for=\"userName\">USERNAME:</label>"+
			"<input type=\"textfield\" id = \"userName\" name=\"userName\"><br/>"+
			"<label for=\"userId\">USERID:</label>"+
			"<input type=\"textfield\" id = \"userId\" name=\"userId\"><br/>"+
			"<label for=\"student\">IS THIS USER A STUDENT:</label>"+
			'<select name = "student" id = "student">'+
			'<option value="FALSE">NO</option>'+
			'<option value="TRUE">YES</option>'+
			'</select></br></br>'+
			"<label for=\"instructor\">IS THIS USER AN INSTRUCTOR:</label>"+
			'<select name = "instructor" id = "instructor">'+
			'<option value="FALSE">NO</option>'+
			'<option value="TRUE">YES</option>'+
			'</select></br></br></br>'+
			"<label for=\"administrator\">IS THIS USER AN ADMINISTRATOR:</label>"+
			'<select name = "administrator" id = "administrator">'+
			'<option value="FALSE">NO</option>'+
			'<option value="TRUE">YES</option>'+
			'</select></br></br></br></br>'+
			"<label for=\"password\">PASSWORD:</label>"+
			"<input type=\"textfield\" id = \"password\" name=\"password\"><br/>"+
			"<input class = \"button\" type=\"submit\" value=\"CREATE USER\">"+
			"</form><br/></div>";
			document.getElementById("selection").value = 'User';
	}
	else if(document.getElementById("selection").value == "Delete")
	{
		document.getElementById("adminQ").innerHTML = '<div id = "adminQ" name = "adminQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="admin.php" method="POST">'+
			'<select name="selection" id="selection" onchange = "changeOptions()">'+
			'<option value="Z">PLEASE SELECT AN OPTION</option>'+
			'<option value="general">GENERAL QUERY</option>'+
			'<option value="Add">ADD TABLES</option>'+
			'<option value="Drop">DROP TABLES</option>'+
			'<option value="Read"> READ INFORMATION FROM FILE</option>'+
			'<option value="User"> ADD USER</option>'+
			'<option value="Delete"> DELETE USER</option>'+
			'<option value="Change"> CHANGE PASSWORD</option>'+
			'<option value="Look"> LOOK UP USER</option>'+
			'<option value="Instructor">INSTRUCTOR OPTIONS</option>'+
			'<option value="Student">STUDENT OPTIONS</option>' +
			'<option value="Class">CLASS OPTIONS</option>'+
			'</select></br>'+
			"<label for=\"userId\">USERID:</label>"+
			"<input type=\"textfield\" id = \"userId\" name=\"userId\"><br/>"+
			"<input class = \"button\" type=\"submit\" value=\"DELETE USER\">"+
			"</form><br/></div>";
			document.getElementById("selection").value = 'Delete';
	}
	else if(document.getElementById("selection").value == "Change")
	{
		document.getElementById("adminQ").innerHTML = '<div id = "adminQ" name = "adminQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="admin.php" method="POST">'+
			'<select name="selection" id="selection" onchange = "changeOptions()">'+
			'<option value="Z">PLEASE SELECT AN OPTION</option>'+
			'<option value="general">GENERAL QUERY</option>'+
			'<option value="Add">ADD TABLES</option>'+
			'<option value="Drop">DROP TABLES</option>'+
			'<option value="Read"> READ INFORMATION FROM FILE</option>'+
			'<option value="User"> ADD USER</option>'+
			'<option value="Delete"> DELETE USER</option>'+
			'<option value="Change"> CHANGE PASSWORD</option>'+
			'<option value="Look"> LOOK UP USER</option>'+
			'<option value="Instructor">INSTRUCTOR OPTIONS</option>'+
			'<option value="Student">STUDENT OPTIONS</option>' +
			'<option value="Class">CLASS OPTIONS</option>'+
			'</select></br>'+
			"<label for=\"userName\">USERNAME:</label>"+
			"<input type=\"textfield\" id = \"userName\" name=\"userName\"><br/>"+
			"<label for=\"userId\">USERID:</label>"+
			"<input type=\"textfield\" id = \"userId\" name=\"userId\"><br/>"+
			"<label for=\"password\">NEWPASSWORD:</label>"+
			"<input type=\"textfield\" id = \"password\" name=\"password\"><br/>"+
			"<input class = \"button\" type=\"submit\" value=\"CHANGE PASSWORD\">"+
			"</form><br/></div>";
			document.getElementById("selection").value = 'Change';
	}
	else if(document.getElementById("selection").value == "Look")
	{
		document.getElementById("adminQ").innerHTML = '<div id = "adminQ" name = "adminQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="admin.php" method="POST">'+
			'<select name="selection" id="selection" onchange = "changeOptions()">'+
			'<option value="Z">PLEASE SELECT AN OPTION</option>'+
			'<option value="general">GENERAL QUERY</option>'+
			'<option value="Add">ADD TABLES</option>'+
			'<option value="Drop">DROP TABLES</option>'+
			'<option value="Read"> READ INFORMATION FROM FILE</option>'+
			'<option value="User"> ADD USER</option>'+
			'<option value="Delete"> DELETE USER</option>'+
			'<option value="Change"> CHANGE PASSWORD</option>'+
			'<option value="Look"> LOOK UP USER</option>'+
			'<option value="Instructor">INSTRUCTOR OPTIONS</option>'+
			'<option value="Student">STUDENT OPTIONS</option>' +
			'<option value="Class">CLASS OPTIONS</option>'+
			'</select></br>'+
			"<label for=\"userName\">SEARCH:</label>"+
			"<input type=\"textfield\" id = \"userName\" name=\"userName\"><br/>"+
			"<input class = \"button\" type=\"submit\" value=\"SEARCH\">"+
			"</form><br/></div>";
			document.getElementById("selection").value = 'Look';
	}
	else if(document.getElementById("selection").value == "Instructor")
	{
		document.getElementById("adminQ").innerHTML = '<div id = "adminQ" name = "adminQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="admin.php" method="POST">'+
			'<select name="selection" id="selection" onchange = "changeOptions()">'+
			'<option value="Z">PLEASE SELECT AN OPTION</option>'+
			'<option value="general">GENERAL QUERY</option>'+
			'<option value="Add">ADD TABLES</option>'+
			'<option value="Drop">DROP TABLES</option>'+
			'<option value="Read"> READ INFORMATION FROM FILE</option>'+
			'<option value="User"> ADD USER</option>'+
			'<option value="Delete"> DELETE USER</option>'+
			'<option value="Change"> CHANGE PASSWORD</option>'+
			'<option value="Look"> LOOK UP USER</option>'+
			'<option value="Instructor">INSTRUCTOR OPTIONS</option>'+
			'<option value="Student">STUDENT OPTIONS</option>' +
			'<option value="Class">CLASS OPTIONS</option>'+
			'</select></br>'+
			'<div id = "insDiv"><label for="instructorOptions">OPTIONS:</label>'+
			'<select name = "instructorOptions" id = "instructorOptions" onchange = "updateInstructorOptions()">'+
			'<option value="ZZ">NO SELECTION</option>'+
			'<option value="directory"> INSTRUCTOR DIRECTORY</option>'+
			'<option value="class">FIND CLASSES TOUGHT BY INSTRUCTOR</option>'+
			'<option value="assign">ASSIGN TEACHING ASSIGNMENT</option>'+
			'<option value="remove">REMOVE TEACHING ASSIGNMENT</option>'+
			'</select></br></div>'+
			"</form><br/></div>";
			document.getElementById("selection").value = 'Instructor';
	}
	else if(document.getElementById("selection").value == "Student")
	{
		document.getElementById("adminQ").innerHTML = '<div id = "adminQ" name = "adminQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="admin.php" method="POST">'+
			'<select name="selection" id="selection" onchange = "changeOptions()">'+
			'<option value="Z">PLEASE SELECT AN OPTION</option>'+
			'<option value="general">GENERAL QUERY</option>'+
			'<option value="Add">ADD TABLES</option>'+
			'<option value="Drop">DROP TABLES</option>'+
			'<option value="Read"> READ INFORMATION FROM FILE</option>'+
			'<option value="User"> ADD USER</option>'+
			'<option value="Delete"> DELETE USER</option>'+
			'<option value="Change"> CHANGE PASSWORD</option>'+
			'<option value="Look"> LOOK UP USER</option>'+
			'<option value="Instructor">INSTRUCTOR OPTIONS</option>'+
			'<option value="Student">STUDENT OPTIONS</option>' +
			'<option value="Class">CLASS OPTIONS</option>'+
			'</select></br>'+
			"<label for=\"studentOptions\">OPTIONS:</label>"+
			'<select name = "studentOptions" id = "studentOptions" onchange = "updateStudentOptions()">'+
			'<option value="ZZ">NO SELECTION</option>'+
			'<option value="directory">STUDENT DIRECTORY</option>'+
			'<option value="class">FIND CLASSES TAKEN BY STUDENT</option>'+
			'</select></br>'+
			"</form><br/></div>";
			document.getElementById("selection").value = 'Student';
	}
	else if(document.getElementById("selection").value == "Class")
	{
		document.getElementById("adminQ").innerHTML = '<div id = "adminQ" name = "adminQ" class = "normal"><form class = "login" id="queryForm" name="queryForm" action="admin.php" method="POST">'+
			'<select name="selection" id="selection" onchange = "changeOptions()">'+
			'<option value="Z">PLEASE SELECT AN OPTION</option>'+
			'<option value="general">GENERAL QUERY</option>'+
			'<option value="Add">ADD TABLES</option>'+
			'<option value="Drop">DROP TABLES</option>'+
			'<option value="Read"> READ INFORMATION FROM FILE</option>'+
			'<option value="User"> ADD USER</option>'+
			'<option value="Delete"> DELETE USER</option>'+
			'<option value="Change"> CHANGE PASSWORD</option>'+
			'<option value="Look"> LOOK UP USER</option>'+
			'<option value="Instructor">INSTRUCTOR OPTIONS</option>'+
			'<option value="Student">STUDENT OPTIONS</option>' +
			'<option value="Class">CLASS OPTIONS</option>'+
			'</select></br>'+
			'<div id = "classDiv"><label for="classOptions">OPTIONS:</label>'+
			'<select name = "classOptions" id = "classOptions" onchange = "updateClassOptions()">'+
			'<option value="ZZ">NO SELECTION</option>'+
			'<option value="directory">CLASS DIRECTORY</option>'+
			'<option value="add">ADD CLASS</option>'+
			'<option value="prereq">CLASS AND PREREQUISITES</option>'+
			'<option value="addPrereq">ADD PREREQUISITE</option>'+
			'<option value="delPrereq">DELETE PREREQUISITE</option>'+
			'</select></br>'+
			"</div></form><br/></div>";
			document.getElementById("selection").value = 'Class';
	}
}//END CHANGE OPTIONS
function updateClassOptions()
{
	var classOption = document.getElementById("classOptions").value;
	if(classOption == "ZZ")
	{
	}
	else if(classOption == "add")
	{
		var temp = '<label for="classId">CLASS ID:</label>'+
		'<input type="textfield" name="classId"><br/>'+
		'<label for="className">CLASS NAME:</label>'+
		'<input type="textfield" name="className"><br/>'+
		'<label for="classNum">COURSE NUMBER:</label>'+
		'<input type="textfield" name="classNum"><br/><br/>'+
		'<label for="sectionNum">SECTION NUMBER:</label>'+
		'<input type="number" name="sectionNum" maxlength = "10"><br/><br/>'+
		'<label for="semester">SEMESTER:</label>'+
		'<select name = "semester"><option value = "fall">FALL</option>'+
		'<option value = "spring">SPRING</option>'+
		'<option value = "summer">SUMMER</option></select><br/>'+
		'<label for="year">YEAR:</label>'+
		'<input type="number" name="year"><br/>'+
		'<label for="creditHours">CREDIT HOURS:</label>'+
		'<input type="number" name="creditHours" maxlength = "7"><br/>'+
		'<label for="maxEnrollment">MAX ENROLLMENT:</label>'+
		'<input type="number" name="maxEnrollment" maxlength = "300"><br/><br/>'+
		'<label for="open">IS THE CLASS OPEN:</label>'+
		'<select name = "open"><option value = "FALSE">NO</option>'+
		'<option value = "TRUE">YES</option></select><br/><br/>'+
		'<label for="finished">IS THE CLASS FINISHED:</label>'+
		'<select name = "finished"><option value = "FALSE">NO</option>'+
		'<option value = "TRUE">YES</option></select><br/><br/>'+
		'<input class = "button" type="submit" value="ADD CLASS">';
		document.getElementById("classDiv").innerHTML = temp;
		
		
	}
	else if(classOption == "prereq")
	{
		var json = JSON.parse(getQueryText("SELECT className,classId,classNum,sectionNum,semester, year FROM Class"));
		var temp='<select name = "changePrereq" id = "changePrereq" onchange = "this.form.submit()"><option value="ZZZ">SELECT CLASS</option>';
		for(var key in json)
		{
			temp+= '<option value="'+json[key].classNum+'">'+json[key].className+ " "+
			json[key].classNum + " SECTION: "+ json[key].sectionNum+ " SEMESTER: "+json[key].semester+ " "+
			json[key].year+'</option>';
		}
		temp+='</select>';
		document.getElementById("classDiv").innerHTML = temp;
		
	}
	else if(classOption == "addPrereq")
	{
		var json = JSON.parse(getQueryText("SELECT className,classId,classNum,sectionNum,semester, year FROM Class"));
		var temp='<label for="addPrereq2">CLASS: </label>'+
		'<select name = "addPrereq2" id = "addPrereq2">'+
		'<option value="ZZZ">SELECT CLASS</option>';
		for(var key in json)
		{
			temp+= '<option value="'+json[key].classNum+'">'+json[key].className+ " "+
			json[key].classNum + " SECTION: "+ json[key].sectionNum+ " SEMESTER: "+json[key].semester+ " "+
			json[key].year+'</option>';
		}
		temp+='</select><br\><br\>';
		temp+='<label for="requiredClassNum">PREREQUISITE</label>'+
		'<input name = "requiredClassNum" type = "textfield"><br\>'+
		'<input type = "submit" class = "button" value ="ADD PREREQUISITE">';
		document.getElementById("classDiv").innerHTML = temp;
		
	}
	else if(classOption == "delPrereq")
	{
		var json = JSON.parse(getQueryText("SELECT className,classId,classNum,sectionNum,semester, year FROM Class"));
		var temp='<label for="delPrereq2">CLASS: </label><select name = "delPrereq2" id = "delPrereq2" onchange = "updatePrereq()">'+
		'<option value="ZZZ">SELECT CLASS</option>';
		for(var key in json)
		{
			temp+= '<option value="'+json[key].classNum+'">'+json[key].className+ " "+
			json[key].classNum + " SECTION: "+ json[key].sectionNum+ " SEMESTER: "+json[key].semester+ " "+
			json[key].year+'</option>';
		}
		temp+='</select><br\><br\><label for = "theReq">PREREQUISITES FOR CLASS: </label><select id ="theReq" name = "theReq">'+
		'<option value="ZZZZ">NO PREREQUISITES FOR THIS CLASS</option></select><br\><input class = "button" type = "submit" value = "DELETE PREREQUISITE">';
	
		document.getElementById("classDiv").innerHTML = temp;
		
	}
	else if(classOption == "class")
	{
		var json = JSON.parse(getQueryText("SELECT name,userId FROM Student"));
		var temp='<option value="ZZZ">SELECT STUDENT</option>';
		for(var key in json)
		{
			temp+= '<option value="'+json[key].userId+'">'+json[key].name+'</option>';
		}
		document.getElementById("classOptions").innerHTML = temp;
		
	}
	else
	{
		document.getElementById("queryForm").submit();
	}
}
function getFileContent()
{
	var httpRequest = new XMLHttpRequest();
    var file = document.getElementById("fileName").value;
	if(file.search(".php")!=-1 || file.search("admin")!=-1)
	{
		document.getElementById("fileName").value = "INVALID TYPE/NAME";
		document.getElementById("query").innerHTML = "INVALID TYPE/NAME";
	}
	else
	{
		var url = "file.php?file=" + document.getElementById("fileName").value;
		httpRequest.open("GET", url, false);
		httpRequest.send(null);
		document.getElementById("query").innerHTML=httpRequest.responseText;
	}
}//END GET FILE CONTENT

function updatePrereq()
{
var className = document.getElementById("delPrereq2").value;
var json = JSON.parse(getQueryText("SELECT DISTINCT requiredClassNum FROM Prerequisite WHERE requiringClassNum ='"+
     className+"';"));
		var temp='';
		if(json!="")
		{
			for(var key in json)
			{
				temp+= '<option value="'+json[key].requiredClassNum+'">'+json[key].requiredClassNum+'</option>';
			}
			temp+='<br\><br\><br\>';
		}
		else
		{
			temp +='<option value="ZZZZ">NO PREREQUISITES FOR THIS CLASS</option>';
		}
	
		document.getElementById("theReq").innerHTML = temp;
}
function updateStudentOptions()
{
	var studentOption = document.getElementById("studentOptions").value;
	if(studentOption == "ZZ")
	{
	}
	else if(studentOption == "class")
	{
		var json = JSON.parse(getQueryText("SELECT name,userId FROM Student"));
		var temp='<option value="ZZZ">SELECT STUDENT</option>';
		for(var key in json)
		{
			temp+= '<option value="'+json[key].userId+'">'+json[key].name+'</option>';
		}
		document.getElementById("studentOptions").innerHTML = temp;
		
	}
	else
	{
		document.getElementById("queryForm").submit();
	}
}
function updateInstructorOptions()
{
	var instructorOption = document.getElementById("instructorOptions").value;
	if(instructorOption == "ZZ")
	{
	}
	else if(instructorOption == "class")
	{
		var json = JSON.parse(getQueryText("SELECT name,userId FROM Instructor"));
		var temp='<option value="ZZZ">SELECT INSTRUCTOR</option>';
		for(var key in json)
		{
			temp+= '<option value="'+json[key].userId+'">'+json[key].name+'</option>';
		}
		document.getElementById("instructorOptions").innerHTML = temp;
		
	}
	else if(instructorOption == "assign")
	{
		var json = JSON.parse(getQueryText("SELECT name,userId FROM Instructor"));
		var temp = '<label for="instructorOptions">INSTRUCTORS:</label>'+
		'<select name = "instructorList" id = "instructorList">'+
		'<option value="ZZZ">SELECT INSTRUCTOR</option>';
		for(var key in json)
		{
			temp+= '<option value="'+json[key].userId+'">'+json[key].name+'</option>';
		}
		temp+='</select></br></br>';
		json = JSON.parse(getQueryText("SELECT className,classId,classNum,sectionNum,semester, year FROM Class"));
		temp += '<label for="classList">CLASS LIST</label>'+
		'<select name = "classList" id = "classList" onchange ='+
		'"updateInstructorOptions()">'+
		'<option value="ZZZ">SELECT CLASS</option>';
		for(var key in json)
		{
			temp+= '<option value="'+json[key].classId+'">'+json[key].className+ " "+
			json[key].classNum + " SECTION: "+ json[key].sectionNum+ " SEMESTER: "+json[key].semester+ " "+
			json[key].year+'</option>';
		}
		temp+='</select></br></br>'+
		'<input class = "button" type="submit" value="FINALIZE POSITION">';
		document.getElementById("insDiv").innerHTML = temp;
	}
	else if(instructorOption == "remove")
	{
	    var query = "SELECT classId,name,className,classNum,sectionNum,semester,userId "+ 
		"FROM Class NATURAL JOIN Teaches NATURAL JOIN Instructor;";
		var json = JSON.parse(getQueryText(query));
		var temp='<select name = "instructorRemove" id = "instructorRemove"'+
		'"><option value="ZZZ">REMOVE '+
		'TEACHING ASSIGNMENT</option>';
		for(var key in json)
		{
			temp+= '<option value="'+json[key].classId+'">'+json[key].name+
			" " + json[key].className + " "+json[key].classNum + " SECTION: " +
			json[key].sectionNum + " " +json[key].semester+ '</option>';
		}
		temp+= '</select><input class = "button" type="submit" value="FINALIZE REMOVAL">';
		document.getElementById("insDiv").innerHTML = temp;
	}
	else if(instructorOption == "class")
	{
	    var query = "SELECT classId,name,className,classNum,sectionNum,semester,userId "+ 
		"FROM Class NATURAL JOIN Teaches NATURAL JOIN Instructor;";
		var json = JSON.parse(getQueryText(query));
		var temp='<select name = "instructorRemove" id = "instructorRemove"'+
		'"><option value="ZZZ">REMOVE '+
		'TEACHING ASSIGNMENT</option>';
		for(var key in json)
		{
			temp+= '<option value="'+json[key].classId+'">'+json[key].name+
			" " + json[key].className + " "+json[key].classNum + " SECTION: " +
			json[key].sectionNum + " SEMESTER: " +json[key].semester+ '</option>';
		}
		temp+= '</select><input class = "button" type="submit" value="FINALIZE REMOVAL">';
		document.getElementById("insDiv").innerHTML = temp;
	}
	else
	{
		document.getElementById("queryForm").submit();
	}
}

function getQueryText(query)
{
	var httpRequest = new XMLHttpRequest();
	var url = "query.php?query=" + query;
	httpRequest.open("GET", url, false);
	httpRequest.send(null);
	return httpRequest.responseText;
}
</script>
<?php
     function confirm($result)
	 {
		 if($result)
		 {
			echo '<div class = "out"><h4 id="out"><pre class = "normal">QUERY SUCCESFUL!!!</pre></h4></div>';
		 }
		 else
		 {
			echo '<div class = "out"><h4 id="out"><pre class = "normal">QUERY FAILED</pre></h4></div>';
		 }
	 }
	 function printTable($query,$database)
	 {
		$result = $database->query($query);
		
		echo "</br></br></br><div>\n";
		//echo $query;
	
		if (!is_object($result))
		{
			if($result)
			{
				echo '<div class = "out"><h4 id="out"><pre class = "normal">QUERY SUCCESFUL!!!</pre></h4></div>';
			}
			else
			{
				echo '<div class = "out"><h4 id="out"><pre class = "normal">QUERY FAILED</pre></h4></div>';
			}
		}
		else 
		{
			// MAKE HTML TABLE
			echo '<table border="2" cellPadding="3">', "\n";
			$row = $result->fetch_array(MYSQLI_ASSOC);
			if ($row) 
			{
				$keys = array_keys($row);
				echo '<tr>';
				foreach ($keys as $key)
				{
					echo "<th>$key</th>";
				}
				echo '</tr>';
				while ($row)
				{
					echo '<tr>';
					foreach ($row as $cell) 
					{
						echo '<td>' . $cell . '</th>';
					}
					echo '</tr>';
					$row = $result->fetch_array(MYSQLI_ASSOC);
				}
			}
			echo "</table>\n";
			echo "<br/></div>";
		} 
	 }
	 function printTable2($result,$database)
	 {
		echo "</br></br></br><div>\n";
		//echo $query;
		
		if (!is_object($result))
		{
			if($result)
			{
				echo '<div class = "out"><h4 id="out"><pre class = "normal">QUERY SUCCESFUL!!!</pre></h4></div>';
			}
			else
			{
				echo '<div class = "out"><h4 id="out"><pre class = "normal">QUERY FAILED</pre></h4></div>';
			}
		}
		else 
		{
			// MAKE HTML TABLE
			echo '<table border="2" cellPadding="3">', "\n";
			$row = $result->fetch_array(MYSQLI_ASSOC);
			if ($row) 
			{
				$keys = array_keys($row);
				echo '<tr>';
				foreach ($keys as $key)
				{
					echo "<th>$key</th>";
				}
				echo '</tr>';
				while ($row)
				{
					echo '<tr>';
					foreach ($row as $cell) 
					{
						echo '<td>' . $cell . '</th>';
					}
					echo '</tr>';
					$row = $result->fetch_array(MYSQLI_ASSOC);
				}
			}
			echo "</table>\n";
			echo "<br/></div>";
		} 
	 }
	?>

</html>