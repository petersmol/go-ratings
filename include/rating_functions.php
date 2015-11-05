<?
require_once('get_rating_kgs.php');

$SYSTEMS=Array('kgs','rfg','dgs','egd');

function get_rating_dgs($id){
	if (empty($id)) return '';

	 	$curl = curl_init() or die ("CURL failed"); 
		curl_setopt($curl,CURLOPT_URL,'http://www.dragongoserver.net/login.php?userid=guest&passwd=guestpass');
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_COOKIEJAR, '/tmp'.'/.cookie');
		curl_setopt($curl,CURLOPT_COOKIEFILE, '/tmp'.'/.cookie');
		$out = curl_exec($curl);
		curl_close($curl);

	 	$curl = curl_init() or die ("CURL failed"); 
		curl_setopt($curl,CURLOPT_URL,'http://www.dragongoserver.net/userinfo.php?uid='.$id);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl,CURLOPT_COOKIEJAR, '/tmp'.'/.cookie');
		curl_setopt($curl,CURLOPT_COOKIEFILE, '/tmp'.'/.cookie');
		$out = curl_exec($curl);
	/* dgs
	Рейтинг доступен только зарегистрированным пользователям, поэтому используется гостевая кука */

	if (preg_match("/([0-9]+)&nbsp;(kyu|dan)&nbsp;\((.*)%\)/", $out, $matches)){
			if ($matches[2]=='kyu')
				$return=$matches[1]."k";
			else
				$return=$matches[1]."d";
			return kyu2elo($return) + $matches[3];
		}
	else {
		#die ("Cannot parse DGS rating!<br>\n".$out);
	}

			return ''; 
}

function get_rating_rfg($id){
	$cachefile='/tmp/rfg.cache';

	if (empty($id)) return '';
	
	# Если нужно, скачиваем новую версию рейтинг-листа
	if (time()-filemtime($cachefile)>3600){
        $remote=fopen('http://gofederation.ru/players/',"r");
		$local=fopen($cachefile, 'w') or die ("Can't open local cache for writing");
		while ($str=fgets($remote)){
			fputs($local, $str);
		}
		fclose($remote);
		fclose($local);
	}

	$fp=fopen($cachefile,'r') or die ("Can't open local cache for reading");
	
	while ($str=fgets($fp)){
        if (preg_match('/'.$id.'<\/a><\/td><td>([^<>]*)<\/td><td>([0-9]+)<\/td>/', $str,$matches)){
			$return=$matches[2];
        }
	}
	
	return $return;
}

function get_rating_egd($id){
	if (empty($id)) return '';
	$fp=fopen("http://www.europeangodatabase.eu/EGD/Player_Card.php?key=".$id,"r");
	while ($str=fgets($fp)){
		if (preg_match('/value="(\d+) \(\d+(k|d)\)"/', $str,$matches))
			$return=$matches[1];
	}
	fclose($fp);
	return $return;
}

function kyu2elo($str){
	if (preg_match('/^([0-9]+)(k|d)/',$str,$matches)){
			if ($matches[2]=='d')
				return 2050 + 100*$matches[1];
			else 
				return 2150 - 100*$matches[1];
	}
	return -1;
}

function elo2kyu($n, $system='kgs'){
    if ($system=='egd'){
        $n+=50;
    }else if ($system=='rfg' and $n<100){ // У рфг шкала в диапазоне 99-0 растянута на 20-30к
        $n = ($n-90)*10;
    }

	if ($n==0)
		return '';
	if ($n>=2100)
		return floor($n/100 - 20).'d';
	else{ 
		return ceil (21 - $n/100).'k';	
    }
}

function elo2razryad($n){
	$EVSK['2350']='МС';
	$EVSK['2150']='КМС';
	$EVSK['1800']='1 разряд';
	$EVSK['1400']='2 разряд';
	$EVSK['1000']='3 разряд';
	$EVSK['900']='1 юношеский';
	$EVSK['600']='2 юношеский';
	$EVSK['300']='3 юношеский';
	$EVSK['0']='';
	
	foreach ($EVSK as $key => $value){
		if ($n>=$key)
			return $value;
	}
}

function get_players_list(){
	global $SYSTEMS;

	if ($_REQUEST['hidden'])
		$res=mysql_query("SELECT * FROM players p") or die (mysql_error());
	else
		$res=mysql_query("SELECT * FROM players p WHERE hidden!='1'") or die (mysql_error());

	while($p=mysql_fetch_assoc($res)){
		$id=$p['id'];
		$players[$id]['name']=$p['rfg'];
	
		$res2=mysql_query("SELECT * FROM ratings WHERE id='$id' ORDER BY date DESC LIMIT 2") or die (mysql_error());
		
		// Заполняем массив текущим значением рейтинга
		$r=mysql_fetch_assoc($res2);
		foreach ($SYSTEMS	as $system){
			$players[$id][$system]=$r[$system];
			$players[$id][$system."_kyu"]=elo2kyu($r[$system], $system);
		}

		// Используем предыдущее значение для сравненения
		$r=mysql_fetch_assoc($res2);
		foreach ($SYSTEMS	as $system){
			$new=floor($players[$id][$system]/100);
			$old=floor($r[$system]/100);
			if ($old==$new) $players[$id][$system."_class"]='stable';
			else if ($old<$new) $players[$id][$system."_class"]='up';
			else $players[$id][$system."_class"]='down';
		}	
		
	}

	uasort($players,'player_sort');
	return $players;
}

// Функция сравнения пользователей по рейтингу рфг или кгс (обратная сортировка)
function player_sort($a,$b){
	if ($a['rfg'] || $b['rfg']) 
		$system='rfg';
	else 
		$system='kgs'; 

	if ($a[$system]==$b[$system]) $system='kgs';

	if ($a[$system]==$b[$system])
		 return 0;

	return ($a[$system] > $b[$system]) ? -1 : 1;
}

// Получает ссылку на профиль РФГ игрока
function get_rfg_url($id){
  $cachefile='/tmp/rfg_urls.cache';
  if (empty($id)) return '';

  # Если нужно, скачиваем новую версию рейтинг-листа
  if (time()-filemtime($cachefile)>3600){
    $remote=fopen('http://gofederation.ru/players/',"r");
    $local=fopen($cachefile, 'w') or die ("Can't open local cache for writing");
    while ($str=fgets($remote)){
      fputs($local, $str);
    }

    fclose($remote);
    fclose($local);
  }

  $fp=fopen($cachefile,'r') or die ("Can't open local cache for reading");
    
  while ($str=fgets($fp)){
    if (preg_match('/<td><a href="?([a-z0-9\/]+)"?>'.$id.'<\/a><\/td>/', $str,$matches))
      $return=$matches[1];
  } 
  
  return $return;
}


?>
