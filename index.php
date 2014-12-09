<html>
<head>
<title>Сай смотрит на тебя</title>
<style type="text/css" > </style>
<link rel="stylesheet" type="text/css" href="/css/main.css?<?= rand(); ?>" />
</head>
<body>
<div class='player_list'>
<table class='player_list'>
<tr><th>Имя</th><th>KGS</th><th><font color='red'>РФГ</font> / <font color='#aaaaaa'>EGD</font></th><th><font color='#a19b00'>DGS</font></th></tr>
<?
require ('include/rating_functions.php');
require ('include/player_db.php');

$showuser=$_REQUEST['id'];

$players=get_players_list();

foreach ($players as $id => $p){
		if ($id==$showuser) {
				$user=$p['name']; 
				$tr_class='current_user'; 
		}
		else {
				$user="<a href='/$id'>".$p['name']."</a>";
				$tr_class='another_user'; 
		}
	$razryad=elo2razryad($p['rfg']);
	if ($razryad != $prevous_razryad)
			echo "<tr class='delimiter'><td style='border-top: 1px dotted black' colspan=4>$razryad</td></tr>";
		
	echo "<tr class='$tr_class'>
			<td>
				$user
			</td>
			<td class='".$p['kgs_class']."'>
				<a title='".$p['kgs']."'>".$p['kgs_kyu']."</a>
			</td>
			<td class='".$p['rfg_class']." nobr'>
				<a title='".$p['rfg']."'>".$p['rfg_kyu']."</a> <font color='#aaaaaa'>/
				<a title='".$p['egd']."'>".$p['egd_kyu']."</a></font>
			</td>
			<td class='".$p['dgs_class']."'>
				<a title='".$p['dgs']."'>".$p['dgs_kyu']."</a>
			</td>
		</tr>";
	$prevous_razryad=$razryad;
}
?>
</table>
</div>
<div class='player_info'> 
<?

if ($showuser){
$res=mysql_query("SELECT * FROM players WHERE id=".sqlesc($showuser)) or die (mysql_error());
$show=mysql_fetch_assoc($res) or die ("Такого игрока нет в базе.");
$res2=mysql_query("SELECT * FROM ratings WHERE id=".sqlesc($showuser)." ORDER BY date DESC LIMIT 1") or die (mysql_error());
$rating=mysql_fetch_assoc($res2);
?>
<b><?= $show['rfg'] ?></b><br><br>

<img src='/<?= $showuser ?>/graph'>
<br/>

<table>
	<tr>
		<td>Рейтинг <a href='http://www.gokgs.com/graphPage.jsp?user=<?= $show['kgs'] ?>'>KGS</a>:</td>
		<td>
			<?= elo2kyu($rating['kgs']) ?>
			(<a href='http://www.gokgs.com/gameArchives.jsp?user=<?= $show['kgs'] ?>'>партии</a>)
		</td>
	</tr><tr>
		<td>Рейтинг <a href='http://gofederation.ru<?= $show['rfg_url'] ?>'>РФГ</a>:</td>
		<td>
			<?= $rating['rfg']. " (".elo2kyu($rating['rfg']).")" ?>
		</td>
	</tr><tr>
		<td>Рейтинг <a href='http://www.europeangodatabase.eu/EGD/Player_Card.php?key=<?= $show['egd'] ?>'>EGD</a>:</td>
		<td>
			<?= $rating['egd']." (".elo2kyu($rating['egd']+50).")" ?>
		</td>
	</tr><tr>
		<td>Рейтинг <a href='http://www.dragongoserver.net/ratinggraph.php?uid=<?= $show['dgs'] ?>'>DGS</a>:</td>
		<td>
			<? 
					$percent=$rating['dgs']%100-50;
					if ($percent>=0) $percent="+$percent";
					if ($rating['dgs']) print elo2kyu($rating['dgs'])." ($percent%)"; 
			?> 
		</td>
	</tr>
</table>
<p/>
<p/>
<p/>
Картинка в подпись:<br>
<? if ($rating['rfg']>=1800) $style='/triangle';  ?>
<img src='/<?= $showuser ?>/infobox<?= $style ?>'>
<?	

}

?>
</div>
</body>
</html>
