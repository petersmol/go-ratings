<?

$CACHE_DIR='cache/';
$DESIGN_DIR='design/';
$CACHE_TTL=3600*24; // Время жизни кэша картинок (в секундах)

$DESIGN_LIST=array('dos', 'sai', 'triangle'); // В код необходимо добавить функции вида sai_banner();

/***************************************************
								Работа с изображениями
****************************************************/



function print_image ($id, $rating, $design = 'default') {
	/* Выводит на экран картинку. При необходимости использует кэширование.
	Генерация происходит в отдельной функции, которую можно задавать вторым параметром.  */
	global $CACHE_DIR;
	$filename=$CACHE_DIR."/".$id."_".$design.".png";
	
	$draw_function=$design."_banner";
	$image = $draw_function ($id, $rating);
	
//	imagepng($image,$filename) or die ('Cannot write cache');
	header('Content-type: image/png');
	imagepng($image);
	imagedestroy($image); 
}



function dos_banner ($id, $rating){
	/* Генерирует простейшую картинку на основе рейтингов							*/
	$image = imagecreatetruecolor(150,70) or die('Cannot create image');
	ImageColorTransparent($image, 0xFFFFFF);
	imagefill($image, 0, 0, 0xFFFFFF);
	
	imagestring($image, 2, 0, 0, "Rating list for $id", 0); $offset+=5;
	if ($rating['rfg'])	imagestring($image, 2, 0, $offset+=15, "rfg: ".$rating['rfg'], 0);
	if ($rating['kgs'])	imagestring($image, 2, 0, $offset+=15, "kgs: ".$rating['kgs'], 0);
	if ($rating['dgs'])	imagestring($image, 2, 0, $offset+=15, "dgs: ".$rating['dgs'], 0);
	return $image;
}

function sai_banner ($id, $rating){
	global $DESIGN_DIR;
	$image = imagecreatefrompng($DESIGN_DIR.'/sai.png');
	$font= $DESIGN_DIR.'/sai.ttf';
	$text=$id;
	$color=0;
	$angle=0;

	foreach (Array('kgs','rfg','dgs') as $val){
		$rating[$val]=elo2kyu($rating[$val]);
	}

	print_centered($image,16,$angle,105,70, $color, $font, $text);
	if ($rating['kgs'])			print_centered($image,12,$angle,105,93, $color,$font, "KGS  ".$rating['kgs']);
	else if($rating['dgs']) print_centered($image,12,$angle,105,93,  $color,$font, "DGS  ".$rating['dgs']);
	if ($rating['rfg'])			print_centered($image,12,$angle,105,110, 	$color,$font, "РФГ  ".$rating['rfg']);

	return $image;
}

function triangle_banner ($id, $rating){
	global $DESIGN_DIR;
	$image = imagecreatefrompng($DESIGN_DIR.'/triangle.png');
	$font= $DESIGN_DIR.'/sai.ttf';
	$text=$id;
	
	$x=80;
	$y=43;

	foreach (Array('kgs','dgs', 'egd') as $val){
		$rating[$val]=elo2kyu($rating[$val], $val);
	}

	$color=0;
	$angle=0;

	
	if ($rating['rfg'])			$strings[]="РФГ  ".$rating['rfg'];
	if ($rating['kgs'])			$strings[]="KGS  ".$rating['kgs'];
	else if ($rating['egd'])			$strings[]="EGF  ".$rating['egd'];
#	if ($rating['dgs']) 			$strings[]="DGS  ".$rating['dgs'];

	print_centered($image,16,$angle,$x,$y, $color, $font, $text);
	$y+=25;
	foreach ($strings as $str){
		print_centered($image,12,$angle,$x,$y+$shift, $color, $font, $str);
		$shift+=23;
		
	}

	return $image;
}


function print_centered($image,$size,$angle,$center_x,$center_y, $color, $font, $text){
	/* Печатает текст по центру */
		$box=imagettfbbox ($size, 0,$font,$text);
		$x_size=$box[2];
		$y_size=$box[3];
		$XX=$center_x-$x_size/2;
		$YY=$center_y-$y_size/2;
		imagettftext($image,$size,$color,$XX,$YY, 0, $font, $text);
}

/***************************************************
								Работа с кэшем
****************************************************/

function have_cache($id, $design) {
	/* Проверяет наличие свежей сгенерированной картинки в кэше */
	global $CACHE_DIR,$CACHE_TTL;
	$filename=$CACHE_DIR."/".$id."_".$design.".png";
	
	if (is_file($filename)) {
		$stat=stat($filename);
		if (time()-$stat[9]<$CACHE_TTL)
			return true;
		}
	return false;
}

function print_image_from_cache($id, $design) {
	/* Выводит на экран картинку из кэша */
	global $CACHE_DIR;
	$filename=$CACHE_DIR."/".$id."_".$design.".png";
	
	header('Content-type: image/png');
	readfile($filename) or die ("Cannot open cached image");
}




?>
