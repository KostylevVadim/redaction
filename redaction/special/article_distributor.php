<?php
include('download.php');
function get_redactor_id($connection){
    $connection->exec('LOCK TABLES redactor WRITE');
    $getter = $connection->prepare('SELECT id, username FROM redactor where username=:f');
    $getter->bindParam('f',$_SESSION["session_username"],PDO::PARAM_STR);
    $res = $getter->execute();
    $res_array = $getter->fetchALL(PDO::FETCH_ASSOC);
    $connection->exec('UNLOCK TABLES');
    if(count($res_array)==0){
        return -1;
    }
    return $res_array[0]['id'];
}

function check_if_it_belongs($connection, $id_ver, $id_red){
    
    $connection->exec('LOCK TABLES version_redactor WRITE');
    $checker = $connection->prepare('SELECT id_ver as id from version_redactor where id_red=:t');
    $checker->bindParam('t', $id_red,PDO::PARAM_INT);
    $checker->execute();
    $res_array = $checker->fetchALL(PDO::FETCH_ASSOC);
    $connection->exec('UNLOCK TABLES');

    if(count($res_array)>0){
        return 1;
    }
    else{
        return 0;
    }
}

function get_last_version_by_name($connection, $name){
    $connection->exec('LOCK TABLES version WRITE');
    $getter=$connection->prepare('SELECT max(id) as id from version where name=:f group by name');
    $getter->bindParam('f',$name,PDO::PARAM_STR);
    $result = $getter->execute();
    $res_array = $getter->fetchALL(PDO::FETCH_ASSOC);
    $connection->exec('UNLOCK TABLES');
    if(count($res_array)==0){
        return -1;
    }
    if(check_if_it_belongs($connection, $res_array[0]['id'], get_redactor_id($connection))==0){
        return -1;
    }
    return $res_array[0]['id'];
}
function get_all_id_where_stat_on_delete($connection){
    $connection->exec('LOCK TABLES version WRITE');
    $getter = $connection->prepare('SELECT id FROM version WHERE stat<0');
    $getter->execute();
    $res_array = $getter->fetchALL(PDO::FETCH_ASSOC);
    $connection->exec('UNLOCK TABLES');
    return $res_array;
}
function get_reviewer_by_name($connection,$name){
    $connection->exec('LOCK TABLES reviewer WRITE');
    $getter=$connection->prepare('SELECT id from reviewer where username=:f');
    $getter->bindParam('f',$name,PDO::PARAM_STR);
    $result = $getter->execute();
    $res_array = $getter->fetchALL(PDO::FETCH_ASSOC);
    $connection->exec('UNLOCK TABLES');
    if(count($res_array)==0){
        return -1;
    }
    return $res_array[0]['id'];
}

function get_corrector_by_name($connection,$name){
    $connection->exec('LOCK TABLES corrector WRITE');
    $getter=$connection->prepare('SELECT id from corrector where username=:f');
    $getter->bindParam('f',$name,PDO::PARAM_STR);
    $result = $getter->execute();
    $res_array = $getter->fetchALL(PDO::FETCH_ASSOC);
    $connection->exec('UNLOCK TABLES');
    if(count($res_array)==0){
        return -1;
    }
    return $res_array[0]['id'];
}
function set_zero_to_redactor($connection){
    $connection->exec('LOCK TABLES version WRITE, prev_next_ver WRITE, version_redactor WRITE');
    $zero_version=$connection->prepare('SELECT id FROM version WHERE id NOT IN (SELECT id FROM version INNER JOIN prev_next_ver ON version.id = prev_next_ver.id_prev WHERE version_number = 0) AND version_number = 0 AND id NOT IN (SELECT id_ver as id from version_redactor)');
    $result = $zero_version->execute();
    $res_array = $zero_version->fetchALL(PDO::FETCH_ASSOC);
    $connection->exec('UNLOCK TABLES');
    //print_r($res_array);
    $isnotbindettoredactor = [];
    for($i = 0;$i<count($res_array);$i++){
        $checker = $connection->prepare('SELECT count(id_red) FROM version_redactor WHERE id_ver =:f GROUP BY id_ver');
        $checker->bindParam('f', $res_array[$i]['id'], PDO::PARAM_INT);
        $ch_result = $checker->execute();
        $ch_res_array = $checker->fetchALL(PDO::FETCH_ASSOC);
        //print_r($ch_res_array);
        if(count($ch_res_array)==0){
        array_push($isnotbindettoredactor, $res_array[$i]['id']);
        }
    }
    
    echo("<h3>---------</h3>");
    //print_r($isnotbindettoredactor);
    if(count($isnotbindettoredactor)){
        $k = array_rand($isnotbindettoredactor);
        $v = $isnotbindettoredactor[$k];
        //echo("<h3>".$v."</h3>");
        $inserter = $connection->prepare('INSERT INTO version_redactor(id_ver, id_red) VALUES(:f,:t)');
        $inserter->bindParam('f',$v,PDO::PARAM_INT);
        $inserter->bindValue('t',get_redactor_id($connection),PDO::PARAM_INT);
        $inserter->execute();
    }
    $connection->exec('UNLOCK TABLES');
}
function article_zero_distributor($connection){
    $connection->exec('LOCK TABLES version WRITE, prev_next_ver WRITE, version_redactor WRITE');
    $getallbidet = $connection->prepare('SELECT id_ver FROM version_redactor WHERE id_red=:f AND id_ver IN (SELECT id FROM version WHERE id NOT IN (SELECT id FROM version INNER JOIN prev_next_ver ON version.id = prev_next_ver.id_prev WHERE version_number = 0) AND version_number = 0)');
    $getallbidet->bindValue('f',get_redactor_id($connection),PDO::PARAM_INT);
    $result1 = $getallbidet->execute();
    $getallbidet_array = $getallbidet->fetchALL(PDO::FETCH_ASSOC);
    $connection->exec('UNLOCK TABLES');
    if(count($getallbidet_array) == 0){
        set_zero_to_redactor($connection);
    }
    $connection->exec('LOCK TABLES version WRITE, prev_next_ver WRITE, version_redactor WRITE');
    $getallbidet = $connection->prepare('SELECT id_ver FROM version_redactor WHERE id_red=:f AND id_ver IN (SELECT id FROM version WHERE id NOT IN (SELECT id FROM version INNER JOIN prev_next_ver ON version.id = prev_next_ver.id_prev WHERE version_number = 0) AND version_number = 0)');
    $getallbidet->bindValue('f',get_redactor_id($connection),PDO::PARAM_INT);
    $result1 = $getallbidet->execute();
    $getallbidet_array = $getallbidet->fetchALL(PDO::FETCH_ASSOC);
    if(count($getallbidet_array)>0){
        $id = $getallbidet_array[0]['id_ver'];
        $getfile = $connection->prepare('SELECT name FROM version WHERE id=:f');
        $getfile->bindParam('f',$id,PDO::PARAM_INT);
        $result1 = $getfile->execute();
        $result_array = $getfile->fetchALL(PDO::FETCH_ASSOC);
        //print_r($result_array);
        echo('<p><a href="download.php?path=versions/'.$result_array[0]['name'].'00'.'">Download TEXT file</a></p>');
        $updater = $connection->prepare('UPDATE version SET stat=1 WHERE id=:f');
        $updater->bindParam('f',$id,PDO::PARAM_INT);
        $result = $updater->execute();
    }
    $connection->exec('UNLOCK TABLES');
    
};

function article_redactor_show($connection){
    //echo(get_redactor_id($connection));
    $connection->exec('LOCK TABLES version WRITE, version_redactor WRITE');
    $getter = $connection->prepare('SELECT version.name, max(version.version_number) as last_version FROM version INNER JOIN version_redactor on version.id=version_redactor.id_ver WHERE version_redactor.id_red =:t AND version.version_number<>0 AND version.stat>=0 AND version.stat<10 GROUP BY version.name; ');
    $getter->bindValue('t',get_redactor_id($connection),PDO::PARAM_INT);
    $result = $getter->execute();
    $result_array =  $getter->fetchALL(PDO::FETCH_ASSOC);
    
    $connection->exec('UNLOCK TABLES');
    for($i=0;$i<count($result_array);$i++){
        echo('<h3>'.$result_array[$i]['name'].'</h3>');
        echo('<p><a href="download.php?path=versions/'.$result_array[$i]['name'].$result_array[$i]['last_version'].$result_array[$i]['last_version'].'">Download TEXT file</a></p>');
    }

};
function send_problems_aut($connection){
    if (isset($_POST["submit"]) and isset($_SESSION["session_username"])){
        $id = get_last_version_by_name($connection,$_POST["title"]);
        if($id == -1){
            echo('<h3>Такой статьи не существует</h3>');
            return 0;
        }
        $connection->exec('LOCK TABLE problem_list WRITE');
        $inserter = $connection->prepare('INSERT INTO problem_list(txt, ver_id) VALUES (:f,:t)');
        $inserter->bindParam('f',$_POST["problem"],PDO::PARAM_STR);
        $inserter->bindParam('t',$id,PDO::PARAM_INT);
        $result = $inserter->execute();
        $connection->exec('UNLOCK TABLE');
        if($result){
            echo('<h3>Проблема успешно зарегистрирована и отправлена автору</h3>');
        }
    }

};

function send_article_rev($connection){
    if (isset($_POST["submit2"]) and isset($_SESSION["session_username"])){
        if(!preg_match("/^[a-zA-Z0-9\s]/", $_POST['name'])){
            //echo("Invalid username");
            die("Invalid title");
        }
        if(!preg_match("/^[a-zA-Z0-9\s]/", $_POST['reviwer'])){
            //echo("Invalid username");
            die("Invalid reviwer");
        }
        $id_ver = get_last_version_by_name($connection,$_POST["name"]);
        if($id_ver == -1){
            echo('<h3>Такой статьи не существует</h3>');
            return 0;
        }
        $id_rev = get_reviewer_by_name($connection,$_POST["reviwer"]);
        if($id_rev == -1){
            echo('<h3>Такого рецензента не существует не существует</h3>');
            return 0;
        }
        if($id_ver and $id_rev){
            $connection->exec('LOCK TABLE version_reviewer WRITE, version WRITE');
            $inserter = $connection->prepare('INSERT INTO version_reviewer(ver_id, rev_id) VALUES (:f,:t)');
            $inserter->bindParam('f',$id_ver,PDO::PARAM_INT);
            $inserter->bindParam('t',$id_rev,PDO::PARAM_INT);
            $result = $inserter->execute();
            if($result){
                echo('<h3>Статья успешно отправлена</h3>');
            };
            $updater = $connection->prepare('UPDATE version SET stat=3 WHERE id=:f');
            $updater->bindParam('f',$id_ver,PDO::PARAM_INT);
            $result = $updater->execute();
            
            $connection->exec('UNLOCK TABLES');
        }
        else{
            echo("<h3>Статьи с таким названием или такого рецензента нет</h3>");
        }
    }
};
function get_review($connection){
    $connection->exec('LOCK TABLES version WRITE, review WRITE, version_redactor WRITE');
    $getter = $connection->prepare('SELECT version.id as id, version.name as name from version INNER JOIN review ON review.ver_id = version.id INNER JOIN version_redactor on version.id = version_redactor.id_ver WHERE version.stat = 3 AND version_redactor.id_red =:t');
    $getter->bindValue('t', get_redactor_id($connection), PDO::PARAM_INT);
    $result = $getter->execute();
    $connection->exec('UNLOCK TABLE');
    $result_array = $getter->fetchALL(PDO::FETCH_ASSOC);
    for($i =0; $i<count($result_array);$i++){
        echo('<h3>'.$result_array[$i]['name'].'</h3>');
        echo('<p><a href="download.php?path=review/review_'.$result_array[$i]['name'].$result_array[$i]['id'].'">Download TEXT file</a></p>');

    }
    
    
};
function if_you_can_send_to_corrector($connection, $id){
    
    $getter = $connection->prepare('SELECT stat from version WHERE id=:t');
    $getter->bindValue('t',$id,PDO::PARAM_INT);
    $res = $getter->execute();
    $res_array = $getter->fetchALL(PDO::FETCH_ASSOC);
    $getter = $connection->prepare('SELECT id from review WHERE ver_id=:t');
    $getter->bindValue('t',$id,PDO::PARAM_INT);
    $res = $getter->execute();
    $res_array1 = $getter->fetchALL(PDO::FETCH_ASSOC);
    //print_r($res_array);
    //print_r($res_array1);
    if(count($res_array1)>0){
        return 1;
    };
    return 0;

}
function send_article_cor($connection){
    if (isset($_POST["submit3"]) and isset($_SESSION["session_username"])){
        if(!preg_match("/^[a-zA-Z0-9\s]/", $_POST['name'])){
            //echo("Invalid username");
            die("Invalid title");
        }
        if(!preg_match("/^[a-zA-Z0-9\s]/", $_POST['corrector'])){
            //echo("Invalid username");
            die("Invalid corrector");
        }
        $id_ver = get_last_version_by_name($connection,$_POST["name"]);
        $id_cor = get_corrector_by_name($connection,$_POST["corrector"]);
        if(if_you_can_send_to_corrector($connection, $id_ver)==0){
            echo('<h3>Вы не можете отправить корректору версию, если ее до этого не рассмотрел рецензент<h3>');
            return 0;
        }
        //echo("<h3>".$id_ver." ".$id_cor."</h3>");
        if($id_ver and $id_cor){
            
            $connection->exec('LOCK TABLES version_corrector WRITE, version WRITE');
            $inserter = $connection->prepare('INSERT INTO version_corrector(id_ver, id_cor) VALUES (:f,:t)');
            $inserter->bindParam('f',$id_ver,PDO::PARAM_INT);
            $inserter->bindParam('t',$id_cor,PDO::PARAM_INT);
            $result = $inserter->execute();
            if($result){
                echo('<h3>Статья успешно отправлена</h3>');
            }
            $updater = $connection->prepare('UPDATE version SET stat=4 WHERE id=:f');
            $updater->bindParam('f',$id_ver,PDO::PARAM_INT);
            $result = $updater->execute();
            
            $connection->exec('UNLOCK TABLES');
        }
        else{
            echo("<h3>Статьи с таким названием или такого корректора нет</h3>");
        }
    }


};
function counter($connection, $id){
    $connection->exec('LOCK TABLES fills WRITE, plan_num WRITE');
    $counter = $connection->prepare('SELECT count(id_ver) as count from fills where id_plan =:t');
    $counter->bindValue('t',$id,PDO::PARAM_INT);
    $counter->execute();
    $res_array = $counter->fetchALL(PDO::FETCH_ASSOC);
    $getter = $connection->prepare('SELECT number_of_articles as n from plan_num where id = :t');
    $getter->bindValue('t',$id,PDO::PARAM_INT);
    $getter->execute();
    $res_array1 = $getter->fetchALL(PDO::FETCH_ASSOC);
    if($res_array1[0]['n']==$res_array[0]['count']){
        $updater = $connection->prepare('UPDATE plan_num SET fulled = 1 where id =:t');
        $updater->bindValue('t',$id,PDO::PARAM_INT);
        $updater->execute();
    };
    $connection->exec('UNLOCK TABLES');
}
function filler($conncetion){
    $conncetion->exec('LOCK TABLES plan_num WRITE, fills WRITE, version WRITE');
    $getter = $conncetion->prepare('SELECT max(id) as id FROM plan_num');
    $getter->execute();
    $res_array = $getter->fetchALL(PDO::FETCH_ASSOC);
    $getter2 = $conncetion->prepare('SELECT count(id_ver) as c FROM fills WHERE id_plan=:t');
    $getter2->bindValue('t', $res_array[0]['id'],PDO::PARAM_INT);
    $getter2->execute();
    $res_array2 = $getter2->fetchALL(PDO::FETCH_ASSOC);

    $getter3 = $conncetion->prepare('SELECT number_of_articles as num FROM plan_num WHERE id =:t');
    $getter3->bindValue('t', $res_array[0]['id'],PDO::PARAM_INT);
    $getter3->execute();
    $res_array3 = $getter3->fetchALL(PDO::FETCH_ASSOC);
    if($res_array2[0]['c']<$res_array3[0]['num']){
        //print_r('imhere');
        $how_may_can_insert = $res_array3[0]['num'] - $res_array2[0]['c'];
        $i = 0;
        while($i <=$how_may_can_insert){
            
            $selector = $conncetion->prepare('SELECT id from version where stat = 10 LIMIT 1');
            $selector->execute();
            $res_array4 = $selector->fetchALL(PDO::FETCH_ASSOC);
            if(count($res_array4)>0){
                //print_r($res_array4[0]['id']);
                $updater = $conncetion->prepare('UPDATE version SET stat = 11 WHERE id =:t');
                $updater->bindValue('t',$res_array4[0]['id'],PDO::PARAM_INT);
                $updater->execute();
                $inserter = $conncetion->prepare('INSERT INTO fills(id_ver, id_plan) VALUES (:t,:f)');
                $inserter->bindValue('t',$res_array4[0]['id'],PDO::PARAM_INT);
                $inserter->bindValue('f',$res_array[0]['id'],PDO::PARAM_INT);
                $inserter->execute();
            }
            $i++;
        }
    }
    $conncetion->exec('UNLOCK TABLES');
    counter($conncetion, $res_array[0]['id']);
    remove($conncetion);
    
}
function is_in_number($connection, $name){
    $getter = $connection->prepare('SELECT count(id_ver) FROM fills WHERE id_ver=:t');
    $getter->bindValue('t',get_last_version_by_name($connection, $name), PDO::PARAM_INT);
    $getter->execute();
    $res_array=$getter->fetchALL(PDO::FETCH_ASSOC);
    if($res_array[0]['count(id_ver)']==0)
            return 0;
    else
            return 1;

}

function if_you_can_send_to_number($connection,$name){
    //echo($name);
    $connection->exec('LOCK TABLES send_recive WRITE, review WRITE, version WRITE');
    $getter = $connection->prepare('SELECT * from send_recive INNER JOIN version on send_recive.id_ver = version.id WHERE sends = 0 AND version.name =:t');
    $getter->bindValue('t',$name,PDO::PARAM_STR);
    $getter->execute();
    $res_array1=$getter->fetchALL(PDO::FETCH_ASSOC);
    $getter = $connection->prepare('SELECT * from review INNER JOIN version on review.ver_id = version.id WHERE version.name =:t');
    $getter->bindValue('t',$name,PDO::PARAM_STR);
    $getter->execute();
    $res_array2=$getter->fetchALL(PDO::FETCH_ASSOC);
    $connection->exec('UNLOCK TABLES');
    //echo(count($res_array1));
    //echo(count($res_array2));
    if(count($res_array1)>0 and count($res_array2)>0){
        return 1;
    }
    return 0;
    
}

function send_to_number($conncetion){
    if(isset($_POST["submit_n"])){
        if(if_you_can_send_to_number($conncetion,$_POST["name"])==0){
            echo('<h3>Вы не можете отправить в номер статью, которая не прошал полный цикл обработки<h3>');
            return 0;
        }
        $conncetion->exec('LOCK TABLES fills WRITE, version WRITE');
        $name = $_POST["name"];
        //print_r(is_in_number($conncetion, $name));
        if(is_in_number($conncetion, $name) == 1){
            echo('<h3>Уже в номере</h3>');
            return 0;
        }
        $updater = $conncetion->prepare('UPDATE version SET stat = 10 WHERE id =:t');
        $updater->bindValue('t',get_last_version_by_name($conncetion, $name),PDO::PARAM_INT);
        $updater->execute();
        $updater = $conncetion->prepare('UPDATE version SET stat = -1 WHERE name=:t and stat!=10');
        $updater->bindValue('t',$name,PDO::PARAM_STR);
        $updater->execute();
        $conncetion->exec('UNLOCK TABLES');
        filler($conncetion);        
    }
}

function if_you_can_delete($connection){
    $getter = $connection->prepare('SELECT MAX(stat) as st FROM version WHERE name=:t GROUP BY name');
    $getter->bindValue('t',$_POST["name"],PDO::PARAM_STR);
    $getter->execute();
    $res_array = $getter->fetchALL(PDO::FETCH_ASSOC);
    //echo($res_array[0]['st']);
    if(count($res_array)>0){
        if($res_array[0]['st']<10){
            return 1;
        }
    }
    return 0;
    

}

function remove($connection){
    if(isset($_POST["submit_bad"])){
        if(!preg_match("/^[a-zA-Z0-9\s]/", $_POST['name'])){
            //echo("Invalid username");
            die("Invalid title");
        }
        if(if_you_can_delete($connection)==0){
            echo('<h3>Вы не можете удалить финальную версию статьи</h3>');
            return 0;
        }
        $name = $_POST['name'];
        $updater = $connection->prepare('UPDATE version SET stat = -2 WHERE name =:t');
        $updater->bindValue('t',$name,PDO::PARAM_STR);
        $updater->execute();
        $result = get_all_id_where_stat_on_delete($connection);
        for($i=0; $i<count($result);$i++){
            $connection->exec('LOCK TABLES version WRITE, prev_next_ver WRITE, problem_list WRITE, problem_list_corr WRITE, review WRITE, send_recive WRITE, version_corrector WRITE, version_redactor WRITE, version_reviewer WRITE');
            $deleter = $connection->prepare('DELETE FROM version WHERE id=:t');
            $deleter->bindValue('t',$result[$i]['id'],PDO::PARAM_INT);
            $deleter->execute();
            $deleter = $connection->prepare('DELETE FROM prev_next_ver WHERE id_prev=:t OR id_next=:f');
            $deleter->bindValue('t',$result[$i]['id'],PDO::PARAM_INT);
            $deleter->bindValue('f',$result[$i]['id'],PDO::PARAM_INT);
            $deleter->execute();
            $deleter = $connection->prepare('DELETE FROM  problem_list WHERE ver_id=:t');
            $deleter->bindValue('t',$result[$i]['id'],PDO::PARAM_INT);
            $deleter->execute();
            $deleter = $connection->prepare('DELETE FROM  problem_list_corr WHERE id_ver=:t');
            $deleter->bindValue('t',$result[$i]['id'],PDO::PARAM_INT);
            $deleter->execute();
            $deleter = $connection->prepare('DELETE FROM  review WHERE ver_id=:t');
            $deleter->bindValue('t',$result[$i]['id'],PDO::PARAM_INT);
            $deleter->execute();
            $deleter = $connection->prepare('DELETE FROM  send_recive WHERE id_ver=:t');
            $deleter->bindValue('t',$result[$i]['id'],PDO::PARAM_INT);
            $deleter->execute();
            $deleter = $connection->prepare('DELETE FROM  version_corrector WHERE id_ver=:t');
            $deleter->bindValue('t',$result[$i]['id'],PDO::PARAM_INT);
            $deleter->execute();
            $deleter = $connection->prepare('DELETE FROM  version_redactor WHERE id_ver=:t');
            $deleter->bindValue('t',$result[$i]['id'],PDO::PARAM_INT);
            $deleter->execute();
            $deleter = $connection->prepare('DELETE FROM  version_reviewer WHERE ver_id=:t');
            $deleter->bindValue('t',$result[$i]['id'],PDO::PARAM_INT);
            $deleter->execute();
            
            $connection->exec('UNLOCK TABLES');
        }
    }
}

function logout($connection){
    if(isset($_POST['submit_exit']) or !isset($_COOKIE['redactor'])){
        $name = $_SESSION['session_username'];
        setcookie('redactor', $name, time() - 1);
        unset($_SESSION['session_username']);
        unset($_FILES["fileupload"]);
        unset($_COOKIE['redactor']);
        //setcookie('redactor', $this->name, time() - 1);
        header('Location: ../startpage.php');

    }
}
?>