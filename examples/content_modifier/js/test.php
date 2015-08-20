<html>
<meta charset="UTF-8"/>
<body>
<h1 class="title">Заголовок</h1>
<span id="text">Текст...</span>
<script type="text/javascript" src="<?php echo dirname($_SERVER['SCRIPT_NAME']); ?>/js.php?selector_type=class&selector=title&modifier_type=append&modifier=Test"></script>
<script type="text/javascript" src="<?php echo dirname($_SERVER['SCRIPT_NAME']); ?>/js.php?selector_type=id&selector=text&modifier_type=replace&modifier=Replaced"></script>
</body></html>