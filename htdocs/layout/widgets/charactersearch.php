<div class="widget">
    <div class="widget_header">
        <h3 class="widget_title">Procurar Personagem</h3>
    </div>
    <div class="widget_body">

        <form class="searchForm" action="characterprofile.php" method="get">
            <input autocomplete="off" type="text" name="name" id="src_name" placeholder="Nome do personagem...">
            <div id="name_suggestion"></div>
            <button type="submit" class="btn">Procurar</button>
        </form>

        <?php
        // Bloco PHP para carregar os nomes dos personagens para o script
        $cache = new Cache('engine/cache/characterNames');
        if ($cache->hasExpired()) {
            // Use a função correta para sua conexão com o banco de dados
            // Se você usa Znote AAC, mysql_select_multi é a padrão
            $names_sql = mysql_select_multi('SELECT `name` FROM `players` ORDER BY `name` ASC;');
            $names = array();
            if ($names_sql) {
                foreach ($names_sql as $name) {
                    $names[] = $name['name'];
                }
            }
            $cache->setContent($names);
            $cache->save();
        } else {
            $names = $cache->load();
        }
        ?>

        <script type="text/javascript">
            // Passa os nomes do PHP para o JavaScript
            window.searchNames = <?php echo json_encode($names); ?>;

            // Garante que o script rode após o carregamento da página
            $(function() {
                if (window.searchNames && window.searchNames.length > 0) {
                    
                    $('#src_name').keyup(function(e) {
                        var suggestionBox = $('#name_suggestion');
                        suggestionBox.html(''); // Limpa sugestões antigas
                        
                        var search = $(this).val().toLowerCase();
                        var results = [];

                        if (search.length > 0) {
                            for (var i = 0; i < window.searchNames.length && results.length < 10; i++) {
                                if (window.searchNames[i].toLowerCase().indexOf(search) !== -1) {
                                    results.push(window.searchNames[i]);
                                }
                            }
                        }

                        if (results.length > 0) {
                            var search_html = "";
                            for (var i = 0; i < results.length; i++) {
                                search_html += '<div class="sname"><a href="characterprofile.php?name=' + results[i] + '">' + results[i] + '</a></div>';
                            }
                            suggestionBox.addClass('show').html(search_html);
                        } else {
                            suggestionBox.removeClass('show');
                        }
                    });
                    
                    // Esconde a caixa de sugestão se clicar fora dela
                    $(document).on('click', function(e) {
                        if (!$(e.target).closest('.widget_body').length) {
                            $('#name_suggestion').removeClass('show');
                        }
                    });
                }
            });
        </script>
    </div>
</div>