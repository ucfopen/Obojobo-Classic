<?php

function printer($var, $RETURN=FALSE){
	$output ="<pre>".print_r($var, TRUE)."</pre>";
	if ($RETURN) return $output;
	else         echo $output;
}

?>
