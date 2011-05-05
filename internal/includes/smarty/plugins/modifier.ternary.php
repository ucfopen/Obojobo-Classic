<?php
function smarty_modifier_ternary($value,$option1,$option2)
{
    return ($value)?$option1:$option2;
}
?>