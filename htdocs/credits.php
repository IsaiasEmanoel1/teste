<?php require_once 'engine/init.php'; include 'layout/overall/header.php'; ?>

<h1>ArcansOrigins</h1>
<p>Site desenvolvido por Hirxz.</p>

<h2>Developer:</h2>
<p>Por: <a href="">Hirxz</a>.
<?php
if(!function_exists('curl_version')) { // If CURL isn't enabled show default version.
?>
<br>Contributor: <a href="https://github.com/Kuzirashi">Kuzirashi</a>.
<br>Contributor: <a href="https://github.com/ninjalulz">ninjalulz</a>.
<br>Contributor: <a href="https://github.com/att3">att3</a>.
<?php } else { // CURL enabled.
	echo '<br />
	Contribuicao:Programador,Mapper,Scripter,HTML';

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, '');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_USERAGENT, 'ZnoteAAC'); // GitHub requires user agent header.
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	$contributors = json_decode(curl_exec($curl), true); // Sorted by contributions.

	$CONTRIBUTORS_PER_ROW = 7;

	$i = 0;
	$first_container = true;
	$div_closed = true;
	foreach($contributors as $contributor)	{
		if($contributor['login'] != 'Znote') { // Exclude Znote as he is mentioned above as main developer.
			$new_div = ctype_digit((String)($i / $CONTRIBUTORS_PER_ROW));

			if($new_div) {
				if(!$first_container)
					echo '</div>';

				echo '<div class="contributors">';
				$div_closed = false;
				$first_container = false;
			}

			echo '<div class="contributor">
					<a href="' . $contributor['html_url'] . '">
						<img src="' . $contributor['avatar_url'] . 'size=80" style="width: 80px; height: 80px" /><br/>
						<span>' . $contributor['login'] . '</span>
					</a>
				</div>';
			$i++;
		}
	}
	if(!$div_closed)
		echo '</div>';
} ?>
</p>

<h3>Atual Equipe Arcan</h3>
<p>
<a href="">Vlad</a> - Administração e Comunity Manager.
<br><a href="">Pinguin</a> - Comunity Manager, Game Master.
<br><a href="">Blaze</a> - Blaze, o Lindo, Game Master.
<br><a href="">Hayatin</a>, <a href=""></a> - Game Master
<br><a href="">Mathzx</a> - Tutor 
<br><a href="">Rafersiq</a> - Tutor.
</p>

<style>
.contributors {
	margin-top: 10px;
	padding: 5px;
	border: 1px solid rgb(184, 184, 184);
	display: inline-flex;
	width: 100%;
}
.contributor {
	padding: 10px;
	text-align: center;
}
</style>
<?php include 'layout/overall/footer.php'; ?>
