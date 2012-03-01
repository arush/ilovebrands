<?php 
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
ob_implicit_flush();

function run_cmd($cmd) { 
  echo "<pre>";
  echo "PWD  : ";
  system('pwd'); 
  echo "USER : " . get_current_user() ."\n";
  echo "EXEC : $cmd \n\n";

  // force out buffer flush
  for($i=0; $i < 10000; $i++) {
    echo ' ';
  }

  system($cmd.' 2>&1'); //2>&1 to see errors in linux
  echo "</pre>";
}


echo "<h3>Backup Filesystem</h3>";

$timestamp = gmdate("YmdHis");
$file = basename(__file__) . ".$timestamp";
$cmd = "tar -czvf ./$file.tgz --exclude=./$file.tgz --exclude=./var/cache ./";
run_cmd($cmd);

echo "<h3>Done</h3>";