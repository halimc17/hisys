// JavaScript Document
function clearForm(){
    document.getElementById('notransaksi').value='';
	document.getElementById('kdOrg').value='';
	document.getElementById('tgl').value='';
	document.getElementById('tgl').disabled=false;
	document.getElementById('nokontrak').value='';
	document.getElementById('komoditi').value='';
	document.getElementById('surveyor').value='';
	document.getElementById('ptsurveyor').value='';
	document.getElementById('chief').value='';
	document.getElementById('hdBlking').value='';
	document.getElementById('nmKapal').value='';
	document.getElementById('tglMulai').value='';
	document.getElementById('tglSlsi').value='';
	document.getElementById('aslKrm').value='';
	document.getElementById('tujuan').value='';
	document.getElementById('kgAwal').value='';
	document.getElementById('kgAkhir').value='';
	document.getElementById('TotMuat').value='';
	document.getElementById('proses').value='insert';
	document.getElementById('tglMulaiJm').value='00';
	document.getElementById('tglMulaiMnt').value='00';
	document.getElementById('tglSlsiJm').value='00';
	document.getElementById('tglSlsiMnt').value='00';
	document.getElementById('tinggiAwal').value='';
	document.getElementById('suhuAwal').value='';
	document.getElementById('tinggiAkhir').value='';
	document.getElementById('suhuAkhir').value='';
}

function add_new_data() {
        document.getElementById('headher').style.display = "block";
        document.getElementById('listData').style.display = "none";
        document.getElementById('detailEntry').style.display = "none";
        unlockForm();
        document.getElementById('contentDetail').innerHTML = '';
        statFrm = 0;
 }

function displayList(){
        document.getElementById('listData').style.display='block';
        document.getElementById('headher').style.display='none';
        document.getElementById('detailEntry').style.display='none';
        clearForm();
        loadData(0);
}
function lockForm(){
        // document.getElementById('jenis').disabled=true;
        // document.getElementById('tgl').disabled=true;
        // document.getElementById('nokontrak').disabled=true;
        // document.getElementById('noba').disabled=true;
        // document.getElementById('lokasi').disabled=true;
        document.getElementById('tombolHeader').style.display="none";
}
function unlockForm(){
        // document.getElementById('jenis').disabled=false;
        // document.getElementById('tgl').disabled=false;
        // document.getElementById('nokontrak').disabled=false;
        // document.getElementById('noba').disabled=false;
        // document.getElementById('lokasi').disabled=false;
        document.getElementById('tombolHeader').style.display="block";
        clearForm();
}
function getKomoditi(){
    nokontrak=document.getElementById('nokontrak');
    nokontrak=nokontrak.options[nokontrak.selectedIndex].value;
    param='nokontrak='+nokontrak+'&proses=getKomoditi';
    tujuan = 'pabrik_slave_dispacth.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert(con.responseText);
                }
                else {
                    document.getElementById('komoditi').innerHTML = con.responseText;
                }
            }
            else
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function loadData(num){
    
    notransaksi=document.getElementById('notransaksiCr').value;
    tgl=document.getElementById('tglCr').value;
    nokontrak=document.getElementById('nokontrakCr').value;
    param = 'proses=loadNewData&page=' + num;
    param+='&notransaksi='+notransaksi+'&notransaksi='+notransaksi;
    param+='&tgl='+tgl+'&nokontrak='+nokontrak;
    tujuan = 'pabrik_slave_dispacth.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alert(con.responseText);
                }
                else {
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                    closeDialog4();
                }
            }
            else
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function add_detail(fileTarget,passParam){
    var passP = passParam.split('##');
    var param = "";
    
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
	
    tujuan=fileTarget+'.php';
    post_response_text(tujuan, param, respog);
    function respog(){
          if(con.readyState==4){
            if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else{
                            document.getElementById('detailEntry').style.display='block';
                            document.getElementById('notransaksi').value=con.responseText;
                            //document.getElementById('detailIsi').innerHTML=con.responseText;
                            //document.getElementById('tmbLheader').innerHTML='';
                            lockForm();
                            showDetail(con.responseText);
                        }
                }
                else{
                        busy_off();
                        error_catch(con.status);
                }
          } 
     }  
}
// $arr="##notransaksi##kdOrg##tgl##nokontrak##komoditi##surveyor##chief##hdBlking##nmKapal";
// $arr.="##tglMulai##tglSlsi##aslKrm##tujuan##kgAwal##kgAkhir##TotMuat##proses";
// $arr.="##tglMulaiJm##tglMulaiMnt##tglSlsiJm##tglSlsiMnt";
//','".$bar['asalkirim']."','".$bar['tujuan']."','".$bar['surveyor']."','".$bar['cheif']."','".$bar['head_bulking']."','".$bar['namakapal']."','".$bar['kgawal']."','".$bar['kgakhir']."','".$bar['totalmuat']."','".$jmMulai[0].":".$jmMulai[1]."','".$jmSlsi[0].":".$jmSlsi[1]."'
function edit(notrans,tgl,nokontrak,komoditi,tglMulai,
				tglSlsi,aslKrm,tujuan,surveyor,ptsurveyor,
				chief,hdBlking,nmKapal,kgAwal,kgAkhir,
				TotMuat,jmMulai,jmSlsi,tinggiAwal,suhuAwal,
				tinggiAkhir,suhuAkhir,kdOrg){
				unlockForm();
	document.getElementById('kdOrg').value=kdOrg;
	document.getElementById('tinggiAwal').value=tinggiAwal;
	document.getElementById('suhuAwal').value=suhuAwal;
	document.getElementById('tinggiAkhir').value=tinggiAkhir;
	document.getElementById('suhuAkhir').value=suhuAkhir;	
    document.getElementById('notransaksi').value=notrans;
	document.getElementById('ptsurveyor').value=ptsurveyor;
    document.getElementById('tgl').value=tgl;
    document.getElementById('tgl').disabled=true;
    document.getElementById('nokontrak').value=nokontrak;
    document.getElementById('komoditi').innerHTML=komoditi;
    document.getElementById('surveyor').value=surveyor;
    document.getElementById('chief').value=chief;
    document.getElementById('hdBlking').value=hdBlking;
    document.getElementById('nmKapal').value=nmKapal;
    document.getElementById('tglMulai').value=tglMulai;
    document.getElementById('tglSlsi').value=tglSlsi;
    jmmulai=jmMulai.split(":");
    jammulai=document.getElementById('tglMulaiJm');
    for(a=0;a<jammulai.length;a++){
        if(jammulai.options[a].value==jmmulai[0]){
            jammulai.options[a].selected=true;
        }
    }
    mntmulai=document.getElementById('tglMulaiMnt');
    for(a=0;a<mntmulai.length;a++){
        if(mntmulai.options[a].value==jmmulai[1]){
            mntmulai.options[a].selected=true;
        }
    }
    jmslsi=jmSlsi.split(":");
    jamslsi=document.getElementById('tglSlsiJm');
    for(a=0;a<jamslsi.length;a++){
        if(jamslsi.options[a].value==jmslsi[0]){
            jamslsi.options[a].selected=true;
        }
    }
    mntslsi=document.getElementById('tglSlsiMnt');
    for(a=0;a<mntslsi.length;a++){
        if(mntslsi.options[a].value==jmslsi[1]){
            mntslsi.options[a].selected=true;
        }
    }
    document.getElementById('aslKrm').value=aslKrm;
    document.getElementById('tujuan').value=tujuan;
    document.getElementById('kgAwal').value=kgAwal;
    document.getElementById('kgAkhir').value=kgAkhir;
    document.getElementById('TotMuat').value=TotMuat;
    document.getElementById('proses').value='editheader';
	//unlockForm();
    showDetail(notrans);
}
function showDetail(notrans){
    param='notransaksi='+notrans+'&proses=createTable';
    tujuan='pabrik_slave_dispacth.php';
    post_response_text(tujuan, param, respog);
    function respog(){
          if(con.readyState==4){
            if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else{
                            document.getElementById('listData').style.display='none';
                            document.getElementById('headher').style.display='block';
                            document.getElementById('detailEntry').style.display='block';
                            //document.getElementById('tmbLheader').innerHTML='';
                            document.getElementById('detailIsi').innerHTML=con.responseText;
                            loadDetail();
                        }
                }
                else{
                        busy_off();
                        error_catch(con.status);
                }
          } 
     }
}
function saveBaa(fileTarget,passParam){
    var passP = passParam.split('##');
    var param = "";
    
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
    notransaksi=document.getElementById('notransaksi').value;
    kdOrg=document.getElementById('kdOrg');
    kdOrg=kdOrg.options[kdOrg.selectedIndex].value;
    komoditi=document.getElementById('komoditi');
    komoditi=komoditi.options[komoditi.selectedIndex].value;
    param+='&proses=addBaa'+'&notransaksi='+notransaksi+'&kdOrg='+kdOrg;
    param+='&komoditi='+komoditi;
    tujuan=fileTarget+'.php';
    post_response_text(tujuan, param, respog);
    function respog(){
          if(con.readyState==4){
            if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else{
                            document.getElementById('nobaa').value=con.responseText;
							clearBaa(passParam);
                            loadDetail();
                        }
                }
                else{
                        busy_off();
                        error_catch(con.status);
                }
          } 
     }
}
/*
saveBaa('pabrik_slave_dispacth','##nobaa##tanggalbaa##kdPbarik##kdTangki##ffa##moisture##dirt##
jamanalisamulai##menitanalisamulai##jamanalisaselesai##menitanalisaselesai')
*/
function clearBaa(passParam){
    var passP = passParam.split('##');
    var param = "";
    for(i=1;i<passP.length;i++) {
        if(passP[i]!=='nobaa'){
            var tmp = document.getElementById(passP[i]).value='';
        }
		 if(passP[i]=='jamanalisamulai' || passP[i]=='menitanalisamulai' || passP[i]=='jamanalisaselesai' || passP[i]=='menitanalisaselesai'){
            var tmp = document.getElementById(passP[i]).value='00';
        }
    }
}

function loadDetail(){
    notransaksi=document.getElementById('notransaksi').value;
    param+='&proses=loadDetail'+'&notransaksi='+notransaksi;
    tujuan='pabrik_slave_dispacth.php';
    post_response_text(tujuan, param, respog);
    function respog(){
          if(con.readyState==4){
            if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else{
                            isiDetail=con.responseText.split("####");
                            document.getElementById('containListBaa').innerHTML=isiDetail[0];
                            document.getElementById('containSounding').innerHTML=isiDetail[1];
                            document.getElementById('containSegel').innerHTML=isiDetail[2];
                            document.getElementById('containPorsi').innerHTML=isiDetail[3];                            
                        }
                }
                else{
                        busy_off();
                        error_catch(con.status);
                }
          } 
     }
}
//'".tanggalnormal($res['tanggalbaa'])."','".$res['millcode']."','".$res['kodetangki']."','".$res['ffa']."','".$res['moisture']."','".$res['dirt']."'
//$arrBaa="##nobaa##tanggalbaa##kdPbarik##kdTangki##ffa##moisture##dirt";
function editLab(nobaa,tanggalbaa,kdPbarik,kdTangki,ffa,moisture,dirt,jamanalisamulai,menitanalisamulai,jamanalisaselesai,menitanalisaselesai){
document.getElementById('nobaa').value=nobaa;
document.getElementById('kdPbarik').value=kdPbarik;
document.getElementById('tanggalbaa').value=tanggalbaa;
document.getElementById('kdTangki').value=kdTangki;
document.getElementById('ffa').value=ffa;
document.getElementById('moisture').value=moisture;
document.getElementById('dirt').value=dirt;
document.getElementById('jamanalisamulai').value=jamanalisamulai;
document.getElementById('menitanalisamulai').value=menitanalisamulai;
document.getElementById('jamanalisaselesai').value=jamanalisaselesai;
document.getElementById('menitanalisaselesai').value=menitanalisaselesai;	
}

function delDetailLab(nobaa){
    param='nobaa='+nobaa+'&proses=delDetailLab';
    tujuan='pabrik_slave_dispacth.php';
    if(confirm("Anda Yakin Menghapus?")){
        post_response_text(tujuan, param, respog);    
    }
    function respog(){
          if(con.readyState==4){
            if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else{
                            loadDetail();
                        }
                }
                else{
                        busy_off();
                        error_catch(con.status);
                }
          } 
     }
}
function saveSounding(fileTarget,passParam){
    var passP = passParam.split('##');
    var param = "";
    
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
    notransaksi=document.getElementById('notransaksi').value;
    kdOrg=document.getElementById('kdOrg');
    kdOrg=kdOrg.options[kdOrg.selectedIndex].value;
    komoditi=document.getElementById('komoditi');
    komoditi=komoditi.options[komoditi.selectedIndex].value;
    param+='&proses=addSounding'+'&notransaksi='+notransaksi+'&kdOrg='+kdOrg;
    param+='&komoditi='+komoditi;
    tujuan=fileTarget+'.php';
    post_response_text(tujuan, param, respog);
    function respog(){
          if(con.readyState==4){
            if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else{
                            clearSounding(passParam);
                            loadDetail();
                        }
                }
                else{
                        busy_off();
                        error_catch(con.status);
                }
          } 
     }
}
function clearSounding(passParam){
    document.getElementById('nopalka').disabled=false;
    var passP = passParam.split('##');
    var param = "";
    for(i=1;i<passP.length;i++) {
            var tmp = document.getElementById(passP[i]).value='';
    }
    
}
//'".$res['nopalka']."','".$res['tinggi']."','".$res['volume']."','".$res['suhu']."','".$res['beratjenis']."','".$res['tonase']."'
function editPalka(nopalka,tinggi,volume,suhu,beratjenis,tonase){
    document.getElementById('nopalka').value=nopalka;
    document.getElementById('nopalka').disabled=true;
    document.getElementById('tinggi').value=tinggi;
    document.getElementById('volume').value=volume;
    document.getElementById('suhu').value=suhu;
    document.getElementById('brtjenis').value=beratjenis;
    document.getElementById('tonase').value=tonase;
}
function delDetailPalka(nopalka,notransaksi){
    param='nopalka='+nopalka+'&proses=delDetailPalka';
    param+='&notransaksi='+notransaksi;
    tujuan='pabrik_slave_dispacth.php';
    if(confirm("Anda Yakin Menghapus?")){
        post_response_text(tujuan, param, respog);    
    }
    function respog(){
          if(con.readyState==4){
            if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else{
                            loadDetail();
                        }
                }
                else{
                        busy_off();
                        error_catch(con.status);
                }
          } 
     }
}
function saveSegel(fileTarget,passParam){
    var passP = passParam.split('##');
    var param = "";
    
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
    notransaksi=document.getElementById('notransaksi').value;
    kdOrg=document.getElementById('kdOrg');
    kdOrg=kdOrg.options[kdOrg.selectedIndex].value;
    komoditi=document.getElementById('komoditi');
    komoditi=komoditi.options[komoditi.selectedIndex].value;
    param+='&proses=addSegel'+'&notransaksi='+notransaksi+'&kdOrg='+kdOrg;
    param+='&komoditi='+komoditi;
    tujuan=fileTarget+'.php';
    post_response_text(tujuan, param, respog);
    function respog(){
          if(con.readyState==4){
            if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else{
                            clearSegel(passParam);
                            loadDetail();
                        }
                }
                else{
                        busy_off();
                        error_catch(con.status);
                }
          } 
     }
}
function clearSegel(passParam){
    document.getElementById('nosegel_view').disabled=false;
    var passP = passParam.split('##');
    var param = "";
    for(i=1;i<passP.length;i++) {
            var tmp = document.getElementById(passP[i]).value='';
    }
    
}
//'".$res['nosegel']."','".$res['posisi_segel']."','".$res['warna_segel']."','".$res['total_segel']."'
//posisi_segel,warna_segel,total_segel
function editSegel(nosegel,notransaksi){
    param='proses=editSegel'+'&nosegel='+nosegel+'&notransaksi='+notransaksi;
    tujuan='pabrik_slave_dispacth.php';
    post_response_text(tujuan, param, respog);
    function respog(){
          if(con.readyState==4){
            if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else{
                            dar=con.responseText.split("####");
                            document.getElementById('nosegel').value=nosegel;
                            document.getElementById('nosegel_view').disabled=true;
                            document.getElementById('posisi_segel').value=dar[0];
                            document.getElementById('warna_segel').value=dar[1];
                            document.getElementById('total_segel').value=dar[2];
                            document.getElementById('nosegel_view').value=dar[3];

                        }
                }
                else{
                        busy_off();
                        error_catch(con.status);
                }
          } 
     }
    
    
}
function delDetailSegel(nosegel,notransaksi){
    param='nosegel='+nosegel+'&proses=delDetailSegel';
    param+='&notransaksi='+notransaksi;
    tujuan='pabrik_slave_dispacth.php';
    if(confirm("Anda Yakin Menghapus?")){
        post_response_text(tujuan, param, respog);    
    }
    function respog(){
          if(con.readyState==4){
            if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else{
                            loadDetail();
                        }
                }
                else{
                        busy_off();
                        error_catch(con.status);
                }
          } 
     }
}

function getStock(){
    kdPt=document.getElementById('kdPt');
    kdPt=kdPt.options[kdPt.selectedIndex].value;
    komoditi=document.getElementById('komoditi');
    komoditi=komoditi.options[komoditi.selectedIndex].value;
    tgl=document.getElementById('tgl').value;
    param='kdPt='+kdPt+'&proses=getStock'+'&komoditi='+komoditi+'&tgl='+tgl;
    tujuan='pabrik_slave_dispacth.php';
    post_response_text(tujuan, param, respog);  
    function respog(){
          if(con.readyState==4){
            if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else{
                            document.getElementById('stockTersedia').value=con.responseText;
                        }
                }
                else{
                        busy_off();
                        error_catch(con.status);
                }
          } 
     }
}
function savePorsi(fileTarget,passParam){
    var passP = passParam.split('##');
    var param = "";
    
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
    notransaksi=document.getElementById('notransaksi').value;
    tgl=document.getElementById('tgl').value;
    kdOrg=document.getElementById('kdOrg');
    kdOrg=kdOrg.options[kdOrg.selectedIndex].value;
    komoditi=document.getElementById('komoditi');
    komoditi=komoditi.options[komoditi.selectedIndex].value;
    TotMuat=document.getElementById('TotMuat').value;
    
    param+='&proses=addPorsi'+'&notransaksi='+notransaksi+'&kdOrg='+kdOrg;
    param+='&komoditi='+komoditi+'&tgl='+tgl+'&TotMuat='+TotMuat;
    tujuan=fileTarget+'.php';
    post_response_text(tujuan, param, respog);
    function respog(){
          if(con.readyState==4){
            if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else{
                            clearSegel(passParam);
                            loadDetail();
                        }
                }
                else{
                        busy_off();
                        error_catch(con.status);
                }
          } 
     }
}
function delDetailPorsi(kdpt,notransaksi,tgl){
    param='kdpt='+kdpt+'&proses=delDetailPorsi';
    param+='&notransaksi='+notransaksi+'&tgl='+tgl;
    tujuan='pabrik_slave_dispacth.php';
    if(confirm("Anda Yakin Menghapus?")){
        post_response_text(tujuan, param, respog);    
    }
    function respog(){
          if(con.readyState==4){
            if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else{
                            loadDetail();
                        }
                }
                else{
                        busy_off();
                        error_catch(con.status);
                }
          } 
     }
}
function deletehead(notrans){
    param='notransaksi='+notrans+'&proses=deletehead';
    tujuan='pabrik_slave_dispacth.php';
    if(confirm("Anda Yakin Menghapus?")){
        post_response_text(tujuan, param, respog);    
    }
    function respog(){
          if(con.readyState==4){
            if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                    }
                    else{
                            //document.getElementById('tmbLheader').innerHTML='';
                            loadData(0);
                        }
                }
                else{
                        busy_off();
                        error_catch(con.status);
                }
          } 
     }
}
function getTonase(){
    kgAwal=document.getElementById('kgAwal').value;
    kgAkhir=document.getElementById('kgAkhir').value;
    var hsil=parseFloat(kgAwal)-parseFloat(kgAkhir);
    if(isNaN(hsil)){
        hsil=0;
    }
    document.getElementById('TotMuat').value=hsil;
}
function detaildt(title,notransaksi){
    title=title+" "+notransaksi;
    var width=650;
    var height=380;
    formListPP(title,width,height);
        param='notransaksi='+notransaksi+'&proses=htmlDetail';
        tujuan='pabrik_slave_dispacth.php';
        post_response_text(tujuan, param, respog);
        function respog(){
              if(con.readyState==4)
              {
                if (con.status == 200) {
                                busy_off();
                                if (!isSaveResponse(con.responseText)) {
                                        alert(con.responseText);
                                }
                                else {
                                       document.getElementById('containerData').innerHTML=con.responseText;
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
              } 
         }  
}
function formListPP(title,wdth,heig){
        //closeDialog();
        width='';
        height='';
        if(wdth!=''){
            width=wdth;
        }
        if(heig!=''){
            height=heig;
        }
        
        content="<div id=containerData></div>";
        ev='event';
        showDialog4(title,content,width,height,ev);
}
function posting(notransaksi,title){
    param='notransaksi='+notransaksi+'&proses=postData';
    tujuan='pabrik_slave_dispacth.php';
    function respog(){
          if(con.readyState==4)
          {
            if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert(con.responseText);
                            }
                            else {
                                   loadData(0);
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
          } 
     } 
     if(confirm(title))
     post_response_text(tujuan, param, respog);
}







function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
    width='600';
    height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog2(title,content,width,height,ev); 	
}
function excel(ev,kodeorg,periodegaji,tipepotongan)
{
        param='method=excel'+'&kodeorg='+kodeorg+'&periodegaji='+periodegaji+'&tipepotongan='+tipepotongan;
        //alert(param);
        tujuan='pabrik_slave_dispacthExcel.php';
        judul='Print Excel';		
        printFile(param,tujuan,judul,ev)	
}
 

 

function delDetail(notransaksi,noTiket,tgl){
        param='&notransaksi='+notransaksi+'&noTiket='+noTiket;
        param+='&proses=delDetail';
        tujuan='pabrik_slave_dispacth.php';
        function respog(){
                if (con.readyState == 4) {
                        if (con.status == 200) {
                                busy_off();
                                if (!isSaveResponse(con.responseText)) {
                                        alert(con.responseText);
                                }
                                else {
                                        showDetail(notransaksi,tgl);
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
                }
        }	
        if(confirm("Deleting, are you sure..?"))
        post_response_text(tujuan, param, respog);	
}