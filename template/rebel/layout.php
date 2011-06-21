<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>TWA Apps (preview)</title>
<style type="text/css">
	@import url(/template/rebel/css/global.css);
</style>
<script src="http://code.jquery.com/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="/template/rebel/js/rebel.js" type="text/javascript"></script>
<?=View::getCss($view)?>
<?=View::getJs($view)?>
</head>

<body>
<div id="wrapper">
	<div id="header">
		<a id="logo" href="/">Melodies of Life - sFramework showcase</a>
	</div>
	<div id="container" class="clearfix">

<?php $this->getContent($action, $view);?>
		
	</div>
	<div id="footer">
		&copy; Copyright 2009 <a href="http://iroy2000.blogdns.com">Melodies of Life</a>. All rights reserved.
	</div>
</div>
</body>
</html>
