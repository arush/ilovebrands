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


$timestamp = gmdate("YmdHis");
$file = basename(__file__) . ".{$timestamp}.sql";
//===========================================
echo '<h1>Database Backup</h1>';

echo '<pre>';
$dom= simplexml_load_file(realpath(dirname(__FILE__). '/app/etc/local.xml'));
$domat_host = $dom->xpath('//global//connection');
print_r($domat_host[0]);
echo '</pre>';

$host = (string)$domat_host[0]->host;
$username = (string)$domat_host[0]->username;
$password = (string)$domat_host[0]->password;
$dbname = (string)$domat_host[0]->dbname;

if( file_exists($host) ) {
$cmd = "mysqldump --socket $host --user=$username --password=$password $dbname > $file";
} else {
$cmd = "mysqldump --host $host --user=$username --password=$password $dbname > $file";
}

run_cmd($cmd);

echo "<h3>Tail of database file</h3>";
run_cmd("tail ./$file.sql");

echo "<h3>Done</h3>";