<?php


class vk{
  //ФУНКЦИЯ ПОЛУЧЕНИЯ ИМЕНИ И ФАМИЛИИ ПОЛЬЗОВАТЕЛЯ
  //ПАРАМЕТРЫ:  id - id пользователя
  //            access_token - токен
  function userName($id, $access_token){
    //отправляем запрос и извлекаем ответ
    $userInfo = json_decode($this->request('users.get', 'user_ids='.$id.'&v=5.0', $access_token));
    
    //возвращаем имя и фамилию пользователя
    return $userInfo->response[0]->first_name . ' ' . $userInfo->response[0]->last_name;
  }
  
  //ФУНКЦИЯ ПОЛУЧЕНИЯ ИМЕНИ И ФАМИЛИИ ПОЛЬЗОВАТЕЛЯ
  //ПАРАМЕТРЫ:  method - метод
  //            params - параметры
  //            access_token - токен
  function request($method, $params, $access_token){
    //отправляем запрос на сервер ВК и возвращаем ответ в виде строки
    return file_get_contents("https://api.vk.com/method/{$method}?{$params}&access_token={$access_token}");
  }
  
  //ФУНКЦИЯ ПОЛУЧЕНИЯ ИМЕНИ И ФАМИЛИИ ПОЛЬЗОВАТЕЛЯ
  //ПАРАМЕТРЫ:  peer - id диалога (пользователя)
  //            text - текст сообщения
  //            access_token - токен (группы)
  function sendMessage($peer, $text, $access_token){
    $request_params = array(
      'message' => $text,
      'peer_id' => $peer,
      'v' => '5.38'
    );
    $params = http_build_query($request_params);
    
    //отправляем запрос и извлекаем ответ
    $response = json_decode($this->request('messages.send', $params, $access_token));
    if($response->error != null){
      //если ошибка, возвращаем 'Error'
      return "Error";
    }else{
      //иначе 'Success'
      return "Success";
    }
  }
}
  
