<?php
include('download.php');
class Review_distributor{
    private $con, $name;
    function __construct($connection,$name) {
        $this->con = $connection;
        $this->name = $name;
        echo("<h1>Добрый день</h1>");
        echo("<h3>Вы зашли как пользователь - рецензент ".$this->name."</h3>");
    }
    function get_reviewer_by_name(){
        $this->con->exec('LOCK TABLES reviewer WRITE');
        $getter=$this->con->prepare('SELECT id from reviewer WHERE username=:f');
        $getter->bindParam('f',$this->name,PDO::PARAM_STR);
        $result = $getter->execute();
        $res_array = $getter->fetchALL(PDO::FETCH_ASSOC);
        $this->con->exec('UNLOCK TABLES');
        return $res_array[0]['id'];
    }
    function get_articles(){
        $this->con->exec('LOCK TABLES version WRITE, version_reviewer WRITE');
        $getter = $this->con->prepare('SELECT name, version_number FROM version INNER JOIN version_reviewer ON version_reviewer.ver_id =version.id WHERE version.stat = 3 AND version_reviewer.rev_id=:f');
        $getter->bindValue('f',$this->get_reviewer_by_name(),PDO::PARAM_INT);
        $result = $getter->execute();
        $res_array = $getter->fetchALL(PDO::FETCH_ASSOC);
        for($i=0;$i<count($res_array);$i++){
            echo('<h3>'.$res_array[$i]['name'].'</h3>');
            echo('<p><a href="download.php?path=versions/'.$res_array[$i]['name'].$res_array[$i]['version_number'].$res_array[$i]['version_number'].'">Download TEXT file</a></p>');
        };
        $this->con->exec('UNLOCK TABLES');
    }
    function if_it_belongs_to_you($title){
        $checker = $this->con->prepare('SELECT version.id from version INNER JOIN version_reviewer ON version_reviwer.ver_id = version.id WHERE version.name=:t AND version_review.rev_id =:f');
        $checker->bindValue('t',$title,PDO::PARAM_STR);
        $checker->bindValue('f',get_reviewer_by_name(),PDO::PARAM_INT);
        $checker->execute();
        $res_array = $getter->fetchALL(PDO::FETCH_ASSOC);
        if(count($res_array)>0){
            return 1;
        }
        else{
            return 0;
        }
    }
    function send_review(){
        if (isset($_POST["submit1"]) and isset($_SESSION["session_username"])){
            $upload_directory = '\versions';
            $this->con->exec('LOCK TABLES version WRITE, version_reviewer WRITE');
            $getter = $this->con->prepare('SELECT id FROM version WHERE name =:f AND id in (SELECT ver_id as id FROM version_reviewer WHERE version_reviewer.rev_id=:t) AND stat=3;');
            $getter->bindParam('f',$_POST["name"],PDO::PARAM_STR);
            $getter->bindValue('t',$this->get_reviewer_by_name(),PDO::PARAM_INT);
            $result = $getter->execute();
            $res_array = $getter->fetchALL(PDO::FETCH_ASSOC);
            if(count($res_array)==0){
                echo('<h3>Вы не можете писать рецензию на эту статью</h3>');

            }
            else{
                $tname = $_FILES["fileupload"]["tmp_name"];
                $pname = 'review_'.$_POST["name"];
                move_uploaded_file($tname,'C:\wamp\www\redaction\review'.'/'.$pname.$res_array[0]['id']);
                $inserter = $this->con->prepare('INSERT INTO review(ver_id,rev_id) VALUES(:f,:t)');
                $inserter->bindValue('f',$res_array[0]['id'],PDO::PARAM_INT);
                $inserter->bindValue('t',$this->get_reviewer_by_name(),PDO::PARAM_INT);
                $result1 = $inserter->execute();
                //$update = $this->con->prepare('UPDATE version SET stat = 2 WHERE id =:f');
                //$update->bindValue('f',$res_array[0]['id'],PDO::PARAM_INT);
                //$result2 = $update->execute();
            };
            $this->con->exec('UNLOCK TABLES');

        }

    }
    function logout_from_session(){
        if(isset($_POST['submit_exit']) or !isset($_COOKIE['reviwer'])){
            
            unset($_SESSION['session_username']);
            unset($_FILES["fileupload"]);
            unset($_COOKIE['reviwer']);
            setcookie('reviewer', $this->name, time() - 1);
            header('Location: ../startpage.php');

        }
    }

}
?>