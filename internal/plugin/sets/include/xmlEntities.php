<?php
function xmlEntities($string){
	$output = null;
	
	for($i = 0; $i < strlen($string); $i++){
		$ascii = ord($string{$i});
		if(($ascii >= 48 && $ascii <= 57) ||
			($ascii >= 65 && $ascii <= 90) ||
			($ascii >= 97 && $ascii <= 122)){
			$output .= $string{$i};
		}
		else{
			$output .= "&#{$ascii};";
		}
	}
	
	return $output;
}

?>