
<form id="login-form" class="overview-details " method="post">
	<h1>Login to Begin</h1>
	<?php if(isset($notice)): ?>
		<p class="login-notice"><?= $notice ?></p>
	<?php endif; ?>

	<ul>
		<li>
			<input type="submit" id="signInSubmit" name="cmdweblogin" value="Login" tabindex="3">
		</li>
	</ul>

	<ul class="foot">
		<li><a href="https://my.ucf.edu/nid.html">Lookup NID</a></li>
		<li><a href="http://mynid.ucf.edu/">Reset Password</a></li>
		<li><a href="/help">Help</a></li>
	</ul>

</form>
