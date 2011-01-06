You recently completed the "{$loTitle}" learning object.  This email is simply a confirmation that we have recorded your score for the item shown below.

Name: {$loTitle}
{$loCourse|ternary: "Course: $loCourse":''}
Instructor: {$loInstructor}
Closes: {$loEnd|date_format:"%D %r"}
Attempts Remaining: {$attemptsRemaining}
URL: {$loLink}
Your Score: {$finalScore}%  (Using Your {if $loScoreMethod == 'm'}Average{elseif $loScoreMethod == 'h'}Highest{else}Latest{/if} Score)