<?php
// file creator: dhyaz sep 20, 2011
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

switch ($_POST['aksi']){


    case'getAfd';
        $whstblok = ""; 
        if($_POST['stblok']!='') $whstblok .= " AND statusblok='{$_POST['stblok']}'";

        $ambilInduk=$owlPDO->query("select induk from ".$dbname.".organisasi where kodeorganisasi='".$_POST['kodeorg']."'");
        $ambilInduk->setFetchMode(PDO::FETCH_OBJ);
        $induk='';
        while($bar=$ambilInduk->fetch())
        {
            $induk=$bar->induk;
        }

        // $optAfd="<option value=''>".$_SESSION['lang']['all']."</option>";
		echo "<table width='80%'>
			<tr>
				<td>
					<label style='cursor:pointer;color:blue' onclick=\"selectall()\">Select All</label>
					&nbsp&nbsp&nbsp&nbsp
					<label style='cursor:pointer;color:blue' onclick=\"unselectall()\">Unselect All</label>
				</td>
			</tr>
			<tr>
				<td>";
        
        # Cek Divisi
            $sql = "SELECT DISTINCT LEFT(kodeorg,6) AS divisi FROM {$dbname}.setup_blok WHERE 5=5 {$whstblok} AND kodeorg LIKE '{$_POST['kodeorg']}%'";
            // echo $sql."<br/>";
            $res = fetchData($sql);
            foreach($res as $row):
                $arrdiv[$row['divisi']] = $row['divisi'];
            endforeach;

            if(count($arrdiv) > 0) {
                $whstnew = " AND kodeorganisasi IN ('".implode("','",$arrdiv)."')";
            }
        # End
        // echo "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_POST['alokasi']."' and tipe='AFDELING' {$whstnew}";
        $iAfd=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_POST['alokasi']."' and tipe='AFDELING' {$whstnew}");
        $iAfd->setFetchMode(PDO::FETCH_ASSOC);
        while($dAfd= $iAfd->fetch())
        {
			echo "<li style='float:left;width:200px;list-style-type:none'>
				<input type='checkbox' id='chkAfd' name='chkAfd[]' value='".$dAfd['kodeorganisasi']."' checked />".$dAfd['namaorganisasi']."</li>";
            // $optAfd.="<option value='".$dAfd['kodeorganisasi']."'>".$dAfd['namaorganisasi']."</option>";
        }
		echo"</td></tr></table>";
        // echo $optAfd;
    break;

    case 'ambilnokas':
        // $str="select nojurnal as notransaksi,'".$_SESSION['empl']['lokasitugas']."' as kodeorg,totaldebet as jumlah from ".$dbname.".keu_jurnalht where tanggal=".tanggalsystem($_POST['tanggal'])." and nojurnal like '%/".$_SESSION['empl']['lokasitugas']."/M%'";
        $str=$owlPDO->query("select nojurnal as notransaksi,'".$_SESSION['empl']['lokasitugas']."' as kodeorg,sum(jumlah) as jumlah from "
                . "".$dbname.".keu_jurnaldt_vw where tanggal=".tanggalsystem($_POST['tanggal'])." and nojurnal like '%/".$_SESSION['empl']['lokasitugas']."/M%' and jumlah > 0 "
                . "group by nojurnal");
        $str->setFetchMode(PDO::FETCH_OBJ);
        $opt="<option value=''>Pilih....</option>";
        while($bar= $str->fetch())
        {
            $opt.="<option value='".$bar->notransaksi."#".$bar->jumlah."#".$bar->kodeorg."'>".$bar->kodeorg.": ".$bar->notransaksi." jumlah ".number_format($bar->jumlah)."</option>";
        }
        echo $opt;
    break;

    case 'ambilTipeAlokasi':
        $ketblok = [
            "BBT" => "Bibitan",
            "LC" => "Land Clearing",
            "TB" => "Tanam Baru",
            "TBM" => "Tanaman Belum Menghasilkan",
            "TM" => "Tanaman Menghasilkan"
        ];
        $opt = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

        $sql = "SELECT DISTINCT statusblok FROM {$dbname}.setup_blok WHERE 5=5 AND statusblok NOT IN ('TB','BBT') AND kodeorg LIKE '{$_POST['kodeorg']}%'";
        $res = fetchData($sql,"OBJECT");
        
        foreach($res as $row):
            $text = "[".$row->statusblok."] - ".$ketblok[$row->statusblok]."";

            $opt .= "<option value='{$row->statusblok}'>{$text}</option>";
        endforeach;

        echo $opt;
    break;

    case 'ambilAlokasi':

        $whstblok = ""; 
        if($_POST['stblok']!='') $whstblok .= " AND a.statusblok='{$_POST['stblok']}'";

        $ambilInduk=$owlPDO->query("select induk from ".$dbname.".organisasi where kodeorganisasi='".$_POST['kodeorg']."'");
        $ambilInduk->setFetchMode(PDO::FETCH_OBJ);
        $induk='';
        while($bar=$ambilInduk->fetch())
        {
            $induk=$bar->induk;
        }

        if(getNamaOrg($_SESSION['empl']['lokasitugas'],'tipe') == 'KEBUN') {
            $whstblok .= " AND LEFT(a.kodeorg,4)='{$_POST['kodeorg']}'";
        } else if(getNamaOrg($_SESSION['empl']['lokasitugas'],'tipe') == 'PABRIK') {
            $whstblok .= " AND LEFT(a.kodeorg,4)='{$_POST['kodeorg']}'";
        } else {
            $whstblok .= " AND LEFT(b.induk,4) IN (SELECT kodeorganisasi FROM {$dbname}.organisasi WHERE induk='{$induk}')";
        }
        
        $str="select distinct left(a.kodeorg,4) as kebun from ".$dbname.".setup_blok a
                  left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
                  where /* a.statusblok in('TB','TBM','LC','TBM1','TBM2','TBM3')
                  and */ 5=5 {$whstblok}"; 
		$res=fetchdata($str);
		$num=count($res);
        $opt="<option value=''>Choose....</option>";
		foreach($res as $key=>$val){
			$opt.="<option value='".$val['kebun']."'>".$val['kebun']."</option>";
		}
        // while($bar= $str->fetch())
        // {
            // $opt.="<option value='".$bar->kebun."'>".$bar->kebun."</option>";
        // }
		
		if($num<1){
			exit("Error:Tidak ada blok blok TB,TBM,LC,TBM1,TBM2,TBM3 di unit ".$_POST['kodeorg']);			
		}
		
        echo $opt;      
    break;

    case 'ambilBlok':

        #periksa tutup buku
        $tg=substr($_POST['tanggal'],6,4)."-".substr($_POST['tanggal'],3,2);
        $str=$owlPDO->query("select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_POST['kodeorg']."' and periode='".$tg."' and tutupbuku=0");
        $str->setFetchMode(PDO::FETCH_OBJ);
        $numrows=owlBaris($str);
        if($numrows<1)
        {
            // exit(" Error: Transaction period is closed");
        }

        #get akun debet jurnal memorial
        $sql="select noakun from ".$dbname.".keu_jurnaldt where nojurnal='".$_POST['nojurnal']."' and jumlah>0";
        $str=$owlPDO->query($sql);
        $str->setFetchMode(PDO::FETCH_OBJ);
        $bar=$str->fetch();
        $numrows=owlBaris($str);
        if($numrows>1)
        {
            $akunkredit='';
        }else{
            $akunkredit=$bar->noakun;
        }

        // echo $akunkredit;
        // exit;

        #periksa apakah  sudah pernah dialokasi   
          #ambil noakun
        $optAk=$optAkun="<option value=''>Choose..</option>";
        if($_SESSION['language']=='EN'){
            $str="select noakun,namaakun1 as namaakun from ".$dbname.".keu_5akun where detail=1 order by noakun";
        }else{
            $str="select noakun,namaakun from ".$dbname.".keu_5akun where detail=1 order by noakun";
        }
        $res=$owlPDO->query($str);
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            $optAkun.="<option value='".$bar->noakun."'>".$bar->noakun ."-".$bar->namaakun."</option>";
            if ($bar->noakun==$akunkredit) {
                $optAk.="<option value='".$bar->noakun."' selected >".$bar->noakun ."-".$bar->namaakun."</option>";
            }else{
                $optAk.="<option value='".$bar->noakun."'>".$bar->noakun ."-".$bar->namaakun."</option>";
            }
        }


		$expAfd = explode("####", $_POST['afdeling']);
		$no=0;
		$listafd="";
		$whereOrg = "";
        foreach ($expAfd as $key) 
		{
			$no++;
			if($no==1)
			{
				$whereOrg .= "(kodeorg like '".$key."%'";
			}
			else
			{
				$whereOrg .= " or kodeorg like '".$key."%'";
			}
			if(count($expAfd)==$no)
			{
				$whereOrg .= ")";
			}
        }
		
		if($whereOrg!='')
        {
            $whereOrg = "where ".$whereOrg;
        }

        $whstblok = ""; 
        if($_POST['stblok']!='') $whstblok .= " AND statusblok='{$_POST['stblok']}'";
		
		### ambil luas perdiv
        // $aLuas=$owlPDO->query("select sum(luasareaproduktif) as luasdivisi from ".$dbname.".setup_blok ".$whereOrg."  and statusblok in ('TB','TBM') ");
        // echo "select sum(luasareaproduktif) as luasdivisi from ".$dbname.".setup_blok ".$whereOrg."  {$whstblok}";
        $aLuas=$owlPDO->query("select sum(luasareaproduktif) as luasdivisi from ".$dbname.".setup_blok ".$whereOrg."  {$whstblok}");
        $aLuas->setFetchMode(PDO::FETCH_ASSOC);
        $cLuas=$aLuas->fetch();
        $totLuasBlok = $cLuas['luasdivisi'] ?? 0;
        
        #ambil blok TBM,TB,LC
        // echo "select luasareaproduktif,kodeorg,statusblok from ".$dbname.".setup_blok ".$whereOrg." {$whstblok}";
        // $str=$owlPDO->query("select luasareaproduktif,kodeorg,statusblok from ".$dbname.".setup_blok ".$whereOrg." and statusblok in ('TB','TBM')");
        $str=$owlPDO->query("select luasareaproduktif,kodeorg,statusblok from ".$dbname.".setup_blok ".$whereOrg." {$whstblok}");
        $str->setFetchMode(PDO::FETCH_OBJ);
        $jumblok=owlBaris($str);
        // while($bar= $str->fetch())
        // {
        //     $totLuasBlok+=$bar->luasareaproduktif;
        // }

        // exit('warning '.$jumblok);
        if($jumblok<1){
            exit(" Error: There is no block to allocate");
        }
        else{
            #ambil total biaya
            // $dat=$owlPDO->query("select luasareaproduktif,kodeorg,statusblok from ".$dbname.".setup_blok ".$whereOrg."  and statusblok in ('TB','TBM') ");
            $dat=$owlPDO->query("select luasareaproduktif,kodeorg,statusblok from ".$dbname.".setup_blok ".$whereOrg."  {$whstblok}");
            $dat->setFetchMode(PDO::FETCH_ASSOC);
            $mulai=1;
            while($row=$dat->fetch()){
                if($mulai==1){
                    $lstBlok="'".$row['kodeorg']."'";
                    $mulai+=1;
                }else{
                    $lstBlok.=",'".$row['kodeorg']."'";
                }
            }
            $totBy=0;
            // $sTotBy="select sum(jumlah) as biy,kodeblok from ".$dbname.".keu_jurnaldt where tanggal like '".$tg."%' and kodeblok in (".$lstBlok.")  and left(noakun,1)>5 group by kodeblok having sum(jumlah)>0";
            // $rTotBy=fetchdata($sTotBy);
            // foreach($rTotBy as $row){
            //     $totBy+=$row['biy'];
            //     $byPerBlok[$row['kodeblok']]=$row['biy'];
            // }

            echo"<fieldset style='width:400px'>".$_SESSION['lang']['idcnote']."</fieldset>
                <table>
                       <tr><td>".$_SESSION['lang']['debet']."</td><td><select id=debet>".$optAkun."</select>Rp.".number_format($_POST['jumlah'])."</td></tr>
                        <tr><td>".$_SESSION['lang']['kredit']."</td><td><select id=kredit>".$optAk."</select>Rp.".number_format($_POST['jumlah'])."</td></tr>
                         </table>   
                        ";
            echo"<button onclick=saveDistribusi('".$_POST['kodeorg']."')>".$_SESSION['lang']['save']."</button>";
            echo"<fieldset><legend>".$_SESSION['lang']['distribusi']."</legend>";//<td>".$_SESSION['lang']['luas']."</td>
            echo"<table class=sortable border=0 cellspacing=1>
                       <thead>
                           <tr class=rowheader><td>".$_SESSION['lang']['no']."</td>
                            <td>".$_SESSION['lang']['blok']."</td>
                            <td>".$_SESSION['lang']['statusblok']."</td>

                            <td>".$_SESSION['lang']['jumlah']." (Rp.)</td></tr>
                       </thead><tbody>";
            $no=0;
            $tot=0;
            while($bar=$str->fetch()){  $no+=1;
                        /*3. alokasi IDC || biaya perblok=luas blok / sum (jumlah luas total blok ) * Rp total pembebanan,,luasareaproduktif
                        contoh : H01E02K010 ---- 11.09 HA / 2,530.28 * 5 jt*/
                @$proporsi=fixnan($bar->luasareaproduktif/$totLuasBlok)*$_POST['jumlah'];//<td>".$bar->luasareaproduktif."</td>
                echo"<tr class=rowcontent>
                            <td class=firsttd>".$no."</td>
                            <td>".$bar->kodeorg."</td>
                            <td>".$bar->statusblok."</td>
                            <td align=right>".number_format($proporsi)."</td>
                            </tr>";
              @$tot+=$proporsi;//<td align=right>".number_format($_POST['jumlah']/$jumblok)."</td>
            }
            echo"<tr class=rowcontent><td colspan=3>".$_SESSION['lang']['total']."</td><td align=right>".number_format($tot)."</td></tr>";
            echo"</tbody><tfoot></tfoot></fieldset>";
        }
        break;

    case 'simpanIDC':
        $tg=substr($_POST['tanggal'],6,4)."-".substr($_POST['tanggal'],3,2);
        $iCek=$owlPDO->query("select count(*) as jumlah,noreferensi from ".$dbname.".keu_jurnalht where noreferensi='".$_POST['nokas']."' ");
        $iCek->setFetchMode(PDO::FETCH_ASSOC);
        $dCek=$iCek->fetch();
            if($dCek['jumlah']>0)
            {
                exit("Error:Jurnal ".$dCek['noreferensi']." has been alocated ");
            }
        
		$expAfd = explode("####", $_POST['afdeling']);
		$no=0;
		$listafd="";
		$whereOrg = "";
        foreach ($expAfd as $key) 
		{
			$no++;
			if($no==1)
			{
				$whereOrg .= "(kodeorg like '".$key."%'";
			}
			else
			{
				$whereOrg .= " or kodeorg like '".$key."%'";
			}
			if(count($expAfd)==$no)
			{
				$whereOrg .= ")";
			}
        }
		
		if($whereOrg!='')
        {
            $whereOrg = "where ".$whereOrg;
        }


        #ambil luas perdiv
        $aLuas=$owlPDO->query("select sum(luasareaproduktif) as luasdivisi from ".$dbname.".setup_blok ".$whereOrg." ");
        $aLuas->setFetchMode(PDO::FETCH_ASSOC);
        $cLuas=$aLuas->fetch();
        $totLuasBlok = $cLuas['luasdivisi'] ?? 0;
        
        #ambil blok TBM,TB,LC
        $str=$owlPDO->query("select kodeorg,statusblok,luasareaproduktif from ".$dbname.".setup_blok ".$whereOrg." ");
        $str->setFetchMode(PDO::FETCH_OBJ);
        $jumblok=owlBaris($str);
        if($jumblok<1){
            exit(" Error: Tidak ada blok yang dapat dialokasi");
        }
        else{
        #ambil total biaya
        $dat=$owlPDO->query("select luasareaproduktif,kodeorg,statusblok from ".$dbname.".setup_blok ".$whereOrg." ");
        $dat->setFetchMode(PDO::FETCH_ASSOC);
        $mulai=1;
        while($row=$dat->fetch()){
            if($mulai==1){
                $lstBlok="'".$row['kodeorg']."'";
                $mulai+=1;
            }else{
                $lstBlok.=",'".$row['kodeorg']."'";
            }
        }
        $totBy=0;
        // $sTotBy="select sum(jumlah) as biy,kodeblok from ".$dbname.".keu_jurnaldt where tanggal like '".$tg."%' and kodeblok in (".$lstBlok.")  and left(noakun,1)>5 group by kodeblok having sum(jumlah)>0";
        // $rTotBy=fetchdata($sTotBy);
        // foreach($rTotBy as $row){
        //     $totBy+=$row['biy'];
        //     $byPerBlok[$row['kodeblok']]=$row['biy'];
        // }        

        #persiapkan no jurnal
        $exist=$owlPDO->query("select nojurnal from ".$dbname.".keu_jurnalht where nojurnal 
                like '%".tanggalsystem($_POST['tanggal'])."/".$_POST['kodeorg']."/IDC/%'");		 
        $exist->setFetchMode(PDO::FETCH_OBJ);
         while($bar1=$exist->fetch()){
           $noterakhir=$bar1->nojurnal;
                 }   		
         if($noterakhir==''){
            $nolanjut='001';
         }else{
                    $xx=explode("/",$noterakhir);
                        $nolanjut=intval($xx[3])+1;
                        $nolanjut=str_pad($nolanjut, 3, "0", STR_PAD_LEFT);
                  }		 
        # Prep Header
        $nojurnal=  tanggalsystem($_POST['tanggal'])."/".$_POST['kodeorg']."/IDC/".$nolanjut; 
         #exit("Error".$nojurnal);		
        $dataRes['header'] = array(
            'nojurnal'=>$nojurnal,
            'kodejurnal'=>'IDC',
            'tanggal'=>  tanggalsystem($_POST['tanggal']),
            'tanggalentry'=>date('Ymd'),
            'posting'=>'1',
            'totaldebet'=>$_POST['jumlah'],
            'totalkredit'=>$_POST['jumlah'],
            'amountkoreksi'=>'0',
            'noreferensi'=>$_POST['nokas'],
            'autojurnal'=>'1',
            'matauang'=>'IDR',
            'kurs'=>'1',
            'revisi'=>'0'
        );

        # Data Detail
        $noUrut = 1;
                        # kredit
                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>tanggalsystem($_POST['tanggal']),
                            'nourut'=>$noUrut,
                            'noakun'=>$_POST['kredit'],
                            'keterangan'=>'Alokasi IDC:'.$_POST['tanggal'],
                            'jumlah'=>-1*$_POST['jumlah'],
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>$_POST['kodeorg'],
                            'kodekegiatan'=>'',
                            'kodeasset'=>'',
                            'kodebarang'=>'',
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$_POST['nokas'],
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>'',
                            'kodeblok'=>'',
                           'revisi'=>'0',
                            'kodesegment'=>''
                        );
                        $noUrut++;  
                while($bar=$str->fetch()){// 'jumlah'=>$_POST['jumlah']/$jumblok,
                        $proporsi=fixnan($bar->luasareaproduktif/$cLuas['luasdivisi'])*$_POST['jumlah'];
                        // if($byPerBlok[$bar->kodeorg]!=0){
                        //     $proporsi=($byPerBlok[$bar->kodeorg]/$totBy)*$_POST['jumlah'];//<td>".$bar->luasareaproduktif."</td>
                        //     $dataRes['detail'][] = array(
                        //         'nojurnal'=>$nojurnal,
                        //         'tanggal'=>tanggalsystem($_POST['tanggal']),
                        //         'nourut'=>$noUrut,
                        //         'noakun'=>$_POST['debet'],
                        //         'keterangan'=>'Alokasi IDC:'.$_POST['tanggal'],
                        //         'jumlah'=>$proporsi,
                        //         'matauang'=>'IDR',
                        //         'kurs'=>'1',
                        //         'kodeorg'=>$_POST['kodeorg'],
                        //         'kodekegiatan'=>$_POST['debet'].'01',
                        //         'kodeasset'=>'',
                        //         'kodebarang'=>'',
                        //         'nik'=>'',
                        //         'kodecustomer'=>'',
                        //         'kodesupplier'=>'',
                        //         'noreferensi'=>$_POST['nokas'],
                        //         'noaruskas'=>'',
                        //         'kodevhc'=>'',
                        //         'nodok'=>'',
                        //         'kodeblok'=>$bar->kodeorg,
                        //        'revisi'=>'0',
                        //         'kodesegment'=>''
                        //     );
                        //     $noUrut++;   
                        // }

                        $dataRes['detail'][] = array(
                            'nojurnal'=>$nojurnal,
                            'tanggal'=>tanggalsystem($_POST['tanggal']),
                            'nourut'=>$noUrut,
                            'noakun'=>$_POST['debet'],
                            'keterangan'=>'Alokasi IDC:'.$_POST['tanggal'],
                            'jumlah'=>$proporsi,
                            'matauang'=>'IDR',
                            'kurs'=>'1',
                            'kodeorg'=>$_POST['kodeorg'],
                            'kodekegiatan'=>$_POST['debet'].'01',
                            'kodeasset'=>'',
                            'kodebarang'=>'',
                            'nik'=>'',
                            'kodecustomer'=>'',
                            'kodesupplier'=>'',
                            'noreferensi'=>$_POST['nokas'],
                            'noaruskas'=>'',
                            'kodevhc'=>'',
                            'nodok'=>'',
                            'kodeblok'=>$bar->kodeorg,
                            'revisi'=>'0',
                            'kodesegment'=>''
                        );
                        $noUrut++;                                              
                }  
              #insert jurnal
                #=== Insert Data ===
                $errorDB = "";
                # Header
                $queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
                try{ $owlPDO->exec($queryH);}
                catch (PDOException $e) {
                $errorDB .= "Error Header :".$e->getMessage()."\n".$queryH;
                }                

                # Detail
                if($errorDB=='') {
                    foreach($dataRes['detail'] as $key=>$dataDet) {
                        $queryD = insertQuery($dbname,'keu_jurnaldt',$dataDet);
                        try{ $owlPDO->exec($queryD);}
                        catch (PDOException $e) {
                            $errorDB .= "Error Detail ".$key.":".$e->getMessage()."\n".$queryH;
                        } 
                    }
                }
                if($errorDB!='')
                {
                    #rollback
                       $where = "nojurnal='".$nojurnal."'";
                       $queryRB = "delete from `".$dbname."`.`keu_jurnalht` where ".$where;
                        try{ $owlPDO->exec($queryRB);}
                        catch (PDOException $e) {
                            $errorDB .= "Rollback 1 Error:".$e->getMessage()."\n".$queryH;
                        } 
                     echo $errorDB;   
                }
        } #end while     
        break;
    case 'hapusJurnal':
        #periksa tutup buku
        $tg=substr($_POST['tanggal'],0,7);
        $str=$owlPDO->query("select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_POST['kodeorg']."' and periode='".$tg."' and tutupbuku=0");
        $str->setFetchMode(PDO::FETCH_OBJ);
        $numrows=owlBaris($str);
        if($numrows<1)
        {
            exit(" Error: Periode tersebut unit telah tutup buku");
        }
        else
        {
                   $str="delete from ".$dbname.".keu_jurnalht where nojurnal='".$_POST['nojurnal']."'";
                        try{ $owlPDO->exec($str);}
                        catch (PDOException $e) {
                            $errorDB .= " Error:".$e->getMessage()."\n".$queryH;
                        } 
        }
        break;
}