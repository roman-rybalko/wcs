<html>
<meta charset="UTF-8"/>
<body>
<form method="post" action="proxy.php?http://<?php echo $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']); ?>/env.php">
<input type="text" name="test" value="test"/><br/>
<input type="submit" value="Post"/><br/>
</form>
<form method="get" action="proxy.php?http://<?php echo $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']); ?>/env.php">
<input type="text" name="test" value="test"/><br/>
<input type="submit" value="Get"/><br/>
</form>
<a href="proxy.php?http://<?php echo $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']); ?>/env.php?test=test">Get</a><br/>
<a href="proxy.php?http://yandex.ru">Yandex</a><br/>
<a href="proxy.php?http://yahoo.com">Yahoo</a><br/>
</body></html>