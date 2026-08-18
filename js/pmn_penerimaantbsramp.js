function displayFormInput()
{
    clearData();
    document.getElementById('formInput').style.display='block';
    document.getElementById('formUpload').style.display='none';
    document.getElementById('listData').style.display='none';
}

function displayFormUpload()
{
    clearData();
    document.getElementById('formInput').style.display='none';
    document.getElementById('formUpload').style.display='block';
    document.getElementById('listData').style.display='none';
}

function getnetto()
{
	beratmasuk = remove_comma(document.getElementById('beratmasuk'));
	beratkeluar = remove_comma(document.getElementById('beratkeluar'));
	potongan = remove_comma(document.getElementById('potongan'));
	
	netto = parseFloat(beratmasuk)-parseFloat(beratkeluar)-parseFloat(Math.round(potongan));
	// beratmasuk = beratmasuk.value;
	// beratkeluar = beratkeluar.value;
	
	document.getElementById('netto').value = netto;
	gettotalrupiah();
}

function getselisihbulanlalu()
{
	
}

function gettotalrupiah()
{
	z.numberFormat('netto',2);

	netto = remove_comma(document.getElementById('netto'));
	harga = remove_comma(document.getElementById('harga'));
	
	bbnPajak = document.getElementById('bbnPajak');
	bbnPajak = bbnPajak.options[bbnPajak.selectedIndex].value;
	
	prsnAll = remove_comma(document.getElementById('prsnAll'));
	
	totalrupiah = parseFloat(netto) * parseFloat(harga);
	
	totalrupiahgross = (parseFloat(totalrupiah) * 100 / (100-parseFloat(prsnAll)));
	
	// totalrupiahpph = totalrupiahgross;
	totalrupiahpph = (parseFloat(totalrupiahgross) * parseFloat(prsnAll))/100;
	
	if(bbnPajak=='1')
	{
		totalpembayaran = totalrupiah;
	}
	else
	{
		totalpembayaran = parseFloat(totalrupiah) - parseFloat(totalrupiahpph);
	}
	

	document.getElementById('totalrupiah').value = totalrupiah;
	document.getElementById('totalrupiahpph').value = totalrupiahpph;
	document.getElementById('totalpembayaran').value = totalpembayaran;
	
	z.numberFormat('totalrupiah',0);
	z.numberFormat('totalrupiahpph',0);
	z.numberFormat('totalpembayaran',0);
}

function getramp(unit,koderamp,kodesupplier)
{
	pt=document.getElementById('pt');
	pt=pt.options[pt.selectedIndex].value;
	
	param='proses=getramp&pt='+pt+'&koderamp='+koderamp+'&kodepabrik='+unit;
	tujuan='pmn_slave_penerimaantbsramp.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					splt = con.responseText.split("##");
					document.getElementById('koderamp').innerHTML=splt[0];
					document.getElementById('kodepabrik').innerHTML=splt[1];
					getsupplier(kodesupplier);
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

function getnotiket(notiket)
{
	if(notiket=='')
	{
		param='proses=getnotiket';
		tujuan='pmn_slave_penerimaantbsramp.php';
		post_response_text(tujuan, param, respog);
		
		function respog()
		{
			if(con.readyState==4)
			{
				if (con.status == 200) 
				{
					busy_off();
					if (!isSaveResponse(con.responseText)) 
					{
						alert(con.responseText);
					}
					else 
					{
						
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
	else
	{
		
	}
}

function getramp2()
{
	pt=document.getElementById('tmplpt');
	pt=pt.options[pt.selectedIndex].value;
	
	param='proses=getramp&pt='+pt;
	tujuan='pmn_slave_penerimaantbsramp.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					splt = con.responseText.split("##");
					document.getElementById('tmplkoderamp').innerHTML=splt[0];
					document.getElementById('tmplkodepabrik').innerHTML=splt[1];
					document.getElementById('listsupplier').innerHTML="";
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

function getcariramp()
{
	pt=document.getElementById('caript');
	pt=pt.options[pt.selectedIndex].value;
	
	param='proses=getcariramp&pt='+pt;
	tujuan='pmn_slave_penerimaantbsramp.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					splt = con.responseText.split("##");
					document.getElementById('carikoderamp').innerHTML=splt[0];
					document.getElementById('cariunit').innerHTML=splt[1];
					getcarisupplier();
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

function getsupplier(kodesupplier)
{
	koderamp=document.getElementById('koderamp');
	koderamp=koderamp.options[koderamp.selectedIndex].value;
	
	param='proses=getsupplier&koderamp='+koderamp+'&supplier='+kodesupplier;
	tujuan='pmn_slave_penerimaantbsramp.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					document.getElementById('supplier').innerHTML=con.responseText;
					getnetto();
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

function getsupplier2()
{
	koderamp=document.getElementById('tmplkoderamp');
	koderamp=koderamp.options[koderamp.selectedIndex].value;
	
	param='proses=getsupplier2&koderamp='+koderamp;
	tujuan='pmn_slave_penerimaantbsramp.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					document.getElementById('listsupplier').innerHTML=con.responseText;
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

function getcarisupplier()
{
	pt=document.getElementById('caript');
	pt=pt.options[pt.selectedIndex].value;
	koderamp=document.getElementById('carikoderamp');
	koderamp=koderamp.options[koderamp.selectedIndex].value;
	
	param='proses=getcarisupplier&koderamp='+koderamp+'&pt='+pt;
	tujuan='pmn_slave_penerimaantbsramp.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					document.getElementById('carisupplier').innerHTML=con.responseText;
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

function saveData(fileTarget,passParam) {
    var passP = passParam.split('##');
    var param = ""
    for(i=1;i<passP.length;i++) {
        var tmp = document.getElementById(passP[i]);
        if(i==1) {
            param += passP[i]+"="+getValue(passP[i]);
        } else {
            param += "&"+passP[i]+"="+getValue(passP[i]);
        }
    }
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(fileTarget+'.php', param, respon);

}

function download() 
{
	tmplsupplier=document.getElementsByName('tmplsupplier[]');
	var vals = "";
	var countsupplier=0;
	for (var i=0;i<tmplsupplier.length;i++) {
		if (tmplsupplier[i].checked) {
			vals += ","+tmplsupplier[i].value;
			countsupplier=countsupplier+1;
		}
	}
	tmplsupplier=vals.substring(1);

    tmplpt=document.getElementById('tmplpt');
	tmplpt=tmplpt.options[tmplpt.selectedIndex].value;
	tmplkodepabrik=document.getElementById('tmplkodepabrik');
	tmplkodepabrik=tmplkodepabrik.options[tmplkodepabrik.selectedIndex].value;
	tmplkoderamp=document.getElementById('tmplkoderamp');
	tmplkoderamp=tmplkoderamp.options[tmplkoderamp.selectedIndex].value;
	tmpltanggal=document.getElementById('tmpltanggal').value;
	
	param='proses=download&tmplkodepabrik='+tmplkodepabrik+'&tmplpt='+tmplpt+'&tmplkoderamp='+tmplkoderamp+'&tmplsupplier='+tmplsupplier+'&tmpltanggal='+tmpltanggal;
	tujuan='pmn_slave_penerimaantbsramp.php';
	
	if (tmplpt == '' || tmplkodepabrik == '' || tmplkoderamp=='' || countsupplier==0) 
	{
			alert('Data inconsistent');
	}
	else
	{
		window.location.href = "pmn_slave_penerimaantbsramp.php?"+param; 
	}
}


function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);	
}

function cariData(pg){
	loadData(pg);
}

function loadData(page){
	caript=document.getElementById('caript');
	caript=caript.options[caript.selectedIndex].value;
	cariunit=document.getElementById('cariunit');
	cariunit=cariunit.options[cariunit.selectedIndex].value;
	carikoderamp=document.getElementById('carikoderamp');
	carikoderamp=carikoderamp.options[carikoderamp.selectedIndex].value;
	carisupplier=document.getElementById('carisupplier');
	carisupplier=carisupplier.options[carisupplier.selectedIndex].value;
	caritanggal=document.getElementById('caritanggal').value;
	
    param='proses=loadData'+'&page='+page;
	
	if(caript!=''){
		param+='&caript='+caript;
	}
	if(cariunit!=''){
		param+='&cariunit='+cariunit;
	}
	if(carikoderamp!=''){
		param+='&carikoderamp='+carikoderamp;
	}
	if(carisupplier!=''){
		param+='&carisupplier='+carisupplier;
	}
	if(caritanggal!=''){
		param+='&caritanggal='+caritanggal;
	}
    
	tujuan='pmn_slave_penerimaantbsramp.php';
    post_response_text(tujuan, param, respog);
    function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
                }
                else 
				{
					isdt=con.responseText.split("####");
                    document.getElementById('formInput').style.display='none';
                    document.getElementById('formUpload').style.display='none';
                    document.getElementById('listData').style.display='block';
                    document.getElementById('continerlist').innerHTML=isdt[0];
                    document.getElementById('footData').innerHTML=isdt[1];
                    clearData();
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

function fillField(noinv){
    param='proses=getData'+'&noasset='+noinv;
    tujuan='asset_slave_assetdata.php';
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
                        document.getElementById('formUpload').style.display='none';
                        document.getElementById('listData').style.display='none';
                        isis=con.responseText.split("###");
                        document.getElementById('noasset').value=isis[0];
                        dtGroup=isis[0].split(".");
                        document.getElementById('noassetlm').value=isis[1];
                        document.getElementById('nopo').value=isis[2];
                        document.getElementById('kdBrg').value=isis[3];
                        document.getElementById('nmBrg').value=isis[4];
                        document.getElementById('model').value=isis[5];
                        document.getElementById('spesifikasi').value=isis[6];
                        document.getElementById('snnumber').value=isis[7];
                        document.getElementById('tglDaftar').value=isis[8];
                        kdcst=document.getElementById('ptId');
                        for(a=0;a<kdcst.length;a++){
                            if(kdcst.options[a].value==isis[9]){
                                    kdcst.options[a].selected=true;
                                }
                        }
						kdcst.disabled=true;
						kdcst2=document.getElementById('status');
                        for(a=0;a<kdcst2.length;a++){
                            if(kdcst2.options[a].value==isis[10]){
                                    kdcst2.options[a].selected=true;
                                }
                        }
                        document.getElementById('keterangan').value=isis[11];
                        getLokasi(isis[12],isis[13]);
                        document.getElementById('unitKerja').disabled=true;
                        document.getElementById('sbbagian').disabled=true;
						if(dtGroup[1]=='V'){
							document.getElementById('groupsbId').innerHTML="<option value="+dtGroup[2]+">"+dtGroup[2]+"</option>";
							document.getElementById('groupkdastId').innerHTML="<option value="+dtGroup[3]+">"+dtGroup[3]+"-"+isis[19]+"</option>";
						}else{
							// groupsbId=document.getElementById('groupsbId');
							// for(a=0;a<groupsbId.length;a++){
								// if(groupsbId.options[a].value==''){
										// groupsbId.options[a].selected=true;
									// }
							// }
							document.getElementById('groupsbId').innerHTML="<option value=>-</option>";
							document.getElementById('groupkdastId').innerHTML="<option value="+dtGroup[2]+">"+dtGroup[2]+"-"+isis[19]+"</option>";
						}
                        kdcst3=document.getElementById('groupId');
                        for(a=0;a<kdcst3.length;a++){
                            if(kdcst3.options[a].value==dtGroup[1]){
                                    kdcst3.options[a].selected=true;
                                }
                        }
						document.getElementById('karyId').value=isis[14];
                        document.getElementById('nmKary').value=isis[15];
                        document.getElementById('namaasset').value=isis[16];
                        document.getElementById('tanggalBeli').value=isis[17];
						if(isis[18]==''){
							document.getElementById('karyId').value=isis[14];
							document.getElementById('nmKary').value=isis[15];
						}else{
							document.getElementById('karyId').value='';
							document.getElementById('nmKary').value=isis[18];
						}
                        document.getElementById('hargaperolehan').value=isis[20];
                        document.getElementById('groupsbId').disabled=true;
                        document.getElementById('groupkdastId').disabled=true;
                        document.getElementById('groupId').disabled=true;
                        document.getElementById('tanggalBeli').disabled=true;
                        document.getElementById('proses').value='update';
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
      }
     }
}
//jamhari
function searchBrg(title,content,ev){
	width='400';
	height='550';
	showDialog2(title,content,width,height,ev);
    getFormBrg();
	//alert('asdasd');
}
function searchPo(title,content,ev){
	width='550';
	height='530';
	showDialog2(title,content,width,height,ev);
	pt=document.getElementById('ptId');
	pt=pt.options[pt.selectedIndex].value;
	kdbrg=document.getElementById('kdBrg').value;
	// if((kdbrg=='')||(pt=='')){
		// closeDialog();
		// alert('warning: PT/Kodebarang Tidak Boleh Kosong');
		// return;
	// }
    getFormPo();
	//alert('asdasd');
}
function searchUser(title,content,ev){
	width='400';
	height='550';
	showDialog2(title,content,width,height,ev);
	pt=document.getElementById('unitKerja');
	pt=pt.options[pt.selectedIndex].value;
	if(pt==''){
		closeDialog();
		alert('warning: Unit Kerja Tidak Boleh Kosong');
		return;
	}
    getFormUser();
	//alert('asdasd');
}
function getFormBrg(){
        param='proses=getFormBrg';
        tujuan='asset_slave_assetdata.php';
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
                                document.getElementById('formPencariandata').innerHTML=con.responseText;
                        }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
              }
	 }
} 
function getFormPo(){
        param='proses=getFormPo';
        tujuan='asset_slave_assetdata.php';
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
                                document.getElementById('formPencariandata').innerHTML=con.responseText;
                        }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
              }
	 }
} 
function getFormUser(){
        param='proses=getFormUser';
        tujuan='asset_slave_assetdata.php';
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
                                document.getElementById('formPencariandata').innerHTML=con.responseText;
                        }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
              }
	 }
} 

function findBrg(){
	txt=trim(document.getElementById('nosipbcr').value);
	param='txtfind='+txt+'&proses=getBrg';
        tujuan='asset_slave_assetdata.php';
        if(txt==''){
            alert("Kodebarang/Namabarang harus diisi.");
        } else {
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
                                    document.getElementById('container2').innerHTML=con.responseText;
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
          }
	 }
} 

function findPo(){
	txt=trim(document.getElementById('nosipbcr').value);
	kdpt=document.getElementById('ptId');
	kdpt=kdpt.options[kdpt.selectedIndex].value;
	kdbrg=document.getElementById('kdBrg').value;
	param='txtfind='+txt+'&proses=getPo'+'&ptId='+kdpt+'&kdBrg='+kdbrg;
        tujuan='asset_slave_assetdata.php';
        if(txt==''){
            alert("No.PP/No.PO is obligatory");
        } else {
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
                                    document.getElementById('container2').innerHTML=con.responseText;
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
          }
	 }
}

function findUser(){

	txt=trim(document.getElementById('nosipbcr').value);
	kdpt=document.getElementById('unitKerja');
	kdpt=kdpt.options[kdpt.selectedIndex].value;
	param='txtfind='+txt+'&proses=getUser'+'&unitKerja='+kdpt;;
        tujuan='asset_slave_assetdata.php';
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
                                    //alert(con.responseText);
                                    document.getElementById('container2').innerHTML=con.responseText;
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
          }
	 }
}
function setData(kdBrg,nmBrg){
	document.getElementById('kdBrg').value=kdBrg;
	document.getElementById('nmBrg').value=nmBrg;
	closeDialog2();
}
function setDataPo(npo,hargaperolehan){
	document.getElementById('nopo').value=npo;
	document.getElementById('hargaperolehan').value=hargaperolehan;
	closeDialog2();	
}
function setNikData(kdKry,nmKary){
	document.getElementById('karyId').value=kdKry;
	document.getElementById('nmKary').value=nmKary;
	closeDialog2();
}

function cancelData(){
	document.getElementById('formInput').style.display='none';
	document.getElementById('formUpload').style.display='none';
	document.getElementById('listData').style.display='block';
	clearData();
}

function clearData()
{
	document.getElementById('tiket').value='';
	document.getElementById('nospb').value='';
	document.getElementById('nokendaraan').value='';
	document.getElementById('jammasuk').selectedIndex=0;
	document.getElementById('menitmasuk').selectedIndex=0;
	document.getElementById('jamkeluar').selectedIndex=0;
	document.getElementById('menitkeluar').selectedIndex=0;
	document.getElementById('beratmasuk').value='0';
	document.getElementById('beratkeluar').value='0';
	document.getElementById('potongan').value='0';
	document.getElementById('jjg').value='0';
	document.getElementById('harga').value='0';
	document.getElementById('bbnPajak').selectedIndex=0;
	document.getElementById('prsnAll').value='0.5';
	document.getElementById('netto').value='0';
	document.getElementById('totalrupiah').value='0';
	document.getElementById('totalrupiahpph').value='0';
	document.getElementById('totalpembayaran').value='0';
	
	document.getElementById('pt').selectedIndex = 0;
	document.getElementById('proses').value='insert';
	
	document.getElementById('pt').disabled=false;
	document.getElementById('kodepabrik').disabled=false;
	document.getElementById('koderamp').disabled=false;
	document.getElementById('supplier').disabled=false;
	
	getramp();
}

function del(pt, kodepabrik, koderamp, supplier,tanggal){
	param='pt='+pt+'&kodepabrik='+kodepabrik+'&koderamp='+koderamp+'&tanggal='+tanggal+'&supplier='+supplier+'&proses=delData';
	tujuan='pmn_slave_penerimaantbsramp.php';  
	if(confirm("Anda yakin menghapus item ini?"))
	{
		post_response_text(tujuan, param, respog);
	}
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					getPage();
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

function posting(notiket, pt, kodepabrik, koderamp, supplier, tanggal){
	param='notiket='+notiket+'&pt='+pt+'&kodepabrik='+kodepabrik+'&koderamp='+koderamp+'&tanggal='+tanggal+'&supplier='+supplier+'&proses=posting';
	tujuan='pmn_slave_penerimaantbsramp.php';  
	if(confirm("Anda yakin Posting item ini?"))
	{
		post_response_text(tujuan, param, respog);
	}
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					getPage();
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
 
function getSubId(sbid,kdid){
	if(sbid==0){
		grpid=document.getElementById('groupId');
    	sbid=grpid.options[grpid.selectedIndex].value;
	}
	param='proses=getSubId'+'&groupId='+sbid+'&groupsubId='+sbid;
    param+='&groupkdastId='+kdid;
	tujuan='asset_slave_assetdata.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		  if(con.readyState==4)
		  {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else{
						dasr=con.responseText.split("####");
						document.getElementById('groupsbId').innerHTML=dasr[0];
						document.getElementById('groupkdastId').innerHTML=dasr[1];
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}
}
function getkdGroup(sbid,kdid){
	grpid=document.getElementById('groupId');
    grpid=grpid.options[grpid.selectedIndex].value;
    if(sbid==0){
    	groupsbId=document.getElementById('groupsbId');
        sbid=groupsbId.options[groupsbId.selectedIndex].value;
    }
    param='proses=getSubId'+'&groupId='+grpid+'&groupsubId='+sbid;
    param+='&groupkdastId='+kdid;
 
	tujuan='asset_slave_assetdata.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		  if(con.readyState==4)
		  {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else{
						dasr=con.responseText.split("####");
						document.getElementById('groupkdastId').innerHTML=dasr[1];
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}
}
function getLokasi(lksi,sbbgin){
	ptid=document.getElementById('ptId');
    ptid=ptid.options[ptid.selectedIndex].value;
    param='proses=getLokasi'+'&ptId='+ptid+'&unitKerja='+lksi;
	tujuan='asset_slave_assetdata.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		  if(con.readyState==4)
		  {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else{
						document.getElementById('unitKerja').innerHTML=con.responseText;
						getSubbagian(lksi,sbbgin);
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}
}
function getSubbagian(lksi,sbbagian){
	if(lksi==0){
		lksiId=document.getElementById('unitKerja');
    	lksi=lksiId.options[lksiId.selectedIndex].value;
	}
	param='proses=getSubagian'+'&unitKerja='+lksi+'&sbbagian='+sbbagian;
	tujuan='asset_slave_assetdata.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		  if(con.readyState==4)
		  {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else{
						document.getElementById('sbbagian').innerHTML=con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}
}

function notrans(grpId,grsbId,grkdId,ptId,unitKrj,subbagian,tgldf){
	var tgl=tgldf.split("-");
	if((tgl[0]=='00')||(tgl[1]=='00')||(tgl[2]=='0000')||(tgldf=='')){
		alert("Tanggal daftar formatnya salah/kosong");
		return;
	}
	param='proses=notransTest'+'&groupId='+grpId+'&groupsbId='+grsbId+'&tglDaftar='+tgldf;
	param+='&groupkdastId='+grkdId+'&ptId='+ptId+'&unitKerja='+unitKrj+'&sbbagian='+subbagian;
	if(grpId!='V'){
		brgid=document.getElementById('kdBrg').value;
		if(brgid==''){
			alert('warning: Kodebarang Tidak Boleh Kosong');
			return;
		}
		param+='&kdBrg='+brgid;
	}

	tujuan='asset_slave_assetdata.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		  if(con.readyState==4)
		  {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else{
						alert(con.responseText);
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}
}

function showalllist(pg){
	document.getElementById('caript').selectedIndex=0;
	document.getElementById('caritanggal').value = '';
	getcariramp2(pg);
}

function getcariramp2(pg)
{
	pt=document.getElementById('caript');
	pt=pt.options[pt.selectedIndex].value;
	
	param='proses=getcariramp&pt='+pt;
	tujuan='pmn_slave_penerimaantbsramp.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					splt = con.responseText.split("##");
					document.getElementById('carikoderamp').innerHTML=splt[0];
					document.getElementById('cariunit').innerHTML=splt[1];
					getcarisupplier2(pg);
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

function getcarisupplier2(pg)
{
	pt=document.getElementById('caript');
	pt=pt.options[pt.selectedIndex].value;
	koderamp=document.getElementById('carikoderamp');
	koderamp=koderamp.options[koderamp.selectedIndex].value;
	
	param='proses=getcarisupplier&koderamp='+koderamp+'&pt='+pt;
	tujuan='pmn_slave_penerimaantbsramp.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					document.getElementById('carisupplier').innerHTML=con.responseText;
					loadData(pg);
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

function edit(notiket,kodeorg, unit, koderamp, kodesupplier, nokendaraan, tglmasuk, jammasuk, menitmasuk, tglkeluar, jamkeluar, menitkeluar, beratmasuk, beratkeluar, potongan, harga, bebanpajak, persenpajak)
{
    document.getElementById('listData').style.display = 'none';
    document.getElementById('formInput').style.display = 'block';
    document.getElementById('formUpload').style.display = 'none';
    
	document.getElementById('proses').value = 'update';
    
	document.getElementById('tiket').value = notiket;
    document.getElementById('pt').value = kodeorg;
	
	
    document.getElementById('nokendaraan').value = nokendaraan;
    
	document.getElementById('tanggalmasuk').value = tglmasuk;
    document.getElementById('jammasuk').value = jammasuk;
    document.getElementById('menitmasuk').value = menitmasuk;
    document.getElementById('beratmasuk').value = beratmasuk;
    document.getElementById('potongan').value = potongan;
    
	document.getElementById('tanggalkeluar').value = tglkeluar;
	document.getElementById('jamkeluar').value = jamkeluar;
	document.getElementById('menitkeluar').value = menitkeluar;
    document.getElementById('beratkeluar').value = beratkeluar;
	
	document.getElementById('harga').value = harga;
    document.getElementById('bbnPajak').value = bebanpajak;
    document.getElementById('prsnAll').value = persenpajak;
	
	getramp(unit,koderamp,kodesupplier);
}