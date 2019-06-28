<?php
require_once('db.php');                                             //ПОДКЛЮЧАЕМСЯ К БАЗЕ ДАННЫХ
require_once('vk.php');                                             //ПОДКЛЮЧАЕМ КЛАСС, ОТВЕЧАЮЩИЙ ЗА ЗАПРОСЫ К ВК
define("TIME_NOW", date("U")-60*3-15);                              //ТЕКУЩЕЕ ВРЕМЯ. УМЕНЬШАЕМ НА 3 МИНУТЫ ПОТОМУ, ЧТО ВРЕМЯ СЕРВЕРА СПЕШИТ НЕМНОГО
$vk = new vk();
$server = $_GET['server'];                                          //ПОЛУЧАЕМ НОМЕР СЕРВЕРА
$pref = json_decode(file_get_contents("json/default.json"), true);  //ПОДКЛЮЧАЕМ СТАНДАРНЫЕ КОНФИГУРАЦИИ КОНКУРСА
$access_token = '';                                                 //СЕРВИСНЫЙ ТОКЕН ВК
$group_access_token = '';                                           //ТОКЕН ГРУППЫ ВК ДЛЯ ОТСЫЛКИ СООБЩЕНИЙ
$settings = json_decode(file_get_contents("json/default.json"));    //ТУТ ХРАНИТСЯ ШАБЛОН СООБЩЕНИЯ
  

if($server == 1){ //ЕСЛИ СЕРВЕР ПЕРВЫЙ, ТО ИСПОЛЬЗУЕМ КОНФИГУРАЦИИ ДЛЯ ПЕРВОГО СЕРВЕРА
  $pref = json_decode(file_get_contents("json/first.json"), true);
}
?>
<h1><?=$server?>-й сервер:</h1>
  <ol>
    <?
    $i = 0;
    while($i < $pref['count'] ){
      
      //ПОЛУЧАЕМ ИНФОРМАЦИЮ О ПОБЕДИТЕЛЕ
      //функция ORDER BY RAND() выбирает рандомную запись в базе данных
      //WHERE `server`='{$server}' означает, что запись нужно взять для определенного сервера, а не в разброс
      //WHERE ... AND `is_winner`='0' — выбирает среди записей только те, кто еще не стал победителем, чтобы один человек не занимал сразу несколько мест
      $user = mysql_fetch_assoc(mysql_query("SELECT * FROM `pp_konkurs` WHERE `server`='{$server}' AND `is_winner`='0' ORDER BY RAND() LIMIT 1"));
      if($user['id'] == ''){
        //ЕСЛИ НЕТ ПОБЕДИТЕЛЯ ВЫВОДИМ no user
        echo '<li>no user</li>';
      }else{
        //ОБНОВЛЯЕМ ЗАПИСЬ В БАЗЕ ДАННЫХ
        //все новые данные будут выводится в команде 'стата'
        mysql_query("UPDATE `pp_konkurs` SET `is_winner`='1',`summ`='{$pref['money'][$i]}',`date`='".TIME_NOW ."' WHERE `id`='{$user['id']}'");
        
        //ПОЛУЧАЕМ ИМЯ ПОБЕДИТЕЛЯ
        $name = $vk->userName($user['user_id'], $access_token);
        
        //ФОРМИРУЕМ ТЕКСТ СООБЩЕНИЯ ИЗ ШАБЛОНА
        $text = str_replace('{server}', $server, $settings->msg_text);
        $text = str_replace('{money}', $pref['money'][$i], $text);
        $text = str_replace('{bank}', $user['bank'], $text);
        
        //ОТПРАВЛЯЕМ СООБЩЕНИЕ ПОБЕДИТЕЛЮ ОТ ИМЕНИ ГРУППЫ И ПОЛУЧАЕМ ОТВЕТ
        $response = $vk->sendMessage($user['user_id'], $text, $group_access_token);
        
        //ВЫВОДИМ ССЫЛКУ И ИНФОРМАЦИЮ О ПОБЕДИТЕЛЕ
        echo "<li><a href='https://vk.com/id{$user['user_id']}'>({$name})</a> — {$user['bank']} — {$pref['money'][$i]} — Отправка сообщения: {$response}</li>";
      }
      $i++;
    }
    ?>
  </ol>
