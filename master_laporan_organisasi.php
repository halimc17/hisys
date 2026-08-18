<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
echo open_body();
?>
<link rel=stylesheet type='text/css' href=style/orgchart.css>
<script   language=javascript1.2 src=js/menusetting.js></script>
<script   language=javascript1.2 src=js/orgChart.js></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['orgchartcap']).'</span>');
echo OPEN_THEME($_SESSION['lang']['orgchartcap'] . ':');
echo "<div class=maincontent>
      <fieldset class=legend><legend>Structure:</legend>
	  </fildset>
	  ";
$country = readCountry("./config/country.lst");

$optCountry = '';
for ($x = 0; $x < count($country); $x++) {
    $optCountry.="<option value='" . $country[$x][2] . "' >" . $country[$x][0] . "</option>";
}

//get organization type
$tipeOrg = readCountry("./config/tipeorganisasi.lst");

$optTipeOrg = '';
for ($x = 0; $x < count($tipeOrg); $x++) {
    $optTipeOrg.="<option value='" . $tipeOrg[$x][0] . "' >" . $tipeOrg[$x][1] . "</option>";
}
//================================  
//ambil alokasi biaya
$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where
      tipe='PT' order by namaorganisasi";
$optalokasi = "<option value=''></option>";

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bark = $res->fetch()) {
    $optalokasi.="<option value='" . $bark->kodeorganisasi . "'>" . $bark->namaorganisasi . "</option>";
}
//======================================   
//nomor akun masing-masing

$strc = "select noakun,namaakun from " . $dbname . ".keu_5akun 
       where detail=1 order by noakun";
$optnoakun = "<option value=''></option>";

$resc = $owlPDO->query($strc) or die(print " Gagal: " . PDOException::getMessage());
$resc->setFetchMode(PDO::FETCH_OBJ);
while ($barc = $resc->fetch()) {
    $optnoakun.="<option value='" . $barc->noakun . "'>" . $barc->noakun . " -" . $barc->namaakun . "</option>";
}
//=========================================
$str = "select * from " . $dbname . ".organisasi where induk='' or induk='0'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$row = owlBaris($res);

echo"<ul>";

if ($row > 0) {
    $res->setFetchMode(PDO::FETCH_OBJ);
    while ($bar = $res->fetch()) {
        echo"<li class=mmgr>
		       <img title=expand class=arrow src='images/foldc_.png'  height=22px onclick=show_sub('gr" . $bar->kodeorganisasi . "',this);>";
        echo "<b class=elink id='el" . $bar->kodeorganisasi . "' style='height:22px;font:20'  style='height:22px' title='Click to change'>" . $bar->kodeorganisasi . ": " . $bar->namaorganisasi . "</b>";
        //++++++++++++++++++++++++++
        $str1 = "select * from " . $dbname . ".organisasi where induk='" . $bar->kodeorganisasi . "'";

        echo"<ul id=gr" . $bar->kodeorganisasi . " style='display:none'>";
        echo"<div id=main" . $bar->kodeorganisasi . ">";

        $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_OBJ);
        while ($bar1 = $res1->fetch()) {
            echo"<li class=mmgr>
					     <img title=expand class=arrow src='images/foldc_.png' height=21px   onclick=show_sub('gr" . $bar1->kodeorganisasi . "',this);>";
            echo "<b class=elink id='el" . $bar1->kodeorganisasi . "'  title='Click to change'>" . $bar1->kodeorganisasi . ": " . $bar1->namaorganisasi . "</b>";
            //++++++++++++++++++++++++++
            $str2 = "select * from " . $dbname . ".organisasi where induk='" . $bar1->kodeorganisasi . "'";

            echo"<ul id=gr" . $bar1->kodeorganisasi . " style='display:none;'>";
            echo"<div id=main" . $bar1->kodeorganisasi . ">";

            $res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
            $res2->setFetchMode(PDO::FETCH_OBJ);
            while ($bar2 = $res2->fetch()) {
                echo"<li class=mmgr>
							     <img title=expand class=arrow src='images/foldc_.png' height=19px  onclick=show_sub('gr" . $bar2->kodeorganisasi . "',this);>";
                echo "<b class=elink id='el" . $bar2->kodeorganisasi . "'  title='Click to change'>" . $bar2->kodeorganisasi . ": " . $bar2->namaorganisasi . "</b>";
                //++++++++++++++++++++++++++
                $str3 = "select * from " . $dbname . ".organisasi where induk='" . $bar2->kodeorganisasi . "'";

                echo"<ul id=gr" . $bar2->kodeorganisasi . " style='display:none;'>";
                echo"<div id=main" . $bar2->kodeorganisasi . ">";

                $res3 = $owlPDO->query($str3) or die(print " Gagal: " . PDOException::getMessage());
                $res3->setFetchMode(PDO::FETCH_OBJ);
                while ($bar3 = $res3->fetch()) {
                    echo"<li class=mmgr>
									     <img title=expand class=arrow src='images/foldc_.png' height=17px   onclick=show_sub('gr" . $bar3->kodeorganisasi . "',this);>";
                    echo "<b class=elink id='el" . $bar3->kodeorganisasi . "' title='Click to change'>" . $bar3->kodeorganisasi . ": " . $bar3->namaorganisasi . "</b>";
                    //++++++++++++++++++++++++++
                    $str4 = "select * from " . $dbname . ".organisasi where induk='" . $bar3->kodeorganisasi . "'";

                    echo"<ul id=gr" . $bar3->kodeorganisasi . " style='display:none;'>";
                    echo"<div id=main" . $bar3->kodeorganisasi . ">";

                    $res4 = $owlPDO->query($str4) or die(print " Gagal: " . PDOException::getMessage());
                    $res4->setFetchMode(PDO::FETCH_OBJ);
                    while ($bar4 = $res4->fetch()) {
                        echo"<li class=mmgr>
											     <img title=expand class=arrow src='images/foldc_.png' height=15px   onclick=show_sub('gr" . $bar4->kodeorganisasi . "',this);>";
                        echo "<b class=elink id='el" . $bar4->kodeorganisasi . "'  title='Click to change'>" . $bar4->kodeorganisasi . ": " . $bar4->namaorganisasi . "</b>";
                        //++++++++++++++++++++++++++
                        $str5 = "select * from " . $dbname . ".organisasi where induk='" . $bar4->kodeorganisasi . "'";

                        echo"<ul id=gr" . $bar4->kodeorganisasi . " style='display:none;'>";
                        echo"<div id=main" . $bar4->kodeorganisasi . ">";

                        $res5 = $owlPDO->query($str5) or die(print " Gagal: " . PDOException::getMessage());
                        $res5->setFetchMode(PDO::FETCH_OBJ);
                        while ($bar5 = $res5->fetch()) {
                            echo"<li class=mmgr>
													     <img title=expand class=arrow src='images/foldc_.png' height=17px   onclick=show_sub('gr" . $bar5->kodeorganisasi . "',this);>";
                            echo "<b class=elink id='el" . $bar5->kodeorganisasi . "'  title='Click to change'>" . $bar5->kodeorganisasi . ": " . $bar5->namaorganisasi . "</b>";
                            //++++++++++++++++++++++++++
                            $str6 = "select * from " . $dbname . ".organisasi where induk='" . $bar5->kodeorganisasi . "'";

                            echo"<ul id=gr" . $bar5->kodeorganisasi . " style='display:none;'>";
                            echo"<div id=main" . $bar5->kodeorganisasi . ">";
                            $res6 = $owlPDO->query($str6) or die(print " Gagal: " . PDOException::getMessage());
                            $res6->setFetchMode(PDO::FETCH_OBJ);
                            while ($bar6 = $res6->fetch()) {

                                echo"<li class=mmgr>
															     <img title=expand class=arrow src='images/foldc_.png' height=17px   onclick=show_sub('gr" . $bar6->kodeorganisasi . "',this);>";
                                echo "<b class=elink id='el" . $bar6->kodeorganisasi . "'  title='Click to change'>" . $bar6->kodeorganisasi . ": " . $bar6->namaorganisasi . "</b>";
                                //++++++++++++++++++++++++++
                                $str7 = "select * from " . $dbname . ".organisasi where induk='" . $bar6->kodeorganisasi . "'";

                                echo"<ul id=gr" . $bar6->kodeorganisasi . " style='display:none;'>";
                                echo"<div id=main" . $bar6->kodeorganisasi . ">";

                                $res7 = $owlPDO->query($str7) or die(print " Gagal: " . PDOException::getMessage());
                                $res7->setFetchMode(PDO::FETCH_OBJ);
                                while ($bar7 = $res7->fetch()) {
                                    echo"<li class=mmgr>
																	     <img title=expand class=arrow src='images/foldc_.png' height=17px   onclick=show_sub('gr" . $bar7->kodeorganisasi . "',this);>";
                                    echo "<b class=elink id='el" . $bar7->kodeorganisasi . "' title='Click to change'>" . $bar7->kodeorganisasi . ": " . $bar7->namaorganisasi . "</b>";
                                    //++++++++++++++++++++++++++
                                    $str8 = "select * from " . $dbname . ".organisasi where induk='" . $bar7->kodeorganisasi . "'";

                                    echo"<ul id=gr" . $bar7->kodeorganisasi . " style='display:none;'>";
                                    echo"<div id=main" . $bar7->kodeorganisasi . ">";

                                    $res8 = $owlPDO->query($str8) or die(print " Gagal: " . PDOException::getMessage());
                                    $res8->setFetchMode(PDO::FETCH_OBJ);
                                    while ($bar8 = $res8->fetch()) {
                                        echo"<li class=mmgr>
																			     <img title=expand  src='images/menu/arrow_10.gif'>";
                                        echo "<b class=elink id='el" . $bar8->kodeorganisasi . "'  title='Click to change'>" . $bar8->kodeorganisasi . ": " . $bar8->namaorganisasi . "</b>";
                                        echo"</li>";
                                    }
                                    echo"</div>";

                                    echo"</ul>";
                                    //============================================	
                                    echo"</li>";
                                }
                                echo"</div>";

                                echo"</ul>";
                                //============================================	
                                echo"</li>";
                            }
                            echo"</div>";
                            echo"</ul>";
                            //============================================	
                            echo"</li>";
                        }
                        echo"</div>";
                        echo"</ul>";
                        //============================================	
                        echo"</li>";
                    }
                    echo"</div>";
                    echo"</ul>";
#									<a id='".$bar3->kodeorganisasi."_new' class=elink title='Create Child'  onclick=\"javascript:orgVal='".$bar3->kodeorganisasi."';clos=5;activeOrg='".$bar3->kodeorganisasi."_new';setpos('inputorg',event);clearForm();\">New Org<a>
                    //============================================	
                    echo"</li>";
                }
                echo"</div>";
                echo"</ul>";
#							<a id='".$bar2->kodeorganisasi."_new' class=elink title='Create Child'  onclick=\"javascript:orgVal='".$bar2->kodeorganisasi."';clos=4;activeOrg='".$bar2->kodeorganisasi."_new';setpos('inputorg',event);clearForm();\">New Org<a>
                //============================================	
                echo"</li>";
            }
            echo"</div>";
            echo"</ul>";
#					<a id='".$bar1->kodeorganisasi."_new' class=elink title='Create Child'  onclick=\"javascript:orgVal='".$bar1->kodeorganisasi."';clos=3;activeOrg='".$bar1->kodeorganisasi."_new';setpos('inputorg',event);clearForm();\">New Org<a>
            //============================================										  

            echo"</li>";
        }
        echo"</div>";
        echo"</ul>";
#			<a id='".$bar->kodeorganisasi."_new' class=elink title='Create Child'  onclick=\"javascript:orgVal='".$bar->kodeorganisasi."';clos=2;activeOrg='".$bar->kodeorganisasi."_new';setpos('inputorg',event);clearForm();\">New<a>
#$headControl = "<img id='addHeaderId' title='Tambah Header' src='images/plus.png'".
#  "style='width:20px;height:20px;cursor:pointer' onclick='addHeader(event)' />&nbsp;";
        //============================================										  
        echo"</li>";
    }
} else {//if head office not yet exist
    echo"<li class=mmgr>	
		<a id=HQ class=elink title='Create New HQ'  onclick=\"javascript:orgVal='';clos=1;activeOrg='HQ';setpos('inputorg',event);clearForm();\">New Entity<a>
		</li>";
}
echo"</ul>";
echo "</div>";

echo CLOSE_THEME();
CLOSE_BOX();

echo close_body();
?>