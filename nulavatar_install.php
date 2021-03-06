<?php
<?php
/***************************************************************************
 *                            nulavatar_update.php
 *                           -----------------------
 *		                Update file
 *
 *		NulAvatar made and (c) by Guido "Nuladion" Kessels
 ***************************************************************************/

define('IN_PHPBB', true);
$phpbb_root_path='./';
include($phpbb_root_path.'extension.inc');
include($phpbb_root_path.'common.'.$phpEx);

//
// Start session management
//
$userdata = session_pagestart($user_ip, PAGE_INDEX);
init_userprefs($userdata);
//
// End session management
//

$file = "nulavatar_install.php";
$na_version = "1.3.1";

if( !$userdata['session_logged_in'] )
{
	header('Location: ' . append_sid("login.$phpEx?redirect=".$file, true));
}

if( $userdata['user_level'] != ADMIN )
{
	message_die(GENERAL_MESSAGE, $lang['Not_Authorised']);
}

if( !strstr($dbms, "mysql") )
{
    if( !isset($_GET['bypass']) )
    {
        $message = 'This mod has only been tested on MySQL and may only work on MySQL.<br />';
        $message .= 'Click <a href="'.$file.'?bypass=true">here</a> to install anyways.';
        message_die(GENERAL_MESSAGE, $message);
    }
}

if($_GET['gdcheck'] != "ok")
{
$info = gd_info(); 
$keys = array_keys($info); 
$values = array_values($info); 

$checked = false;

echo "<b>NulAvatar ".$na_version." GDLibrary check!</b> <br />";
echo "<br />";

if(!$values[0]) {
	echo("
		You don't seem to have the <b>GD Library</b> installed on your server! <br />
		The GD Library <i>is</i> required to use NulAvatar!<br />
		<br />
		Click <a href=\"".$file."?gdcheck=ok\">here</a> if you want to update NulAvatar anyway (not recommended)
	");
	$checked = true;
}
if(!$values[7]) {
	if(!$values[4]) {
	echo("
		You have the GD Library installed, but don't seem to have <b>PNG Support</b> enabled on your server! <br />
		The GD Library with PNG Support <i>is</i> required to use NulAvatar!<br />
		<br />
		Click <a href=\"".$file."?gdcheck=ok\">here</a> if you want to update NulAvatar anyway (not recommended)
	");
	$checked = true;
	}
	else {
	echo("
		You have the GD Library installed, but don't seem to have <b>PNG Support</b> enabled on your server! <br />
		However, you seem to have GIF Read Support enabled!<br />
		Make sure you set the NulAvatar 'Imagetype' to 'GIF' in the Settings Admin panel, and use GIF images instead of PNG!
		<br />
		Click <a href=\"".$file."?gdcheck=ok\">here</a> to update NulAvatar
	");
	$checked = true;
	}
}
if(($values[4]) && ($values[7])) {
	echo("
		You have the GD Library installed with PNG Support and GIF Read Support enabled!<br />
		This means you can use both PNG and GIF images with NulAvatar! <br />
		Make sure to correctly change the NulAvatar 'Imagetype' in the Settings Admin panel if you switch from one image format to another! <br />
		<br />
		Click <a href=\"".$file."?gdcheck=ok\">here</a> to update NulAvatar
	");
	$checked = true;
}

if($checked == false)
{
	echo("
		The script failed to check your GD Library status...<br />
		If you think you have the GD Library installed with 'PNG Support' and/or 'GIF Read Support' enabled,
		you can still install NulAvatar. If you're not sure, I recommend you contact your host first! <br />
		<br />
		Click <a href=\"".$file."?gdcheck=ok\">here</a> to update NulAvatar
	");
}

}
else
{

echo "<html>\n";
echo "<body>\n";

$sql = array();
$dat = array();

echo "<b>NulAvatar ".$na_version." Installer!</b> <br /><br />";

// Check if xxx_nulmods already exist!
echo "Check if ".$table_prefix."nulmods table exists...";
$check = mysql_query("SELECT * FROM ".$table_prefix."nulmods LIMIT 0,1");
if($check) { 
	// Table exists! -- Check if NulAvatar entry exists yet!
	echo "<b><font color=\"007700\">YES</font></b><br />\n";
	echo "Check for NulAvatar entry...";
	$check2 = mysql_query("SELECT * FROM ".$table_prefix."nulmods WHERE title='NulAvatar' LIMIT 0,1");
	$count2= mysql_num_rows($check2);
	if($count2 > 0) { 
		// Found! -- Update NA version number!
		echo "<b>found!</b> ";
		$dat[] = "Updating version number";
		$sql[] = "UPDATE `".$table_prefix."nulmods` SET version = '".$na_version."' WHERE title = 'NulAvatar'";
	} else {
		// Not found! -- Insert NulAvatar to nulmods table!
		echo "<b>not found!</b> ";
		$dat[] = "Inserting NulAvatar info";
		$sql[] = "INSERT INTO `".$table_prefix."nulmods` (title,version) VALUES ('NulAvatar','".$na_version."')";
	}
}
else {
	// Table doesn't exist! -- Create it and add NulAvatar info!
	echo "<b><font color=\"orange\">NO</font></b><br />\n";
	$dat[] = "Creating ".$table_prefix."nulmods table";
	$sql[] = "CREATE TABLE `".$table_prefix."nulmods` (
	  `id` int(10) unsigned NOT NULL auto_increment,
	  `title` MEDIUMTEXT NOT NULL,
	  `version` MEDIUMTEXT NOT NULL,
	  PRIMARY KEY  (`id`)
	)";
	
	$dat[] = "Inserting NulAvatar info";
	$sql[] = "INSERT INTO `".$table_prefix."nulmods` (title,version) VALUES ('NulAvatar','".$na_version."')";
}


$sql[] = "CREATE TABLE `".$table_prefix."nulavatar_settings` (
  `config_id` int(10) unsigned NOT NULL auto_increment,
  `config_name` MEDIUMTEXT NOT NULL,
  `config_value` MEDIUMTEXT NOT NULL,
  `config_type` MEDIUMTEXT NOT NULL,
  PRIMARY KEY  (`config_id`)
)";
$sql[] = "INSERT INTO `".$table_prefix."nulavatar_settings` (config_name,config_value,config_type) VALUES ('avatars','','header')";
$sql[] = "INSERT INTO `".$table_prefix."nulavatar_settings` (config_name,config_value,config_type) VALUES ('avatar_height','48','avatars')";
$sql[] = "INSERT INTO `".$table_prefix."nulavatar_settings` (config_name,config_value,config_type) VALUES ('avatar_width','48','avatars')";
$sql[] = "ALTER TABLE `".$table_prefix."nulavatar_settings` ADD config_isradio TINYINT(1) NOT NULL DEFAULT '0' ";
$sql[] = "ALTER TABLE `".$table_prefix."nulavatar_settings` ADD config_radio_choices MEDIUMTEXT NOT NULL DEFAULT '' ";
$sql[] = "INSERT INTO `".$table_prefix."nulavatar_settings` (config_name,config_value,config_type,config_isradio,config_radio_choices) VALUES ('rpgmod','','header','','')";
$sql[] = "INSERT INTO `".$table_prefix."nulavatar_settings` (config_name,config_value,config_type,config_isradio,config_radio_choices) VALUES ('rpgsystem','moogies','rpgmod','1','moogies,shop3,adr')";
$sql[] = "INSERT INTO `".$table_prefix."nulavatar_settings` (config_name,config_value,config_type,config_isradio,config_radio_choices) VALUES ('imagetype','png','avatars','1','png,gif')";

$sql[] = "CREATE TABLE `".$table_prefix."nulavatar_images` (
	  `id` int(10) unsigned NOT NULL auto_increment,
	  `name` mediumtext NOT NULL,
	  `image` mediumtext NOT NULL,
	  `layer` int(10) NOT NULL default '0',
	  `itemneeded` mediumtext NOT NULL,
	  `dontshowlayer` int(10) NOT NULL default '0',
	  PRIMARY KEY  (`id`)
)";

$sql[] = "CREATE TABLE `".$table_prefix."nulavatar_layers` (
	  `id` int(10) unsigned NOT NULL auto_increment,
	  `name` mediumtext NOT NULL,
	  `position` tinyint(3) NOT NULL default '0',
	  `compulsive` tinyint(1) NOT NULL default '0',
	  PRIMARY KEY  (`id`)
)";

$sql[] = "CREATE TABLE `".$table_prefix."nulavatar_userchars` (
	  `id` int(10) unsigned NOT NULL auto_increment,
	  `user` int(10) unsigned NOT NULL default '0',
	  PRIMARY KEY  (`id`)
)";

$dat[] = 'Creating table "nulavatar_settings"';
$dat[] = 'Updating Settings table';
$dat[] = 'Updating Settings table';
$dat[] = 'Updating Settings table';
$dat[] = 'Altering Settings table';
$dat[] = 'Altering Settings table';
$dat[] = 'Updating Settings table';
$dat[] = 'Updating Settings table';
$dat[] = 'Updating Settings table';
$dat[] = 'Creating "nulavatar_layers" table';
$dat[] = 'Creating "nulavatar_images" table';
$dat[] = 'Creating "nulavatar_userchars" table';
$dat[] = 'Updating Settings table';
$sql[] = "INSERT INTO `".$table_prefix."nulavatar_settings` (config_name,config_value,config_type,config_isradio,config_radio_choices) VALUES ('generalconfig','','header','','')";
$dat[] = "Updating Settings table";
$sql[] = "INSERT INTO `".$table_prefix."nulavatar_settings` (config_name,config_value,config_type,config_isradio,config_radio_choices) VALUES ('display','1','generalconfig','1','0,1')";
$dat[] = "Updating Settings table";
$sql[] = "INSERT INTO `".$table_prefix."nulavatar_settings` (config_name,config_value,config_type,config_isradio,config_radio_choices) VALUES ('eamountofitems','5','generalconfig','0','')";
$dat[] = "Updating Settings table";
$sql[] = "INSERT INTO `".$table_prefix."nulavatar_settings` (config_name,config_value,config_type,config_isradio,config_radio_choices) VALUES ('replaceavatar','0','generalconfig','1','0,1,2')";

$sql_count = count($sql);

for($i = 0; $i < $sql_count; $i++) {
	echo "" . $dat[$i];
	flush();

	if ( !$db->sql_query($sql[$i]) )
	{
		$errored = true;
		$error = $db->sql_error();
		echo "... <b><font color=\"FF0000\">FAILED</font></b><BR />Error Message:<i>" . $error['message'] . "</i><br />\n";
	}
	else
	{
		echo "... <b><font color=\"007700\">COMPLETED</font></b><br />\n";
	}
}

echo "<br />\n<br />\n";

// Do CHMOD
$filename = $phpbb_root_path.'images/nulavatar_avatars/';
echo "Checking if NulAvatar is able to write to <b>".$filename."</b>... ";
if (file_exists($filename))
{
	if (!is_writable($filename))
	{
		echo "<font color=\"orange\"><b>NO</b></font><br />\n Attempting to CHMOD <b>".$filename."</b> to 0777...";
		if (!chmod($filename, 0777))
		{
			echo "<br />Could not CHMOD <b>".$filename."</b> to 0777! Please do it manually! (required for NulAvatar to save your avatars!)<br />\n";
		}
		else
		{
			echo "The folder <b>".$filename."</b> was succesfully CHMODded to 0777!<br />\n";
		}
	}
	else
	{
		echo "<font color=\"007700\"><b>YES</b></font><br />\n";
	}
}
else
{
	echo "<font color=\"FF0000\"><b>NO</b></font><br />\n Make sure the folder exists!<br />\n";
}

echo "<br />\n<br />\n";

if( $errored ) {
    $message = "The installation was <b>not</b> successful. Please try again!<br />If the problem persists, please post in the NulAvatar thread!";
}
else {
    $message = "<b>NulAvatar has been installed succesfully!</b><br />Make sure to delete this install file!<br /><b>Enjoy!</b>";
}

echo "\n<br />\n<b>Finished!</b><br />\n";
echo $message . "<br />\n";
echo "</body>\n";
echo "</html>\n";
exit();

// End GD Check
}

?>