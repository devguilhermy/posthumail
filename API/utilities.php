<?php
session_start();

// B10-2-B16(12237514)

function set_response_error($responseArray, $errorCode, $message) {
	$responseArray["status"] = "error";
	$responseArray["status_code"] = $errorCode;
	$responseArray["message"] = $message;

	return $responseArray;
}

function authenticate_user($dbConnection, $email, $passwd) {
	$dbQuery = $dbConnection->prepare("SELECT `id` FROM `users` WHERE `email` LIKE :email AND `password` LIKE MD5(:passwd)");
	$dbQuery->bindValue(':email', $email);
	$dbQuery->bindValue(':passwd', $passwd);
	$dbQuery->execute();
	$user = $dbQuery->fetchAll(PDO::FETCH_ASSOC);
	
	if (count($user) == 0) {
		return -1;
	}

	$clientId = $user[0]['id'];
	return $clientId;
}

function validate_session() {
	// Verifica se há uma sessão aberta
	if (!isset($_SESSION['client_email'])) {
		return 1;
	}

	// Se houver, verifica se ela ainda não expirou
	if (strtotime("now") > $_SESSION['expiration']) {
		// Se expirou, destrói a sessão e retorna 2
		session_destroy();
		return 2;
	}

	// Do contrário, ela existe e ela é válida
	return 0;
}

function randString($size){
    $basic = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $return= "";
    for($count = 0; $size > $count; $count++){
        $return .= $basic[rand(0, strlen($basic) - 1)];
    }

    return $return;
}

function register_confirmation_token($dbConnection, $clientId) {
	$dbQuery = $dbConnection->prepare("INSERT INTO `tokens` (`id`, `token_name`, `token_nature`, `client_id`, `expiration`) VALUES (NULL, :token_name, 'confirmation', :client_id, :expiration)");

	$dbQuery->bindValue(":token_name", randString(130));
	$dbQuery->bindValue(":client_id", $clientId);
	$dbQuery->bindValue(":expiration", strtotime("+7 days"));

	$dbQuery->execute();
}

function isJson($string) {
	json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE);
}