<?
/*
Скрипт выводит на экран картинку с рейтингами игрока в различных системах (DGS, KGS, EGD).

*/
require ('include/image_functions.php');
require ('include/rating_functions.php');
require ('include/player_db.php');

// Получаем параметром id юзера и выбранным дизайном
$id=$_GET['id']; 
if ( in_array($_GET['design'], $DESIGN_LIST) )	$design=$_GET['design'];
else	$design='sai';

//$player = find_user($id) or die ("Неизвестный игрок '$id'. Напишите мне на go(at)petersmol.ru, если хотите чтобы Большой Брат следил и за вами.");

$rating = get_rating($id) or die ("Неизвестный игрок '$id'. Напишите мне на go(at)petersmol.ru, если хотите чтобы Большой Брат следил и за вами.");
#if(!$rating['kgs'])
#	$rating['kgs']='?';
#else
/*
$rating['kgs']=elo2kyu($rating['kgs']);
$rating['rfg']=elo2kyu($rating['rfg']);
$rating['dgs']=elo2kyu($rating['dgs']);
*/
print_image($id, $rating, $design);

?>
