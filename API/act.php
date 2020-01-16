<?php
require("dbCredentials.php");
require("actions.php");

// B10-2-B16(12237514)

$requestMethod = $_SERVER['REQUEST_METHOD'];

$dbConnection = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPasswd);

// Variável para armazenar a resposta
$response = NULL;

switch ($requestMethod) {
	case "POST":
		$action = $_POST["action"];
		if ($action == "register_new_user")
			$response = register_new_user($dbConnection);
		else if ($action == "register_new_recipient")
			$response = register_new_recipient($dbConnection);
		else if ($action == "register_new_recipient_sm")
			$response = register_new_recipient_sm($dbConnection);
		else if ($action == "delete_recipient")
			$response = delete_recipient($dbConnection);
		else if ($action == "delete_recipient_sm")
			$response = delete_recipient_sm($dbConnection);
		else if ($action == "change_message")
			$response = change_message($dbConnection);
		else if ($action == "change_message_sm")
			$response = change_message_sm($dbConnection);
		else if ($action == "add_message")
			$response = add_message($dbConnection);
		else if ($action == "add_message_sm")
			$response = add_message_sm($dbConnection);
		else if ($action == "link_recipient_to_message")
			$response = link_recipient_to_message($dbConnection);
		else if ($action == "link_recipient_to_message_sm")
			$response = link_recipient_to_message_sm($dbConnection);
		else if ($action == "retrieve_user_info")
			$response = retrieve_user_info($dbConnection);
		else if ($action == "retrieve_user_info_sm")
			$response = retrieve_user_info_sm($dbConnection);
		else if ($action == "update_user_info")
			$response = update_user_info($dbConnection);
		else if ($action == "update_user_info_sm")
			$response = update_user_info_sm($dbConnection);
		else if ($action == "retrieve_session_info")
			$response = retrieve_session_info($dbConnection);
		else if ($action == "retrieve_session_info_sm")
			$response = retrieve_session_info_sm($dbConnection);
		else if ($action == "retrieve_message_info")
			$response = retrieve_message_info($dbConnection);
		else if ($action == "retrieve_message_info_sm")
			$response = retrieve_message_info_sm($dbConnection);
		else if ($action == "retrieve_recipient_info")
			$response = retrieve_recipient_info($dbConnection);
		else if ($action == "update_message_info")
			$response = update_message_info($dbConnection);
		else if ($action == "update_message_info_sm")
			$response = update_message_info_sm($dbConnection);
		else if ($action == "open_session")
			$response = open_session($dbConnection);
		else if ($action == "close_session")
			$response = close_session($dbConnection);
		break;
	case "GET":
		$action = $_GET["action"];
		if ($action == "confirm_by_token")
			$response = confirm_by_token($dbConnection);
		else if ($action == "retrieve_session_info")
			$response = retrieve_session_info($dbConnection);
		break;
}

// Imprime a resposta
echo $response;

// Fecha a conexão
$dbConnection = null;