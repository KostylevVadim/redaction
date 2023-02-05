<h1>Старница для регистрации</h1>
<?php
    include_once('special/config.php');
    if(isset($_POST['register'])){
        if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            //Email not valid, 
            //echo("Invalid email");
            //$ercounter++;
            die("Invalid email");
        };
        if(!preg_match("/^[a-zA-Z0-9\s]/", $_POST['username'])){
            //echo("Invalid username");
            //$ercounter++;
            die("Invalid username");
        }
        if(!preg_match("/^[a-zA-Z0-9\s]/", $_POST['password'])){
            //echo("Invalid pwd");
            //$ercounter++;
            die("Invalid pwd");
        }
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        if($username =='admin' or $email=='admin@mail.ru' or $password=='adminpassword'){
            echo('<h3>Эти данные зарегистрированы за шеф редактором</h3>');
        }
        else
        {
            
        $existigs_auth = $connection->prepare("SELECT * FROM author WHERE pas=:password OR username=:u OR email=:t");
        $existigs_auth->bindParam("u", $username, PDO::PARAM_STR);
        $existigs_auth->bindParam("password", $password, PDO::PARAM_STR);
        $existigs_auth->bindParam("t", $email, PDO::PARAM_STR);
        $existigs_auth->execute();
        $existigs_red = $connection->prepare("SELECT * FROM corrector WHERE passwd=:password OR username=:u OR email=:t");
        $existigs_red->bindParam("u", $username, PDO::PARAM_STR);
        $existigs_red->bindParam("password", $password, PDO::PARAM_STR);
        $existigs_red->bindParam("t", $email, PDO::PARAM_STR);
        $existigs_red->execute();
        $existigs_corr = $connection->prepare("SELECT * FROM redactor WHERE passwd=:password OR username=:u OR email=:t");
        $existigs_corr->bindParam("u", $username, PDO::PARAM_STR);
        $existigs_corr->bindParam("password", $password, PDO::PARAM_STR);
        $existigs_corr->bindParam("t", $email, PDO::PARAM_STR);
        $existigs_corr->execute();
        $existigs_rev = $connection->prepare("SELECT * FROM reviewer WHERE passwd=:password OR username=:u OR email=:t");
        $existigs_rev->bindParam("u", $username, PDO::PARAM_STR);
        $existigs_rev->bindParam("password", $password, PDO::PARAM_STR);
        $existigs_rev->bindParam("t", $email, PDO::PARAM_STR);
        $existigs_rev->execute();
        $spec_sum = $existigs_auth->rowCount()+$existigs_red->rowCount()+$existigs_corr->rowCount()+$existigs_rev->rowCount();
        if($spec_sum > 0){
            echo '<h3>Вы уже зарегесрированы в системе, или пароль, почта, имя уже существует</h3>';
            echo '<a href="login.php">"Вход"</a>';
        }
        else{
            $date = date('Y-m-d H:i:s');
            $inserter = $connection->prepare("INSERT INTO author(username,email,pas,reg_date,deleted) VALUES (:username,:email,:password,:date, :del)");

            $inserter->bindValue(":username", $username, PDO::PARAM_STR);
            $inserter->bindValue(":email", $email, PDO::PARAM_STR);
            $inserter->bindValue(":password", $password, PDO::PARAM_STR);
            $inserter->bindValue(":date", $date, PDO::PARAM_STR);
            $inserter->bindValue(":del", FALSE, PDO::PARAM_BOOL);
            $result = $inserter->execute();
            if ($result) {
                echo '<h1>Регистрация прошла успешно!</h1>';
                echo '<a href="login.php">"Вход"</a>';
            } else {
                echo '<h1>ОШИБКА!</h1>';
            }

        }

        }
        
    }
?>
<form method="post" action="" name="signup-form">
<div class="form-element">
<label>Username</label>
<input type="text" name="username" pattern="[a-zA-Z0-9\s]+" required />
</div>
<div class="form-element">
<label>Email</label>
<input type="email" name="email" required />
</div>
<div class="form-element">
<label>Password</label>
<input type="password" name="password" pattern="[a-zA-Z0-9\s]+" required />
</div>
<button type="submit" name="register" value="register">Зарегистрироваться</button>
</form>