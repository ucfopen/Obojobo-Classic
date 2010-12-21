<?php
$ASE_timestamp = '1291230140';
$ASE_time = 'December 1, 2010, 2:02 pm';
$ASE_savedby = 'obojobo.ucf.edu,,,132.170.240.85';
$ASE_chunk_raw = <<<'NOWDOC'
a:8:{s:2:"id";s:1:"9";s:4:"name";s:12:"WebLoginForm";s:11:"description";s:0:"";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"0";s:10:"cache_type";s:1:"0";s:7:"snippet";s:847:"<!-- #declare:separator <hr> --> 
<!-- login form section-->
<h2>Log In</h2>
<form method="post" name="loginfrm" action="[+action+]" style="margin: 0px; padding: 0px;"> 
UCF NID<br/>
<input type="text" name="username" tabindex="1" onkeypress="return webLoginEnter(document.loginfrm.password);" style="width: 100px;" value="" onfocus="this.value=(this.value=='NID')? '' : this.value ;"  /><br/>
Password<br/>
<input type="password" name="password" tabindex="2" onkeypress="return webLoginEnter(document.loginfrm.cmdweblogin);" style="width: 100px;"   onfocus="this.value=(this.value=='password')? '' : this.value ;" /><br/><br/>
<input type="submit" tabindex="3" value="[+logintext+]" name="cmdweblogin" /><br/>
</form>
<hr>
<!-- log out hyperlink section -->
<h2>Logged In</h2>
<a href='[+action+]'>[+logouttext+] [+username+]</a>

";s:6:"locked";s:1:"0";}'
NOWDOC;
?>