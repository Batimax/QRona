<?php

require_once __DIR__.'/security/env_secur.php';
require_once __DIR__.'/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/security/');
$dotenv->load();

// env. variables
$env_ENV = 'ENVIRONMENT';
$ENVIRONMENT = decryptEncryptedEnv($env_ENV);

if ($ENVIRONMENT == 'prod_env') {

	$DATABASE_USER = decryptEncryptedEnv('DATABASE_USE');
	$DATABASE_PASSWORD = decryptEncryptedEnv('DATABASE_PASSWORD');

	try {
		$dtb = new PDO(
		'mysql:
			host=localhost;
			dbname=QRona;
			charset=utf8',
		$DATABASE_USER,
		$DATABASE_PASSWORD,
		array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
		);
	} catch (Exception $e) {
		die('Erreur : ' . $e->getMessage());
	}

	} else {
	try {
		$dtb = new PDO(
			'mysql:
			host=localhost;
			dbname=QRona;
			charset=utf8',
			'root',
			'root',
			array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
		);
	} catch (Exception $e) {
		die('Erreur : ' . $e->getMessage());
	}
}
