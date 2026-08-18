
function posting(notran){
	param = 'proses=posting' + '&notran=' + notran;
    tujuan = 'pabrikasi_slave_absensi.php';
	
	if(confirm('Are you sure confirm transaction:'+notran+
        '?\n, the data can not be edited.')) {
        post_response_text(tujuan, param, respon);
    }
	
    // post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    loadData();
                }
            }
            else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}





function loadData(page){
    document.getElementById('txtsearch').value='';
    document.getElementById('tgl_cari').value='';
    ntrs=document.getElementById('txtsearch').value;
    tglcr=document.getElementById('tgl_cari').value;
    param='proses=loadData'+'&page='+page;
    if(ntrs!=''){
        param+='&notrancari='+ntrs;
    }
    if(tglcr!=''){
        param+='&tanggalCr='+tglcr;
    }
    tujuan='pabrikasi_slave_absensi.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
      if(con.readyState==4)
      {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                        isdt=con.responseText.split("####");
                        document.getElementById('listData').style.display='block';
                        document.getElementById('continerlist').innerHTML=isdt[0];
                        document.getElementById('footData').innerHTML=isdt[1];
                        cancelData();
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
      }
     }
}
function cariData(page){
    ntrs=document.getElementById('txtsearch').value;
    tglcr=document.getElementById('tgl_cari').value;
    param='proses=loadData'+'&page='+page;
    if(ntrs!=''){
        param+='&notrancari='+ntrs;
    }
    if(tglcr!=''){
        param+='&tanggalCr='+tglcr;
    }
    tujuan='pabrikasi_slave_absensi.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
      if(con.readyState==4)
      {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                        isdt=con.responseText.split("####");
                        document.getElementById('listData').style.display='block';
                        document.getElementById('continerlist').innerHTML=isdt[0];
                        document.getElementById('footData').innerHTML=isdt[1];
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
      }
     }
}
function cancelData(){
document.getElementById('formDetail').style.display='none';
document.getElementById('formInput').style.display='none';
document.getElementById('listData').style.display='block';
document.getElementById('tgldata').disabled=false;
document.getElementById('periodedt').disabled=false;
document.getElementById('kdorg').disabled=false;
document.getElementById('tomblSimpan').disabled=false;
clearData();
}

function clearData(){
   document.getElementById('tgldata').value='';
   document.getElementById('pernah').value='0';
   document.getElementById('notransaksi').value='';
   document.getElementById('kdorg').value='';
   document.getElementById('periodedt').value='';
   document.getElementById('proses').value='insert';
}

function saveData(){
     tgl=document.getElementById('tgldata').value;
     kdorg=document.getElementById('kdorg');
     kdorg=kdorg.options[kdorg.selectedIndex].value;
     periodedt=document.getElementById('periodedt');
     periodedt=periodedt.options[periodedt.selectedIndex].value;
     proses=document.getElementById('proses').value;
     param='tgl='+tgl+'&proses=cekData';  
     param+='&kdorg='+kdorg+'&periodedt='+periodedt
     tujuan='pabrikasi_slave_absensi.php';
     post_response_text(tujuan+'?'+'', param, respog);
    function respog(){
              if(con.readyState==4){
                if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                        }
                        else {
                                //alert(con.responseText);
                                  document.getElementById('formDetail').style.display='block';
                                  document.getElementById('karyId').innerHTML=con.responseText;
                                  document.getElementById('detailData').innerHTML='';
								  document.getElementById('kdorg').disabled=true;
								  document.getElementById('periodedt').disabled=true;
								  document.getElementById('tgldata').disabled=true;
                        }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
              }
     }
    
}
function getGaji(){
    karyId=document.getElementById('karyId');
    karyId=karyId.options[karyId.selectedIndex].value;
    jhk=document.getElementById('jhk').value;
    periodedt=document.getElementById('periodedt');
    periodedt=periodedt.options[periodedt.selectedIndex].value;
    param='proses=getGaji'+'&karyId='+karyId+'&jhk='+jhk+'&periode='+periodedt;
    post_response_text(tujuan, param, respog);
    function respog(){
              if(con.readyState==4){
                if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                        }
                        else {
                                //alert(con.responseText);
                                document.getElementById('umr').value=con.responseText;
                        }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
              }
    }       
}

function addDetail(){
     prnh=document.getElementById('pernah').value;
     tgl=document.getElementById('tgldata').value;
     jhk=document.getElementById('jhk').value;
     umr=document.getElementById('umr').value;
     premi=document.getElementById('premi').value;
     pros=document.getElementById('proses').value;
     kdorg=document.getElementById('kdorg');
     kdorg=kdorg.options[kdorg.selectedIndex].value;
     karyId=document.getElementById('karyId');
     karyId=karyId.options[karyId.selectedIndex].value;
     periodedt=document.getElementById('periodedt');
     periodedt=periodedt.options[periodedt.selectedIndex].value;
     pabrikasiId=document.getElementById('pabrikasiId');
     pabrikasiId=pabrikasiId.options[pabrikasiId.selectedIndex].value;
     absensiId=document.getElementById('absensiId');
     absensiId=absensiId.options[absensiId.selectedIndex].value;
     notransaksi=document.getElementById('notransaksi').value;

	 if(jhk>'1')
	{
		 alert('warning: HK tidak boleh lebih dari 1');
		 return;
	}
	 
	if(karyId=='' || pabrikasiId=='' || absensiId=='' || umr=='')
	{
		 alert('Nama karyawan, Kode Pabrikasi, Kehadiran, HK dan UMR wajib terisi.');
		 return;
	}
	
	
	
     param='tgl='+tgl+'&proses='+pros+'&jhk='+jhk+'&umr='+umr+'&karyId='+karyId+'&absensiId='+absensiId;
     param+='&kdorg='+kdorg+'&periodedt='+periodedt+'&premi='+premi+'&pabrikasiId='+pabrikasiId+'&prnh='+prnh;
     if(notransaksi!=''){
        param+='&notransaksi='+notransaksi;
     }

     tujuan='pabrikasi_slave_absensi.php';
     function respog(){
              if(con.readyState==4){
                if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                        }
                        else {
                                //alert(con.responseText);
                                document.getElementById('pernah').value=1;
                                if(notransaksi==''){
                                  document.getElementById('notransaksi').value=con.responseText;
                                  loadDetail(con.responseText);
                                }else{
                                  loadDetail(notransaksi);
                                }
                                
                                
                                //document.getElementById('detailData').innerHTML=isis[1];
                        }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
              }
     } 
     if(prnh==0){
        if(confirm(andayakin)){
          post_response_text(tujuan, param, respog);
        }
     }else{
          post_response_text(tujuan, param, respog);
     }
}
function loadDetail(notran){
    param='proses=loadDetail'+'&notransaksi='+notran;
    tujuan='pabrikasi_slave_absensi.php';
    
    function respog(){
      if(con.readyState==4)
      {
        if (con.status == 200){
          busy_off();
          if(!isSaveResponse(con.responseText)){
            alert(con.responseText);
          }
          else{
            clearDetail();
            document.getElementById('detailData').innerHTML=con.responseText;
          }
        }
        else {
          busy_off();
          error_catch(con.status);
        }
      }
    }
  post_response_text(tujuan, param, respog);
}
function clearDetail(){
  document.getElementById('jhk').value='';
  document.getElementById('umr').value='';
  document.getElementById('premi').value='';
  document.getElementById('proses').value='insert';
  document.getElementById('karyId').disabled=false;
  document.getElementById('pabrikasiId').disabled=false;
}
function fillField(notran){
    param='proses=getData'+'&notransaksi='+notran;
    tujuan='pabrikasi_slave_absensi.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
      if(con.readyState==4)
      {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
            						document.getElementById('formInput').style.display='block';
                        document.getElementById('formDetail').style.display='block';
                        document.getElementById('listData').style.display='none';
                        document.getElementById('pernah').value=1;
                        isis=con.responseText.split("#####");
                        document.getElementById('notransaksi').value=isis[0];
                        document.getElementById('tgldata').value=isis[1];
						            kdcst=document.getElementById('kdorg');
                        for(a=0;a<kdcst.length;a++){
                            if(kdcst.options[a].value==isis[2]){
                                    kdcst.options[a].selected=true;
                                }
                        }
                        periodedt=document.getElementById('periodedt');
                        for(a=0;a<periodedt.length;a++){
                            if(periodedt.options[a].value==isis[3]){
                                    periodedt.options[a].selected=true;
                                }
                        }
                        document.getElementById('tgldata').disabled=true;
                        document.getElementById('periodedt').disabled=true;
                        document.getElementById('kdorg').disabled=true;
                        document.getElementById('tomblSimpan').disabled=true;
                        document.getElementById('karyId').innerHTML=isis[4];
						            loadDetail(notran);
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
      }
     }
}
function fillFieldDet(karyawanid,kodepabrikasi,jhk,umr,premi){
  kdcst=document.getElementById('karyId');
  for(a=0;a<kdcst.length;a++){
      if(kdcst.options[a].value==karyawanid){
              kdcst.options[a].selected=true;
          }
  }
  kdcst.disabled=true;
  kdcst2=document.getElementById('pabrikasiId');
  for(a=0;a<kdcst2.length;a++){
      if(kdcst2.options[a].value==kodepabrikasi){
              kdcst2.options[a].selected=true;
          }
  }
  kdcst2.disabled=true;
  document.getElementById('jhk').value=jhk;
  document.getElementById('umr').value=umr;
  document.getElementById('premi').value=premi;
  document.getElementById('proses').value='editdet';
}
function delDataDet(notransaksi,karyawanid,kodepabrikasi){
  param='notransaksi='+notransaksi+'&karyId='+karyawanid+'&pabrikasiId='+kodepabrikasi;
  param+='&proses=delDetail';
  tujuan='pabrikasi_slave_absensi.php';  
  if(confirm("Anda yakin menghapus data ini? ")){
      post_response_text(tujuan, param, respog);
  }
  function respog()
  {
          if(con.readyState==4)
          {
            if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert(con.responseText);
                            }
                            else {
                                    //alert(con.responseText);
                                    loadDetail(notransaksi);
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
          }
   }
}
function delData(notran){
        param='notransaksi='+notran+'&proses=delData';
        tujuan='pabrikasi_slave_absensi.php';  
        if(confirm("Anda yakin menghapus notransaksi ini? "+notran)){
            post_response_text(tujuan, param, respog);
        }
	function respog()
	{
          if(con.readyState==4)
          {
            if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert(con.responseText);
                            }
                            else {
                                    //alert(con.responseText);
                                    getPage();
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
          }
	 }
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);	
}

function displayFormInput(){
        clearData();
		document.getElementById('formInput').style.display='block';
		document.getElementById('listData').style.display='none';
}