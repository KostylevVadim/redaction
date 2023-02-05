<?php
include('download.php');
class Correction_distributor{
    private $con, $name;
    function __construct($connection,$name) {
        $this->con = $connection;
        $this->name = $name;
        echo("<h1>Добрый день</h1>");
        echo("<h3>Вы зашли как пользователь - корректор ".$this->name."</h3>");
       
    }
    function if_it_belongs_to_you($title){
        $checker = $this->con->prepare('SELECT id from version INNER JOIN version_corrector ON version_corrector.id_ver = version.id WHERE version.name =:t AND version_corrector.id_cor =:f');
        $checker->bindValue('t',$title,PDO::PARAM_STR);
        $checker->bindValue('f',$this->get_corrector_by_name(),PDO::PARAM_INT);
        $checker->execute();
        $res_array = $checker->fetchALL(PDO::FETCH_ASSOC);
        if(count($res_array)>0){
            return 1;
        }
        else{
            return 0;
        }
    }
    function get_corrector_by_name(){
        $this->con->exec('LOCK TABLES corrector WRITE');
        $getter=$this->con->prepare('SELECT id from corrector where username =:f');
        $getter->bindParam('f',$this->name,PDO::PARAM_STR);
        $result = $getter->execute();
        $res_array = $getter->fetchALL(PDO::FETCH_ASSOC);
        $this->con->exec('UNLOCK TABLES');
        return $res_array[0]['id'];
    }
    function get_articles(){
        $this->con->exec('LOCK TABLES version WRITE, version_corrector WRITE');
        $getter = $this->con->prepare('SELECT name, max(version_number) as version_number FROM version INNER JOIN version_corrector ON version_corrector.id_ver=version.id where version_corrector.id_cor =:f and stat<10 and stat>0 GROUP BY name');
        $getter->bindValue('f',$this->get_corrector_by_name(),PDO::PARAM_INT);
        $result = $getter->execute();
        $res_array = $getter->fetchALL(PDO::FETCH_ASSOC);
        
        if(is_array($res_array)){
            for($i=0;$i<count($res_array);$i++){
                echo('<h3>'.$res_array[$i]['name'].'</h3>');
                echo('<p><a href="download.php?path=versions/'.$res_array[$i]['name'].$res_array[$i]['version_number'].$res_array[$i]['version_number'].'">Download TEXT file</a></p>');
            };
        }
        $this->con->exec('UNLOCK TABLES');
    }

    function get_article_author($id){
        $this->con->exec('LOCK TABLES send_recive WRITE');
        $getter = $this->con->prepare('SELECT id_a as id from send_recive where id_ver=:t');
        $getter ->bindValue('t',$id,PDO::PARAM_INT);
        $result = $getter->execute();
        $res_array = $getter->fetchALL(PDO::FETCH_ASSOC);
        $this->con->exec('UNLOCK TABLES');
        return $res_array[0]['id'];
    }
    function get_last_version_by_name($name){
        $this->con->exec('LOCK TABLES version WRITE');
        $getter = $this->con->prepare('SELECT max(id) as id from version where name=:t group by name');
        $getter->bindParam('t',$name,PDO::PARAM_STR);
        $result = $getter->execute();
        $res_array = $getter->fetchALL(PDO::FETCH_ASSOC);
        $this->con->exec('UNLOCK TABLES');
        return $res_array[0]['id'];
    }
    function count_sended_version($name){
        
        $this->con->exec('LOCK TABLES version WRITE, send_recive WRITE');
        $getter=$this->con->prepare('SELECT count(version.id) as num from version INNER JOIN send_recive on send_recive.id_ver=version.id WHERE send_recive.sends=0 AND version.name=:t GROUP BY version.name');
        $getter->bindValue('t', $name, PDO::PARAM_STR);
        $result = $getter->execute();
        $res_array =$getter->fetchALL(PDO::FETCH_ASSOC);
        $this->con->exec('UNLOCK TABLES');
        return $res_array[0]['num'];
    }
    function send_article_to_author(){
        if (isset($_POST["submit1"]) and isset($_SESSION["session_username"])){
            $name = $_POST["name"];
            $this->con->exec('LOCK TABLES version WRITE, version_corrector WRITE');
            if($this->if_it_belongs_to_you($name)==0){
                echo('<h3>Эту статью курируете не вы или такой статьи не существует</h3>');
                return 0;
            }
            $this->con->exec('UNLOCK TABLES');
            $this->con->exec('LOCK TABLES send_recive WRITE');
            $inserter = $this->con->prepare('INSERT INTO send_recive(id_a,id_ver,sends) VALUES(:f,:t, 0)');
            $inserter->bindValue('f', $this->get_article_author($this->get_last_version_by_name($name)),PDO::PARAM_INT);
            $inserter->bindValue('t', $this->get_last_version_by_name($name),PDO::PARAM_INT);
            $result = $inserter->execute();
            if($result){
                echo('<h3>Успешно отправлено</h3>');
            }
            $this->con->exec('UNLOCK TABLES');
            $tname = $_FILES["fileupload"]["tmp_name"];
            $pname = 'review_'.$_POST["name"];
            move_uploaded_file($tname,'C:\wamp\www\redaction\corrected_articles'.'/'.$pname.$this->get_last_version_by_name($name).$this->count_sended_version($name));

        }
    }
    function last_correction(){
        $getter = $this->con->prepare('SELECT max(problem_list_corr.id) as id FROM problem_list_corr INNER JOIN version_corrector ON version_corrector.id_ver=problem_list_corr.id_ver INNER JOIN version ON version.id=version_corrector.id_ver WHERE version_corrector.id_cor=:t GROUP BY version.name');
        $getter->bindValue('t', $this->get_corrector_by_name(),PDO::PARAM_INT);
        $result = $getter->execute();
        $res_array = $getter->fetchALL(PDO::FETCH_ASSOC);
        return $res_array;
    }

    function show_problems(){
        $our_ids = $this->last_correction();
        for($i=0;$i<count($our_ids);$i++){
            $printer = $this->con->prepare('SELECT problem_list_corr.txt as txt, version.name as name from problem_list_corr INNER JOIN version on problem_list_corr.id_ver=version.id where problem_list_corr.id=:t');
            $printer->bindValue('t',$our_ids[$i]['id'],PDO::PARAM_INT);
            $printer->execute();
            $res_array = $printer->fetchALL(PDO::FETCH_ASSOC);
            echo('<h4>'.$res_array[$i]['name'].': '.$res_array[$i]['txt'].'</h4>');
        }
        
    }
    function logout_from_session(){
        if(isset($_POST['submit_exit']) or !isset($_COOKIE['corrector'])){
            
            unset($_SESSION['session_username']);
            unset($_FILES["fileupload"]);
            unset($_COOKIE['corrector']);
            setcookie('corrector', $this->name, time() - 1);
            header('Location: ../startpage.php');

        }
    }
}
?>