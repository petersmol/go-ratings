<?
include_once('myfunc.php');
include_once('config.php');

function dbconn() {
    global $mysql_host, $mysql_user, $mysql_pass, $mysql_db;
    if (!mysql_connect($mysql_host, $mysql_user, $mysql_pass))
    {
          die('dbconn: mysql_connect: ' . mysql_error());
    }
    mysql_select_db($mysql_db)
        or die('dbconn: mysql_select_db: ' . mysql_error());
}

/* find_user($name) - берет идентификаторы пользователя из базы данных.

А поскольку базы данных у нас нет, используется локальный массив.
*/
function get_rating($name) {
	$res=mysql_query("SELECT * FROM ratings WHERE id=".sqlesc($name)." ORDER BY date DESC LIMIT 1");
	if (mysql_num_rows($res)==0){
		echo "No such user '$name'!<br>\n";
		return -1;
	}
	return mysql_fetch_assoc($res);
}

function find_user($name) {
	$res=mysql_query("SELECT * FROM players WHERE id=".sqlesc($name));
	if (mysql_num_rows($res)==0){
		echo "No such user '$name'!<br>\n";
		return -1;
	}
	return mysql_fetch_assoc($res);
}

function list_users() {
	global $player_DB;
 	foreach ($player_DB as $key => $value)
		$arr[]=$key;
	return $arr;
}

dbconn();

// Просто добавляйте юзеров сюда
$player_DB['petersmol']	=	array('EGD' => '15749250',	'KGS' => 'petersmol',		'DGS' => '24822', 'RFG' => 'Смолович Пётр');
$player_DB['myexe']			=	array('EGD' => '14825910',	'KGS' => 'mylogin',			'DGS' => '48307');
$player_DB['mmk']				=	array('EGD' => '15786749',	'KGS' => 'mmk',					'DGS' => '57582');
$player_DB['dietess']		=	array('EGD' => '15862792',	'KGS' => 'dietess',			'DGS' => '');
$player_DB['morriell'] 	=	array('EGD' => '15849790',	'KGS' => 'morriell',		'DGS' => '55263');
$player_DB['novaanto'] 	=	array('EGD' => '15725743',	'KGS' => 'novaanto',		'DGS' => '');
$player_DB['akinfeevk']	=	array('EGD' => '15662537',	'KGS' => 'akinfeevk',		'DGS' => '58022');



?>
