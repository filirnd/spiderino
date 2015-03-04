<?php

/*
This file is part of spiderino package.
Writen by
	Cantarella Danilo (http://cantarelladanilo.com)
    Maccarrone Roberta (http://robertamaccarrone.altervista.org)
    Parasiliti Parracello Cristina (http://parasiliticristina.altervista.org)
    Randazzo Filippo (http://randazzofilippo.com);
    Safarally Dario (http://dariosafarally.altervista.org);
    Siragusa Sebastiano (http://sebastianosiragusa.altervista.org/)
    Vindigni Federico (http://federicovindigni.altervista.org)
Full spiderino is released by GPL3 licence.
*/

class Database{
	protected static $servername = "localhost"; 
	protected static $username = "root";
	protected static $password = "root";
	protected static $dbName = "spiderino";

	/*Function to create DB if not exist*/
	public static function createDb(){ 
		/* Create connection */
		$conn = new mysqli( self::$servername, self::$username, self::$password);
		/* Check connection */
		if ($conn->connect_error) {
			die("CreateDB connection failed: " . $conn->connect_error);
		} 

		/* Create database */
		$sql = "CREATE DATABASE ". self::$dbName;
		if ($conn->query($sql) === TRUE) {
			echo "Database created successfully\n";
		} $conn->close();
	}

	/*Function to create table in DB*/
	public static function createTable($tableName){
		/* Create connection */
		$conn = mysqli_connect(self::$servername, self::$username, self::$password, self::$dbName);
		/* Check connection */
		if (!$conn) {
			die("CreateTable connection failed: " . mysqli_connect_error());
		}

		/*SQL to create table*/
		$sql = "CREATE TABLE ".$tableName." (id INT(11), filename INT(11), url VARCHAR(300) NOT NULL PRIMARY KEY, father VARCHAR(300) NOT NULL, depth INT(11)  NOT NULL, discoveredurls INT(11) NOT NULL)";

		if (mysqli_query($conn, $sql)) {
			echo "Table ".$tableName." created successfully \n\n";
		} 

		mysqli_close($conn);

	}


	/*Function to insert url in table. Return 0 if record is correctly insert, 1 otherwise (ex: url duplicate) */
	public static function insert($tableName, $id, $filename, $url, $father, $depth, $discoveredurls){
		$toReturn = 0;
		/* Create connection */
		$conn = mysqli_connect(self::$servername, self::$username, self::$password, self::$dbName);
		/* Check connection */
		if (!$conn) {
			die("Insert function connection failed: " . mysqli_connect_error()). "\n";
		}

		/*SQL to insert url into table*/
		$sql = "INSERT INTO ".$tableName." (id, filename, url, father, depth, discoveredurls) VALUES ('".$id."','".$filename."', '".$url."', '".$father."', '".$depth."', '".$discoveredurls."')";

		if (mysqli_query($conn, $sql)) {
			echo $url." inserted successfully.\n";

		} else {
			$toReturn = 1;
		}

		mysqli_close($conn);
		return $toReturn;
	}

	/*Function to update a url's row. */
	public static function update($tableName, $filename, $url, $discoveredurls){
		$toReturn = 0;
		/* Create connection */
		$conn = mysqli_connect(self::$servername, self::$username, self::$password, self::$dbName);
		/* Check connection */
		if (!$conn) {
			die("Update function connection failed: " . mysqli_connect_error()). "\n";
		}

		/*SQL to update url's info such as number of urls founded*/
		$sql = "UPDATE ".$tableName." SET filename = '".$filename."', discoveredurls = '".$discoveredurls."' WHERE url = '".$url."' ";

		if (mysqli_query($conn, $sql)) {
			echo $url." updated successfully.\n";

		} else {
			$toReturn = 1;
		}

		mysqli_close($conn);
		return $toReturn;
	}

	/*Function to get depth's url father*/
	public static function getDepth($tableName, $father){
		$toReturn = 0;
		/* Create connection */
		$conn = mysqli_connect(self::$servername, self::$username, self::$password, self::$dbName);
		/* Check connection */
		if (!$conn) {
			die("getDepth function connection failed: " . mysqli_connect_error()). "\n";
		}

		/*SQL to extract depth's url from db*/
		$sql = "SELECT depth FROM ".$tableName." WHERE url = '".$father."'";

		if ($result = mysqli_query($conn, $sql)) {
			$row = mysqli_fetch_assoc($result);
			$toReturn = $row['depth'];
		} else {
			$toReturn = -2;
		}

		mysqli_close($conn);
		return $toReturn;


	}

	/*Function to get url from DB*/
	public static function getUrl($tableName, $index){
		$toReturn = 0;
		/* Create connection */
		$conn = mysqli_connect(self::$servername, self::$username, self::$password, self::$dbName);
		/* Check connection */
		if (!$conn) {
			die("Insert function connection failed: " . mysqli_connect_error()). "\n";
		}

		/*SQL to extract url from DB*/
		$sql = "SELECT url FROM ".$tableName." WHERE id = '".$index."'";

		if ($result = mysqli_query($conn, $sql)) {
			$row = mysqli_fetch_assoc($result);
			$toReturn = $row['url'];
		} else {
			$toReturn = -1;
		}

		mysqli_close($conn);
		return $toReturn;
	}
}

/*
This file is part of spiderino package.
Writen by
	Cantarella Danilo (http://cantarelladanilo.com)
    Maccarrone Roberta (http://robertamaccarrone.altervista.org)
    Parasiliti Parracello Cristina (http://parasiliticristina.altervista.org)
    Randazzo Filippo (http://randazzofilippo.com);
    Safarally Dario (http://dariosafarally.altervista.org);
    Siragusa Sebastiano (http://sebastianosiragusa.altervista.org/)
    Vindigni Federico (http://federicovindigni.altervista.org)
Full spiderino is released by GPL3 licence.
*/

?>


