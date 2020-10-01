<?php
require_once __DIR__.'/init.php';

//folder location change to suit
$folder= getenv("DOCUMENT_ROOT").'/db_dumps/';
$halt='1';
//retrieve the data from the URL
$p= $secret = decryptEncryptedEnv('BD_BACKUP_CRYPT_KEY');

if (isset($_POST['date'])) {

	$d = $_POST['date'];

	//file locations and names
	$encrypted= $folder."db_backup".$d.".enc";
	$sql= $folder."db_backup_up".$d.".sql";
	//decrypt the .enc file into a readable sql file
	$command="openssl aes-256-cbc -d -in $encrypted -out $sql -k $p";
	//Gzip the decrypted sql file
	$command .= " ;gzip -f -q $sql";
	$command .= " ; sleep $halt; ";
	//execute it
	$output = shell_exec($command);
	// echo $command;
	echo "db_backup" . $d . " decrypted. in " .$sql;

} else
{
?>

	<p>Enter the date of the db backup to decrypt.</p>

	<form method="post">
		<p>
			Date : <input type="text" name="date"><br /><br />

			<input type="submit" value="Decrypt!" placeholder="130120">
		</p>
	</form>

<?php
}



