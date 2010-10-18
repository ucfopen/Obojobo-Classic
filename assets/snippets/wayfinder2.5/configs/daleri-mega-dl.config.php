<?php
	$level = 2;
	
	$innerRowTpl = '@CODE:<dd[+wf.classes+]>
    	<a href="[+wf.link+]" title="[+wf.title+]">[+wf.linktext+]</a>
    </dd>
    [+wf.wrapper+]';
    
	$parentRowTpl = '@CODE:<dl class="nav">
        <dt>
    	<a href="[+wf.link+]" title="[+wf.title+]">[+wf.linktext+]</a>
        </dt>
        [+wf.wrapper+]
    </dl>';
    
	$outerTpl = '@CODE:[+wf.wrapper+]';
	
	$rowTpl = '@CODE:<dl class="nav">
    <dt[+wf.classes+]>
    	<a href="[+wf.link+]" title="[+wf.title+]">[+wf.linktext+]</a>
    </dt>
    [+wf.wrapper+]
    </dl>';
?>