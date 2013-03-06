<?php
error_reporting(7);
$url = $_GET['url'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
	<title>Xiuno BBS </title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<p>
	<form action="proxy.php" method="post">
		<input type="text" name="url" value="<?php echo $url;?>" size="64" /> <input type="submit" name="xx" value="提交" />
	</form>
</p>
<?php

if($url) {
	$s = file_get_contents($url);
	$s = preg_replace('#<script[^>]>.*?</script>#ism', '', $s);
	preg_match('#<body[^>]*>(.*)</body>#ism', $s, $m);
	echo $m[1];
}
?>

</body>
</html>