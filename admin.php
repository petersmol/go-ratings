<?
include ('include/player_db.php');

$action=$_REQUEST['action'];
$id=sqlesc($_REQUEST['id']);
$kgs=sqlesc($_REQUEST['kgs']);
$dgs=sqlesc($_REQUEST['dgs']);
$rfg=sqlesc($_REQUEST['rfg']);
$egd=sqlesc($_REQUEST['egd']);
$hidden=$_REQUEST['hidden'] ? 1:0;

if ($action=='new'){
	mysql_query ("INSERT INTO players VALUES ($id, $kgs, $dgs, $rfg, NULL, $egd, '0000-00-00 00:00:00',$hidden)") or die (mysql_error());
	Header ("Location: admin.php");
}
else if ($action=='del'){
	mysql_query ("DELETE FROM players WHERE id=$id") or die (mysql_error());
	Header ("Location: admin.php");
}
else if ($action=='save'){
	mysql_query ("DELETE FROM players WHERE id=$id") or die (mysql_error());
	mysql_query ("INSERT INTO players VALUES ($id, $kgs, $dgs, $rfg, NULL, $egd, '0000-00-00 00:00:00',$hidden)") or die (mysql_error());
	Header ("Location: admin.php");
}

$res=mysql_query("SELECT * FROM players ORDER BY id");

echo '<table><tr><td>ID</td><td>KGS</td><td>DGS</td><td>РФГ</td><td>EGD</td><td>Редактировать</td></tr>';
while ($row=mysql_fetch_assoc($res)){
	echo '<tr><td>'.$row["id"].'</td><td>'.$row["kgs"].'</td><td>'.$row["dgs"].'</td><td>'.$row["rfg"].'</td><td>'.$row["egd"].'</td>
	<td>
	<a href="admin.php?id='.$row["id"].'&action=edit">edit</a>
	<a href="admin.php?id='.$row["id"].'&action=del">del</a>
	</td>
	</tr>';
}
echo '</table>';

?>
<form method=get>
<?
if ($action=='edit'){
	echo '<input type=hidden name=action value=save>';
	$res=mysql_query("SELECT * FROM players WHERE id=$id")  or die (mysql_error());
	$row=mysql_fetch_assoc($res);
	$kgs=$row['kgs'];
	$dgs=$row['dgs'];
	$rfg=$row['rfg'];
	$egd=$row['egd'];
	$hidden=$row['hidden'];
	}
else {
	echo '<input type=hidden name=action value=new>';
	unset($rfg);
	}
?>
ID: <input type=text name=id value=<?= $id ?> > 
<input type=checkbox name=hidden value='yes' <?= $hidden ? 'checked' : '' ?> > скрытый<br>
KGS: <input type=text name=kgs value=<?= $kgs ?> > 
DGS: <input type=text name=dgs value=<?= $dgs ?> > 
РФГ: <input type=text name=rfg value="<?= $rfg ?>" > 
EGD: <input type=text name=egd value=<?= $egd ?> ><br>
<input type=submit value='Save'>
</form>
