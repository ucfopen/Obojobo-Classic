<html><body><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
  "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<body style="font-size:12px;font-family:arial;" leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" bgcolor='#DDD' >

<table width="100%" cellpadding="10" cellspacing="0"  bgcolor='#DDD' >
<style type=text/css>
td { font-size:12px; color:#000000; font-family:arial; } a { color:#FF0000;}
</style>
<tr>
<td valign="top" align="center">

<table width="550" cellpadding="0" cellspacing="0">
<tr>
<td style="background-color:#FF4647;border-top:0px solid #000000;border-bottom:0px solid #FFF;text-align:right;" align="center"></td>
</tr>

<tr>
<td style="background-color:#FFFFFF;border-top:0px solid #FFFFFF;border-bottom:0px solid #333333;font-size:15px;font-weight:bold;"><img src="{$imgDir}score-confirm-header.png" width="550" height="57" alt="Learning Object Score Confirmation"></td>
</tr>

{* -------------- FINAL SCORE -----------------  *}

<tr>
<td style="background-color:#5c8695;color:#FFF;padding:10px;">
	<table border="0" cellspacing="0" cellpadding="0" >
		<tr>
			<td align="center" style="color:#2f2f2f;font-size:23px;font-weight:bold;width:195px;background-color:#d5e0e4;color:#FFF;">
				<center>
				<span align="center" style="color:#2f2f2f;font-size:23px;font-weight:bold;">Recorded Score</span><br>
				<span style="color:#2f2f2f;font-size:12px;">(Using Your {if $loScoreMethod == 'm'}Average{elseif $loScoreMethod == 'h'}Highest{else}Latest{/if} Score)</span><br>
				<span align="center" style="font-family:arial;color:#4f1919;font-weight:bold;font-size:72px;padding-bottom:10px;">{$finalScore}%</span>
				</center>
			</td>
			<td align="left" style="color:#FFF;padding:10px;font-size:15px;line-height:120%;">
				You recently completed the <b>"{$loTitle}"</b> learning object.  This email is simply a confirmation that we have recorded your score for the item shown below.
			</td>
		</tr>
	</table>
</td>
</tr>
</table>

{* -------------- LEARNING OBJECT DETAILS  -----------------  *}
<table width="550" cellpadding="20" cellspacing="0" bgcolor="#FFFFFF">
<tr>
<td bgcolor="#fff5eb" align="center" style="font-size:12px;color:#000000;">
<span style="font-size:22px;font-weight:bold;color:#6b1919;font-family:arial;text-transform:uppercase;">About The Learning Object</span><br>
<table align="center" border="0" cellspacing="0" cellpadding="0" style="">
	<tr>
		<td align="right" style="">
			<a href="{$loLink}"><img src="{$imgDir}score-confirm-icon.png"  width="81" height="61" alt="View Object"></a>
		</td>
		<td align="left" style="color:#2f2f31;padding:10px;font-size:12px;">
			<ul style="list-style-type:none;padding-left:0px;">
				<li><b>Name</b>: {$loTitle}</li>
				{$loCourse|ternary: "<li><b>Course</b>: $loCourse </li>":''}
				<li><b>Instructor</b>: {$loInstructor}</li>
				<li style="font-size:15px;"><b>Closes</b>: {$loEnd|date_format:"%D %r"}</li>
			</ul>
		</td>
	</tr>
</table>
{* -------------- ATTEMPTS REMAINING START NOW BUTTON -----------------  *}
{if $attemptsRemaining > 0}
<table align="center" border="0" cellspacing="0" cellpadding="0" style="background-color:#FF4748;margin-top:10px;">
	<tr >
		<td align="center" style="color:#FFF;font-size:17px;padding:10px;">
			You have {$attemptsRemaining} attempt{if $attemptsRemaining > 1}s{/if} left:
		</td>
		<td style="">
			<a href="{$loLink}"><img src="{$imgDir}score-confirm-start-now.png" width="206" height="54" alt="Start Now"></a>
		</td>
	</tr>
</table>
<br>
<br>
{/if}

{* -------------- ATTEMPT DETAILS  -----------------  *}
{if $attempts|@count > 0}
<table align="center" border="0" cellspacing="0" cellpadding="0" style="background-color:#FFF;margin-top:40px;">
	<tr >
		<td colspan="2" style="background-color:#f2c191;font-size:15px;font-weight:bold;"><img src="{$imgDir}score-confirm-attempt-history.png" width="357" height="54" alt="Your Attempt History"></td>
	</tr>
	{foreach $attempts as $attempt}
	<tr style="padding:10px;">
		<td align="center" style="padding:10px;">
				<span style="background-color:#f3efeb;display:block;padding:7px;width:60px">
					<span align="center" style="font-size:12px;font-weight:bold;">Attempt</span>
					<span align="center" style="font-family:arial;font-weight:bold;font-size:40px;">{$attempt@total-$attempt@index}</span>
				</span>
		</td>
		<td align="left" style="padding:10px;">
				<span align="center" style="font-size:22px;">Score: <b>{$attempt['score']}%</b></span><br>
				<span align="center" style="font-size:15px;">{$attempt['submitDate']|date_format:"%D %r"}</span>
		</td>
		<td colspan="2" style=""></td>
	</tr>
	{/foreach}
	
</table>
{/if}

</td>
</tr>

</table>
<img src="{$imgDir}score-confirm-footer.png" width="550" height="63" alt="Copyright University of Central Florida">

</td>
</tr>

</table>

</body>
</html>