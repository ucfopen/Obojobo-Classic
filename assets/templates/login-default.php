
<form id="login-form" class="overview-details " method="post">
	<h1>Login to Begin</h1>
	<?php if(isset($notice)): ?>
		<p class="login-notice"><?= $notice ?></p>
	<?php endif; ?>

	<ul>
		<li>
			<label for="username">Username</label><br>
			<input type="text" id="username" name="username" value="" title="Username" placeholder="Username" tabindex="1">
		</li>
		<li>
			<label for="password">Password</label><br>
			<input type="password" id="password" name="password" value="" title="Password" placeholder="Password" tabindex="2">
		</li>

		<li>
			<input type="submit" id="signInSubmit" name="cmdweblogin" value="Login" tabindex="3">
		</li>
	</ul>

 	<ul class="foot">
		<li><a href="/forgot">Forgot Login?</a></li>
		<li><a href="/reset">Password Reset</a></li>
		<li><a href="/help">Help</a></li>
	</ul>

</form>
