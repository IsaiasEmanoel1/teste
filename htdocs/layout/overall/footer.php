				</div>
				<!-- MAIN FEED END -->

				<?php include 'layout/aside.php'; ?>
			</div>

			<footer class="well preventCollapse">
				<div class="pull-left">
					<p>&copy; <?php echo $config['site_title'];?>. <?php echo ' Pagina gerada em '. elapsedTime() .' segundos. Q: '.$aacQueries; ?>. Desenvolvido <a href="https://discord.gg/heNXEAmfZp" target="_blank">ArcansOrigins</a>. Engine: <a href="credits.php">Znote AAC</a>.</p>
				</div>
				<div class="pull-right">
					<p><?php echo 'Server date and clock is: '. getClock(false, true); ?></p>
				</div>
				<!--
					Designed By <a href="https://otland.net/members/snavy.155163/" target="_blank">Snavy</a>
				-->
				
				
			</footer>
		</div><!-- Main container END -->
		<div class="discord-corner-icon">
			<a href="https://discord.gg/8y7QDm9sq2" target="_blank">
				<img src="layout/img/discordico.png" alt="Discord">
			</a>
		</div>
	</body>
</html>
