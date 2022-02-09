<?php
$host = "#";
$user = "#";
$password = "#";
$dbname = "#";
$port = 5432;

try{
  $conn = "pgsql:host=" . $host . ";port=" . $port .";dbname=" . $dbname . ";user=" . $user . ";password=" . $password . ";";
	
	$pdo = new PDO($conn, $user, $password);
}
catch (PDOException $e) {
echo 'Connection failed: ' . $e->getMessage();
}

$sql = 'SELECT * FROM questions';
$stmt = $pdo->prepare($sql);
$stmt->execute();
$rowCount = $stmt->rowCount();

define('TOKEN', '#');

$body = file_get_contents('php://input');
$data = json_decode($body, true);

$userName = $data["message"]["from"]["first_name"];
$data = $data['callback_query'] ? $data['callback_query'] : $data['message'];

/* $message = mb_strtolower(($data['text'] ? $data['text'] : $data['data']),'utf-8');
 */
$message = $data['text'];

if ($message == "/start"){
		/* RESET ITTERATION FOR START*/
		$sql = "UPDATE itteration SET itteration = 0";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();

$method = 'sendMessage';
        $send_data = [
            'text'   => '
						Привет, '.$userName.'! Я буду задавать тебе вопросы, а ты отвечай на них от 0 до 100. 
						Можешь почитать дополнительную информацию о том, как со мной общаться и моих возможностях. Используй для этого кнопку "Помощь" или напиши команду /help.
						А	можем сразу перейти к разговору. Начинаем ?:)',
            'reply_markup' => [
                'resize_keyboard' => true,
                'keyboard' => [
                    [
                        ['text' => "Начать"],
												['text' => "Помощь"],
                    ],
                ]
            ]
        ];
}

else if ($message == "/help" or $message == "Помощь"){
$method = 'sendMessage';
        $send_data = [
            'text'   => '	
						~~~~~~~ИСПОЛЬЗОВАНИЕ~~~~~~~
						1. Для ответов используйте числа в диапазоне от 0 до 100, которые будут выражать процент Вашего согласия с утверждением или то, на сколько вопрос касается Вас;
						2. При ответе не нужно использовать отрицательные числа, значок процентов (%), плавающие точки, запятые и иные символы - только числа в указанном диапазоне;
						3. Не воспринимаются текстовые сообщения во время разговора со мной;
						4. Перед ответом хорошо подумай - возможности поменять его не будет;
						
						~~~~~~~ВОЗМОЖНОСТИ~~~~~~~
						1. Я - специализированный собеседник, пообщаться со мной на иные темы, пока что не получится :)
						2. В запасе имею достаточное количество вопросов и предполагаемых профессий;
						3. По окончанию теста, я покажу 3-и наиболее подходящих тебе профессии;
						4. Чтобы начать тестирование сначала - нажми на кнопку "Заново". (!)Все предыдущие ответы нужно будет вводить снова;
						5. Если тебя утомили мои вопросы и ты хочешь закончить тестирование - нажимай на кнопку "Закончить" и я покажу тебе результат на основе того, что есть. (!)Но учти, в этом случае результат может быть лишь приблизительный. Советую всё же пройти тест до конца :)
						',
            'reply_markup' => [
                'resize_keyboard' => true,
                'keyboard' => [
                    [
                        ['text' => "Начать"],
                    ],
                ]
            ]
        ];
}

else if ($message == "Начать" || $message == "Заново" ) {
		/* RANDOMIZE QUESTION */
		$aUse = [];
		$index = rand(1, $rowCount);
		$aUse[0] = $index;
		$cSovp = 0;

		for ($i=1; $i < $rowCount; $i++) { 
			$index =  rand(1, $rowCount);

			for ($j=0; $j < count($aUse); $j++) {
				$cSovp = 0;
				if($index == $aUse[$j]){
					$cSovp++;
					$index = rand(1, $rowCount);
					$j = -1;				
				}
			}
			if ($cSovp == 0) {
					$aUse[$i] = $index;
			}
				$cSovp = 0;
		}
		/* UPDATE NEW ID FOR QUESTION */
		for ($i=1; $i < $rowCount+1; $i++) {
			$aIndex = $aUse[$i - 1]; 
			$sql = "UPDATE questions SET idrand = '$aIndex' WHERE questions . id = '$i'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
		}

		/* RESET ITTERATION */
		$sql = "UPDATE itteration SET itteration = 1";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();

		/* SET DEFAULT Pa */
		$sql = 'SELECT id FROM professions';
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$rowCountProff = $stmt->rowCount();
		for ($i=1; $i < $rowCountProff+1; $i++) { 
			$sql = "SELECT * FROM professions WHERE professions . id = '$i'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$padef = $row['padef'];					
			$sql = "UPDATE professions SET pa = '$padef' WHERE professions . id = '$i'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
		}

		/* FIRST QUESTION */
		$sql = "SELECT * FROM questions WHERE questions . idRand = 1";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$answer = $row['q'];

		$method = 'sendMessage';
		$send_data = [
      'text' => 'Начнём с первого вопроса:
			  
			'.$answer.'',
      'reply_markup' => [
                'resize_keyboard' => true,
                'keyboard' => [
                    [
                        ['text' => "Заново"],
												['text' => "Закончить"],
												['text' => "Помощь"],
                    ],
                ]
            ]
		];

}

else if ($message == "Закончить") {
		/* RESET ITTERATION FOR START*/
		$sql = "UPDATE itteration SET itteration = 0";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();

		$sql = "SELECT * FROM professions ORDER BY pa DESC";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$answeArr[1];
		for ($i=1; $i < 4; $i++) { 
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$name = $row['name'];
			$pa = $row['pa'];				
			$pa = round($pa,2) * 100;
			$answeArr[$i] = "$i. $name с вероятностью: ".round($pa,2)."%; \n";
		}

		$answer= implode($answeArr);

		$method = 'sendMessage';
		$send_data = [
      'text' => $answer,
      'reply_markup' => [
                'resize_keyboard' => true,
                'keyboard' => [
                    [
                        ['text' => "Начать"],
												['text' => "Помощь"],
                    ],
                ]
            ]
		];

}


else if ($message || $message == 0) {
		$sql = "SELECT itteration FROM itteration";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$itteration =  $row['itteration'];

	if ($itteration>0) {
		if ($message>=0 && $message<=100) {
			if ($message == 0) {
					$message = 0.01;
			}
			else {
					$message = $message / 100;
			}

		$sql = "SELECT id FROM questions WHERE questions . idrand = '$itteration'";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$id =  $row['id'];

		$sql = "SELECT * FROM professions";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();

						while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { 
								$idProf = $row['id'];
								$json = $row['value'];
								$P = $row['pa'];
								$name = $row['name'];
								
								$idValue = json_decode($json);
								$Py = json_decode($json);
								$Pn = json_decode($json);
								$idValue = $idValue->{$id};
								if ($idValue != Array()){
									$Py = $Py->{$id}[0];
									$Pn = $Pn->{$id}[1];
									if ($Py == 0){
										$Pa =  $P / ($P + $Pn * (1-$P));
										$Pa = $Pa * (1-$message);
									}

									else {
										$Pa = ($Py * $P) / (($Py * $P) + ($Pn * (1-$P)));
										$Pa = $Pa * $message;
									}
									if ($Pa > 0 or $itteration < $rowCount+1) {				
										$sql = "UPDATE professions SET pa = '$Pa' WHERE professions . id = '$idProf'";
										if ($pdo->query($sql) === FALSE) {
											echo "Ошибка: " . $sql . "<br>" . $link->error;
										};
									}
								}

							}
			$itteration++;
			$sql = "UPDATE itteration SET itteration = '$itteration'";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
	
			if ($itteration < $rowCount+1)  {
						$sql = "SELECT * FROM questions WHERE questions . idRand = '$itteration'";
						$stmt = $pdo->prepare($sql);
						$stmt->execute();
						$row = $stmt->fetch(PDO::FETCH_ASSOC);
						$question = $row['q'];
						$method = 'sendMessage';
						$send_data = [
							'text'   => $question
						];

				}
				else {
						$sql = "SELECT * FROM professions ORDER BY pa DESC";
						$stmt = $pdo->prepare($sql);
						$stmt->execute();
						$answeArr[1];
						for ($i=1; $i < 4; $i++) { 
							$row = $stmt->fetch(PDO::FETCH_ASSOC);
							$name = $row['name'];
							$pa = $row['pa'];
							$pa = round($pa,2) * 100;
							$answeArr[$i] = "$i. $name, с вероятностью: ".round($pa,2)."%; \n";
						}

						$answer= implode($answeArr);

						$method = 'sendMessage';
						$send_data = [
							'text' => $answer,
							'reply_markup' => [
												'resize_keyboard' => true,
												'keyboard' => [
														[
																['text' => "Начать"],
																['text' => "Помощь"],
														],
												]
										]
						];
				}
			}

			else if ($message<0 || $message>100) {
				$answeARRrs = array("Ответ в диапазоне от 0 до 100", "Некорректный ответ", "Задайте правильное число", "Ошибка при вводе", "Что-то пошло не так", "Проверьте свой ответ", "Напишите число от 0 до 100");
				$randANS = rand(0, count($answeARRrs)-1);
				$method = 'sendMessage';
							$send_data = [
									'text' => $answeARRrs[$randANS]
							];
			}

			else {
				$answeARRrs = array("Не играйтесь!)", "Пообщаемся как-нибудь позже)", "Используйте числа", "Не понимаю, о чём речь )", "Что-то не понимаю", "Я лучше воспринимаю числа)", "Не могу разобрать Ваше сообщение", "Попробуйте ответить числами");
				$randANS = rand(0, count($answeARRrs)-1);
				$method = 'sendMessage';
							$send_data = [
									'text' => $answeARRrs[$randANS]
							];
			}
	}

else  {
		$answeARRrs = array("Не играйтесь!)", "Пообщаемся как-нибудь позже)", "Используйте кнопку Начать", "Не понимаю, о чём речь )", "Напишите Начать или нажмите соответствующую кнопку", "Вы не начали тестирование", "Давайте начнём общаться, нажмите на кнопку Начать", "Так дело не пойдёт, нажмите кнопку Начать", "Погодите, не спешите с ответами, нажмите на кнопку Начать", "Что-то вы рано начали отвечать", "Вам нужно нажать кнопку или написать команду Начать");
		$randANS = rand(0, count($answeARRrs)-1);
		$method = 'sendMessage';
					$send_data = [
							'text' => $answeARRrs[$randANS]
					];
	}

}

$send_data['chat_id'] = $data['chat']['id'];

$result = sendTelegram($method, $send_data);

function sendTelegram($method, $data, $headers = [])
{
    $curl = curl_init();
    curl_setopt_array($curl, [
			 	CURLOPT_URL => 'https://api.telegram.org/bot' . TOKEN . '/' . $method,
				CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POST => true,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array_merge(array("Content-Type: application/json"), $headers)
    ]);   
    
    $result = curl_exec($curl);
    curl_close($curl);
    return (json_decode($result, 1) ? json_decode($result, 1) : $result);
}

exit('ok'); // говорим телеге, что все окей

?>