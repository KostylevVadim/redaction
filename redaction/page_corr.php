<?php
session_start();
include('special/config.php');
include('special/correction_distributor.php');
$dis = new Correction_distributor($connection,$_SESSION["session_username"]);



?>
<body>
<h3>Если хотите выйти, воспользуйтесь кнопкой:</h3>
    <form method = "post" enctype="multipart/form-data">
        <input type = "submit" name = "submit_exit" value = "выйти">
    </form>
    <?php
    $dis->logout_from_session();
    ?>
<?php
echo('<h3>На коррекцию определены статьи:<h3>');
$dis->get_articles();
?>
<h3>Отправте свою версию статьи автору</h3>
    <form method = "post" enctype="multipart/form-data">
        <lable>Имя статьи</lable>
        <input type = "text" name = "name" pattern="[a-zA-Z0-9\s]+">
        <lable>File upload</lable>
        <input name = "fileupload" type = "file" required>
        <input type = "submit" name = "submit1">
    </form>
    <?php
    $lifetime = 120;
    $username = $_SESSION["session_username"];
    setcookie('corrector', $username, time()+$lifetime,'/');
    if (isset($_POST['name'])){
        if(!preg_match("/^[a-zA-Z0-9\s]/", $_POST['name'])){
            //echo("Invalid username");
            die("Invalid username");
        }
    }
    if(!isset($_FILES['fileupload'])){
        die('Нет файла');
    }
    $dis->send_article_to_author();
    ?>
<h3>Посмотрите, какие ошибки нашли авторы у вас</h3>
<?php
$dis->show_problems();
?>
</body>