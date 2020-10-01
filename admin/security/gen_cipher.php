<?php
// TEST ENV
require_once __DIR__.'/../init.php';

if (isset($_POST['name']) and isset($_POST['pass'])) {

	$name = $_POST['name'];
	$pass_crypte = generateEncryptedEnv($_POST['pass']);

	echo '<p>Ligne à copier dans le .env :<br />' . $name . ' = \'' . $pass_crypte . '\'</p>';

} else
{
?>

	<p>Entrez votre login et votre mot de passe pour le crypter.</p>

	<form method="post">
		<p>
			Login : <input type="text" name="name"><br />
			Mot de passe : <input type="text" name="pass"><br /><br />

			<input type="submit" value="Crypter !">
		</p>
	</form>

<?php
}
