<?php
session_start();
include('special/config.php');
include('special/author_class.php');
//print_r($_SESSION);
$dis = new Author($connection,$_SESSION["session_username"]);
?>
<style>

h1   {color: red;}
h2   {color: red;}
h3   {color: red;}
</style>
<body>
    <table>
    <tr>
        <td>
        <h1>Добрый день</h1>
        <?php
        echo($_SESSION["session_username"]);
        ?>
        </td>
        <td>
        <h3>Если хотите выйти, воспользуйтесь кнопкой:</h3>
        <form method = "post" enctype="multipart/form-data">
            <input type = "submit" name = "submit_exit" value = "выйти">
        </form>
        <?php
        $dis->logout_from_session();
        ?>
        </td>
    </tr>
    </table>
    <h3>Вы зашли как пользователь - автор</h3>
    
    <table>
        <tr><th>Если хотите отправить новую версию статьи, то направьте сюда</th><th>Если хотите отправить претензию корректору, то направьте сюда</th></tr>
        <tr>
        <td>
            <form method = "post" enctype="multipart/form-data">
            <lable>Название</lable>
            <input type = "text" name = "title" pattern="[a-zA-Z0-9\s]+" required>
            <lable>File upload</lable>
            <input name = "fileupload" type = "file" required>
            <input type = "submit" name = "submit">
            </form>
            <?php
                if (isset($_POST['title']) and isset($_POST['submit'])){
                    if(!preg_match("/^[a-zA-Z0-9\s]/", $_POST['name'])){
                        //echo("Invalid username");
                        die("Invalid title");
                    }
                }
                if(!isset($_FILES['fileupload']) and isset($_POST['submit'])){
                    die('Нет файла');
                }
                $username = $_SESSION["session_username"];
                $lifetime = 120;
                setcookie('author', $username, time()+$lifetime,'/');
                if(!isset($_POST['title']) and isset($_POST['submit'])){
                    die("Нет имени файла");
                }
                $dis->upload_to_server();
            ?>
        </td>
        <td>
            <form method = "post" enctype="multipart/form-data">
            <lable>Имя статьи</lable>
            <input type = "text" name = "name" pattern="[a-zA-Z0-9\s]+" required>
            <lable>Проблема</lable>
            <input type = "text" name = "problem" pattern="[a-zA-Z0-9\s]+" required>
            <input type = "submit" name = "submit1">
        </form>
            <?php
                if(isset($_POST['submit1']) and isset($_POST['name']) and isset($_POST['problem'])){
                    if(!preg_match("/^[a-zA-Z0-9\s]/", $_POST['name'])){
                        //echo("Invalid username");
                        die("Invalid title");
                    }
                    if(!preg_match("/^[a-zA-Z0-9\s]/", $_POST['problem'])){
                        //echo("Invalid username");
                        die("Invalid problem");
                    }
                
                    $username = $_SESSION["session_username"];
                    $lifetime = 120;
                    setcookie('author', $username, time()+$lifetime,'/');
                    $dis->send_problems_to_corrector();
            }
            ?>
        </td><tr>
    </table>
    <h2>Если хотите удалить статью вовсе, то воспользуйтесь этой формой<h2>
    <form method = "post" enctype="multipart/form-data">
            <lable>Имя статьи</lable>
            <input type = "text" name = "name" pattern="[a-zA-Z0-9\s]+" required>
            <input type = "submit" name = "submit_delete">
    </form>
    <?php
    $username = $_SESSION["session_username"];
    $lifetime = 120;
    setcookie('author', $username, time()+$lifetime,'/');
    if(isset($_POST['name'])){
        if(!preg_match("/^[a-zA-Z0-9\s]/", $_POST['name'])){
            //echo("Invalid username");
            die("Invalid title");
        }
    $dis->delete_all_about_article();
    }
    ?>
    <h2>Если со всем согласны, то отправте статью редактору и он решит, можно ли отправлять ее в номер</h2>
    <table>
        <tr><th>Тут Вы увидите информацию об уже отправленных статьях</th><th>Тут вы увидите информацию о предложениях от редакторов</th><th>Тут вы увидите уже откорректированные статьи</th><th>Тут вы увидите присланные рецензии</th></tr>
        <tr>
        <td>
            <?php
            $dis->show_last_version();
            ?>
        </td>
        <td>
            <?php
            $dis->show_all_problems();
            ?>
        </td>
        <td>
            <?php
            $dis->show_corrected_versions();
            ?>
        </td>
        <td>
            <?php
            $dis->show_reviews_for_author();
            ?>
        </td>
    </tr>
    </table>
    
    
    
</body>

