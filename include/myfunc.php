<?
/*

Типичные функции для PHP

Created by: Peter Smol pub@petersmol.ru

*/


// IP клиента
function getip() {
   if (isset($_SERVER)) {
     if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && validip($_SERVER['HTTP_X_FORWARDED_FOR'])) {
       $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
     } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && validip($_SERVER['HTTP_CLIENT_IP'])) {
       $ip = $_SERVER['HTTP_CLIENT_IP'];
     } else {
       $ip = $_SERVER['REMOTE_ADDR'];
     }
   } else {
     if (getenv('HTTP_X_FORWARDED_FOR') && validip(getenv('HTTP_X_FORWARDED_FOR'))) {
       $ip = getenv('HTTP_X_FORWARDED_FOR');
     } elseif (getenv('HTTP_CLIENT_IP') && validip(getenv('HTTP_CLIENT_IP'))) {
       $ip = getenv('HTTP_CLIENT_IP');
     } else {
       $ip = getenv('REMOTE_ADDR');
     }
   }

   return $ip;
 }

// Время в формате DATETIME
function get_date_time($timestamp = 0)
{
if ($timestamp)
	return date("Y-m-d H:i:s", $timestamp);
else
  return date("Y-m-d H:i:s");
}


// Экранировка для запросов
function sqlesc($x) {
    return "'".mysql_real_escape_string($x)."'";
}


/*

Удобное отображение на экран

*/

// Размеры по-русски
function mksize($bytes) {
  if ($bytes < 1000 * 1024)
    return number_format($bytes / 1024, 2) . " Кб";
  if ($bytes < 1000 * 1048576)
    return number_format($bytes / 1048576, 2) . " Мб";
  if ($bytes < 1000 * 1073741824)
        return number_format($bytes / 1073741824, 2) . " Гб";
  return number_format($bytes / 1099511627776, 2) . " Тб";
}



// Рекурсивный вывод массива для HTML
function hprint_r ($var)
{
  echo nl2br(print_r($var,true));
}



// Отображение времени в формате "13 секунд назад"
function human_readable_period ($old, $ago = " назад")
{
$now=get_date_time();
$delta=strtotime ($now) - strtotime ($old);

if ($delta>60*60*24*365)
	$str= russian_numeral_forms((int)($delta/(60*60*24*365)),"год","года","лет").$ago ;
else if ($delta>60*60*24*30)
	$str= russian_numeral_forms((int)($delta/(60*60*24*30)),"месяц","месяца","месяцев").$ago ;
else if ($delta>60*60*24*7)
	$str= russian_numeral_forms((int)($delta/(60*60*24*7)),"неделю","недели","недель").$ago ;
else if ($delta>60*60*24)
	$str= russian_numeral_forms((int)($delta/(60*60*24)),"день","дня","дней").$ago ;
else if ($delta>60*60)
	$str= russian_numeral_forms((int)($delta/(60*60)),"час","часа","часов").$ago ;
else if ($delta>60*30)	
	$str= "полчаса".$ago;
else if ($delta>60)
	$str= russian_numeral_forms((int)($delta/(60)),"минуту","минуты","минут").$ago ;
else
	$str= russian_numeral_forms($delta,"секунду","секунды","секунд").$ago ;

return $str;
}

// Склонение числительных: (14, "секунду","секунды","секунд")
function russian_numeral_forms ($n,$one,$two,$five)
{

if ($n>10 && $n<20)
	$units=$five;
else if ($n%10 == 1)
	$units=$one;
else if (($n%10>1) && ($n%10<5))
	$units=$two;
else 
	$units=$five;

if ($n==1)
	return $units;
else
	return $n." ".$units;
}


?>
