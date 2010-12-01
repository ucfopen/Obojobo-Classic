<?php
$ASE_timestamp = '1291228266';
$ASE_time = 'December 1, 2010, 1:31 pm';
$ASE_savedby = 'obo,,iturgeon,127.0.0.1';
$ASE_chunk_raw = <<<'NOWDOC'
a:8:{s:2:"id";s:1:"8";s:4:"name";s:16:"ajaxSearchLayout";s:11:"description";s:0:"";s:11:"editor_type";s:1:"0";s:8:"category";s:1:"2";s:10:"cache_type";s:1:"0";s:7:"snippet";s:639:"[+as.showForm:is=`1`:then=`
<form [+as.formId+] action="[+as.formAction+]" method="post">
	<input type="hidden" name="advSearch" value="[+as.advSearch+]" />	
	<label for="ajaxSearch_input">
		<input id="ajaxSearch_input" class="cleardefault" type="text" name="search" value="[+as.inputValue+]"[+as.inputOptions+] />
	</label>
	<label for="ajaxSearch_submit">
		<input id="ajaxSearch_submit" type="submit" name="sub" value="[+as.submitText+]" />
	</label>
</form>
`+]
[+as.showIntro:is=`1`:then=`
<p class="ajaxSearch_intro" id="ajaxSearch_intro">[+as.introMessage+]</p>
`+]
[+as.showResults:is=`1`:then=`
[+as.results+]
`+]";s:6:"locked";s:1:"0";}'
NOWDOC;
?>