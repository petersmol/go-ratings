<?

require ('get_rating_kgs_hashes.php');

if ($_REQUEST['DEBUG']) $DEBUG=true;

	$BLACK=0;
	$YELLOW=14540168;
	$BORDER=16777215;

function get_color($rgb){
	global $BLACK,$YELLOW, $BORDER;
	if ($rgb==$BLACK) return 'BLACK';
	else if ($rgb==$YELLOW) return 'YELLOW';
	else if ($rgb==$BORDER) return 'BORDER';
	else if ($rgb>100000) return 'GREY';
	else return 'GREEN';
}


function get_rating_kgs($id){
if (empty($id)) return '';
global $DEBUG;
global $BLACK,$YELLOW, $BORDER;
$img = @imagecreatefrompng('http://www.gokgs.com/servlet/graph/'.$id.'-en_US.png'); 
if(!$img) return '';



// Green line detector

while ($shift<10 && empty($RATING_LINE)){
	$prev_rgb=-1;
	for ($y=0;$y<480;$y++,$prev_rgb=$rgb){
		$rgb=imagecolorat($img,638-$shift,$y);
		$color=get_color($rgb);
		$prev_color=get_color($prev_rgb);
#		if ($DEBUG) print "$shift.$y Color #$color<br>\n";
		if ($prev_color=='BLACK' && $color=='YELLOW') {$SCALE_LINE[]=$y; continue;}
		if ($prev_color=='YELLOW' && $color=='BLACK') {$SCALE_LINE[]=$y;  continue;}
		if ($color=='BLACK' or $color== 'YELLOW' ) continue;
		if (($prev_color=='GREEN' or $prev_color=='GREY') && $color==$prev_color )  continue;
	
		if ($rgb< 100000) {
			$RATING_LINE[]=$y; // Green line!
			if($DEBUG) print ">>>>>>>> green $y (#$rgb)<br>\n"; 
		}else if ($prev_color!='GREY'){
		 	$SCALE_LINE[]=$y;   // Grey
			if($DEBUG) print ">>>>>>>>> grey $y (#$rgb)<br>\n"; 
		}
	}
	$shift++;
}

if (get_color(imagecolorat($img,300,300))=='YELLOW')
	return 'fuck'; ## Sorry, no rank graph available

// Scale detector
for ($y=0;$y<480;$y++){
	$with_text=false;
	for ($x=0;$x<18;$x++){
		$rgb=imagecolorat($img,$x,$y);
  	if (get_color($rgb)!='YELLOW') $with_text=true;
	}

	if ($with_text & !$prevous_with_text){
		$TEXT_LINE[]=$y;
		$sample=imagecreate(50,50);
		$yellow_arr=imagecolorsforindex($img,$YELLOW);
		imagecolorallocate($sample,$yellow_arr['red'],$yellow_arr['green'],$yellow_arr['blue']);
		$black=imagecolorallocate($sample,0,0,0);

		imagecopy($img,$img,620,$y, 0, $y, 20, 10);
	}
	$prevous_with_text=$with_text;
}

#header('Content-Type: image/png'); imagepng($img); exit();
if ($DEBUG){ 
echo "scale \n"; hprint_r($SCALE_LINE);
echo "text \n"; hprint_r($TEXT_LINE);
echo "rating \n"; hprint_r($RATING_LINE);
}

for ($border=0;get_color(imagecolorat($img,$border,200))!='BORDER';$border++);

foreach ($TEXT_LINE as $offset){
	$hash='';
	$hash2='';
	for ($x=0;$x<$border;$x++){
		for ($y=9,$line='';$y>=0;$y--){
			if (get_color(imagecolorat($img,$x,$offset+$y))=='YELLOW') 
				$line.=' ';
			else 
				$line.='x';
		}
	if (strlen(trim($line))>0) $hash.=$line."\n";
	}
	$TEXT_HASH[]= $hash;
	$TEXT[]= recognize_text($hash);
}

if ($DEBUG){ 
	foreach ($TEXT_HASH as $hash){
		#print "<pre>'$hash' => '".recognize_text ($hash)."',\n</pre>";
	}
}

// Calculate rating
if (empty($RATING_LINE[0])){
	if ($DEBUG) print "empty rating line!\n";
	return '';
}

$i=0;
while ($RATING_LINE[0]>$SCALE_LINE[$i] && $i<50) { 
	if ($DEBUG) print "\$SCALE_LINE[$i]=".$SCALE_LINE[$i]." (rating at ".$RATING_LINE[0].")<br>\n";
	$i++;  
}
 
$rating=$TEXT[$i];
if ($rating=='') {
	if ($DEBUG) print "No text for this rating!\n";
	if ($DEBUG) print "";
	return $rating;
}
$shift=round(100*($RATING_LINE[0]-$SCALE_LINE[$i])/($SCALE_LINE[$i-1]-$SCALE_LINE[$i]));
$rating+=$shift;

return $rating;

}


function recognize_text ($hash){
	global $DEBUG, $HASHES;

	foreach ($HASHES as $pic => $rating){
		$probability=compare_str($hash,$pic);
		if ($DEBUG) echo "Рейтинг $rating с вероятностью $probability<br>\n";
		if ($probability>$MAX_PROB){
			$answer=$rating;
			$MAX_PROB=$probability;
		}
	}

	if ($DEBUG) echo "<br>Анализировали:<pre>'$hash' => '$answer',</pre><br>***<br><br>\n";
	return $answer;
}

function compare_str($a,$b){
	$probability=0;
	for ($i=0;$i<strlen($a);$i++){
		if ($a[$i]==$b[$i]) $probability++;
	}
	return $probability;
}

?>
