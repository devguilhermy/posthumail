<?php
require("utilities.php");

/*
	Esse é o arquivo em que as actions são implementadas.
	
	Cada uma requer dados específicos, que devem ser passados de acordo com o método (POST/GET):
	open_session [POST]:
		- Função: Abre uma sessão para o cliente especificado (loga)
		- Campos requeridos:
			client_email
			client_passwd
	
	close_session [POST]:
		- Função: Fecha uma sessão ativa (desloga)
		- Campos requeridos:
			*nenhum*
	
	register_new_user [POST]:
		- Função: Registra um novo usuário
		- Campos requeridos:
			email
			passwd
			name
			confirmation_interval (:= intervalo entre os emails de confirmação em segundos)
			deadline (:= limite para o envio do email póstumo em segundos) 
			message (:= mensagem póstuma)
	
	*Obs: Ações cujo nome termina em "_sm" NÃO requer os campos client_email e client_passwd; a autenticação é feita
	      através de sessão (PHP SESSION)
	      
	register_new_recipient(_sm) [POST]:
		- Função: Adiciona mais um destinatário à lista do cliente especificado
		- Campos requeridos:
			client_email*
			client_passwd*
			recipient_email
			recipient_name

	delete_recipient(_sm) [POST]:
		- Função: Deleta o destinatário especificado da lista do cliente especificado
		- Campos requeridos:
			client_email*
			client_passwd*
			recipient_email
			
	change_message(_sm) [POST]:
		- Função: Altera a mensagem do cliente especificado
		- Campos requeridos:
			client_email*
			client_passwd*
			new_message

	retrieve_user_info(_sm) [POST]:
		- Função: Obtém as informações a respeito do cliente especificado
		- Campos requeridos:
			client_email*
			client_passwd*

	update_user_info(_sm) [POST]:
		- Função: Atualiza as informações do cliente especificado
		- Campost requeridos:
			client_email*
			client_passwd*
			data (:= JSON contendo a(s) informação(ões) a ser atualizada(s) como chave e um novo valor para essa informação.

					Ex:

					{
						"name": "Guilérme Zezé de Camargo",
						"deadline": "765432"
					}

					Obs.: Somente as informações especificadas serão alteradas.
				)
	
	confirm_by_token [GET]:
		- Função: Confirma a vida do cliente "dono" do token
		- Campos requeridos:
			token
		
*/

// B10-2-B16(12237514)

// error_reporting(E_NOTICE);
//  **** Lembrete: Suprimir erros e warnings do PHP após o período de testes ****

function open_session($dbConnection) {
	$responseArray = [
		"action" => "open_session",
		"status" => "ok",
		"status_code" => 1500,
		"message" => "Session was opened."
	];

	if (
		!(array_key_exists("client_email", $_POST) &&
		array_key_exists("client_passwd", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 1501, "Client_email and client_passwd keys are required. At least one of them is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_POST['client_email'];
	$clientPasswd = $_POST['client_passwd'];

	$clientId = authenticate_user($dbConnection, $clientEmail, $clientPasswd);

	if ($clientId == -1) {
		$responseArray = set_response_error($responseArray, 1502, "Either client's email or password is incorrect. No account with such credentials was found.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Se houver uma sessão válida/aberta
	if (validate_session() == 0) {
		$responseArray = set_response_error($responseArray, 1503, "A session is already open. Call close_session action to close. ");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$_SESSION['client_email'] = $clientEmail;
	$_SESSION['client_id'] = $clientId;
	$_SESSION['opened_at'] = strtotime("now");
	$_SESSION['expiration'] = strtotime("now") + 5*60;

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function close_session($dbConnection) {
	$responseArray = [
		"action" => "open_session",
		"status" => "ok",
		"status_code" => 1500,
		"message" => "Session was closed."
	];

	session_destroy();

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function register_new_user($dbConnection) {
	// Define uma array associativa contendo a resposta-padrão da função
	// Ou seja, uma resposta a ser retornada caso não ocorra nenhum erro
	$responseArray = [
		"action" => "register_new_user",
		"status" => "ok",
		"status_code" => 1000,
		"message" => "New user registered."
	];

	// Obtém as informações requeridas para realizar a ação, se elas existirem
	if (
		!(array_key_exists("email", $_POST) &&
		array_key_exists("passwd", $_POST) &&
		array_key_exists("name", $_POST) &&
		array_key_exists("confirmation_interval", $_POST) &&
		array_key_exists("deadline", $_POST) &&
		array_key_exists("message", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 1001, "Email, passwd, name, confirmation_interval, deadline and message keys are required. At least one of them is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$email = $_POST['email'];
	$passwd = $_POST['passwd'];
	$name = $_POST['name'];
	$confirmationInterval = $_POST['confirmation_interval'];
	$deadline = $_POST['deadline'];
	$message = $_POST['message'];

	// Antes de registrar, deve-se verificar se existe ou não um usuário registrado com o email especificado
	$dbQuery = $dbConnection->prepare("SELECT * FROM `users` WHERE `email` LIKE :email");
	$dbQuery->bindValue(':email', $email);
	$dbQuery->execute();
	$users = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	if (count($users) > 0) {
		$responseArray = set_response_error($responseArray, 1002, "A user with same email address is already registered.");
		// Como houve um erro, interrompe a função retornando uma resposta
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Se não houver nenhum, o registro é efetuado
	// Do contrário, a função modifica a resposta-padrão e especifica o erro
	$dbQuery = $dbConnection->prepare("INSERT INTO `users` (`id`, `email`, `password`, `name`, `confirmation_interval`, `deadline`, `message`) VALUES (NULL, :email, MD5(:passwd), :name, :confirmation_interval, :deadline, :message)");
	$dbQuery->bindValue(':email', $email);
	$dbQuery->bindValue(':passwd', $passwd);
	$dbQuery->bindValue(':name', $name);
	// Atenção:
	// O confirmation_interval está em segundos
	// Portanto segue a fórmula pra calculá-lo a partir de um intervalo em dias
	// confirmation_interval = (dias - 1) * 86400
	$dbQuery->bindValue(':confirmation_interval', $confirmationInterval);
	// Atenção:
	// A deadline, assim como o confirmation_interval, está em segundos
	// Portanto segue a fórmula pra calculá-la a partir de uma quantidade de dias
	// deadline = (dias - 1) * 86400
	$dbQuery->bindValue(':deadline', $deadline);
	$dbQuery->bindValue(':message', $message);
	$dbQuery->execute();

	$responseArray['data'] = [
		"id" => $dbConnection->lastInsertId()
	];

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function register_new_recipient_sm($dbConnection) {
	$responseArray = [
		"action" => "register_new_recipient_sm",
		"status" => "ok",
		"status_code" => 1600,
		"message" => "New recipient added."
	];

	if (
		!(array_key_exists("recipient_email", $_POST) &&
		array_key_exists("recipient_name", $_POST))) {
		$responseArray = set_response_error($responseArray, 1601, "recipient_email and recipient_name keys are required. At least one of them is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$recipientEmail = $_POST['recipient_email'];
	$recipientName = $_POST['recipient_name'];

	// Verifica se a sessão é válida
	$session = validate_session();
	if ($session != 0) {
		$responseArray = set_response_error($responseArray, 1602, "Session is not valid or is not open.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_SESSION['client_email'];
	$clientId = $_SESSION['client_id'];

	// Antes de cadastrar, verifica se não há uma duplicata
	$dbQuery = $dbConnection->prepare("SELECT * FROM `recipients` WHERE `email` LIKE :email");
	$dbQuery->bindValue(':email', $recipientEmail);
	$dbQuery->execute();
	$recipient = $dbQuery->fetchAll(PDO::FETCH_ASSOC);
	
	if (count($recipient) > 0) {
		// Se o destinatário já estiver cadastrado para o cliente especificado, retorna um erro
		$responseArray = set_response_error($responseArray, 1103, "This recipient is already registered.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Cadastra o novo destinatário
	$dbQuery = $dbConnection->prepare("INSERT INTO `recipients` (`id`, `email`, `name`) VALUES (NULL, :email, :name)");
	$dbQuery->bindValue(':email', $recipientEmail);
	$dbQuery->bindValue(':name', $recipientName);
	$dbQuery->execute();

	$responseArray['data'] = [
		"id" => $dbConnection->lastInsertId()
	];
	
	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function register_new_recipient($dbConnection) {
	$responseArray = [
		"action" => "register_new_recipient",
		"status" => "ok",
		"status_code" => 1100,
		"message" => "New recipient added."
	];

	if (
		!(array_key_exists("client_email", $_POST) &&
		array_key_exists("client_passwd", $_POST) &&
		array_key_exists("recipient_email", $_POST) &&
		array_key_exists("recipient_name", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 1101, "Client_email, client_passwd, recipient_email and recipient_name keys are required. At least one of them is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_POST['client_email'];
	$clientPasswd = $_POST['client_passwd'];
	$recipientEmail = $_POST['recipient_email'];
	$recipientName = $_POST['recipient_name'];

	// Verifica as credenciais e pega o id do cliente especificado
	$clientId = authenticate_user($dbConnection, $clientEmail, $clientPasswd);

	if ($clientId == -1) {
		// Se o usuário não for válido, retorna um erro
		$responseArray = set_response_error($responseArray, 1102, "Either client's email or password is incorrect. No account with such credentials was found.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Antes de cadastrar, verifica se não há uma duplicata
	$dbQuery = $dbConnection->prepare("SELECT * FROM `recipients` WHERE `email` LIKE :email");
	$dbQuery->bindValue(':email', $recipientEmail);
	$dbQuery->execute();
	$recipient = $dbQuery->fetchAll(PDO::FETCH_ASSOC);
	
	if (count($recipient) > 0) {
		// Se o destinatário já estiver cadastrado para o cliente especificado, retorna um erro
		$responseArray = set_response_error($responseArray, 1103, "This recipient is already registered.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Cadastra o novo destinatário
	$dbQuery = $dbConnection->prepare("INSERT INTO `recipients` (`id`, `email`, `name`) VALUES (NULL, :email, :name)");
	$dbQuery->bindValue(':email', $recipientEmail);
	$dbQuery->bindValue(':name', $recipientName);
	$dbQuery->execute();

	$responseArray['data'] = [
		"id" => $dbConnection->lastInsertId()
	];
	
	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function delete_recipient($dbConnection) {
	$responseArray = [
		"action" => "delete_recipient",
		"status" => "ok",
		"status_code" => 1200,
		"message" => "Recipient deleted from client's list."
	];

	if (
		!(array_key_exists("client_email", $_POST) &&
		array_key_exists("client_passwd", $_POST) &&
		array_key_exists("recipient_email", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 1201, "Client_email, client_passwd, recipient_email keys are required. At least one of them is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_POST['client_email'];
	$clientPasswd = $_POST['client_passwd'];
	$recipientEmail = $_POST['recipient_email'];

	// Verifica as credenciais
	$clientId = authenticate_user($dbConnection, $clientEmail, $clientPasswd);

	if ($clientId == -1) {
		$responseArray = set_response_error($responseArray, 1202, "Either client's email or password is incorrect. No account with such credentials was found.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$dbQuery = $dbConnection->prepare("SELECT `id` FROM `recipients` WHERE `email` LIKE :email");
	$dbQuery->bindValue(':email', $recipientEmail);
	$dbQuery->execute();
	$recipient = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	if (count($recipient) == 0) {
		$responseArray = set_response_error($responseArray, 1203, "This client has no such recipient.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$recipientId = $recipient[0]['id'];

	// Deleta todos os links de mensagens do cliente especificado que o contém o destinatário especificado
	$dbQuery = $dbConnection->prepare("SELECT * FROM `messages` WHERE `client` = :client_id");
	$dbQuery->bindValue(":client_id", $clientId);
	$dbQuery->execute();
	$clientMessages = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	for ($i = 0; $i < count($i); $i += 1) {
		$messageId = $clientMessages[$i]['id'];
		$dbQuery = $dbConnection->prepare("DELETE FROM `recipient_message_link` WHERE `message_id` = :message_id AND `recipient_id` = :recipient_id");
		$dbQuery->bindValue(":message_id", $messageId);
		$dbQuery->bindValue(":recipient_id", $recipientId);
		$dbQuery->execute();
	}

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function delete_recipient_sm($dbConnection) {
	$responseArray = [
		"action" => "delete_recipient_sm",
		"status" => "ok",
		"status_code" => 1700,
		"message" => "Recipient deleted from client's list."
	];

	if (
		!(array_key_exists("recipient_email", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 1701, "recipient_email is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Verifica se a sessão é válida
	$session = validate_session();
	if ($session != 0) {
		$responseArray = set_response_error($responseArray, 1702, "Session is not valid or is not open.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_SESSION['client_email'];
	$clientId = $_SESSION['client_id'];
	$recipientEmail = $_POST['recipient_email'];

	$dbQuery = $dbConnection->prepare("SELECT `id` FROM `recipients` WHERE `email` LIKE :email");
	$dbQuery->bindValue(':email', $recipientEmail);
	$dbQuery->execute();
	$recipient = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	if (count($recipient) == 0) {
		$responseArray = set_response_error($responseArray, 1203, "This client has no such recipient.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$recipientId = $recipient[0]['id'];

	// Deleta todos os links de mensagens do cliente especificado que o contém o destinatário especificado
	$dbQuery = $dbConnection->prepare("SELECT * FROM `messages` WHERE `client` = :client_id");
	$dbQuery->bindValue(":client_id", $clientId);
	$dbQuery->execute();
	$clientMessages = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	for ($i = 0; $i < count($i); $i += 1) {
		$messageId = $clientMessages[$i]['id'];
		$dbQuery = $dbConnection->prepare("DELETE FROM `recipient_message_link` WHERE `message_id` = :message_id AND `recipient_id` = :recipient_id");
		$dbQuery->bindValue(":message_id", $messageId);
		$dbQuery->bindValue(":recipient_id", $recipientId);
		$dbQuery->execute();
	}

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function change_message($dbConnection) {
	$responseArray = [
		"action" => "change_message",
		"status" => "ok",
		"status_code" => 1300,
		"message" => "Client's message changed."
	];

	if (
		!(array_key_exists("client_email", $_POST) &&
		array_key_exists("client_passwd", $_POST) &&
		array_key_exists("new_message", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 1301, "Client_email, client_passwd and new_message keys are required. At least one of them is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_POST['client_email'];
	$clientPasswd = $_POST['client_passwd'];
	$newMessage = $_POST['new_message'];

	$clientId = authenticate_user($dbConnection, $clientEmail, $clientPasswd);

	if ($clientId == -1) {
		$responseArray = set_response_error($responseArray, 1302, "Either client's email or password is incorrect. No account with such credentials was found.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$dbQuery = $dbConnection->prepare("UPDATE `users` SET `message` = :new_message WHERE `id` = :client_id");
	$dbQuery->bindValue(":new_message", $newMessage);
	$dbQuery->bindValue(":client_id", $clientId);
	$dbQuery->execute();

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function change_message_sm($dbConnection) {
	$responseArray = [
		"action" => "change_message_sm",
		"status" => "ok",
		"status_code" => 1800,
		"message" => "Client's message changed."
	];

	if (
		!(array_key_exists("new_message", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 1801, "New_message key is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Verifica se a sessão é válida
	$session = validate_session();
	if ($session != 0) {
		$responseArray = set_response_error($responseArray, 1802, "Session is not valid or is not open.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_SESSION['client_email'];
	$clientId = $_SESSION['client_id'];
	$newMessage = $_POST['new_message'];

	$dbQuery = $dbConnection->prepare("UPDATE `users` SET `message` = :new_message WHERE `id` = :client_id");
	$dbQuery->bindValue(":new_message", $newMessage);
	$dbQuery->bindValue(":client_id", $clientId);
	$dbQuery->execute();

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function add_message($dbConnection) {
	$responseArray = [
		"action" => "add_message",
		"status" => "ok",
		"status_code" => 2300,
		"message" => "Added message to client's list."
	];

	if (
		!(array_key_exists("client_email", $_POST) &&
		array_key_exists("client_passwd", $_POST) &&
		array_key_exists("message", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 2301, "Client_email, client_passwd and message keys are required. At least one of them is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_POST['client_email'];
	$clientPasswd = $_POST['client_passwd'];
	$message = $_POST['message'];

	$clientId = authenticate_user($dbConnection, $clientEmail, $clientPasswd);

	if ($clientId == -1) {
		$responseArray = set_response_error($responseArray, 2302, "Either client's email or password is incorrect. No account with such credentials was found.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$dbQuery = $dbConnection->prepare("INSERT INTO `messages` (`id`, `client`, `message`) VALUES (NULL, :client_id, :message)");
	$dbQuery->bindValue(":client_id", $clientId);
	$dbQuery->bindValue(":message", $message);
	$dbQuery->execute();

	$responseArray['data'] = [
		"id" => $dbConnection->lastInsertId()
	];

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function add_message_sm($dbConnection) {
	$responseArray = [
		"action" => "add_message_sm",
		"status" => "ok",
		"status_code" => 2400,
		"message" => "Added message to client's list."
	];

	if (
		!(array_key_exists("message", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 2401, "Message key is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Verifica se a sessão é válida
	$session = validate_session();
	if ($session != 0) {
		$responseArray = set_response_error($responseArray, 2402, "Session is not valid or is not open.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_SESSION['client_email'];
	$clientId = $_SESSION['client_id'];
	$message = $_POST['message'];

	$dbQuery = $dbConnection->prepare("INSERT INTO `messages` (`id`, `client`, `message`) VALUES (NULL, :client_id, :message)");
	$dbQuery->bindValue(":client_id", $clientId);
	$dbQuery->bindValue(":message", $message);
	$dbQuery->execute();

	$responseArray['data'] = [
		"id" => $dbConnection->lastInsertId()
	];

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function link_recipient_to_message($dbConnection) {
	$responseArray = [
		"action" => "link_recipient_to_message",
		"status" => "ok",
		"status_code" => 2500,
		"message" => "Recipient linked to message."
	];

	if (
		!(array_key_exists("client_email", $_POST) &&
		array_key_exists("client_passwd", $_POST) &&
		array_key_exists("message_id", $_POST)&&
		array_key_exists("recipient_email", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 2501, "Client_email, client_passwd, message_id and recipient_email keys are required. At least one of them is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_POST['client_email'];
	$clientPasswd = $_POST['client_passwd'];
	$messageId = $_POST['message_id'];
	$recipientEmail = $_POST['recipient_email'];

	$clientId = authenticate_user($dbConnection, $clientEmail, $clientPasswd);

	if ($clientId == -1) {
		$responseArray = set_response_error($responseArray, 2502, "Either client's email or password is incorrect. No account with such credentials was found.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Verifica se a mensagem referida existe e pertence ao cliente em questão
	$dbQuery = $dbConnection->prepare("SELECT * FROM `messages` WHERE `id` = :message_id");
	$dbQuery->bindValue(":message_id", $messageId);
	$dbQuery->execute();
	$messageInfo = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	if (count($messageInfo) == 0) {
		$responseArray = set_response_error($responseArray, 2503, "There's no such message.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$messageInfo = $messageInfo[0];
	
	if ($messageInfo['client'] != $clientId || count($messageInfo) == 0) {
		$responseArray = set_response_error($responseArray, 2503, "The specified client does not own the specified message.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Verifica se o destinatário com esse email existe
	$dbQuery = $dbConnection->prepare("SELECT * FROM `recipients` WHERE `email` = :recipient_email");
	$dbQuery->bindValue(":recipient_email", $recipientEmail);
	$dbQuery->execute();
	$recipients = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	if (count($recipients) == 0) {
		$responseArray = set_response_error($responseArray, 2504, "There's no such recipient registered.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$recipientId = $recipients[0]['id'];

	// Se houver...
	$dbQuery = $dbConnection->prepare("INSERT INTO `recipient_message_link` (`id`, `message_id`, `recipient_id`) VALUES (NULL, :message_id, :recipient_id)");
	$dbQuery->bindValue(":message_id", $messageId);
	$dbQuery->bindValue(":recipient_id", $recipientId);
	$dbQuery->execute();

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function link_recipient_to_message_sm($dbConnection) {
	$responseArray = [
		"action" => "link_recipient_to_message_sm",
		"status" => "ok",
		"status_code" => 2600,
		"message" => "Added message to client's list."
	];

	if (
		!(array_key_exists("message_id", $_POST) &&
			array_key_exists("recipient_email", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 2601, "Message_id and recipient_email is required. At least one of them is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Verifica se a sessão é válida
	$session = validate_session();
	if ($session != 0) {
		$responseArray = set_response_error($responseArray, 2602, "Session is not valid or is not open.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_SESSION['client_email'];
	$clientId = $_SESSION['client_id'];
	$messageId = $_POST['message_id'];
	$recipientId = $_POST['recipient_email'];

	// Verifica se a mensagem referida pertence ao cliente em questão
	$dbQuery = $dbConnection->prepare("SELECT * FROM `messages` WHERE `id` = :message_id");
	$dbQuery->bindValue(":message_id", $messageId);
	$dbQuery->execute();
	$messageInfo = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	if (count($messageInfo) == 0) {
		$responseArray = set_response_error($responseArray, 2503, "There's no such message.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$messageInfo = $messageInfo[0];
	
	if ($messageInfo['client'] != $clientId || count($messageInfo) == 0) {
		$responseArray = set_response_error($responseArray, 2503, "The specified client does not own the specified message.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Verifica se o destinatário com esse email existe
	$dbQuery = $dbConnection->prepare("SELECT * FROM `recipients` WHERE `email` = :recipient_email");
	$dbQuery->bindValue(":recipient_email", $recipientEmail);
	$dbQuery->execute();
	$recipients = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	if (count($recipients) == 0) {
		$responseArray = set_response_error($responseArray, 2504, "There's no such recipient registered.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$recipientId = $recipients[0]['id'];

	// Se houver...
	$dbQuery = $dbConnection->prepare("INSERT INTO `recipient_message_link` (`id`, `message_id`, `recipient_id`) VALUES (NULL, :message_id, :recipient_id)");
	$dbQuery->bindValue(":message_id", $messageId);
	$dbQuery->bindValue(":recipient_id", $recipientId);
	$dbQuery->execute();

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function unlink_recipient_from_message($dbConnection) {
	$responseArray = [
		"action" => "unlink_recipient_from_message",
		"status" => "ok",
		"status_code" => 3100,
		"message" => "Recipient unlinked from message."
	];

	if (
		!(array_key_exists("client_email", $_POST) &&
		array_key_exists("client_passwd", $_POST) &&
		array_key_exists("message_id", $_POST)&&
		array_key_exists("recipient_id", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 3101, "Client_email, client_passwd, message_id and recipient_id keys are required. At least one of them is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_POST['client_email'];
	$clientPasswd = $_POST['client_passwd'];
	$messageId = $_POST['message_id'];
	$recipientId = $_POST['recipient_id'];

	$clientId = authenticate_user($dbConnection, $clientEmail, $clientPasswd);

	if ($clientId == -1) {
		$responseArray = set_response_error($responseArray, 3102, "Either client's email or password is incorrect. No account with such credentials was found.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Verifica se a mensagem referida existe e pertence ao cliente em questão
	$dbQuery = $dbConnection->prepare("SELECT * FROM `messages` WHERE `id` = :message_id");
	$dbQuery->bindValue(":message_id", $messageId);
	$dbQuery->execute();
	$messageInfo = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	if (count($messageInfo) == 0) {
		$responseArray = set_response_error($responseArray, 3103, "There's no such message.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$messageInfo = $messageInfo[0];
	
	if ($messageInfo['client'] != $clientId || count($messageInfo) == 0) {
		$responseArray = set_response_error($responseArray, 3104, "The specified client does not own the specified message.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Verifica se o destinatário com esse id existe
	$dbQuery = $dbConnection->prepare("SELECT * FROM `recipients` WHERE `id` = :recipient_id");
	$dbQuery->bindValue(":recipient_id", $recipientId);
	$dbQuery->execute();
	$recipients = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	if (count($recipients) == 0) {
		$responseArray = set_response_error($responseArray, 3105, "There's no such recipient registered.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Se houver...
	$dbQuery = $dbConnection->prepare("DELETE FROM `recipient_message_link` WHERE `client_id`");
	$dbQuery->bindValue(":message_id", $messageId);
	$dbQuery->bindValue(":recipient_id", $recipientId);
	$dbQuery->execute();

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function unlink_recipient_from_message_sm($dbConnection) {
	$responseArray = [
		"action" => "unlink_recipient_from_message_sm",
		"status" => "ok",
		"status_code" => 3200,
		"message" => "Recipient unlinked from message."
	];

	if (
		!(array_key_exists("message_id", $_POST) &&
		array_key_exists("recipient_id", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 3201, "Client_email, client_passwd, message_id and recipient_id keys are required. At least one of them is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Verifica se a sessão é válida
	$session = validate_session();
	if ($session != 0) {
		$responseArray = set_response_error($responseArray, 3202, "Session is not valid or is not open.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_SESSION['client_email'];
	$clientId = $_SESSION['client_id'];
	$messageId = $_POST['message_id'];
	$recipientId = $_POST['recipient_id'];


	// Verifica se a mensagem referida existe e pertence ao cliente em questão
	$dbQuery = $dbConnection->prepare("SELECT * FROM `messages` WHERE `id` = :message_id");
	$dbQuery->bindValue(":message_id", $messageId);
	$dbQuery->execute();
	$messageInfo = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	if (count($messageInfo) == 0) {
		$responseArray = set_response_error($responseArray, 3203, "There's no such message.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$messageInfo = $messageInfo[0];
	
	if ($messageInfo['client'] != $clientId || count($messageInfo) == 0) {
		$responseArray = set_response_error($responseArray, 3204, "The specified client does not own the specified message.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Verifica se o destinatário com esse id existe
	$dbQuery = $dbConnection->prepare("SELECT * FROM `recipients` WHERE `id` = :recipient_id");
	$dbQuery->bindValue(":recipient_id", $recipientId);
	$dbQuery->execute();
	$recipients = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	if (count($recipients) == 0) {
		$responseArray = set_response_error($responseArray, 3205, "There's no such recipient registered.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Se houver...
	$dbQuery = $dbConnection->prepare("DELETE FROM `recipient_message_link` WHERE `client_id`");
	$dbQuery->bindValue(":message_id", $messageId);
	$dbQuery->bindValue(":recipient_id", $recipientId);
	$dbQuery->execute();

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function confirm_by_token($dbConnection) {
	$responseArray = [
		"action" => "confirm_by_token",
		"status" => "ok",
		"status_code" => 1400,
		"message" => "Client's life confirmed."
	];

	if (
		!(array_key_exists("token", $_GET))
	) {
		$responseArray = set_response_error($responseArray, 1401, "Token is required.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$token = $_GET['token'];

	// Validar token
	$dbQuery = $dbConnection->prepare("SELECT `client_id` FROM `tokens` WHERE `token_nature` = 'confirmation' AND `token_name` = :token_name AND `expiration` >= CURRENT_TIMESTAMP");
	$dbQuery->bindValue(":token_name", $token);
	$dbQuery->execute();
	$tokens = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	if (count($tokens) == 0) {
		$responseArray = set_response_error($responseArray, 1402, "This token either does not exist, is not valid for life's confirmation, was used before or is expired.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Se for válido, atualiza a data de última confirmação do cliente dono do token
	$clientId = $tokens[0]['client_id'];
	$dbQuery = $dbConnection->prepare("UPDATE `users` SET `last_confirmation` = CURRENT_DATE WHERE `id` = :client_id");
	$dbQuery->bindValue(":client_id", $clientId);
	$dbQuery->execute();

	// Agora, exclui-se o token utilizado
	$dbQuery = $dbConnection->prepare("DELETE FROM `tokens` WHERE `token_name` = :token_name");
	$dbQuery->bindValue(":token_name", $token);
	$dbQuery->execute();

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function retrieve_user_info($dbConnection) {
	$responseArray = [
		"action" => "retrieve_user_info",
		"status" => "ok",
		"status_code" => 1900,
		"message" => "User info retrieved.",
		"data" => ""
	];

	if (
		!(array_key_exists("client_email", $_POST) &&
		array_key_exists("client_passwd", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 1901, "Client_email and client_passwd keys are required. At least one of them is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_POST['client_email'];
	$clientPasswd = $_POST['client_passwd'];

	$clientId = authenticate_user($dbConnection, $clientEmail, $clientPasswd);

	if ($clientId == -1) {
		$responseArray = set_response_error($responseArray, 1902, "Either client's email or password is incorrect. No account with such credentials was found.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$dbQuery = $dbConnection->prepare("SELECT * FROM `users` WHERE `id` = :client_id");
	$dbQuery->bindValue(":client_id", $clientId);
	$dbQuery->execute();
	$queryResultUserInfo = $dbQuery->fetchAll(PDO::FETCH_ASSOC)[0];

	$dbQuery = $dbConnection->prepare("SELECT * FROM `messages` WHERE `client` = :client_id");
	$dbQuery->bindValue(":client_id", $clientId);
	$dbQuery->execute();
	$queryResultUserMessages = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	$queryResultUserInfo["messages"] = Array();

	for ($i = 0; $i < count($queryResultUserMessages); $i += 1) {
		$dbQuery = $dbConnection->prepare("SELECT COUNT(*) FROM `recipient_message_link` WHERE `message_id` = :message_id");
		$dbQuery->bindValue(":message_id", (int) $queryResultUserMessages[$i]['id']);
		$dbQuery->execute();
		$recipientsCount = $dbQuery->fetch(PDO::FETCH_ASSOC)["COUNT(*)"];
		$queryResultUserMessages[$i]['recipients_count'] = $recipientsCount;
		unset($queryResultUserMessages[$i]['client']);
		array_push($queryResultUserInfo["messages"], $queryResultUserMessages[$i]);
	}

	// Exclui a chave 'password' da array de dados
	unset($queryResultUserInfo['password']);

	$responseArray['data'] = $queryResultUserInfo;
	
	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function retrieve_user_info_sm($dbConnection) {
	$responseArray = [
		"action" => "retrieve_user_info_sm",
		"status" => "ok",
		"status_code" => 2000,
		"message" => "User info retrieved.",
		"data" => ""
	];

	// Verifica se a sessão é válida
	$session = validate_session();
	if ($session != 0) {
		$responseArray = set_response_error($responseArray, 2001, "Session is not valid or is not open.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_SESSION['client_email'];
	$clientId = $_SESSION['client_id'];

		$dbQuery = $dbConnection->prepare("SELECT * FROM `users` WHERE `id` = :client_id");
	$dbQuery->bindValue(":client_id", $clientId);
	$dbQuery->execute();
	$queryResultUserInfo = $dbQuery->fetchAll(PDO::FETCH_ASSOC)[0];

	$dbQuery = $dbConnection->prepare("SELECT * FROM `messages` WHERE `client` = :client_id");
	$dbQuery->bindValue(":client_id", $clientId);
	$dbQuery->execute();
	$queryResultUserMessages = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	$queryResultUserInfo["messages"] = Array();

	for ($i = 0; $i < count($queryResultUserMessages); $i += 1) {
		$dbQuery = $dbConnection->prepare("SELECT COUNT(*) FROM `recipient_message_link` WHERE `message_id` = :message_id");
		$dbQuery->bindValue(":message_id", (int) $queryResultUserMessages[$i]['id']);
		$dbQuery->execute();
		$recipientsCount = $dbQuery->fetch(PDO::FETCH_ASSOC)["COUNT(*)"];
		$queryResultUserMessages[$i]['recipients_count'] = $recipientsCount;
		unset($queryResultUserMessages[$i]['client']);
		array_push($queryResultUserInfo["messages"], $queryResultUserMessages[$i]);
	}

	// Exclui a chave 'password' da array de dados
	unset($queryResultUserInfo['password']);

	$responseArray['data'] = $queryResultUserInfo;
	
	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function update_user_info($dbConnection) {
	$responseArray = [
		"action" => "update_user_info",
		"status" => "ok",
		"status_code" => 2100,
		"message" => "User info updated.",
	];


	if (
		!(array_key_exists("client_email", $_POST) &&
		array_key_exists("client_passwd", $_POST) &&
		array_key_exists("data", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 2101, "Client_email, client_passwd and data keys are required. At least one of them is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_POST['client_email'];
	$clientPasswd = $_POST['client_passwd'];
	$receivedData = $_POST['data'];

	$clientId = authenticate_user($dbConnection, $clientEmail, $clientPasswd);

	if ($clientId == -1) {
		$responseArray = set_response_error($responseArray, 2102, "Either client's email or password is incorrect. No account with such credentials was found.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	if (!isJson($receivedData)) {
		$responseArray = set_response_error($responseArray, 2103, "Data passed is not a valid JSON.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// JSON recebido, convertido em array
	$receivedArray = json_decode($receivedData, true);

	// Verifica se as chaves passadas são válidas, isso é, correspondem a um campo válido da tabela 'users'
	$validKeys = ["email", "password", "name", "confirmation_interval", "deadline", "message"];
	// $currentKeys := chaves passadas
	$currentKeys = array_keys($receivedArray);

	for ($i = 0; $i < count($currentKeys); $i += 1) {
		$currentKey = $currentKeys[$i];
		if (array_search($currentKey, $validKeys) === false) {
			$responseArray = set_response_error($responseArray, 2104, "$currentKey is either not valid or not eligible for updating.");
			return json_encode($responseArray, JSON_PRETTY_PRINT);
		}
	}

	// Se todas forem válidas, atualizam-se os dados
	for ($i = 0; $i < count($currentKeys); $i += 1) {
		$currentKey = $currentKeys[$i];

		$dbQuery = $dbConnection->prepare("UPDATE `users` SET $currentKey = :key_value WHERE `id` = :client_id");

		// if ($currentKey == "password")
		// 	$dbQuery = $dbConnection->prepare("UPDATE `users` SET :key = MD5(:key_value) WHERE `id` = :client_id");

		$dbQuery->bindValue(":key_value", $receivedArray[$currentKey]);
		$dbQuery->bindValue(":client_id", $clientId);
		echo "Setting $currentKey to '".$receivedArray[$currentKey]."'. :)";
		$dbQuery->execute();
	}

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function update_user_info_sm($dbConnection) {
	$responseArray = [
		"action" => "update_user_info_sm",
		"status" => "ok",
		"status_code" => 2200,
		"message" => "User info updated.",
	];


	if (
		!(array_key_exists("data", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 2201, "Data key is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Verifica se a sessão é válida
	$session = validate_session();
	if ($session != 0) {
		$responseArray = set_response_error($responseArray, 2202, "Session is not valid or is not open.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_SESSION['client_email'];
	$clientId = $_SESSION['client_id'];

	if (!isJson($receivedData)) {
		$responseArray = set_response_error($responseArray, 2203, "Data passed is not a valid JSON.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// JSON recebido, convertido em array
	$receivedArray = json_decode($receivedData, true);

	// Verifica se as chaves passadas são válidas, isso é, correspondem a um campo válido da tabela 'users'
	$validKeys = ["email", "password", "name", "confirmation_interval", "deadline", "message"];
	// $currentKeys := chaves passadas
	$currentKeys = array_keys($receivedArray);

	for ($i = 0; $i < count($currentKeys); $i += 1) {
		$currentKey = $currentKeys[$i];
		if (array_search($currentKey, $validKeys) === false) {
			$responseArray = set_response_error($responseArray, 2204, "$currentKey is either not valid or not eligible for updating.");
			return json_encode($responseArray, JSON_PRETTY_PRINT);
		}
	}

	// Se todas forem válidas, atualizam-se os dados
	for ($i = 0; $i < count($currentKeys); $i += 1) {
		$currentKey = $currentKeys[$i];

		$dbQuery = $dbConnection->prepare("UPDATE `users` SET $currentKey = :key_value WHERE `id` = :client_id");

		if ($currentKey == "password")
			$dbQuery = $dbConnection->prepare("UPDATE `users` SET `password` = MD5(:key_value) WHERE `id` = :client_id");

		$dbQuery->bindValue(":key_value", $receivedArray[$currentKey]);
		$dbQuery->bindValue(":client_id", $clientId);
		$dbQuery->execute();
	}

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function retrieve_session_info($dbConnection) {
	$responseArray = [
		"action" => "retrieve_session_info",
		"status" => "ok",
		"status_code" => 2700,
		"message" => "Session info retrieved."
	];

	if (validate_session() == 1 || validate_session() == 2) {
		$responseArray = set_response_error($responseArray, 2701, "No open session.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$responseArray["data"] = [
		"client_id" => $_SESSION['client_id'],
		"client_email" => $_SESSION['client_email'],
		"expiration_in" => ($_SESSION['expiration'] - strtotime("now"))
	];

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function retrieve_message_info($dbConnection) {
	$responseArray = [
		"action" => "retrieve_message_info",
		"status" => "ok",
		"status_code" => 2800,
		"message" => "Message info retrieved."
	];

	if (
		!(array_key_exists("client_email", $_POST) &&
		array_key_exists("client_passwd", $_POST) &&
		array_key_exists("message_id", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 2801, "Client_email, client_passwd and message_id keys are required. At least one of them is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_POST['client_email'];
	$clientPasswd = $_POST['client_passwd'];
	$messageId = $_POST['message_id'];

	$clientId = authenticate_user($dbConnection, $clientEmail, $clientPasswd);

	if ($clientId == -1) {
		$responseArray = set_response_error($responseArray, 2802, "Either client's email or password is incorrect. No account with such credentials was found.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$dbQuery = $dbConnection->prepare("SELECT `id`, `message` FROM `messages` WHERE `client` = :client_id AND `id` = :message_id");
	$dbQuery->bindValue(":client_id", $clientId);
	$dbQuery->bindValue(":message_id", $messageId);
	$dbQuery->execute();
	$messages = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	if (count($messages) == 0) {
		$responseArray = set_response_error($responseArray, 2803, "This message does not exist or is not owned by the specified client.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$message = $messages[0];

	$dbQuery = $dbConnection->prepare("SELECT * FROM `recipient_message_link` WHERE `message_id` = :message_id");
	$dbQuery->bindValue(":message_id", $messageId);
	$dbQuery->execute();
	$messageRecipients = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	for ($i = 0; $i < count($messageRecipients); $i += 1) {
		unset($messageRecipients[$i]['message_id']);
		unset($messageRecipients[$i]['id']);
		$dbQuery = $dbConnection->prepare("SELECT * FROM `recipients` WHERE `id` = :recipient_id");
		$dbQuery->bindValue(":recipient_id", (int) $messageRecipients[$i]["recipient_id"]);
		$dbQuery->execute();
		$recipientEmail = $dbQuery->fetch(PDO::FETCH_ASSOC)["email"];
		$messageRecipients[$i]['email'] = $recipientEmail;
		$messageRecipients[$i]['id'] = $messageRecipients[$i]['recipient_id'];
		unset($messageRecipients[$i]['recipient_id']);
	}

	$message['recipients'] = $messageRecipients;
	$responseArray['data'] = $message;

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function retrieve_message_info_sm($dbConnection) {
	$responseArray = [
		"action" => "retrieve_message_info",
		"status" => "ok",
		"status_code" => 2900,
		"message" => "Message info retrieved."
	];

	if (
		!(array_key_exists("message_id", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 2901, "Message_id key is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Verifica se a sessão é válida
	$session = validate_session();
	if ($session != 0) {
		$responseArray = set_response_error($responseArray, 2902, "Session is not valid or is not open.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_SESSION['client_email'];
	$clientId = $_SESSION['client_id'];
	$messageId = $_POST['message_id'];

	$dbQuery = $dbConnection->prepare("SELECT `id`, `message` FROM `messages` WHERE `client` = :client_id AND `id` = :message_id");
	$dbQuery->bindValue(":client_id", $clientId);
	$dbQuery->bindValue(":message_id", $messageId);
	$dbQuery->execute();
	$messages = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	if (count($messages) == 0) {
		$responseArray = set_response_error($responseArray, 2803, "This message does not exist or is not owned by the specified client.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$message = $messages[0];

	$dbQuery = $dbConnection->prepare("SELECT * FROM `recipient_message_link` WHERE `message_id` = :message_id");
	$dbQuery->bindValue(":message_id", $messageId);
	$dbQuery->execute();
	$messageRecipients = $dbQuery->fetchAll(PDO::FETCH_ASSOC);

	$messageRecipientsId = Array();
	for ($i = 0; $i < count($messageRecipients); $i += 1) {
		array_push($messageRecipientsId, $messageRecipients[$i]["recipient_id"]);
	}

	$message['recipients'] = $messageRecipientsId;
	$responseArray['data'] = $message;

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function retrieve_recipient_info($dbConnection) {
	$responseArray = [
		"action" => "retrieve_recipient_info",
		"status" => "ok",
		"status_code" => 3300,
		"message" => "Recipient info retrieved."
	];

	if (
		!(array_key_exists("recipient", $_POST))
	) {
		$responseArray = set_response_error($responseArray, 3301, "Recipient key is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$recipient = $_POST['recipient'];

	$identifyRecipientBy = strpos($recipient, "@") !== false?'email':'id';

	if ($identifyRecipientBy == 'email') {
		$dbQuery = $dbConnection->prepare("SELECT * FROM `recipients` WHERE `email` = :recipient_email");
		$dbQuery->bindValue(":recipient_email", $recipient);
		$dbQuery->execute();
	} else if ('id') {
		$dbQuery = $dbConnection->prepare("SELECT * FROM `recipients` WHERE `id` = :recipient_id");
		$dbQuery->bindValue(":recipient_id", $recipient);
		$dbQuery->execute();
	}

	$data = $dbQuery->fetch(PDO::FETCH_ASSOC);

	if ($data == NULL) {
		$responseArray = set_response_error($responseArray, 3302, "No such recipient.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$responseArray['data'] = $data;
	$responseArray['data']['identifiedBy'] = $identifyRecipientBy;

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function update_message_info($dbConnection) {
	$responseArray = [
		"action" => "update_message_info",
		"status" => "ok",
		"status_code" => 2900,
		"message" => "Message info updated."
	];

	if (
		!(array_key_exists("client_email", $_POST) &&
		array_key_exists("client_passwd", $_POST) &&
		array_key_exists("message_id", $_POST) &&
		array_key_exists("message", $_POST)
		)
	) {
		$responseArray = set_response_error($responseArray, 2901, "Client_email, client_passwd, message_id and message keys are required. At least one of them is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_POST['client_email'];
	$clientPasswd = $_POST['client_passwd'];
	$message = $_POST['message'];
	$messageId = $_POST['message_id'];

	$clientId = authenticate_user($dbConnection, $clientEmail, $clientPasswd);

	if ($clientId == -1) {
		$responseArray = set_response_error($responseArray, 2902, "Either client's email or password is incorrect. No account with such credentials was found.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$dbQuery = $dbConnection->prepare("UPDATE `messages` SET `message` = :message WHERE `id` = :message_id AND `client` = :client_id");
	$dbQuery->bindValue(":message", $message);
	$dbQuery->bindValue("message_id", $messageId);
	$dbQuery->bindValue(":client_id", $clientId);
	$dbQuery->execute();
	$affectedRows = $dbQuery->rowCount();

	if ($affectedRows == 0) {
		$responseArray = set_response_error($responseArray, 2903, "This message does not exist or is not owned by the specified client.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}

function update_message_info_sm($dbConnection) {
	$responseArray = [
		"action" => "update_message_info",
		"status" => "ok",
		"status_code" => 3000,
		"message" => "Message info updated."
	];

	if (
		!(array_key_exists("message_id", $_POST) &&
		array_key_exists("message", $_POST)
		)
	) {
		$responseArray = set_response_error($responseArray, 3001, "Message_id and message keys are required. At least one of them is missing.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	// Verifica se a sessão é válida
	$session = validate_session();
	if ($session != 0) {
		$responseArray = set_response_error($responseArray, 3002, "Session is not valid or is not open.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	$clientEmail = $_SESSION['client_email'];
	$clientId = $_SESSION['client_id'];
	$message = $_POST['message'];
	$messageId = $_POST['message_id'];

	// Verifica se a mensagem existe e se seu dono é o cliente especificado
	$dbQuery = $dbConnection->prepare("SELECT * FROM `messages` WHERE `id` = :message_id AND `client` = :client_id");
	$dbQuery->bindValue(":message", $message);
	$dbQuery->bindValue("message_id", $messageId);
	$dbQuery->bindValue(":client_id", $clientId);
	$dbQuery->execute();

	$dbQuery = $dbConnection->prepare("UPDATE `messages` SET `message` = :message WHERE `id` = :message_id AND `client` = :client_id");
	$dbQuery->bindValue(":message", $message);
	$dbQuery->bindValue("message_id", $messageId);
	$dbQuery->bindValue(":client_id", $clientId);
	$dbQuery->execute();
	$affectedRows = $dbQuery->rowCount();

	if ($affectedRows == 0) {
		$responseArray = set_response_error($responseArray, 3003, "This message either does not exist or is not owned by the specified client.");
		return json_encode($responseArray, JSON_PRETTY_PRINT);
	}

	return json_encode($responseArray, JSON_PRETTY_PRINT);
}