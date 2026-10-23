<?php
    $senha = "bolo";
    $senha_hash = password_hash($senha,PASSWORD_DEFAULT);

    echo"$senha";
    echo"$senha_hash";
?>