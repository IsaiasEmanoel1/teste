
<div class="well loginContainer widget" id="loginContainer">
	<div class="header" style="font-family:'Luckiest Guy'">
		Login / Registrar
	</div>
	<div class="body">
		<form class="loginForm" action="login.php" method="post">
			<div class="well">
				<label for="login_username">Usuario:</label> <input type="text" name="username" id="login_username">
			</div>
			<div class="well">
				<label for="login_password">Senha:</label> <input type="password" name="password" id="login_password">
			</div>
			<?php if ($config['twoFactorAuthenticator']): ?>
				<div class="well">
					<label for="login_password">Token:</label> <input type="password" name="authcode">
				</div>
			<?php endif; ?>
			<div class="well">
				<input type="submit" value="Log in" class="submitButton">
			</div>
			<?php
				/* Form file */
				Token::create();
			?>
			<center>
				<h3><a href="register.php">Nova conta</a></h3>
				<p>Esqueceu o<a href="recovery.php?mode=username"> Usuario</a> ou a <a href="recovery.php?mode=password">senha</a>?</p>
			</center>
		</form>
	</div>
</div>

<script>
// Garante que o script só rode depois que a página carregar completamente
$(document).ready(function() {
    // Intercepta o envio do formulário com a classe 'loginForm'
    $('.loginForm').on('submit', function(e) {
        // Previne o recarregamento padrão da página
        e.preventDefault(); 

        var form = $(this);
        var url = form.attr('action'); // Pega a URL do action do form (login.php)
        var formData = form.serialize(); // Pega todos os dados do formulário

        // Remove erros antigos e esconde a caixa de erro
        var errorContainer = $('.login-error-container');
        if (errorContainer.length) {
            errorContainer.hide().empty();
        } else {
            // Se a div de erro não existir, cria ela antes do formulário
            form.before('<div class="login-error-container" style="display: none;"></div>');
            errorContainer = $('.login-error-container');
        }

        // Inicia a requisição AJAX
        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            dataType: 'json', // Esperamos uma resposta em JSON do servidor
            success: function(response) {
                if (response.success) {
                    // Se o login foi bem-sucedido, redireciona para a página da conta
                    window.location.href = 'myaccount.php';
                } else {
                    // Se houver erros, monta a lista de erros
                    var errorHtml = '';
                    $.each(response.errors, function(index, error) {
                        errorHtml += '<p>' + error + '</p>';
                    });
                    
                    // Mostra a caixa de erro com as mensagens
                    errorContainer.html(errorHtml).slideDown();

                    // ----- ESTA É A PARTE NOVA E CRUCIAL -----
                    // Verifica se o servidor enviou um novo token
                    if (response.new_token) {
                        // Encontra o campo do token no formulário e atualiza seu valor
                        form.find('input[name="token"]').val(response.new_token);
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // ESTA FUNÇÃO FOI MODIFICADA PARA DIAGNÓSTICO
                console.error("--- ERRO AJAX DETECTADO ---");
                console.error("Status: ", textStatus);
                console.error("Exceção: ", errorThrown);
                console.error("Resposta do Servidor (login.php):");
                console.log(jqXHR.responseText); // ISSO VAI MOSTRAR A RESPOSTA EXATA DO PHP

                var errorContainer = $('.login-error-container');
                if (!errorContainer.length) {
                    $('.loginForm').before('<div class="login-error-container"></div>');
                    errorContainer = $('.login-error-container');
                }
                // Mostra um erro mais útil
                errorContainer.html('<p><b>Falha na comunicação com o servidor.</b><br>Verifique o console do navegador (pressione F12) para ver os detalhes do erro.</p>').slideDown();
            }
        });
    });
});
</script>