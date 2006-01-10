<html>
	<head>
		<title><?php echo $page_title ?></title>
		<?php
			require_once("../core/api.php"); # replace with actual path
			pwn_head();
			echo '<style type="text/css">';
			print_css( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'sample.css' );
			echo '</style>';
		?>
	</head>
	<body>
<table class="layout" summary="" cellspacing="0">
<tr>
<td class="title" colspan="2"><h1>Webopedia Manual</h1></td>
</tr>
<tr>
<td class="search" colspan="2">search functionality should go here.</td>
</tr>
<tr valign="top">
<td class="side" width="200">
<?php
	pwn_index( $page, 'Webopedia', $page_parent );
?>
</td>
<td class="body">