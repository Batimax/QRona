<?php
// TEST ENV

use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;

function loadEncryptionKeyFromConfig()
{
	$path = dirname(__DIR__, 4).'/key_file.txt';
    $keyAscii = file_get_contents($path);
    return Key::loadFromAsciiSafeString($keyAscii);
}

function generateEncryptedEnv($value_to_encrypt) {
	$key  = loadEncryptionKeyFromConfig();
	$ciphertext = Crypto::encrypt($value_to_encrypt, $key);
	return $ciphertext;
}

function decryptEncryptedEnv($env_variable_to_get) {
	$key  = loadEncryptionKeyFromConfig();

	// DIR of .env
	$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
	$dotenv->load();

	try {
		$ciphertext = $_ENV[$env_variable_to_get];
	} catch (Exception $e) {
		echo 'Variable not found!';
	}

	try {
		$ciphertext = Crypto::decrypt($ciphertext, $key);
		return $ciphertext;
		} catch (\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
		echo 'Wrong decipheration key';}
		catch (Exception $e) {
		echo 'error!';
	}
}
