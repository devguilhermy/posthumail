<?php
require("dbCredentials.php");

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
		$dateToday = date("d/m/Y", strtotime("now"));

		$lastConfirmationTimestamp = strtotime($users[$i]['last_confirmation']);
		$dateLastConfirmation = date("d/m/Y", $lastConfirmationTimestamp);
		$emailInterval = $users[$i]['confirmation_interval'];
		$emailDeadline = $users[$i]['deadline'];
		$lastEmailTimestamp = strtotime($users[$i]['last_email']);
		$nextEmailDate = date("d/m/Y", $lastEmailTimestamp+$emailInterval);
		$sendPosthumousEmailDate = date("d/m/Y", $lastConfirmationTimestamp+$emailDeadline);

		$clientName = $users[$i]['name'];
		$clientEmail = $users[$i]['email'];

		if ($dateToday > $dateLastConfirmation) {
			echo "Cliente: ".$clientName."<br/>";
			echo "Email: ".$clientEmail."<br/>";
			echo "Última confirmação: ".$dateLastConfirmation."<br/>";
			echo "Intervalo de envios de email: ".round((int)($emailInterval)/86400, 2)." dias<br/>";
			echo "Tolerância: ".round((int)($emailDeadline)/86400, 2)." dias<br/>";
			echo "Email póstumo deve ser enviado em: ".$sendPosthumousEmailDate."<br/><br/>";
		}
	}
}






