<html>
<head>
<?php if ($is_hr == TRUE) { header('Location:users'); ?><?php } ?>
<?php if ($is_boss == TRUE || $is_admin == TRUE) {header('Location:requests');?><?php } ?>
<?php if ($is_boss != TRUE && $is_admin != TRUE && $is_hr != TRUE) { header('Location:leaves'); ?><?php } ?>
<meta name="keywords" content="automatic redirection">
</head>
</html>