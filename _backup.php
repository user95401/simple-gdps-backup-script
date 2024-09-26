<? session_start(); ?>
<html data-bs-theme="dark">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    html {
        color-scheme: dark;
    }
    nav, .card {
        --bs-card-bg: transparent!important;
        backdrop-filter: contrast(1.1);
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" integrity="sha384-fbbOQedDUMZZ5KreZpsbe1LCZPVmfTnH7ois6mU1QK+m14rQ1l2bGBq41eYeM/fS" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>

<div class="container px-1 my-4" style="
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
">

    <div class="card p-3" style="
    max-width: 90%;
">
	
<?php
require_once "config/connection.php";

$form = "
<form action='' method='POST'>
  <div class='mb-3'>
    <input class='d-none' value='$username' name='username'>
    <label for='InputPassword' class='form-label h4'>Database password:</label>
    <input type='password' name='verify_password' class='form-control' id='InputPassword'>
  </div>
  <button type='submit' class='btn btn-primary form-control'>Submit</button>
</form>
";

if(isset($_POST["verify_password"])) $_SESSION["verify_password"] = $_POST["verify_password"];
	
if(@$_SESSION["verify_password"] == $password) {
	if(isset($_GET["delete"])) {
		@$to_delete = __DIR__.'/data/data_backup['.$_GET["uniqid"].'].zip';
		
		echo("
			<h3>Backup #{$_GET['uniqid']} deletion</h3>Unlinking (deleting) \"$to_delete\"
			<br>
		");
		unlink($to_delete);
		
		echo("
			<a class='btn btn-primary mt-3' href='?'>Create New</a>
		");
	}
	else {

		$export_file_path = __DIR__ . '/data/'.$dbname.'.sql';

		$exec_str = "mysqldump --user=$username --password=$password --host=$servername $dbname > $export_file_path";
		if(exec($exec_str) === 0) die("failed to exec mysqldump");

		function addFileRecursion($zip, $dir, $start = '') {
			if (empty($start)) {
				$start = $dir;
			}
			
			if ($objs = glob($dir . '/*')) {
				foreach($objs as $obj) { 
					if (is_dir($obj)) {
						addFileRecursion($zip, $obj, $start);
					} else {
						$zip->addFile($obj, str_replace(dirname($start) . '/', '', $obj));
					}
				}
			}
		}

		$uniqid = uniqid();

		$zip = new ZipArchive();
		$zip->open(__DIR__ . '/data/data_backup['.$uniqid.'].zip', ZipArchive::CREATE|ZipArchive::OVERWRITE);
		addFileRecursion($zip, __DIR__ . '/data/accounts');
		addFileRecursion($zip, __DIR__ . '/data/levels');
		$zip->addFile($export_file_path, $dbname.'.sql');
		$zip->addFromString('log.txt', "At ".date('m/d/Y h:i:s a', time()).":\n\n".print_r(apache_request_headers(), 1));
		$zip->close();

		unlink($export_file_path);

		echo("

			<h1 style='margin-bottom: -12!important;'>Backup was created...</h1>
			<hr>	
			Created zip file that contains:
			<pre>
- dir and files in \"/data/accounts\",
- dir and files in \"/data/levels\",
- the mysqldump named \"$dbname.sql\"
- info about this request in \"log.txt\"</pre>

			<a class='btn btn-primary mb-3' href='/data/data_backup[{$uniqid}].zip'>
				Download
			</a>

			<a class='btn btn-danger' href='?delete&uniqid={$uniqid}'>
				Delete
			</a>

		");
	};
}
else {
	if(isset($_POST["verify_password"])) echo("
		<h5 class=\"link-danger\">
		Password do not matches to \$password from config/connection.php
		</h5>
	".$form);
	else echo($form);
}
?>
    </div>
	
	<figcaption class="blockquote-footer m-0 p-0">
		user95401's original tool.
	</figcaption>
	
</div>