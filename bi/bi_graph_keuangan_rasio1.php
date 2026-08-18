<?php
include('master_validation.php'); 
include('../config/connection.php');
include('../lib/nangkoelib.php');
include('../lib/zLib.php');
 
$pt = checkPostGet('pt','');
$thn = checkPostGet('thn','');
$method = checkPostGet('method','');
$unit = checkPostGet('unit','');
$id = checkPostGet('id','');

$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi','char_length(kodeorganisasi)<6');
//ambil namapt
$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$pt."'";
$namapt=$_SESSION['lang']['all'];
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
    $namapt=strtoupper($bar->namaorganisasi);
}
#++++++++++++++++++++++++++++++++++++++++++
$kodelaporan='RASIO';
if($pt=='')
    $where=" kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where tipe='HOLDING')";
else 
    $where=" kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='HOLDING')";
$captionCUR=$thn;
$captionPRF=intval($thn-1);

switch($method){
	case'detailgraph':
	
		$jlhkolom=7;
		$addEx="";

		$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' order by nourut asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$res->fetch()){
		    $dzArr[$bar->nourut]['nourut']=$bar->nourut;
		    $dzArr[$bar->nourut]['tampil']=$bar->variableoutput;    
		    $dzArr[$bar->nourut]['tipe']=$bar->tipe;
		    if($_SESSION['language']=='ID'){
		        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay;
		    }
		    else{
		        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay1;
		    }
		    $dzArr[$bar->nourut]['noakundari']=$bar->noakundari;
		    $dzArr[$bar->nourut]['noakunsampai']=$bar->noakunsampai;
		    $dzArr[$bar->nourut]['noakundisplay']=$bar->noakundisplay;
		}
	

		#query data#
		$sData="select right(periode,2) as bulan,sum(pendapatan) as pendapatan,sum(lababersih) as lababersih,sum(bebanpokok) as bebanpokok,
		        sum(labakotor) as labakotor,sum(labausaha) as labausaha,sum(beban_keuangan) as beban_keuangan,
		        sum(by_umum) as by_umum,sum(depresiasi) as depresiasi,sum(total_ekuitas) as total_ekuitas,
		        sum(total_asset) as total_asset,sum(aset_lancar) as aset_lancar,sum(piutang_lancar) as piutang_lancar,
		        sum(persediaan) as persediaan,sum(liabilitas_pendek) as liabilitas_pendek,sum(hutang_lancar) as hutang_lancar,
		        sum(liabilitas_panjang) as liabilitas_panjang,sum(total_liabilitas) as total_liabilitas,sum(hutangjksthun) as hutangjksthun,induk
		        from ".$dbname.".keu_4rasio a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
		        where ".$where." and left(periode,4)='".$captionCUR."' group by right(periode,2) ";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		while($rDataCur=$qData->fetch()){
			$lstBulan[$rDataCur['bulan']]=$rDataCur['bulan'];
			$lstBulanSkrg[$rDataCur['bulan']]=$rDataCur['bulan'];
			$dtCur[$rDataCur['bulan']]['pendapatan']=$rDataCur['pendapatan'];
			$dtCur[$rDataCur['bulan']]['lababersih']=$rDataCur['lababersih'];
			$dtCur[$rDataCur['bulan']]['bebanpokok']=$rDataCur['bebanpokok'];
			$dtCur[$rDataCur['bulan']]['labakotor']=$rDataCur['labakotor'];
			$dtCur[$rDataCur['bulan']]['labausaha']=$rDataCur['labausaha'];
			$dtCur[$rDataCur['bulan']]['beban_keuangan']=$rDataCur['beban_keuangan'];
			$dtCur[$rDataCur['bulan']]['by_umum']=$rDataCur['by_umum'];
			$dtCur[$rDataCur['bulan']]['depresiasi']=$rDataCur['depresiasi'];
			$dtCur[$rDataCur['bulan']]['total_ekuitas']=$rDataCur['total_ekuitas'];
			$dtCur[$rDataCur['bulan']]['total_asset']=$rDataCur['total_asset'];
			$dtCur[$rDataCur['bulan']]['aset_lancar']=$rDataCur['aset_lancar'];
			$dtCur[$rDataCur['bulan']]['piutang_lancar']=$rDataCur['piutang_lancar'];
			$dtCur[$rDataCur['bulan']]['persediaan']=$rDataCur['persediaan'];
			$dtCur[$rDataCur['bulan']]['hutang_lancar']=$rDataCur['hutang_lancar'];
			$dtCur[$rDataCur['bulan']]['liabilitas_pendek']=$rDataCur['liabilitas_pendek'];
			$dtCur[$rDataCur['bulan']]['liabilitas_panjang']=$rDataCur['liabilitas_panjang'];
			$dtCur[$rDataCur['bulan']]['total_liabilitas']=$rDataCur['total_liabilitas'];
			$dtCur[$rDataCur['bulan']]['hutangjksthun']=$rDataCur['hutangjksthun'];
		}

		$sData="select right(periode,2) as bulan,sum(pendapatan) as pendapatan,sum(lababersih) as lababersih,sum(bebanpokok) as bebanpokok,
		        sum(labakotor) as labakotor,sum(labausaha) as labausaha,sum(beban_keuangan) as beban_keuangan,
		        sum(by_umum) as by_umum,sum(depresiasi) as depresiasi,sum(total_ekuitas) as total_ekuitas,
		        sum(total_asset) as total_asset,sum(aset_lancar) as aset_lancar,sum(piutang_lancar) as piutang_lancar,
		        sum(persediaan) as persediaan,sum(liabilitas_pendek) as liabilitas_pendek,sum(hutang_lancar) as hutang_lancar,
		        sum(liabilitas_panjang) as liabilitas_panjang,sum(total_liabilitas) as total_liabilitas,sum(hutangjksthun) as hutangjksthun,induk
		        from ".$dbname.".keu_4rasio a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
		        where ".$where." and left(periode,4)='".$captionPRF."' group by right(periode,2)";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
		while($rDataPrf=$qData->fetch()){
			$lstBulan[$rDataPrf['bulan']]=$rDataPrf['bulan'];
			$dtPrf[$rDataPrf['bulan']]['pendapatan']=$rDataPrf['pendapatan'];
			$dtPrf[$rDataPrf['bulan']]['lababersih']=$rDataPrf['lababersih'];
			$dtPrf[$rDataPrf['bulan']]['bebanpokok']=$rDataPrf['bebanpokok'];
			$dtPrf[$rDataPrf['bulan']]['labakotor']=$rDataPrf['labakotor'];
			$dtPrf[$rDataPrf['bulan']]['labausaha']=$rDataPrf['labausaha'];
			$dtPrf[$rDataPrf['bulan']]['beban_keuangan']=$rDataPrf['beban_keuangan'];
			$dtPrf[$rDataPrf['bulan']]['by_umum']=$rDataPrf['by_umum'];
			$dtPrf[$rDataPrf['bulan']]['depresiasi']=$rDataPrf['depresiasi'];
			$dtPrf[$rDataPrf['bulan']]['total_ekuitas']=$rDataPrf['total_ekuitas'];
			$dtPrf[$rDataPrf['bulan']]['total_asset']=$rDataPrf['total_asset'];
			$dtPrf[$rDataPrf['bulan']]['aset_lancar']=$rDataPrf['aset_lancar'];
			$dtPrf[$rDataPrf['bulan']]['piutang_lancar']=$rDataPrf['piutang_lancar'];
			$dtPrf[$rDataPrf['bulan']]['persediaan']=$rDataPrf['persediaan'];
			$dtPrf[$rDataPrf['bulan']]['hutang_lancar']=$rDataPrf['hutang_lancar'];
			$dtPrf[$rDataPrf['bulan']]['liabilitas_pendek']=$rDataPrf['liabilitas_pendek'];
			$dtPrf[$rDataPrf['bulan']]['liabilitas_panjang']=$rDataPrf['liabilitas_panjang'];
			$dtPrf[$rDataPrf['bulan']]['total_liabilitas']=$rDataPrf['total_liabilitas'];
			$dtPrf[$rDataPrf['bulan']]['hutangjksthun']=$rDataPrf['hutangjksthun'];
		}
	@$jmlhBln=count($lstBulan);
	@$jldata=count($lstBulanSkrg);
	if($jldata==0){
		echo $_SESSION['lang']['dataempty'];
	}else{
		foreach($dzArr as $data){
		foreach($lstBulan as $dtBulan){
			switch ($data['nourut']) {
	        case '120002':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=$dtCur[$dtBulan]['labakotor']/$dtCur[$dtBulan]['pendapatan'];
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=$dtPrf[$dtBulan]['labakotor']/$dtPrf[$dtBulan]['pendapatan'];
	        break;
	        case '120003':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=$dtCur[$dtBulan]['labausaha']/$dtCur[$dtBulan]['pendapatan'];
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=$dtPrf[$dtBulan]['labausaha']/$dtPrf[$dtBulan]['pendapatan'];
	        break;
	        case '120004':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=$dtCur[$dtBulan]['lababersih']/$dtCur[$dtBulan]['pendapatan'];
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=$dtPrf[$dtBulan]['lababersih']/$dtPrf[$dtBulan]['pendapatan'];
	        break;
	        case '120005':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=$dtCur[$dtBulan]['labausaha']/($dtCur[$dtBulan]['beban_keuangan']*-1);
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=$dtPrf[$dtBulan]['labausaha']/($dtPrf[$dtBulan]['beban_keuangan']*-1);
	        break;
	        case'220002':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=$dtCur[$dtBulan]['lababersih']/$dtCur[$dtBulan]['total_ekuitas'];
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=$dtPrf[$dtBulan]['lababersih']/$dtPrf[$dtBulan]['total_ekuitas'];
	        break;
	        case'220003':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=$dtCur[$dtBulan]['lababersih']/$dtCur[$dtBulan]['total_asset'];
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=$dtPrf[$dtBulan]['lababersih']/$dtPrf[$dtBulan]['total_asset'];
	        break;
	        case'220004':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=$dtCur[$dtBulan]['aset_lancar']/$dtCur[$dtBulan]['liabilitas_pendek'];
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=$dtPrf[$dtBulan]['aset_lancar']/$dtPrf[$dtBulan]['liabilitas_pendek'];
	        break;
	        case'220005':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=($dtCur[$dtBulan]['aset_lancar']-$dtCur[$dtBulan]['persediaan'])/$dtCur[$dtBulan]['liabilitas_pendek'];
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=($dtPrf[$dtBulan]['aset_lancar']-$dtPrf[$dtBulan]['persediaan'])/$dtPrf[$dtBulan]['liabilitas_pendek'];
	        break;
	        case'320002':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=($dtCur[$dtBulan]['bebanpokok']*-1)/$dtCur[$dtBulan]['persediaan'];
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=($dtPrf[$dtBulan]['bebanpokok']*-1)/$dtPrf[$dtBulan]['persediaan'];
	        break;
	        case'320003':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=$dtCur[$dtBulan]['pendapatan']/$dtCur[$dtBulan]['piutang_lancar'];
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=$dtPrf[$dtBulan]['pendapatan']/$dtPrf[$dtBulan]['piutang_lancar'];
	        break;
	        case'320004':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=299/$dzArr['320003'][$dtBulan]['rasiosekarang'];
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=299/$dzArr['320003'][$dtBulan]['rasiolalu'];
	        break;
	        case'320005':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=($dtCur[$dtBulan]['bebanpokok']*-1)/$dtCur[$dtBulan]['hutang_lancar'];
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=($dtPrf[$dtBulan]['bebanpokok']*-1)/$dtPrf[$dtBulan]['hutang_lancar'];
	        break;
	        case'320006':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=299/$dzArr['320005'][$dtBulan]['rasiosekarang'];
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=299/$dzArr['320005'][$dtBulan]['rasiolalu'];
	        break;
	        case'420002':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=($dtCur[$dtBulan]['hutangjksthun']+$dtCur[$dtBulan]['liabilitas_panjang'])/$dtCur[$dtBulan]['total_ekuitas'];
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=($dtPrf[$dtBulan]['hutangjksthun']+$dtPrf[$dtBulan]['liabilitas_panjang'])/$dtPrf[$dtBulan]['total_ekuitas'];
	        break;
	        case'420003':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=$dtCur[$dtBulan]['total_liabilitas']/$dtCur[$dtBulan]['total_ekuitas'];
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=$dtPrf[$dtBulan]['total_liabilitas']/$dtPrf[$dtBulan]['total_ekuitas'];
	        break;
	        case'520002':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=$dtCur[$dtBulan]['cash_flow']+$dtCur[$dtBulan]['capex'];
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=$dtPrf[$dtBulan]['cash_flow']+$dtPrf[$dtBulan]['capex'];
	        break;
	        case'520003':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=($dzArr['520002'][$dtBulan]['rasiosekarang']/$dtCur[$dtBulan]['beban_keuangan'])*-1;
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=($dzArr['520002'][$dtBulan]['rasiolalu']/$dtPrf[$dtBulan]['beban_keuangan'])*-1;
	        break;
	        case'520004':
	            @$dzArr[$data['nourut']][$dtBulan]['rasiosekarang']=($dtCur[$dtBulan]['investing_cashflow']/$dtCur[$dtBulan]['cash_flow'])*-1;
	            @$dzArr[$data['nourut']][$dtBulan]['rasiolalu']=($dtPrf[$dtBulan]['investing_cashflow']/$dtPrf[$dtBulan]['cash_flow'])*-1;
	        break;

	    }
		}
	}

		 echo"<link rel=stylesheet type=text/css href=../style/genericbi.css>";
		 $stream.="<table class=sortable border=0 cellspacing=1 width=100%>
				   <thead>
				   <tr class=rowheader>
				   <td rowspan=2 colspan=5 align=center>".$_SESSION['lang']['keterangan']."</td>    
				   <td rowspan=2 align=center>Standard</td>
				   <td colspan=".$jmlhBln." align=center>".$_SESSION['lang']['rasio']."</td>
				   </tr>
				   <tr class=rowheader>";
				   foreach($lstBulan as $dtBulan){
				   	$stream.="<td align=center>".$dtBulan."-".$captionCUR."</td>";
				   }
		$stream.="</tr>
				   </thead><tbody>";
		#ambil format mesinlaporan==========
		if(!empty($dzArr)){
		        foreach($dzArr as $data){
		        
		        if($data['tipe']=='Header')
		        {
		            if($data['tampil']==0)
		                $stream.="<tr class=rowcontent><td colspan=".(6+$jmlhBln)."><b>".strtoupper($data['keterangan'])."</b></td></tr>";  
		            else{
		                $stream.="<tr class=rowcontent>
		                    <td colspan=".$data['tampil']."></td>
		                    <td colspan=".(6+$jmlhBln)."><b>".strtoupper($data['keterangan'])."</b></td>
		                </tr>"; 
		            }
		        }
		        else if($data['tipe']=='Total'){
		            if($data['tampil']==0){
		                $stream.="<tr class=rowcontent>
		                    <td colspan=5><b>".strtoupper($data['keterangan'])."</b></td>
		                    <td align=center><b>".$data['noakundisplay']."</b></td>";
		                foreach($lstBulan as $dtBulan){
			                	$stream.="
			                    <td align=right><b>".number_format($dzArr[$data['nourut']][$dtBulan]['rasiosekarang'],2)."</b></td>";
		                	
		                }
		                $stream.="</tr>";
		                if($proses!='excel'){
		                    $stream.="<tr class=rowcontent>
		                        <td colspan=".(6+$jmlhBln).">&nbsp;</td>
		                    </tr>";
		                } 
		            }
		            else
		            {
		                $stream.="<tr class=rowcontent>
		                    <td colspan=".(6+$jmlhBln)."></td>
		                    
		                </tr>
		                <tr class=rowcontent>
		                    <td colspan=".$data['tampil']."></td>
		                    <td>".$data['keterangan']."</td>
		                    <td align=center >".$data['noakundisplay']."</td>";
		                foreach($lstBulan as $dtBulan){
			                	$stream.="
			                    <td align=right><b>".number_format($dzArr[$data['nourut']][$dtBulan]['rasiosekarang'],2)."</b></td>";
		                }
		                $stream.="</tr>
		                <tr class=rowcontent><td colspan=".(6+$jmlhBln).">.</td></tr>
		                ";                
		            }   
		        }
		        else if($data['tipe']=='Detail'){
		            $stream.="
		            <tr class=rowcontent>
		                <td colspan=".($data['tampil'])."></td>
		                <td colspan=".(5-$data['tampil']).">".$data['keterangan']."</td>
		                <td align=center>".$data['noakundisplay']."</td>";
		                foreach($lstBulan as $dtBulan){
		                	$stream.="
			                    <td align=right><b>".number_format($dzArr[$data['nourut']][$dtBulan]['rasiosekarang'],2)."</b></td>";
		                }   
		            $stream.="</tr>";   
		        }
		    }//end for foreach $dZarr
		}
		
		$stream.= "</tbody></tfoot></tfoot></table>";
		echo $stream;
	}
		
	break;
	
	
	case'global':
	$jlhkolom=7;
	$addEx="";

	$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."' order by nourut asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while($bar=$res->fetch()){
	    $dzArr[$bar->nourut]['nourut']=$bar->nourut;
	    $dzArr[$bar->nourut]['tampil']=$bar->variableoutput;    
	    $dzArr[$bar->nourut]['tipe']=$bar->tipe;
	    if($_SESSION['language']=='ID'){
	        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay;
	    }
	    else{
	        $dzArr[$bar->nourut]['keterangan']=$bar->keterangandisplay1;
	    }
	    $dzArr[$bar->nourut]['noakundari']=$bar->noakundari;
	    $dzArr[$bar->nourut]['noakunsampai']=$bar->noakunsampai;
	    $dzArr[$bar->nourut]['noakundisplay']=$bar->noakundisplay;
	}
	

	#query data#
	$sData="select sum(pendapatan) as pendapatan,sum(lababersih) as lababersih,sum(bebanpokok) as bebanpokok,
	        sum(labakotor) as labakotor,sum(labausaha) as labausaha,sum(beban_keuangan) as beban_keuangan,
	        sum(by_umum) as by_umum,sum(depresiasi) as depresiasi,sum(total_ekuitas) as total_ekuitas,
	        sum(total_asset) as total_asset,sum(aset_lancar) as aset_lancar,sum(piutang_lancar) as piutang_lancar,
	        sum(persediaan) as persediaan,sum(liabilitas_pendek) as liabilitas_pendek,sum(hutang_lancar) as hutang_lancar,
	        sum(liabilitas_panjang) as liabilitas_panjang,sum(total_liabilitas) as total_liabilitas,sum(hutangjksthun) as hutangjksthun,induk
	        from ".$dbname.".keu_4rasio a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
	        where ".$where." and left(periode,4)='".$captionCUR."'";
	$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
	$qData->setFetchMode(PDO::FETCH_ASSOC);
	$rDataCur=$qData->fetch();

	$sData="select sum(pendapatan) as pendapatan,sum(lababersih) as lababersih,sum(bebanpokok) as bebanpokok,
	        sum(labakotor) as labakotor,sum(labausaha) as labausaha,sum(beban_keuangan) as beban_keuangan,
	        sum(by_umum) as by_umum,sum(depresiasi) as depresiasi,sum(total_ekuitas) as total_ekuitas,
	        sum(total_asset) as total_asset,sum(aset_lancar) as aset_lancar,sum(piutang_lancar) as piutang_lancar,
	        sum(persediaan) as persediaan,sum(liabilitas_pendek) as liabilitas_pendek,sum(hutang_lancar) as hutang_lancar,
	        sum(liabilitas_panjang) as liabilitas_panjang,sum(total_liabilitas) as total_liabilitas,sum(hutangjksthun) as hutangjksthun,induk
	        from ".$dbname.".keu_4rasio a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
	        where ".$where." and left(periode,4)='".$captionPRF."'";
	$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
	$qData->setFetchMode(PDO::FETCH_ASSOC);
	$rDataPrf=$qData->fetch();

	foreach($dzArr as $data){
	    switch ($data['nourut']) {
	        case '120002':
	            @$dzArr[$data['nourut']]['rasiosekarang']=$rDataCur['labakotor']/$rDataCur['pendapatan'];
	            @$dzArr[$data['nourut']]['rasiolalu']=$rDataPrf['labakotor']/$rDataPrf['pendapatan'];
	        break;
	        case '120003':
	            @$dzArr[$data['nourut']]['rasiosekarang']=$rDataCur['labausaha']/$rDataCur['pendapatan'];
	            @$dzArr[$data['nourut']]['rasiolalu']=$rDataPrf['labausaha']/$rDataPrf['pendapatan'];
	        break;
	        case '120004':
	            @$dzArr[$data['nourut']]['rasiosekarang']=$rDataCur['lababersih']/$rDataCur['pendapatan'];
	            @$dzArr[$data['nourut']]['rasiolalu']=$rDataPrf['lababersih']/$rDataPrf['pendapatan'];
	        break;
	        case '120005':
	            @$dzArr[$data['nourut']]['rasiosekarang']=$rDataCur['labausaha']/($rDataCur['beban_keuangan']*-1);
	            @$dzArr[$data['nourut']]['rasiolalu']=$rDataPrf['labausaha']/($rDataPrf['beban_keuangan']*-1);
	        break;
	        case'220002':
	            @$dzArr[$data['nourut']]['rasiosekarang']=$rDataCur['lababersih']/$rDataCur['total_ekuitas'];
	            @$dzArr[$data['nourut']]['rasiolalu']=$rDataPrf['lababersih']/$rDataPrf['total_ekuitas'];
	        break;
	        case'220003':
	            @$dzArr[$data['nourut']]['rasiosekarang']=$rDataCur['lababersih']/$rDataCur['total_asset'];
	            @$dzArr[$data['nourut']]['rasiolalu']=$rDataPrf['lababersih']/$rDataPrf['total_asset'];
	        break;
	        case'220004':
	            @$dzArr[$data['nourut']]['rasiosekarang']=$rDataCur['aset_lancar']/$rDataCur['liabilitas_pendek'];
	            @$dzArr[$data['nourut']]['rasiolalu']=$rDataPrf['aset_lancar']/$rDataPrf['liabilitas_pendek'];
	        break;
	        case'220005':
	            @$dzArr[$data['nourut']]['rasiosekarang']=($rDataCur['aset_lancar']-$rDataCur['persediaan'])/$rDataCur['liabilitas_pendek'];
	            @$dzArr[$data['nourut']]['rasiolalu']=($rDataPrf['aset_lancar']-$rDataPrf['persediaan'])/$rDataPrf['liabilitas_pendek'];
	        break;
	        case'320002':
	            @$dzArr[$data['nourut']]['rasiosekarang']=($rDataCur['bebanpokok']*-1)/$rDataCur['persediaan'];
	            @$dzArr[$data['nourut']]['rasiolalu']=($rDataPrf['bebanpokok']*-1)/$rDataPrf['persediaan'];
	        break;
	        case'320003':
	            @$dzArr[$data['nourut']]['rasiosekarang']=$rDataCur['pendapatan']/$rDataCur['piutang_lancar'];
	            @$dzArr[$data['nourut']]['rasiolalu']=$rDataPrf['pendapatan']/$rDataPrf['piutang_lancar'];
	        break;
	        case'320004':
	            @$dzArr[$data['nourut']]['rasiosekarang']=299/$dzArr['320003']['rasiosekarang'];
	            @$dzArr[$data['nourut']]['rasiolalu']=299/$dzArr['320003']['rasiolalu'];
	        break;
	        case'320005':
	            @$dzArr[$data['nourut']]['rasiosekarang']=($rDataCur['bebanpokok']*-1)/$rDataCur['hutang_lancar'];
	            @$dzArr[$data['nourut']]['rasiolalu']=($rDataPrf['bebanpokok']*-1)/$rDataPrf['hutang_lancar'];
	        break;
	        case'320006':
	            @$dzArr[$data['nourut']]['rasiosekarang']=299/$dzArr['320005']['rasiosekarang'];
	            @$dzArr[$data['nourut']]['rasiolalu']=299/$dzArr['320005']['rasiolalu'];
	        break;
	        case'420002':
	            @$dzArr[$data['nourut']]['rasiosekarang']=($rDataCur['hutangjksthun']+$rDataCur['liabilitas_panjang'])/$rDataCur['total_ekuitas'];
	            @$dzArr[$data['nourut']]['rasiolalu']=($rDataPrf['hutangjksthun']+$rDataPrf['liabilitas_panjang'])/$rDataPrf['total_ekuitas'];
	        break;
	        case'420003':
	            @$dzArr[$data['nourut']]['rasiosekarang']=$rDataCur['total_liabilitas']/$rDataCur['total_ekuitas'];
	            @$dzArr[$data['nourut']]['rasiolalu']=$rDataPrf['total_liabilitas']/$rDataPrf['total_ekuitas'];
	        break;
	        case'520002':
	            @$dzArr[$data['nourut']]['rasiosekarang']=$rDataCur['cash_flow']+$rDataCur['capex'];
	            @$dzArr[$data['nourut']]['rasiolalu']=$rDataPrf['cash_flow']+$rDataPrf['capex'];
	        break;
	        case'520003':
	            @$dzArr[$data['nourut']]['rasiosekarang']=($dzArr['520002']['rasiosekarang']/$rDataCur['beban_keuangan'])*-1;
	            @$dzArr[$data['nourut']]['rasiolalu']=($dzArr['520002']['rasiolalu']/$rDataPrf['beban_keuangan'])*-1;
	        break;
	        case'520004':
	            @$dzArr[$data['nourut']]['rasiosekarang']=($rDataCur['investing_cashflow']/$rDataCur['cash_flow'])*-1;
	            @$dzArr[$data['nourut']]['rasiolalu']=($rDataPrf['investing_cashflow']/$rDataPrf['cash_flow'])*-1;
	        break;

	    }
	}

		 echo"<link rel=stylesheet type=text/css href=../style/generic.css>";
		 $stream.="<table class=sortable border=0 cellspacing=1 width=100%>
				   <thead>
				   <tr class=rowheader>
				   <td rowspan=2 colspan=5 align=center>".$_SESSION['lang']['keterangan']."</td>    
				   <td rowspan=2 align=center>Standard</td>
				   <td colspan=2 align=center>".$_SESSION['lang']['rasio']."</td>
				   </tr>
				   <tr class=rowheader>
				   <td align=center>".$captionCUR."</td>
				   <td align=center>".$captionPRF."</td>
				   </tr>
				   </thead><tbody>";
		#ambil format mesinlaporan==========
		if(!empty($dzArr)){
		        foreach($dzArr as $data){
		        
		        if($data['tipe']=='Header')
		        {
		            if($data['tampil']==0)
		                $stream.="<tr class=rowcontent><td colspan=8><b>".strtoupper($data['keterangan'])."</b></td></tr>";  
		            else{
		                $stream.="<tr class=rowcontent>
		                    <td colspan=".$data['tampil']."></td>
		                    <td colspan=".($jlhkolom-$data['tampil'])."><b>".strtoupper($data['keterangan'])."</b> sdsd</td>
		                </tr>"; 
		            }
		        }
		        else if($data['tipe']=='Total'){
		            if($data['tampil']==0){
		                if($method!='excel'){
		                    $stream.="<tr class=rowcontent>
		                        <td colspan=8></td>
		                        </tr>";    
		                }
		                $stream.="<tr class=rowcontent>
		                    <td colspan=5><b>".strtoupper($data['keterangan'])."</b></td>
		                    <td align=center><b>".$data['noakundisplay']."</b></td>
		                    <td align=right><b>".number_format($data['rasiosekarang'],2)."</b></td>
		                    <td align=right><b>".number_format($data['rasiolalu'],2)."</b></td>
		                </tr>";
		                if($method!='excel'){
		                    $stream.="<tr class=rowcontent>
		                        <td colspan=8>&nbsp;</td>
		                    </tr>";
		                } 
		            }
		            else
		            {
		                $stream.="<tr class=rowcontent>
		                    <td colspan=8></td>
		                    
		                </tr>
		                <tr class=rowcontent>
		                    <td colspan=".$data['tampil']."></td>
		                    <td><b>".$data['keterangan']."</b></td>
		                    <td align=center ><b>".$data['noakundisplay']."</b></td>
		                    <td align=right><b>".number_format($data['rasiosekarang'],2)."</b></td>
		                    <td align=right><b>".number_format($data['rasiolalu'],2)."</b></td>    
		                </tr>
		                <tr class=rowcontent><td colspan=8>.</td></tr>
		                ";                
		            }   
		        }
		        else if($data['tipe']=='Detail'){
		            $stream.="
		            <tr class=rowcontent>
		                <td colspan=".($data['tampil'])."></td>
		                <td colspan=".(5-$data['tampil']).">".$data['keterangan']."</td>
		                <td align=center>".$data['noakundisplay']."</td>
		                <td align=right >".number_format($data['rasiosekarang'],2)."</td>
		                <td align=right>".number_format($data['rasiolalu'],2)."</b></td>    
		            </tr>";   
		        }
		    }//end for foreach $dZarr
		}
		
		$stream.= "</tbody></tfoot></tfoot></table>";
		echo $stream;
	break;
}

?>