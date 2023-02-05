<?php
session_start();
include('special/config.php');
include('special/article_distributor.php');
echo("<h1>Добрый день</h1>");
echo("<h3>Вы зашли как пользователь - редактор ".$_SESSION["session_username"]."</h3>");

$lifetime = 120;
setcookie('redactor', $_SESSION["session_username"], time()+$lifetime,'/');
$username = $_SESSION["session_username"];
?>
<h3>Если хотите выйти, воспользуйтесь кнопкой:</h3>
    <form method = "post" enctype="multipart/form-data">
        <input type = "submit" name = "submit_exit" value = "Выйти">
    </form>
    <?php
    setcookie('redactor', $username, time()+$lifetime,'/');
    logout($connection);
    ?>
<script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<?php
echo("<h3>До близжайшего собрания вам отправлены на рассмотрение следующие 0 версии статьи:</h3>");
article_zero_distributor($connection);
echo("<h3>Вам определены на редактуру статьи:</h3>");
article_redactor_show($connection);
?>
<body>
    <table>
        <tr>
            <th><h3>Напришите замечания по текущей статье и отправте ее пользователю - автору</h3></th>
            <th><h3>Отправте версию статьи рецензенту</h3></th>
            <th><h3>Отправте версию статьи корректору</h3></th>
            <th><h3>Признайте статью негодной</h3></th>
            
        </tr>
        <tr>
            <td>
    <form method = "post" enctype="multipart/form-data">
        <lable>Title</lable>
        <input type = "text" name = "title" pattern="[a-zA-Z0-9\s]+" required>
        <lable>Problem</lable>
        <input type = "text" name = "problem" pattern="[a-zA-Z0-9\s]+" required>
        <input type = "submit" name = "submit">
    </form>
    <?php
    setcookie('redactor', $username, time()+$lifetime,'/');
    send_problems_aut($connection)
    ?>
    </td>
    <td>
        
    <form method = "post" enctype="multipart/form-data">
        <lable>Имя пользователя рецензента</lable>
        <input type = "text" name = "reviwer" pattern="[a-zA-Z0-9\s]+">
        <lable>Название статьи</lable>
        <input type = "text" name = "name" pattern="[a-zA-Z0-9\s]+">
        <input type = "submit" name = "submit2">
    </form>
    <?php
    setcookie('redactor', $username, time()+$lifetime,'/');
    send_article_rev($connection);
    ?>
    </td>
    <td>
    <form method = "post" enctype="multipart/form-data">
        <lable>Имя пользователя корректора</lable>
        <input type = "text" id ="corrector" name = "corrector">
        <lable>Название статьи</lable>
        <input type = "text" name = "name" pattern="[a-zA-Z0-9\s]+">
        <input type = "submit" name = "submit3">
    </form>
    <?php
    setcookie('redactor', $username, time()+$lifetime,'/');
    send_article_cor($connection);
    ?>
    </td>
    <td>
    <form method = "post" id = "send_to_number" enctype="multipart/form-data">
        <lable>Название статьи</lable>
        <input type = "text" id="name" name = "name" pattern="[a-zA-Z0-9\s]+">
        <input type = "submit" name = "submit_bad">
    </form>
    <?php
    setcookie('redactor', $username, time()+$lifetime,'/');
    remove($connection);
    ?>
    </td>
    
    
        </tr>
    </table>
    <h3>Отправте версию в номер</h3>
    <form method = "post" id = "send_to_number" enctype="multipart/form-data">
        <lable>Название статьи</lable>
        <input type = "text" id="name" name = "name" pattern="[a-zA-Z0-9\s]+">
        <input type = "submit" name = "submit_n">
    </form>
    <?php
    setcookie('redactor', $username, time()+$lifetime,'/');
    if(isset($_POST["submit_n"]) and !isset($_POST["name"])){
        die("Нет имени ");
    }
    if(isset($_POST["submit_n"]) and isset($_POST["name"])){
        if(!preg_match("/^[a-zA-Z0-9\s]/", $_POST['name'])){
            //echo("Invalid username");
            die("Invalid title");
        }
    }
    send_to_number($connection);
    ?>
    <h3>Вы получили следующую рецензию</h3>
    <?php
    ///setcookie('redactor', $username, time()+$lifetime,'/');
    get_review($connection);
    ?>
    <h3>Увидеть заполненность номеров номер</h3>
    <form id="see_current_number" method="post">
        <button type="submit" name="submit_new" value ="увидеть"></button>
    </form>
    <div id="res"></div>
    
    <script type ="text/javascript">
        $(document).ready(function() {
            //alert('I am here');
            $('#see_current_number').on('submit',function(e)
            {
                //alert('new');
                e.preventDefault();
                $.ajax({
                    
                    type: "POST",
                    url: "youdidit.php",
                    
                    success: function(data){
                        $('#res').append(data);
                    }

                }

                )
            })
            
        })
    </script>
</body>