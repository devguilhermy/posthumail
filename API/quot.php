<?php
require("dbCredentials.php");

/*
	Script que deverá ser rodado todos os dias.
	Responsável por enviar os emails de confirmação e o póstumo.
*/

// ATENÇÃO: Esse script ainda não está finalizado, portanto não é funcional.

// B10-2-B16(12237514)

$dbConn = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPasswd);
$dbQuery = $dbConn->query("SELECT count(*) FROM `users`");
$usersCount = $dbQuery->fetch(PDO::FETCH_NUM)[0];

$usersPerCicle = 200;
$usersIndex = 0;
$ui = 0;

$stopCicles = FALSE;
while (!$stopCicles) {
	$dbQuery = $dbConn->query("SELECT * FROM `users` ORDER BY `name` LIMIT $usersIndex, $usersPerCicle");
	$usersIndex += $usersPerCicle;
	$users = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	// Se a consulta retornou menos de $usersPerCicle registros, como pedido, então os registros acabaram
	// Portanto, esse é o último ciclo
	if (count($users) < $usersPerCicle)
		$stopCicles = TRUE;

	for ($i = 0; $i < count($users); $i += 1) {
		$ui += 1;
		$todayDate = date("d/m/Y", strtotime("now"));

		$lastConfirmationTimestamp = strtotime($users[$i]['last_confirmation']);
		$lastConfirmationDate = date("d/m/Y", $lastConfirmationTimestamp);
		$emailInterval = $users[$i]['confirmation_interval'];
		$emailDeadline = $users[$i]['deadline'];
		$lastEmailTimestamp = strtotime($users[$i]['last_email']);
		$lastEmailDate = date("d/m/Y", $lastEmailTimestamp);
		$nextEmailDate = date("d/m/Y", $lastEmailTimestamp+$emailInterval);
		$sendPosthumousEmailDate = date("d/m/Y", $lastConfirmationTimestamp+$emailDeadline);

		$clientName = $users[$i]['name'];
		$clientEmail = $users[$i]['email'];
		$clientId = $users[$i]['id'];

		// Envia um novo pedido de confirmação, se o intervalo já tiver sido atingido
		if ($todayDate >= $nextEmailDate && $todayDate < $sendPosthumousEmailDate) {
			// Obs.: A comparação $todayDate == $nextEmailDate seria suficiente.
			// O >= foi colocado no lugar para garantir que, mesmo que o script não rode em algum dia,
			// no próximo dia em que ele rodar a ação seja executada.

			// [FUNÇÃO AINDA NÃO IMPLEMENTADA]
			
			// Registra um token para confirmação no DB
			// Que será enviado no link do email de confirmação
			register_confirmation_token($dbConn, $clientId);

			// Atualiza o campo last_email do usuário no DB para a data de hoje
			$dbQuery = $dbConn->prepare("UPDATE `users` SET `last_email` = CURRENT_DATE WHERE `id` = :client_id");
			$dbQuery->bindValue(":client_id", $clientId);
			$dbQuery->execute();
			
			echo "Email de confirmação enviado!";
		}

		if ($todayDate >= $sendPosthumousEmailDate) {

			// [FUNÇÃO AINDA NÃO IMPLEMENTADA]

			echo "Email póstumo enviado!";
		}
	}
}