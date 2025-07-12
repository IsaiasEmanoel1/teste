<?php
require_once 'engine/init.php';

// Inicia a sessão APENAS se ela ainda não estiver ativa.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PARTE 1: LÓGICA PARA O CLIENTE DO JOGO (Tibia 11) - Intacta
if ($_SERVER['HTTP_USER_AGENT'] == "Mozilla/5.0" && $config['TFSVersion'] === 'TFS_10') {
    // ... (Toda a sua lógica do webservice permanece aqui, intacta e funcional)
    function jsonError($message, $code = 3) {
        die(json_encode(array('errorCode' => $code, 'errorMessage' => $message)));
    }

    header("Content-Type: application/json");
    $input = file_get_contents("php://input");

    if (strlen($input) > 10) {
        $jsonObject = json_decode($input);
        $username = sanitize($jsonObject->accountname);
        $password = SHA1($jsonObject->password);
        $token = (isset($jsonObject->token)) ? sanitize($jsonObject->token) : false;
        
        $fields = '`id`, `premdays`, `secret`';
        if ($config['twoFactorAuthenticator']) $fields .= ', `secret`';

        $account = mysql_select_single("SELECT {$fields} FROM `accounts` WHERE `name`='{$username}' AND `password`='{$password}' LIMIT 1;");
        if ($account === false) {
            jsonError('Wrong username and/or password.');
        }

        if ($config['twoFactorAuthenticator'] === true && is_array($account) && $account['secret'] !== null) {
            if ($token === false) {
                jsonError('Submit a valid two-factor authentication token.', 6);
            } else {
                require_once("engine/function/rfc6238.php");
                if (TokenAuth6238::verify($account['secret'], $token) !== true) {
                    jsonError('Two-factor authentication failed, token is wrong.', 6);
                }
            }
        }

        $players = mysql_select_multi("SELECT `name`, `sex` FROM `players` WHERE `account_id`='".$account['id']."';");
        if ($players !== false) {

            $gameserver = $config['gameserver'];
            $sessionKey = $username."\n".$jsonObject->password;
            if (is_array($account) && strlen($account['secret']) > 5) $sessionKey .= "\n".$token."\n".floor(time() / 30);
            $response = array(
                'session' => array(
                    'fpstracking' => false,
                    'isreturner' => true,
                    'returnernotification' => false,
                    'showrewardnews' => false,
                    'sessionkey' => $sessionKey,
                    'lastlogintime' => 0,
                    'ispremium' => (is_array($account) && $account['premdays'] > 0) ? true : false,
                    'premiumuntil' => time() + ((is_array($account)) ? $account['premdays'] * 86400 : 0),
                    'status' => 'active'
                ),
                'playdata' => array(
                    'worlds' => array(
                        array(
                            'id' => 0,
                            'name' => $gameserver['name'],
                            'externaladdress' => $gameserver['ip'],
                            'externalport' => $gameserver['port'],
                            'previewstate' => 0,
                            'location' => 'ALL',
                            'externaladdressunprotected' => $gameserver['ip'],
                            'externaladdressprotected' => $gameserver['ip'],
                            'anticheatprotection' => false
                        )
                    ),
                    'characters' => array()
                )
            );

            foreach ($players as $player) {
                $response['playdata']['characters'][] = array(
                    'worldid' => 0,
                    'name' => $player['name'],
                    'ismale' => ($player['sex'] === 1) ? true : false,
                    'tutorial' => false
                );
            }
            die(json_encode($response));
        } else {
            jsonError("Character list is empty.");
        }
    } else {
        jsonError("Unrecognized event.");
    }
}
// FIM DA PARTE 1


// PARTE 2: LÓGICA PARA O LOGIN DO SITE (AJAX) - CORRIGIDA
$errors = []; // Inicia o array de erros.

if (empty($_POST) === false) {

    if ($config['log_ip']) {
        znote_visitor_insert_detailed_data(5);
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validação do Token movida para o topo para que possamos regenerá-lo se outros erros ocorrerem
    if ($config['use_token'] && !Token::isValid($_POST['token'])) {
        $errors[] = 'Token is invalid.';
    } else {
        if (empty($username) || empty($password)) {
            $errors[] = 'Você precisa digitar um nome de usuário e uma senha.';
        } else if (strlen($username) > 32 || strlen($password) > 64) {
            $errors[] = 'Username or password is too long.';
        } else if (user_exist($username) === false) {
            $errors[] = 'Falha ao entrar na sua conta, os detalhes estão corretos? Você tem  ja se <a href=\'register.php\'>registrou</a>?';
        } else {
            if ($config['TFSVersion'] == 'TFS_02' || $config['TFSVersion'] == 'TFS_10') $login = user_login($username, $password);
            else if ($config['TFSVersion'] == 'TFS_03') $login = user_login_03($username, $password);
            else $login = false;
            
            if ($login === false) {
                $errors[] = 'A combinação de nome de usuário e senha está incorreta.';
            } else {
                $status = false;
                if ($config['mailserver']['register']) {
                    $authenticate = mysql_select_single("SELECT `id` FROM `znote_accounts` WHERE `account_id`='$login' AND `active`='1' LIMIT 1;");
                    if ($authenticate !== false) {
                        $status = true;
                    } else {
                        $errors[] = "Your account is not activated. An email should have been sent to you when you registered. Please find it and click the activation link to activate your account.";
                    }
                } else $status = true;

                if ($status) {
                    if ($config['TFSVersion'] == 'TFS_10' && $config['twoFactorAuthenticator']) {
                        require_once("engine/function/rfc6238.php");
                        $authcode = (isset($_POST['authcode'])) ? getValue($_POST['authcode']) : false;
                        $query = mysql_select_single("SELECT `a`.`secret` AS `secret`, `za`.`secret` AS `znote_secret` FROM `accounts` AS `a` INNER JOIN `znote_accounts` AS `za` ON `a`.`id` = `za`.`account_id` WHERE `a`.`id`='".(int)$login."' LIMIT 1;");

                        if ($query !== false) {
                            if ($query['secret'] !== NULL) {
                                if (TokenAuth6238::verify($query['secret'], $authcode) !== true) {
                                    $errors[] = "Submitted Two-Factor Authentication token is wrong.";
                                    $status = false;
                                }
                            } else if ($query['znote_secret'] !== NULL && $authcode !== false && !empty($authcode)) {
                                if (TokenAuth6238::verify($query['znote_secret'], $authcode)) {
                                    mysql_update("UPDATE `accounts` SET `secret`= '".$query['znote_secret']."' WHERE `id`='$login';");
                                } else {
                                    $errors[] = "Activating Two-Factor authentication failed.";
                                    $status = false;
                                }
                            }
                        }
                    }

                    if ($status) {
                        setSession('user_id', $login);
                        $znote_data = user_znote_account_data($login);
                        if ($znote_data !== false && $znote_data['ip'] == 0) {
                            $update_data = array('ip' => getIPLong());
                            user_update_znote_account($update_data);
                        }
                    }
                }
            }
        }
    }
} else {
    $errors[] = 'Formulário inválido.';
}

// Resposta Final para o AJAX
header('Content-Type: application/json');

if (empty($errors)) {
    echo json_encode(['success' => true]);
} else {
    $response = ['success' => false, 'errors' => $errors];
    
    // ----- ESTA É A CORREÇÃO FINAL E DEFINITIVA -----
    if ($config['use_token']) {
        ob_start(); // 1. Inicia o buffer: Começa a "escutar" a saída
        Token::create(); // 2. A função imprime o <input>... mas ele é capturado pelo buffer
        ob_end_clean(); // 3. Limpa o buffer: Joga fora o <input> capturado, sem imprimir na tela.
        
        $response['new_token'] = $_SESSION['token']; // 4. Pega o novo token da sessão (que a função atualizou) para enviar
    }
    
    echo json_encode($response);
}
exit();
?>