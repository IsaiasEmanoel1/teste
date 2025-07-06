<div class="well widget">
	<div class="header">
		ArcansOrigins - 2023 Seu Servidor
	</div>
	<div class="body">
			<select name="<?php if ($config['ServerEngine'] !== 'TFS_10') echo "selected"; else echo "id" ;?>">
				<?php
				foreach ($config['towns'] as $id => $name) 
					echo '<option value="'. $id .'">'. $name .'</option>';
				?>
			</select> 
			<?php
				/* Form file */
			?>
			<input type="submit" value="Fetch houses">
		</form>
	</div>
</div>