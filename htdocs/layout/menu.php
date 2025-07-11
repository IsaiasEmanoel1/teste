<?php
// Pega o nome do arquivo da página que está sendo visitada.
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav id="main-navbar" style="border: 2px solid rgba(255, 255, 255, 0.54);" >
    <div class="container" style="background-color:black">
        <div class="pull-left">
            <ul>
                <li>
                    <a href="/" id="logo-link">
                        <video src="layout/img/logo.mp4" autoplay loop muted playsinline style="height: 120px;"></video>
                    </a>
                </li>

                <li>
                    <a href="index.php" class="<?php if ($currentPage == 'index.php' || $currentPage == '') echo 'active'; ?>">
                        <i class="fa fa-home"></i> Início
                    </a>
                </li>


                <li>
                    <a href="downloads.php" class="<?php if ($currentPage == 'downloads.php') echo 'active'; ?>">
                        <i class="fa fa-download"></i> Download
                    </a>
                </li>
                
                <li>
                    <a href="shop.php" class="<?php if ($currentPage == 'shop.php') echo 'active'; ?>">
                        <i class="fa fa-shopping-cart"></i> Shop
                    </a>
                </li>
                
                <li>
                    <a href="medias.php" class="<?php if ($currentPage == 'medias.php') echo 'active'; ?>">
                        <i class="fa fa-info-circle"></i> Média de Catch
                    </a>
                </li>
            </ul>
        </div>

        <div class="pull-right">
            <ul>
                <li>
                    <a href="sub.php?page=loginhelp" class="modIcon loginBtn <?php if ($currentPage == 'sub.php') echo 'active'; ?>">
                        <i class="fa fa-lock"></i><i class="fa fa-unlock"></i> Login
                    </a>
                </li>
                
                <li>
                    <a href="register.php" class="<?php if ($currentPage == 'register.php') echo 'active'; ?>">
                        <i class="fa fa-key"></i> Registrar
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>