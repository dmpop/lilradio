<?php
$theme = "dark";
$volume_cookie = "volume_level";
if (!isset($_COOKIE[$volume_cookie])) {
	setcookie($volume_cookie, "90", time() + (86400 * 30), "/");
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $theme ?>">

<!-- Author: Dmitri Popov, dmpop@linux.com
	 License: GPLv3 https://www.gnu.org/licenses/gpl-3.0.txt -->

<head>
	<title>Little radio</title>
	<meta charset="utf-8">
	<link rel="shortcut icon" href="favicon.png" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="css/classless.css" />
	<link rel="stylesheet" href="css/themes.css" />
</head>

<body>
	<div class="card text-center" style="margin-bottom: 2em;">
		<h1 style="margin-top: 0em; margin-bottom: 0.7em; vertical-align: middle; letter-spacing: 3px; margin-top: 0.5em; color: #f6a159ff; text-transform: uppercase;">Little radio</h1>
		<hr style="margin-bottom: 1em;">
		<?php
		$dir = "pls";
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}
		?>
		<form action=" " method="POST">
			<label for='weight'>Station:</label>
			<select name="station">
				<?php
				$files = glob($dir . "/*");
				foreach ($files as $file) {
					$filename = basename($file);
					$name = pathinfo($file)['extension'];
					echo "<option value='$filename'>" . pathinfo($file)['filename'] . "</option>";
				}
				?>
			</select>
			<button type='submit' role='button' name='play'>Play</button>
			<button type='submit' role='button' name='stop'>Stop</button>
			<input style="margin-top: 1em; width: 90%; display: inline-block;" type="range" min="1" max="100" step="1" value="<?php echo $_COOKIE[$volume_cookie]; ?>" id="slider" name="slider" oninput="this.nextElementSibling.value = this.value" />
			<output style="vertical-align: 1.3em;"><?php echo $_COOKIE[$volume_cookie]; ?></output>
			<select name="sound">
				<option value='Headphone'>Headphone</option>
				<option value='Master'>Speaker</option>
			</select>
			<button type='submit' role='button' name='volume'>Volume</button>
			<button name="delete">Delete station</button>
	</div>
	</form>
	</div>
	<?php
	if (isset($_POST['upload'])) {
		$countfiles = count($_FILES['file']['name']);
		// looping all files
		for ($i = 0; $i < $countfiles; $i++) {
			$filename = $_FILES['file']['name'][$i];
			if (!file_exists($dir)) {
				mkdir($dir, 0777, true);
			}
			move_uploaded_file($_FILES['file']['tmp_name'][$i], $dir . DIRECTORY_SEPARATOR . $filename);
			echo "<script>";
			echo "window.location.href='.';";
			echo "</>";
		}
	}
	?>

	<div class="card text-center" style="margin-bottom: 2em;">
		<h2 style="margin-top: 0em;">Upload</h2>
		<form method='post' action='' enctype='multipart/form-data'>
			<input type="file" name="file[]" id="file" multiple>
			<button type='submit' role='button' name='upload'>Upload</button>
		</form>
	</div>

	<div class="card text-center" style="margin-bottom: 2em;">
		<h2 style="margin-top: 0em;">Play in browser</h2>
		<form action=" " method="POST">
			<input style="margin-top: 0.5em;" type='text' name='url'>
			<button type='submit' role='button' name='stream'>Stream</button>
		</form>
	</div>
	<?php
	if (isset($_POST["url"])) {
		echo '<div class="card text-center" style="margin-bottom: 2em;">';
		echo '<h3 style="margin-top: 0em;">Streaming controls</h3>';
		echo '<p> <audio src="' . $_POST['url'] . '" controls="true" volume="1.0"></audio></p>';
		echo '</div>';
	}
	?>
	</div>
	<?php
	if (isset($_POST['play'])) {
		echo "<script>";
		echo 'alert("Now streaming: ' . ($_POST['station']) . '")';
		echo "</script>";
		shell_exec('killall mplayer > /dev/null 2>&1 & echo $!');
		shell_exec('killall play > /dev/null 2>&1 & echo $!');
		shell_exec('mplayer -playlist ' . $dir . '/' . escapeshellarg($_POST['station']) . ' > /dev/null 2>&1 & echo $!');
	}
	if (isset($_POST['delete'])) {
		unlink(escapeshellcmd($dir . '/' . $_POST['station']));
		echo "<script>";
		echo "window.location.href='.';";
		echo "</script>";
	}
	if (isset($_POST['stop'])) {
		shell_exec('killall mplayer > /dev/null 2>&1 & echo $!');
		shell_exec('killall play > /dev/null 2>&1 & echo $!');
	}
	if (isset($_POST['volume'])) {
		shell_exec('amixer sset "' . ($_POST['sound']) . '" ' . ($_POST['slider']) . '% > /dev/null 2>&1 & echo $!');
		setcookie($volume_cookie, ($_POST['slider']), time() + (86400 * 30), "/");
		header("Refresh:0");
	}
	?>
	</div>
	<details>
		<summary style="letter-spacing: 1px; text-transform: uppercase;">Help</summary>
		<ul>
			<li>
				Use the <strong>Upload</strong> form to upload playlist files
			</li>
			<li>
				Choose the desired station, press <strong>Play</strong>
			</li>
			<li>
				Press <strong>Stop</strong> to stop streaming
			</li>
			<li>
				To adjust volume, use slider and press <strong>Volume</strong>
			</li>
			<li>
				Press <strong>Delete station</strong> to delete the currently selected playlist file
			</li>
			<li>
				To stream a radio station in the browser, enter the URL of the desired MP3 stream and press <strong>Stream</strong>
			</li>
		</ul>
	</details>
</body>

</html>