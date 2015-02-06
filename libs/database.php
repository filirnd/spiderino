<?php

class Database{
	protected static $servername = "localhost"; 
	protected static $username = "root";
	protected static $password = "root";
	protected static $dbName = "spiderino";
	protected static $tableName = "spiderinoTable";


	public static function createDb(){ /* create db if not exist */
		//echo "---- Try to create DB ". self::$dbName ."\n";
		// Create connection
		$conn = new mysqli( self::$servername, self::$username, self::$password);
		// Check connection
		if ($conn->connect_error) {
			die("CreateDB connection failed: " . $conn->connect_error);
		} 

		// Create database
		$sql = "CREATE DATABASE ". self::$dbName;
		if ($conn->query($sql) === TRUE) {
			echo "Database created successfully\n";
		} else {
			//echo "Error creating database: " . $conn->error. "\n";
		}

		$conn->close();
	}


	public static function createTable(){
		//echo "Try to create table ". self::$tableName ."\n";
		// Create connection
		$conn = mysqli_connect(self::$servername, self::$username, self::$password, self::$dbName);
		// Check connection
		if (!$conn) {
			die("CreateTable connection failed: " . mysqli_connect_error());
		}


		// sql to create table
		$sql = "CREATE TABLE ".self::$tableName." (id INT(11) , url VARCHAR(300) NOT NULL PRIMARY KEY, father VARCHAR(300) NOT NULL, depth INT(11)  NOT NULL, discoveredurls INT(11) NOT NULL)";

		if (mysqli_query($conn, $sql)) {
			echo "Table ".self::$tableName." created successfully \n\n";
		} else {
			//echo "Error creating table: " . mysqli_error($conn)."\n\n";
		}

		mysqli_close($conn);

	}


	/* return 0 if record is correctly insert, 1 otherwise */
	public static function insert($id, $url, $father, $depth, $discoveredurls){
		$toReturn = 0;
		// Create connection
		$conn = mysqli_connect(self::$servername, self::$username, self::$password, self::$dbName);
		// Check connection
		if (!$conn) {
			die("Insert function connection failed: " . mysqli_connect_error()). "\n";
		}

		$sql = "INSERT INTO ".self::$tableName." (id, url, father, depth, discoveredurls) VALUES ('".$id."', '".$url."', '".$father."', '".$depth."', '".$discoveredurls."')";

		if (mysqli_query($conn, $sql)) {
			echo $url." inserted successfully.\n";

		} else {
			$toReturn = 1;
			//echo "\n---- Error: " . $sql . "\n" . mysqli_error($conn) . "\n";
		}

		mysqli_close($conn);
		return $toReturn;


	}

	/* return 0 if record is correctly update, 1 otherwise */
	public static function update($id, $url, $discoveredurls){
		$toReturn = 0;
		// Create connection
		$conn = mysqli_connect(self::$servername, self::$username, self::$password, self::$dbName);
		// Check connection
		if (!$conn) {
			die("Update function connection failed: " . mysqli_connect_error()). "\n";
		}

		$sql = "UPDATE ".self::$tableName." SET id = '".$id."', discoveredurls = '".$discoveredurls."' WHERE url = '".$url."' ";

		if (mysqli_query($conn, $sql)) {
			echo $url." updated successfully.\n";

		} else {
			$toReturn = 1;
			//echo "---- Error: " . $sql . "<br>" . mysqli_error($conn) . "\n";
		}

		mysqli_close($conn);
		return $toReturn;


	}

	/* return 0 if record is correctly insert, 1 otherwise */
	public static function getDepth($father){
		$toReturn = 0;
		// Create connection
		$conn = mysqli_connect(self::$servername, self::$username, self::$password, self::$dbName);
		// Check connection
		if (!$conn) {
			die("getDepth function connection failed: " . mysqli_connect_error()). "\n";
		}

		$sql = "SELECT depth FROM ".self::$tableName." WHERE url = '".$father."'";

		if ($result = mysqli_query($conn, $sql)) {
			$row = mysqli_fetch_assoc($result);
			$toReturn = $row['depth'];
			//echo "Profondita di ".$father." uguale a ".$toReturn."\n";

		} else {
			$toReturn = -2;
			//echo "\n---- Error: " . $sql . "\n" . mysqli_error($conn) . "\n";
		}

		mysqli_close($conn);
		return $toReturn;


	}


}

?>


