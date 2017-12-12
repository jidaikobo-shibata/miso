<?php
namespace Miso;
$errors = Session::fetch('messages', 'errors');
if ($errors):
?>
<div class="notice error is-dismissible">
<ul>
<?php foreach ($errors as $error): ?>
	<li><?php echo Util::s($error); ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php
endif;

$messages = Session::fetch('messages', 'messages');
if ($messages):
?>
<div class="notice notice-success is-dismissible">
<ul>
<?php foreach ($messages as $message): ?>
	<li><?php echo Util::s($message); ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php
endif;
