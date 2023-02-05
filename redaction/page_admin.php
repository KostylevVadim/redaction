<?php
session_start();
include('special/config.php');
include('special/admin_class.php');
$dis = new Admin($connection,'admin');
?>

<body>
    <h3>Если хотите выйти, воспользуйтесь кнопкой:</h3>
    <form method = "post" enctype="multipart/form-data">
        <input type = "submit" name = "submit_exit" value = "выйти">
    </form>
    <?php
    $dis->logout_from_session();
    ?>
    <h3>Нулевые версии</h3>
    <?php
    $lifetime =120;
    setcookie('admin', 'admin', time()+$lifetime,'/');
    $dis->show_all_zero_version_of_article();
    ?>
    <table>
        <tr><th><h3>Отправте нулевую версию редактору</h3></th><th><h3>Создайте новый номер журнала</h3></th></tr>
        <tr>
            <td>
            <form method = "post" enctype="multipart/form-data">
        <lable>Имя статьи</lable>
        <input type = "text" name = "name" pattern="[a-zA-Z0-9\s]+" required>
        <lable>имя редактора</lable>
        <input type = "text" name = "redactor" pattern="[a-zA-Z0-9\s]+" required>
        <lable>File upload</lable>
        <input name = "fileupload" type = "file" required>
        <input type = "submit" name = "submit1">
    </form>
    <?php
    //print_r($_POST);
    if(isset($_POST['submit1'])){
        if(!preg_match("/^[a-zA-Z0-9\s]/", $_POST["name"])){
            //echo("Invalid name");
            //$ercounter++;
            die("Invalid name");
        }
        if(!preg_match("/^[a-zA-Z0-9\s]/", $_POST["redactor"])){
            //echo("Invalid name");
            //$ercounter++;
            die("Invalid redactor name");
        }
    }
    $lifetime =120;
    setcookie('admin', 'admin', time()+$lifetime,'/');
    $dis->send_zero_version_to_redactor();
    ?>
            </td>
            <td>
            <form method = "post" enctype="multipart/form-data">
            <lable>Сколько статей может быть в номере</lable>
            <input type = "number" name = "number" required>
            <lable>Имя номера</lable>
            <input type = "text" name = "name" pattern="[a-zA-Z0-9\s]+" required>
            <input type = "submit" name = "submit2">
        </form>
        <?php
        if(isset($_POST['submit2'])){
        if(!preg_match("/^[a-zA-Z0-9\s]/", $_POST["name"])){
            die("Invalid name");
        }
    }
    $dis->create_new_number();
    ?>
            </td>
        </tr>
    </table>
    
    
    
    
    <h3>Посмотрите на статьи и номера</h3>
    <?php
        $dis->show_numbers_and_articles_in_them();
    ?>
    <h3>Некоторая метаинформация по редакции</h3>
    <?php
        $dis->show_meta();
    ?>
</body>