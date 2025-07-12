<?php
require_once 'engine/init.php';
logged_in_redirect();
include 'layout/overall/header.php';
require_once('config.countries.php');

// A sua lógica de validação original permanece 100% intacta.
if (empty($_POST) === false) {
    // $_POST['']
    $required_fields = array('username', 'password', 'password_again', 'email', 'selected');
    foreach($_POST as $key=>$value) {
        if (empty($value) && in_array($key, $required_fields) === true) {
            $errors[] = 'Você precisa preencher todos os campos.';
            break 1;
        }
    }

    if (empty($errors) === true) {
        if (!Token::isValid($_POST['token'])) {
            $errors[] = 'Token is invalid.';
        }
        if ($config['use_captcha']) {

            $captcha = (isset($_POST['g-recaptcha-response'])) ? $_POST['g-recaptcha-response'] : false;

            if(!$captcha) {

                $errors[] = 'Por favor, verifique o formulário captcha.';

            } else {

                $secretKey = $config['captcha_secret_key'];

                $ip = $_SERVER['REMOTE_ADDR'];

                // curl start

                $curl_connection = curl_init("https://www.google.com/recaptcha/api/siteverify");

                $post_string = "secret=".$secretKey."&response=".$captcha."&remoteip=".$ip;

                curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 5);

                curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);

                curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);

                curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 0);

                curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);

                $response = curl_exec($curl_connection);

                curl_close($curl_connection);

                // Curl end

                $responseKeys = json_decode($response,true);

                if(intval($responseKeys["success"]) !== 1) {

                $errors[] = 'Captcha failed.';

                }

            }

        }
        if (user_exist($_POST['username']) === true) {
            $errors[] = 'Desculpe, esse nome de usuário já existe.';
        }
        $isNoob = in_array(strtolower($_POST['username']), $config['page_admin_access']) ? true : false;
        if ($isNoob) {
            $errors[] = 'Este nome de conta está bloqueado para registro.';
        }
        if (preg_match("/^[a-zA-Z0-9]+$/", $_POST['username']) == false) {
            $errors[] = 'O nome da sua conta só pode conter os caracteres de a-z, A-Z e 0-9.';
        }
        $resname = explode(" ", $_POST['username']);
        foreach($resname as $res) {
            if(in_array(strtolower($res), $config['invalidNameTags'])) {
                $errors[] = 'Seu nome de usuário contém uma palavra restrita.';
            }
            else if(strlen($res) == 1) {
                $errors[] = 'Palavras muito curtas no seu nome.';
            }
        }
        if (strlen($_POST['username']) > 32) {
            $errors[] = 'O nome da sua conta deve ter menos de 33 caracteres.';
        }
        if (strlen($_POST['password']) < 6) {
            $errors[] = 'Sua senha deve ter pelo menos 6 caracteres.';
        }
        if (strlen($_POST['password']) > 100) {
            $errors[] = 'Sua senha deve ter menos de 100 caracteres.';
        }
        if ($_POST['password'] !== $_POST['password_again']) {
            $errors[] = 'Suas senhas não correspondem.';
        }
        if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'É necessário um endereço de e-mail válido.';
        }
        if (user_email_exist($_POST['email']) === true) {
            $errors[] = 'Esse endereço de e-mail já está em uso.';
        }
        if ($_POST['selected'] != 1) {
            $errors[] = 'Você só poderá ter uma conta se aceitar as regras.';
        }
        if (validate_ip(getIP()) === false && $config['validate_IP'] === true) {
            $errors[] = 'Falha ao reconhecer seu endereço IP. (Não é um endereço IPv4 válido).';
        }
        if (strlen($_POST['flag']) < 1) {
            $errors[] = 'Por favor, escolha o país.';
        }
    }
}
?>

<div class="well loginContainer widget" id="loginContainer">
    <div class="header">
        Registrar Nova Conta
    </div>
    <div class="body">

        <?php
        // A lógica para exibir mensagens de sucesso ou autenticação permanece, mas agora dentro do novo layout.
        if (isset($_GET['success']) && empty($_GET['success'])) {
            if ($config['mailserver']['register']) {
                echo '<h2>Autenticação de e-mail necessária</h2>';
                echo '<p>Enviamos um e-mail com um link de ativação para o endereço de e-mail fornecido.</p>';
                echo "<p>Se você não conseguir encontrar o e-mail em 5 minutos, verifique seu <strong>junk/trash inbox (spam filter)</strong> as it may be mislocated there.</p>";
            } else {
                echo '<h2>Congratulations!</h2> <p>Sua conta foi criada. Agora você pode fazer login para criar um personagem.</p>';
            }
        } elseif (isset($_GET['authenticate']) && empty($_GET['authenticate'])) {
            $auid = (isset($_GET['u']) && (int)$_GET['u'] > 0) ? (int)$_GET['u'] : false;
            $akey = (isset($_GET['k']) && (int)$_GET['k'] > 0) ? (int)$_GET['k'] : false;
            $user = mysql_select_single("SELECT `id`, `active` FROM `znote_accounts` WHERE `account_id`='$auid' AND `activekey`='$akey' LIMIT 1;");
            if ($user !== false) {
                $user = (int) $user['id'];
                $active = (int) $user['active'];
                if ($active == 0) {
                    mysql_update("UPDATE `znote_accounts` SET `active`='1' WHERE `id`= $user LIMIT 1;");
                }
                echo '<h2>Parabéns!!</h2> <p>Sua conta foi criada. Agora você pode fazer login para criar um personagem.</p>';
            } else {
                echo '<h2>Authentication failed</h2> <p>Either the activation link is wrong, or your account is already activated.</p>';
            }
        } else {
            // Lógica de registro e exibição de erros
            if (empty($_POST) === false && empty($errors) === true) {
                if ($config['log_ip']) {
                    znote_visitor_insert_detailed_data(1);
                }
                $register_data = array(
                    'name'      =>  $_POST['username'],
                    'password'  =>  $_POST['password'],
                    'email'     =>  $_POST['email'],
                    'created'   =>  time(),
                    'ip'        =>  getIPLong(),
                    'flag'      =>  $_POST['flag']
                );
                user_create_account($register_data, $config['mailserver']);
                if (!$config['mailserver']['debug']) header('Location: register.php?success');
                exit();
            } else if (empty($errors) === false){
                // Usa a mesma caixa de erro moderna que criamos para a página de login
                echo '<div class="login-error-container">';
                foreach ($errors as $error) {
                    echo '<p>' . $error . '</p>';
                }
                echo '</div>';
            }
        ?>
        <form action="" method="post">
            <div class="well">
                <label for="username">Conta:</label>
                <input type="text" name="username" id="username">
            </div>
            <div class="well">
                <label for="password">Senha:</label>
                <input type="password" name="password" id="password">
            </div>
            <div class="well">
                <label for="password_again">Repetir Senha:</label>
                <input type="password" name="password_again" id="password_again">
            </div>
            <div class="well">
                <label for="email">Email:</label>
                <input type="text" name="email" id="email">
            </div>
            <div class="well">
                <label for="flag">País:</label>
                <select name="flag" id="flag">
                    <option value="">(Por favor escolha)</option>
                    <?php
                    foreach(array('pl', 'se', 'br', 'us', 'gb', ) as $c)
                        echo '<option value="' . $c . '">' . $config['countries'][$c] . '</option>';

                    echo '<option value="">----------</option>';
                    foreach($config['countries'] as $code => $c)
                        echo '<option value="' . $code . '">' . $c . '</option>';
                    ?>
                </select>
            </div>

            <?php if ($config['use_captcha']): ?>
                <div class="well" style="text-align: center;">
                    <div class="g-recaptcha" data-sitekey="<?php echo $config['captcha_site_key']; ?>"></div>
                </div>
            <?php endif; ?>

            <div class="well">
                <label for="selected">Você concorda em seguir as <a href=\'regras.php\' style='color:#a0a0a0'>regras</a> do servidor?</label>
                <select name="selected" id="selected">
                    <option value="0">Umh...</option>
                    <option value="1">Yes.</option>
                    <option value="2">No.</option>
                </select>
            </div>
            
            <?php Token::create(); ?>

            <div class="well" style="text-align: center;">
                <input type="submit" value="Criar conta" class="submitButton">
            </div>
        </form>
        <?php
        }
        ?>
    </div>
</div>

<?php
include 'layout/overall/footer.php';
?>