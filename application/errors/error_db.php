<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta http-equiv="refresh" content="1;url=/">
		<title>Kikbook - Erro de base de dados</title>
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
		<div style="padding:20px; margin: auto; width: 640px;">
			<h1 style="font: normal 16px Arial; color: #000000;">Desculpe, ocorreu um erro na execução da sua solicação.</h4>
			<br/>
			<br/>
			<div style="font-family: Consolas, Monaco, Courier New, Courier, monospace; font-size: 12px; border: 1px solid #D0D0D0; color: #000000; display: block; margin: 0px; padding: 15px;">
				<h4 style="font: normal 14px Arial; color: #000000;">
					<?php echo $heading; ?>
				</h4>
				<?php echo $message; ?>
			</div>
		<div style="text-align: center; bottom: 10px; width: 500px; margin: auto; position: fixed;">
			<a href="http://singlesolutions.com.br" style="color: whitesmoke;">Single Solutions (c) 2012</a>
		</div>
	</body>
</html>

<?php log_message('error', 'DB ERROR (início)' ); ?>
<?php log_message('error', '     Heading: ' . $heading );  ?>
<?php log_message('error', '         Msg: ' . $message );  ?>
<?php log_message('error', 'DB ERROR (fim)' ); ?>
