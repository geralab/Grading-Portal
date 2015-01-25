<!DOCTYPE html>

<html>

	<head>

		<title> GERALD's READ FROM FILE PAGE </title>

		<link rel="stylesheet" type="text/css" href="style2.css">

	</head>

	<body>

		<h1 class = "title"> WELCOME TO GERALD BLAKE'S READ FROM FILE PAGE </h1>

		<ul id="navbar">

			
            <li><a href = "http://www.cs.okstate.edu/~geralab/">CS HOME</a></li>	
			<li><a href = "Add Tables.php">Add Table Page</a></li>

			<li><a href = "Drop Tables.php">Drop Table Page</a></li>

		

			

		</ul>

		<?php

	// MAIN CODE

	

	// If there is a fileName, read the file

	if (array_key_exists('fileName', $_POST) ) 

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

		$fileName = $_POST['fileName'];

       

	

		

	

		if (mysqli_connect_errno()) 

		{

			printf("Connect failed: %s\n", mysqli_connect_error());

			exit();

		}

		

	}

 

	

	    echo '<div class = "login"><form class = "login" id="queryForm" name="queryForm" action="Read File.php" method="POST">', "\n";

		echo '<label for="fileName">Filename:</label>';

		echo '<input type="textfield" name="fileName"><br/>', "\n";


		echo '<textarea name="query" form="queryForm">', "\n";

		if (array_key_exists('fileName', $_POST) ) 

	{

		runQueriesFromFile($database, $fileName, true);

		}

		echo "</textarea><br/>", "\n";

		

		echo '<input class = "button" type="submit" value="GET FILE CONTENTS">', "\n";

		echo '</form><br/>', "\n";

		echo '</div>';

	

	?>

	<?php

	// Function to read a bunch of queries from a file and send them to the database



	function runQueriesFromFile($database, $fileName, $printResults)

	{

		$fileText = file_get_contents($fileName, FILE_USE_INCLUDE_PATH);

		$queries = explode(';', $fileText);

		

		echo "<pre>\n";

		foreach ($queries as $query)

		{

			$query = trim($query) . ';';

			if ($query != ';') {

				$result = $database->query($query);

				if ($printResults) 

				{

					echo $query . "\nresult = " . $result . "\n\n";

				}

			}

		}

		echo "</pre>\n";

	}

?>

	</body>

</html>

