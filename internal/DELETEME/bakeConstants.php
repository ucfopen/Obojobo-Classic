<?php
require_once(dirname(__FILE__)."/../app.php");



$classes = getDirectory(AppCfg::DIR_BASE . AppCfg::DIR_CLASSES);
$configs = getDirectory(CONFIG_ROOT);
$plugins = getDirectory(AppCfg::DIR_BASE . AppCfg::DIR_PLUGIN);

// parallel arrays
$const = array();
$constV = array();

// locate the constants


echo "<pre>";
foreach($configs AS $file)
{
	
	$contents = file_get_contents($file);
	if(preg_match( '/class\s?(\w+)\s?[\r\n]/', $contents, $matches) > 0)
	{
		$class = $matches[1];
		
		preg_match_all( '/const\s+(\w+)\s?=\s?(.+?)\s?;/', $contents, $matches, PREG_SET_ORDER);
		
		foreach($matches AS $match)
		{
			$const[] = $class."::".$match[1];
			$constV[] = $match[2];

		}
	}

}

echo count($const) . " Constants Defined \n";
echo "replacing uses...\n";
flush();
foreach($classes AS $file)
{
	
	$contents = file_get_contents($file);
	$i = count($const);
	while($i--)
	{
		$contents = str_replace ($const[$i] , $constV[$i] , $contents);
	}
	$fh = fopen($file.'.baked.php', 'w') or die("can't open file");
	fwrite($fh, $contents);
	fclose($fh);
	echo "replacing in $file...\n";
	flush();
	
}

















function getDirectory( $path = '.', $level = 0 ){ 

$scripts = array();

   $ignore = array( 'cgi-bin', '.', '..', '.cache', '.DS_Store', '.settings', '.svn'); 
   // Directories to ignore when listing output. Many hosts 
   // will deny PHP access to the cgi-bin. 

   $dh = @opendir( $path ); 
   // Open the directory to the handle $dh 
    
   while( false !== ( $file = readdir( $dh ) ) ){ 
   // Loop through the directory 
    
       if( !in_array( $file, $ignore ) ){ 
            
           $spaces = str_repeat( '&nbsp;', ( $level * 4 ) ); 
           if( is_dir( "$path/$file" ) ){              
               $scripts = array_merge($scripts , getDirectory( "$path/$file", ($level+1) ) ); 
            
           } else { 
            	if(substr($file, -10) == '.class.php' || $file == 'config.php')
			{
				$scripts[] = $path. '/' .$file;
			}
            
           } 
        
       } 
    
   } 
    
   closedir( $dh ); 
return $scripts;
}

?>