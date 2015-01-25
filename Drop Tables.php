<!DOCTYPE html>

<html>

	<head>

		<title> GERALD's DROP TABLE PAGE </title>

		<link rel="stylesheet" type="text/css" href="style2.css">

	</head>

	<body>

		<h1 class = "title"> Welcome to Gerald Blake's DROP TABLE Page </h1>

		<ul id="navbar">

		
			<li><a href = "http://www.cs.okstate.edu/~geralab/">CS HOME</a></li>
			<li><a href = "Add Tables.php">Add Table Page</a></li>

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

	    echo '<div class = "login"><form class = "login" id="queryForm" name="queryForm" action="Drop Tables.php" method="POST">', "\n";

		echo '<textarea name="query" form="queryForm">', "\n";

		echo "DROP TABLE User; \n

              DROP TABLE Student;\n
              DROP TABLE Instructor; \n
			  DROP TABLE Teaches; \n
			  DROP TABLE Class; \n
			  DROP TABLE Prerequisite; \n
			  DROP TABLE Takes; \n
			  DROP TABLE Assignment; \n
			  DROP TABLE AssignmentGrade; \n
			  </textarea><br/>", "\n";

		echo '<input class = "button" type="submit" value="ENTER QUERY">', "\n";

		echo '</form><br/>', "\n";

		echo '</div>';

	}

	?>

	</body>

</html>

