<?php

class Suggestion
{
	private $data = array();
	
	public function __construct($arr = array())
	{
		if(!empty($arr)){
			
			// The $arr array is passed only when we manually
			// create an object of this class in ajax.php
			
			$this->data = $arr;
		}
	}
	
	public function __get($property){
		
		// This is a magic method that is called if we
		// access a property that does not exist.
		
		if(array_key_exists($property,$this->data)){
			return $this->data[$property];
		}
		
		return NULL;
	}
	
	public function __toString()
	{
		// This is a magic method which is called when
		// converting the object to string:
		
		return '
		<li id="s'.$this->id.'">
			<div class="vote '.($this->have_voted ? 'inactive' : 'active').'">
				<span class="up">Vote Up</span>
				<span class="down">Vote Down</span>
			</div>
			<div class="id">'.$this->id.'</div>
			<div class="text">'.$this->suggestion.'</div>
			<div class="rating">'.(int)$this->rating.'</div>
		</li>';
	}
}

?>