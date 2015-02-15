<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta http-equiv="refresh" content="1;url=/">
		<title>Kikbook - Página não encontrada</title>
		<link rel="shortcut icon" href="/assets/img/favicon.ico">
		<link rel="apple-touch-icon" href="/assets/img/apple-touch-icon.png">
		<link rel="stylesheet" href="/assets/css/reset.css">
		<script src="/assets/js/modernizr.min.js"></script>
	</head>
	<body style="background-color: #FFFFFF;">
		<div>
			<img src="/assets/img/start_image_logo.png" style="height: 55px; width: 70px;"/>
			<img src="/assets/img/start_image_txt.png"  style="height: 60px; width: 85px;" />
		</div>
		<div style="padding: 20px; margin: auto; width: 640px;">
			<h1 style="font: normal 16px Arial; color: #000000;">Desculpe, a página que você solicitou não existe.</h4>
			<small><?php echo $message; ?></small>
		</div>
		<div style="padding: 20px; margin: auto; width: 640px;">
			<a href="/" style="text-decoration: none; color: #08C;">Volte para a página inicial</a>.</h4>
		</div>
	</body>
</html>

<?php log_message('error', '404 ERROR (início)' ); ?>
<?php log_message('error', '     Heading: ' . $heading );  ?>
<?php log_message('error', '         Msg: ' . $message );  ?>
<?php log_message('error', '404 ERROR (fim)' ); ?>
