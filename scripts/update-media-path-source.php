<?php
	$host = 'localhost';
	$db   = '';
	$user = '';
	$pass = '';
	$charset = 'utf8';

	$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
	$opt = [
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES   => false,
	];
	$pdo = new PDO($dsn, $user, $pass, $opt);

	$stmt = $pdo->query('SELECT MUSIC_ID, path FROM music');
	while ($row = $stmt->fetch()) {
		$id   = $row ['MUSIC_ID'];
		$path = $row ['path'];

		$newpath = str_replace ('//192.168.1.2', '//192.168.2.2', $path);
		$newpath = str_replace ('\'', '\\\'', $newpath);
		$updateQuery = "UPDATE music SET path='$newpath' WHERE MUSIC_ID=$id";
		echo "$updateQuery\n";

		$pdo->query($updateQuery);
	}
