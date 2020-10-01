<?php
// https://www.portaltechnologies.uk/mysql-database-backup-encryption-and-decryption/
require_once __DIR__.'/init.php';
//configuration
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
// /Applications/MAMP/Library/bin/mysqldump -u root -p'root' --opt QRona > /Users/Max/Sites/WebServer/db_dumps/db_backup011020.sql
// /Applications/MAMP/Library/bin/mysqldump -u root -p'root' --host=localhost --opt QRona > /Users/Max/Sites/WebServer/db_dumps/db_backup011020.sql

//Encrypt the file with OpenSSL and add a password to it
$command .= "openssl enc -aes-256-cbc -salt -in $sql -out $encrypted -pass pass:$secret";
$command .= " ; sleep $halt ; ";
//remove any dumps older than 7 days in the folder
// $seven_days_ago = date("Ymd", strtotime("-7 day"));
// print_r($files = scandir($folder));

// sudo find /var/backup/db/. -mtime +7 -exec rm {} \;
// echo $f = $files[2];
// 	$modified = date("Ymd", filemtime($f));
// 	if ($modified < $seven_days_ago) {
		// $command .= 'rm -f ' . "$folder$f";

// }

// foreach ($files as $f) {
// 	$f;
// 	$modified = date("Ymd", filemtime($f));
// 	if ($modified < $seven_days_ago) {
// 		// $command .= 'rm -f ' . "$folder$f";
// 	}
// }
//remove the sql dump file and just leave the encrypted file
$command .= "rm -f $sql";
// //execute it
$output = shell_exec($command);
echo "<br /> command executed";

?>
