<div style="padding-left:20px; padding-top: 25px; backgroud-color: #ffffff;">
	<h4 style="font: normal 14px Arial; color: #000000;">A PHP Error was encountered</h4>
	<br/>
	<p style="font-family: Consolas, Monaco, Courier New, Courier, monospace; font-size: 12px; color: whiteSmoke; display: block;padding: 12px 10px 12px 10px; color: #000000;">
		Severity: <?php echo $severity; ?>
	</p>
	<p style="font-family: Consolas, Monaco, Courier New, Courier, monospace; font-size: 12px; color: whiteSmoke; display: block;padding: 12px 10px 12px 10px; color: #000000;">
		Message:  <?php echo $message; ?>
	</p>
	<p style="font-family: Consolas, Monaco, Courier New, Courier, monospace; font-size: 12px; color: whiteSmoke; display: block;padding: 12px 10px 12px 10px; color: #000000;">
		Filename: <?php echo $filepath; ?>
	</p>
	<p style="font-family: Consolas, Monaco, Courier New, Courier, monospace; font-size: 12px; color: whiteSmoke; display: block;padding: 12px 10px 12px 10px; color: #000000;">
		Line Number: <?php echo $line; ?>
	</p>
</div>

<?php log_message('error', 'PHP ERROR (início)' ); ?>
<?php log_message('error', '    Severity: ' . $severity );  ?>
<?php log_message('error', '         Msg: ' . $message );  ?>
<?php log_message('error', '        File: ' . $filepath );  ?>
<?php log_message('error', '        Line: ' . $line );  ?>
<?php log_message('error', 'PHP ERROR (fim)' ); ?>
