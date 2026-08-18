<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kodehead=checkPostGet('kodehead','');
$kodeheadedit=checkPostGet('kodeheadedit','');	
$matauangheadedit=checkPostGet('matauangheadedit','');	
$simbolheadedit=checkPostGet('simbolheadedit','');	
$kodeisoheadedit=checkPostGet('kodeisoheadedit','');	

$per=checkPostGet('per','');
$kode=checkPostGet('kode','');
$kodedetail=checkPostGet('kodedetail','');
$matauang=checkPostGet('matauang','');
$simbol=checkPostGet('simbol','');
$kodeiso=checkPostGet('kodeiso','');

$kodedetail=checkPostGet('kodedetail','');
$kodedet=checkPostGet('kodedet','');

$jm=checkPostGet('jm','');
$mn=checkPostGet('mn','');
$jmsavedet=$jm.':'.$mn;
$tgl=tanggalsystem(checkPostGet('tgl',''));
$kursdet=checkPostGet('kursdet','');
$kursjual=checkPostGet('kursjual','');
$kursbeli=checkPostGet('kursbeli','');

$jam=checkPostGet('jam','');
$daritanggal=tanggalsystem(checkPostGet('daritanggal',''));

$kodetambah=checkPostGet('kodetambah','');
$matauangtambah=checkPostGet('matauangtambah','');
$simboltambah=checkPostGet('simboltambah','');
$kodeisotambah=checkPostGet('kodeisotambah','');
$method=checkPostGet('method','');

$optPer="<option selected value=''>".$_SESSION['lang']['all']."</option>";
$iPer = $owlPDO->query("SELECT distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc");
$iPer->setFetchMode(PDO::FETCH_ASSOC);
//$optPer="";
while ($dPer=$iPer->fetch())
{
    if($dPer['periode']==$per)
    {
        $optPer.="<option selected value=".$dPer['periode'].">".$dPer['periode']."</option>";
    }
    else
    {
        $optPer.="<option value=".$dPer['periode'].">".$dPer['periode']."</option>";
    }
}

##untuk jam dan menit option
$jm="";
for($t=0;$t<24;)
{
        if(strlen($t)<2)
        {
                $t="0".$t;
        }
        $jm.="<option value=".$t." ".($t==00?'selected':'').">".$t."</option>";
        $t++;
}
$mnt="";
for($y=0;$y<60;)
{
        if(strlen($y)<2)
        {
                $y="0".$y;
        }
        $mnt.="<option value=".$y." ".($y==00?'selected':'').">".$y."</option>";
        $y++;
}	
?>

<?php
switch($method)
{
        case 'insert':
                $str="insert into ".$dbname.".setup_matauang (`kode`,`matauang`,`simbol`,`kodeiso`)
                values ('".$kodetambah."','".$matauangtambah."','".$simboltambah."','".$kodeisotambah."')";
                 try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        break;
        //daritanggal	jam	kurs
        case 'simpandetail':
                $str="insert into ".$dbname.".setup_matauangrate (`kode`,`daritanggal`,`jam`,`kursjual`,`kursbeli`,`kurs`)
                values ('".$kodedet."','".$tgl."','".$jmsavedet."','".$kursjual."','".$kursbeli."','".$kursdet."')";
                 try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        break;

        case 'edithead':
                $str="update ".$dbname.".setup_matauang set kode='".$kodeheadedit."',matauang='".$matauangheadedit."',simbol='".$simbolheadedit."',kodeiso='".$kodeisoheadedit."'
                                where kode='".$kodehead."' ";
                 try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        break;

case'loadData':
        if ($kode=='')
        {
                $kode=$kodedetail;
        }
        $perSch="";
        if($per!='')
        {
                $perSch="and daritanggal like '%".$per."%' ";
        }

        echo"
                <tr>
                         <td>Periode</td>
                         <td>:</td>
                         <td><select id=per onchange=loadData('".$kode."') style='widht:150px'>".$optPer."</select></td>
                </tr>";  
    echo"
        <table class=sortable cellspacing=1 border=0>
                <thead>
                        <tr class=rowheader>
                                <td align=center>No.</td>
                                <td align=center>".$_SESSION['lang']['kode']."</td>
                                <td align=center>".$_SESSION['lang']['tanggal']."</td>
                                <td align=center hidden>".$_SESSION['lang']['jam']."</td>
                                <td align=center>".$_SESSION['lang']['kursjual']."</td>
                                <td align=center>".$_SESSION['lang']['kursbeli']."</td>
                                <td align=center>".$_SESSION['lang']['kurstengah']."</td>
                                <td align=center>".$_SESSION['lang']['action']."</td>
                        </tr>
                </thead>
                <tbody>";

                $limit=31;
                $page=0;
                if(isset($_POST['page']))
                {
                        $page=$_POST['page'];
                        if($page<0) $page=0;
                }
                $offset=$page*$limit;
                $maxdisplay=($page*$limit);

                $ql2=$owlPDO->query("select count(*) as jmlhrow from ".$dbname.".setup_matauangrate where kode='".$kode."' ".$perSch." ");// echo $ql2;notran
                $ql2->setFetchMode(PDO::FETCH_ASSOC);
                while($jsl=$ql2->fetch()){
                @$jlhbrs= $jsl->jmlhrow;
                }
                $ha=$owlPDO->query("select * from ".$dbname.". setup_matauangrate where kode='".$kode."' ".$perSch." order by daritanggal desc limit ".$offset.",".$limit."");
                $ha->setFetchMode(PDO::FETCH_ASSOC);
                $no=$maxdisplay;
                echo"<tr class=rowcontent>";
				echo"<tr class=rowcontent><td></td>
                        <td><input type=text maxlength=3 id=kodedet value=".$kode." disabled onkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:50px;\"></td>

                        <td><input type='text' class='myinputtext' id='tgl' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:75px; /></td>
                        
                        <td hidden><select id=jm>".$jm."</select>:<select id=mn>".$mnt."</select></td>
                        
                        <td><input type=text id=kursjual onkeyup=\"gethitung()\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"  value=0></td>

                        <td><input type=text id=kursbeli onkeyup=\"gethitung()\" onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"  value=0></td>

                        <td><input type=text id=kursdet onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:50px;\"  value=0></td>
                        
                        <td align=center><img src=images/application/application_add.png class=resicon  title='Save'  onclick=simpandetail('".$kode."')></td>
                </tr>";
				while($hu=$ha->fetch())
                {
                $no+=1;
                echo"<tr class=rowcontent>";
				echo"   <td align=center>".$no."</td>
                        <td>".$hu['kode']."</td>
                        <td>".tanggalnormal($hu['daritanggal'])."</td>
                        <td hidden>".$hu['jam']."</td>
                        <td align=right>".number_format($hu['kursjual'],2)."</td>
                        <td align=right>".number_format($hu['kursbeli'],2)."</td>
                        <td align=right>".number_format($hu['kurs'],2)."</td>
                        <td align=center>
                                <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldetail('".$hu['kode']."','".tanggalnormal($hu['daritanggal'])."','".$hu['jam']."');\" >
                        </td>
                </tr>
                ";
                }
                	
                echo"</tbody></table>";

                echo"
                <br></br><fieldset style='width:600px;'><legend>Upload CSV:</legend>
                      <span>Data type:<select id=udatatype onclick=getFormUplaod(this.options[this.selectedIndex].value)>
                                                    <option value=''>Please choose..</option>
                                                    <option value='KURS'>KURS : Setup KURS Mata Uang</option>
                                                    </select> <br></br>
                            
                <div id=uForm style='display:none'>

                                    <span id=sample></span><br><br>
                                         (File type support only CSV).
                                        <form id=frm name=frm enctype=multipart/form-data method=post action=tool_slave_uploadData.php target=frame>    
                                        <input type=hidden name=jenisdata id=jenisdata value=''>
                                        <input type=hidden name=MAX_FILE_SIZE value=1024000>
                                        File:<input name=filex type=file id=filex size=25 class=mybutton>
                                        Field separated by<select name=pemisah>
                                        <option value=','>, (comma)</option>
                                        
                                        </select>
                                        <input type=button class=mybutton  value=".$_SESSION['lang']['save']." title='Submit this File' onclick=submitFile()>
                                    </form>
 
                                    <iframe frameborder=0 width=600px height=200px name=frame>
                                    </iframe>
                     </div>
                     </fieldset>  ";

    break;

        case 'delhead':
        //exit("Error:hahaha");delhead(kode,matauang,simbol,kodeiso)
                $str="delete from ".$dbname.".setup_matauang where kode='".$kode."' and matauang='".$matauang."' and simbol='".$simbol."' and kodeiso='".$kodeiso."'";
                try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        break;

        case 'deldetail':
        //exit("Error:hahaha");delhead(kode,matauang,simbol,kodeiso)
                $str="delete from ".$dbname.".setup_matauangrate where kode='".$kode."' and daritanggal='".$daritanggal."' and jam='".$jam."'";
                try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        break;

default:
}
?>