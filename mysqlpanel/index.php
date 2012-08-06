<?php
error_reporting(0);
if (!isset($_POST['cnx']) && $_SERVER['SERVER_NAME']!='www.wyomind.com') {
    $mageFilename = '../app/Mage.php';
    require_once $mageFilename;
    Mage::app();
    $cnx['host'] = (string) Mage::getConfig()->getNode('global/resources/default_setup/connection/host');
    $cnx['user'] = (string) Mage::getConfig()->getNode('global/resources/default_setup/connection/username');
    $cnx['pwd'] = (string) Mage::getConfig()->getNode('global/resources/default_setup/connection/password');
    $cnx['db'] = (string) Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname');
    $cnx['prefix'] = (string) Mage::getConfig()->getNode('global/resources/default_setup/db/table_prefix');
}
else
    foreach ($_POST as $key => $value)
        $$key = $value;
?>

<html>
    
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title>mySqlPanel</title>
    </head>
    <body>
        <form action='index.php' method="POST">
            <fieldset>
                <legend>
                    Connexion
                </legend>
                <label>Host </label><input name="cnx[host]" type='text' value="<?php echo $cnx['host']; ?>"/>
                <label>User </label><input name="cnx[user]" type='text' value="<?php echo $cnx['user']; ?>"/>
                <label>Password </label><input name="cnx[pwd]" type='text' value="<?php echo $cnx['pwd']; ?>"/>
                <label>Database</label><input name="cnx[db]" type='text' value="<?php echo $cnx['db']; ?>"/> 
                <label>Prefix</label><input type='text' disabled value="<?php echo $cnx['prefix']; ?>"/>
                <?php
                if (count($cnx) == 4 ) {
                    $connexion = mysql_connect($cnx['host'], $cnx['user'], $cnx['pwd']) or print("<div class='error'>Erreur de connexion au serveur [" . mysql_error() . "]</div>");
                    mysql_select_db($cnx['db'], $connexion) or print("<div class='error'>Erreur de connexion à la bdd [" . mysql_error() . "]</div>");
                }
                else
                    print("<div class='error'>Paramètres de connexion invalides.</div>");
                ?>
            </fieldset>
            <fieldset>
                <legend>
                    Request
                </legend>
                <textarea style="width:100%;resize:none;height:400px;" name="sql"><?php echo stripslashes(@$sql); ?></textarea>

            </fieldset>
            <input type="submit" value="Go !" style='width:100%;margin-top:5px'/>
            <fieldset>
                <legend>
                    Results
                </legend>
                <?php
                if (isset($sql)) {
                    echo "<table>";
                    if($result = mysql_query(stripslashes($sql))){
                    
                        $first=true;
                        if(@mysql_fetch_assoc($result)){
							$result = mysql_query(stripslashes($sql));
                            while (($line = mysql_fetch_assoc($result)) !== FALSE) {
                                if ($first) {
                                    echo "<tr>";
                                    foreach ($line as $field => $value) {
                                        echo "<th>" . $field . "</th>";
                                    }
                                    echo "</tr>";
                                    $first=false;
                                }
                                echo "<tr>";
                                foreach ($line as $field => $value) {
                                    echo "<td title='".$value."'>" . substr($value,0,50) . "</td>";
                                }
                                echo "</tr>";
                            }
                            echo "</table>";
                        }
                        else  print("<div class='success'>Requête exécutée avec succés.</div>");
                        mysql_close();
                    } else print("<div class='error'>Erreur de requête SQL [" . mysql_error() . "]</div>");
                }
                ?>
            </fieldset>

        </form>
    </body>

</html>

