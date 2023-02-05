<?php
session_start();
include('special/config.php');
include('special/review_distributor.php');
$dis = new Review_distributor($connection,$_SESSION["session_username"]);
echo('<h3>Cтатьи, отправленные к Вам для написания рецензии</h3>');
$dis->get_articles();
//print_r($_COOKIE);
?>
<body>
<h3>Если хотите выйти, воспользуйтесь кнопкой:</h3>
    <form method = "post" enctype="multipart/form-data">
        <input type = "submit" name = "submit_exit" value = "выйти">
    </form>
    <?php
    $dis->logout_from_session();
    ?>
    <h3>Отправте свою рецензию на статью редактору</h3>
    <form method = "post" enctype="multipart/form-data">
        <lable>Имя статьи</lable>
        <input type = "text" name = "name" pattern="[a-zA-Z0-9\s]+" required>
        <lable>File upload</lable>
        <input name = "fileupload" type = "file" required>
        <input type = "submit" name = "submit1">
    </form>
    <?php 
    $username = $_SESSION["session_username"];
    $lifetime =120;
    setcookie('reviwer', $username, time()+$lifetime,'/');
    $dis->send_review()?>
</body>
