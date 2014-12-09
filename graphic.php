<?
// Задаем входные данные ############################################

// Входные данные - три ряда, содержащие случайные данные.
// Деление на 2 и 3 взято для того чтобы передние ряды не
// пересекались

// Массив $DATA["x"] содержит подписи по оси "X"

require ('include/rating_functions.php');
require ('include/player_db.php');
include("pChart/class/pData.class.php");
include("pChart/class/pDraw.class.php");
include("pChart/class/pImage.class.php");

$id=sqlesc($_REQUEST['id']);

$res=mysql_query("SELECT * FROM ratings WHERE id=$id") or die (mysql_error());
if (!mysql_num_rows($res)) die ('no such user');

$DATA=Array();

while ($row=mysql_fetch_array($res))    {

		if ($row['kgs']==0)  $row['kgs']=VOID; else $kgs_exist=true;
		if ($row['dgs']==0) $row['dgs']=VOID; else $dgs_exist=true;
		if ($row['rfg']==0) $row['rfg']=VOID; else $rfg_exist=true;
		if ($row['egd']==0) $row['egd']=VOID; else {$egd_exist=true;  $row['egd']+=50;}

    $DATA['kgs'][]=$row['kgs'];
    $DATA['dgs'][]=$row['dgs'];
    $DATA['rfg'][]=$row['rfg'];
    $DATA['egd'][]=$row['egd']; 
    $DATA["x"][]=$row['date'];
}

$max_null=max($DATA['kgs'][0],$DATA['rfg'][0],$DATA['dgs'][0],$DATA['egd'][0]);
foreach ($SYSTEMS as $system){
	if ($DATA[$system][0]==VOID) $DATA[$system][0]=$max_null; 
}

// Округляем дату в зависимости от размера графика
$cnt=sizeof($DATA["x"]);
$RUS_MONTH=Array('нул','янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек');
foreach ($DATA["x"] as $key => $val){
	$month=0+substr($val,5,2);
	$year=substr($val,2,2);
	if ($cnt>2*365) {
		if(($month-1)%6!=0) $month-=($month-1)%6;
	}else if ($cnt>365) {
		if($month%3!=0) $month-=$month%3;
	}else if ($cnt>30*9) {
		if($month%2!=0) $month--;
	}
	if ($month<1) {$month+=12; $year--;}
	$DATA["x"][$key]=$RUS_MONTH[$month]."'".$year;
}

// Округляем хвостик в начале
$first = $DATA["x"][0];
$cnt_first = 0;
foreach ($DATA["x"] as $val){
    if ($val==$first)
        $cnt_first++;
    else{
        $second=$val;
        break;
    }
}
if ($cnt_first<$cnt/10) 
  foreach ($DATA["x"] as $key=>$val){
    if ($val==$first)
        $DATA["x"][$key]=$second;
    else{
        break;
    }
  }  



$myData = new pData();
if ($kgs_exist){
$myData->addPoints($DATA['kgs'], 'kgs');
$myData->setSerieDescription("kgs","KGS");
$myData->setSerieOnAxis("kgs",0);
}

if ($rfg_exist){
$myData->addPoints($DATA['rfg'], 'rfg');
$myData->setSerieDescription("rfg","РФГ");
$myData->setSerieOnAxis("rfg",0);
}

if (1+$dgs_exist){
$myData->addPoints($DATA['dgs'], 'dgs');
$myData->setSerieDescription("dgs","DGS");
$myData->setSerieOnAxis("dgs",0);
}

if ($egd_exist){
$myData->addPoints($DATA['egd'], 'egd');
$myData->setSerieDescription("egd","EGD");
$myData->setSerieOnAxis("egd",0);
}

$myData->addPoints($DATA["x"],'x');
$myData->setAbscissa('x');

$myData->setAxisDisplay(0,AXIS_FORMAT_GO);
$myData->loadPalette("pChart/palettes/go.color", TRUE);

$myPicture = new pImage(500,400,$myData);
$myPicture->drawRectangle(0,0,499,399,array("R"=>0,"G"=>0,"B"=>0));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"pChart/fonts/GeosansLight.ttf","FontSize"=>14));
$TextSettings = array("Align"=>TEXT_ALIGN_MIDDLEMIDDLE
, "R"=>0, "G"=>0, "B"=>0);
$myPicture->drawText(250,25,$_REQUEST['id']." rating history",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,475,360);
$myPicture->setFontProperties(array("R"=>0,"G"=>0,"B"=>0,"FontName"=>"design/courier.ttf","FontSize"=>8));

$Settings = array("Pos"=>SCALE_POS_LEFTRIGHT
, "Mode"=>SCALE_MODE_FLOATING
, "LabelingMethod"=>LABELING_DIFFERENT
, "GridR"=>156, "GridG"=>156, "GridB"=>156, "GridAlpha"=>190, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>0, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>array(0,0,1));
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("BreakVoid"=>FALSE);
$myPicture->drawSplineChart($Config);

$Config = array("FontR"=>0, "FontG"=>0, "FontB"=>0, "FontName"=>"pChart/fonts/pf_arma_five.ttf", "FontSize"=>6, "Margin"=>6, "Alpha"=>30, "BoxSize"=>5, "Style"=>LEGEND_NOBORDER
, "Mode"=>LEGEND_HORIZONTAL,
);
$myPicture->drawLegend(363,16,$Config);

$myPicture->stroke();
?>







