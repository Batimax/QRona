<?php
// https://www.portaltechnologies.uk/mysql-database-backup-encryption-and-decryption/
//configuration

genDBBackup();
function genDBBackup () {
	require_once __DIR__.'/init.php';

	$secret = decryptEncryptedEnv('BD_BACKUP_CRYPT_KEY');
	// $folder = '/srv/data/nextcloud/mcurvat/files/export';
	$folder = getenv("DOCUMENT_ROOT") . '/db_dumps/'; // change the folder name to suit
	$d = date('dmy');
	$halt = '1';
	//Your database details
	if ($ENVIRONMENT == 'prod_env') {
		$dbusername = decryptEncryptedEnv('DATABASE_USE');
		$dbpassword = decryptEncryptedEnv('DATABASE_PASSWORD');
		$msqldump_path = 'mysqldump';
	} else {
		$dbusername = 'root';
		$dbpassword = '\'root\'';
		$msqldump_path = '/Applications/MAMP/Library/bin/mysqldump';
	}
	$dbname = 'QRona';
	$dbhost = 'localhost';

	//the names of the files
	$sql = $folder . "db_backup" . $d . ".sql";
	$encrypted = $folder . "db_backup" . $d . ".enc";
	//Dump the SQL data
	$command = "$msqldump_path -u $dbusername -p$dbpassword  --host=$dbhost --opt $dbname > $sql";
	$command .= " ; sleep $halt ; ";

	//Encrypt the file with OpenSSL and add a password to it
	$command .= "openssl enc -aes-256-cbc -salt -in $sql -out $encrypted -pass pass:$secret";
	$command .= " ; sleep $halt ; ";

	// Remove files older than 7 days
	$command .= "find $folder -mtime +7 -exec rm {} \; ;";

	//remove the sql dump file and just leave the encrypted file
	$command .= "rm -f $sql";
	// //execute it
	echo $command;
	echo $output = shell_exec($command);
	echo "<br /> command executed";
}
?>
