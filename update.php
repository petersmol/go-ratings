<?
require ('include/rating_functions.php');
require ('include/player_db.php');

if ($_REQUEST['id']) 
	$where="id=".sqlesc($_REQUEST['id']); # Если указан id, обновляем только его
else
	$where="TO_DAYS(NOW())>TO_DAYS(lastupdate) OR lastupdate=0"; # Иначе обновляем все старые
$res=mysql_query("SELECT * FROM players WHERE $where ORDER BY id LIMIT 10") or die (mysql_error);
while ($ID=mysql_fetch_assoc($res)){


	$id=$ID['id'];
	$kgs=get_rating_kgs($ID['kgs']);
	$dgs=get_rating_dgs($ID['dgs']);
	$rfg=get_rating_rfg($ID['rfg']);
	$egd=get_rating_egd($ID['egd']);
	$url=get_rfg_url($ID['rfg']);
	echo "update $id: kgs $kgs, dgs $dgs, rfg $rfg, egd $egd, rfg_url $url<br>\n";

	
	mysql_query("DELETE FROM ratings WHERE id='$id' AND TO_DAYS(date)=TO_DAYS(NOW())") or die ("delete ".mysql_error());
	mysql_query("INSERT INTO ratings VALUES ('$id', NOW(), '$kgs', '$dgs', '$rfg','$egd')") or die ("insert ".mysql_error());
	mysql_query("UPDATE players SET lastupdate=NOW(), rfg_url=".sqlesc($url)." WHERE id='$id'") or die ('update '.mysql_error);
	sleep (2);
}
?>
