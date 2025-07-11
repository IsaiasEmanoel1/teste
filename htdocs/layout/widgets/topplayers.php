<div class="widget">
    <div class="widget_header">
        <h3 class="widget_title">Top 5 Players</h3>
    </div>
    <div class="widget_body">
        <?php
        // Busca no banco de dados os 5 jogadores com maior nível
        $top_players = mysql_select_multi('SELECT `name`, `level`, `experience` FROM `players` WHERE `group_id` < ' . $config['highscore']['ignoreGroupId'] . ' ORDER BY `level` DESC, `experience` DESC LIMIT 5;');

        if ($top_players) {
            echo '<ul>';
            foreach ($top_players as $player) {
                // Previne ataques XSS usando htmlspecialchars
                $playerName = htmlspecialchars($player['name']);
                $playerLevel = (int)$player['level'];
                
                // Exibe cada jogador na lista
                echo '<li><a href="characterprofile.php?name=' . urlencode($playerName) . '">' . $playerName . '</a> <span>Level ' . $playerLevel . '</span></li>';
            }
            echo '</ul>';
        } else {
            echo '<p>Nenhum jogador no ranking no momento.</p>';
        }
        ?>
    </div>
</div>