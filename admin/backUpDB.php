<?php
//configuration
$secret = 'a_password_of_your_choice';
$folder = '/srv/data/nextcloud/mcurvat/files/export';
//$folder = getenv("DOCUMENT_ROOT") . '/db_dumps/'; // change the folder name to suit
$d = date('dmy');
$halt = '5';
//Your database details
$dbusername = 'QRona';
$dbpassword = 'root';
$dbhost = 'root';
//the names of the files
$sql = $folder . "db_backup" . $d . ".sql";
$encrypted = $folder . "db_backup" . $d . ".enc";
//Dump the SQL data
$command = "mysqldump -u $dbusername -p $dbpassword --host= $dbhost --opt -A > $sql";
$command .= " ; sleep $halt ; ";
//Encrypt the file with OpenSSL and add a password to it
$command .= "openssl enc -aes-256-cbc -salt -in $sql -out $encrypted -pass pass:$secret";
$command .= " ; sleep $halt ; ";
$command .= "sleep $halt ; ";
//remove any dumps older than 7 days in the folder
$seven_days_ago = date("Ymd", strtotime("-7 day"));
$files = scandir($folder);
foreach ($files as $f) {
	$modified = date("Ymd", filemtime($f));
	if ($modified < $seven_days_ago) {
		$command .= 'rm -f ' . "$folder$f";
	}
}
//remove the sql dump file and just leave the encrypted file
$command .= "rm -f $sql";
//execute it
$output = shell_exec($command);
echo "get the file here ";

?>
