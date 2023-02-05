<?php
function artciler(){
    if(isset($_SESSION["session_username"])){
        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        require_once('special/config.php');
        //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
        include('download.php');
        $getter = $connection->prepare("SELECT version.name,max(version.version_number), author.username FROM version INNER JOIN send_recive ON send_recive.id_ver = version.id INNER JOIN author ON author.id = send_recive.id_a GROUP BY version.name");
        $r1 = $getter->execute();
        $res = $getter->fetchAll(PDO::FETCH_ASSOC);
        //print_r($_FILES);
        for($i =0; $i<count($res);$i++){
            echo('<h3>Имя статьи: '.$res[$i]['name'].' Последняя версия: '.$res[$i]['max(version.version_number)'].'</h3>');
            echo('<p><a href="download.php?path=versions/'.$res[$i]['name'].$res[$i]['max(version.version_number)'].$res[$i]['max(version.version_number)'].'">Download TEXT file</a></p>');
        }
    
    }
}
?>