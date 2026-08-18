<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');

$proses=$_POST['proses'];

$loksi=		isset($_POST['loksi'])? $_POST['loksi']: '';
$ipAdd=		isset($_POST['ipAdd'])? $_POST['ipAdd']: '';
$idRemote=	isset($_POST['idRemote'])? $_POST['idRemote']: '';
$ipAdd=		isset($_POST['ipAdd'])? $_POST['ipAdd']: '';
$userName=	isset($_POST['userName'])? $_POST['userName']: '';
$passwrd=   isset($_POST['passwrd'])? $_POST['passwrd']: '';
$port=		isset($_POST['port'])? $_POST['port']: '';
$dbnm=		isset($_POST['dbnm'])? $_POST['dbnm']: '';


        switch($proses)
        {
                case'LoadData':
                $limit=10;
                $page=0;
                if(isset($_POST['page']))
                {
                $page=$_POST['page'];
                if($page<0)
                $page=0;
                }
                $offset=$page*$limit;

                $ql2=$owlPDO->query("select count(*) as jmlhrow from ".$dbname.".setup_remotetimbangan order by `id` desc");// echo $ql2;
                $ql2->setFetchMode(PDO::FETCH_OBJ);
                while($jsl=$ql2->fetch()){
                    $jlhbrs= $jsl->jmlhrow;
                }


                $str=$owlPDO->query("select * from ".$dbname.".setup_remotetimbangan order by `id` desc limit ".$offset.",".$limit."");
                $str->setFetchMode(PDO::FETCH_OBJ);
                        $no=0;
                        while($bar=$str->fetch())
                        {
                                $no+=1;
                        //echo $minute_selesai; exit();
                        echo"<tr class=rowcontent id='tr_".$no."'>
                        <td align='center'>".$no."</td>
                        <td>".$bar->lokasi."</td>
                        <td>".$bar->ip."</td>
                        <td>".$bar->username."</td>
                        <td>**************</td>
                        <td>".$bar->port."</td>
                        <td>".$bar->dbname."</td>
                        <td align='center'><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->id."');\"><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldata('".$bar->id."');\"></td>
                        </tr>";
                        }	 	 
                        echo"
                        <tr><td colspan=8 align=center>
                        ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                        <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                        <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                        </td>
                        </tr>";     	
                break;
                case'insert':
                if(($loksi=='')||($ipAdd=='')||($userName=='')||($port=='')||($passwrd=='')||($dbnm==''))
                {
                        echo"warning: Lengkapi Form Inputan";
                        exit();
                }
                if(!preg_match("/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/",$ipAdd))
                {
                        echo"warning : Please Input Valid IP Address";
                        exit();
                }

                        $sIns="insert into ".$dbname.".setup_remotetimbangan (lokasi, ip, username, password, port,dbname) values ('".$loksi."', '".$ipAdd."', '".$userName."', '".$passwrd."', '".$port."','".$dbnm."')";
                        try{$owlPDO->exec($sIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }

                break;
                case'showData':
                $sql=$owlPDO->query("select* from ".$dbname.".setup_remotetimbangan where id='".$idRemote."'");
                $sql->setFetchMode(PDO::FETCH_ASSOC);
                $res=$sql->fetch();
                echo $res['id']."###".$res['lokasi']."###".$res['ip']."###".$res['username']."###".$res['password']."###".$res['port']."###".$res['dbname'];
                break;
                case'update':
                if(($loksi=='')||($ipAdd=='')||($userName=='')||($port=='')||($passwrd=='')||($dbnm==''))
                {
                        echo"warning: Lengkapi Form Inputan";
                        exit();
                }
                if(!preg_match("/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/",$ipAdd))
                {
                        echo"warning : Please Input Valid IP Address";
                        exit();
                }
                        $sUpd="update ".$dbname.".setup_remotetimbangan set   lokasi='".$loksi."', ip='".$ipAdd."', username='".$userName."', password='".$passwrd."', port='".$port."',dbname='".$dbnm."'  where  id='".$idRemote."'";
                        try{$owlPDO->exec($sUpd); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }

                break;
                case'delData':
                $sDel="delete from ".$dbname.".setup_remotetimbangan where id='".$idRemote."'";
                    try{$owlPDO->exec($sDel); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
                break;
                default:
                break;
        }