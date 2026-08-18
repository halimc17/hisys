function getkomoditi(kualitas1,kualitas2,kualitas3,kualitas4,nokontrak,period)
{
	komoditi=document.getElementById('komoditi').options[document.getElementById('komoditi').selectedIndex].value;
	isipros=document.getElementById('proses').value;
	notrans=document.getElementById('notransaksi').value;
	param='proses=getkomoditi&komoditi='+komoditi+'&kualitas1='+kualitas1+'&kualitas2='+kualitas2+'&kualitas3='+kualitas3+'&kualitas4='+kualitas4;
	param+='&notransaksi='+notrans+'&isipros='+isipros;
	tujuan='pmn_slave_scr.php';
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
					document.getElementById('trkualitas').innerHTML=con.responseText;
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

function getapproval(pt,notransaksi,komoditi,kualitas1,kualitas2,kualitas3,kualitas4,norek,nokontrak,period)
{
	if(pt===undefined)
	{
		pt=document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	}
	else
	{
		pt=pt;
	}
	
	param='proses=getapproval&pt='+pt+'&notransaksi='+notransaksi;
	if(norek!=''){
		param+='&norek='+norek;
	}
	tujuan='pmn_slave_scr.php';
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
					hsl=con.responseText.split("####");
					document.getElementById('trapproval').innerHTML=hsl[0];
					document.getElementById('bayarke').innerHTML=hsl[1];
					if(pt===undefined)
					{
						
					}
					else
					{
						getkomoditi(kualitas1,kualitas2,kualitas3,kualitas4,nokontrak,period);
					}
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

function cancelData(){
	document.getElementById('formInput').style.display='none';
	document.getElementById('formUpload').style.display='none';
	document.getElementById('listData').style.display='block';
	clearData();
}

function clearData()
{
	document.getElementById('notransaksi').value='';
	document.getElementById('pt').selectedIndex=0;
	document.getElementById('buyer').selectedIndex=0;
	document.getElementById('scn').value='';
	document.getElementById('berikat').checked=false;
	document.getElementById('komoditi').selectedIndex=0;
	document.getElementById('kuantitas').value='0';
	document.getElementById('harga').value='0';
	document.getElementById('bayarke').selectedIndex=0;
	
	document.getElementById('trkualitas').innerHTML='';
	document.getElementById('trapproval').innerHTML='';
	
	document.getElementById('pt').disabled=false;
}

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

function saveData() 
{
	notransaksi=document.getElementById('notransaksi').value;
	pt=document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	tanggal=document.getElementById('tanggal').value;
	buyer=document.getElementById('buyer').options[document.getElementById('buyer').selectedIndex].value;
	scn=document.getElementById('scn').value;
	trapproval=document.getElementById('trapproval').innerHTML;
	
	berikat=document.getElementById('berikat').checked;
	if(berikat==true)
	{
		valberikat = 1;
	}
	else
	{
		valberikat = 0;
	}
	
	komoditi=document.getElementById('komoditi').options[document.getElementById('komoditi').selectedIndex].value;
	kuantitas=document.getElementById('kuantitas').value;
	harga=document.getElementById('harga').value;
	ppn=document.getElementById('ppn').options[document.getElementById('ppn').selectedIndex].value;
	tanggalbayar=document.getElementById('tanggalbayar').value;
	bayarke=document.getElementById('bayarke').options[document.getElementById('bayarke').selectedIndex].value;
	
	if(pt==''||buyer==''||komoditi==''||bayarke==''||kuantitas==''||kuantitas=='0'||harga==''||harga=='0')
	{
		alert("Please complete form");
		return;
	}
	
	if(trapproval=='')
	{
		alert("Please contact administrator to setup Approval.");
		return;
	}
	
	var tbl = document.getElementById("trapproval");
	var row = parseFloat(tbl.rows.length)+1;
	strUrl = '';
	for(i=1;i<row;i++)
	{
		persetujuan = document.getElementById('persetujuan'+i).options[document.getElementById('persetujuan'+i).selectedIndex].value
		if(persetujuan=='')
		{
			alert("Please compelete Approval");
			return;
		}
		 strUrl += '&persetujuan['+i+']='+persetujuan;
	}
	
	kualitas1=document.getElementById('kualitas1').value;
	kualitas2=document.getElementById('kualitas2').value;
	kualitas3=document.getElementById('kualitas3').value;
	kualitas4=document.getElementById('kualitas4').value;
	
	proses=document.getElementById('proses').value;
	
	param='proses='+proses+'&notransaksi='+notransaksi+'&pt='+pt+'&tanggal='+tanggal+'&buyer='+buyer+'&scn='+scn+'&berikat='+valberikat+'&komoditi='+komoditi+'&kuantitas='+kuantitas+'&harga='+harga+'&ppn='+ppn+'&tanggalbayar='+tanggalbayar+'&bayarke='+bayarke+'&kualitas1='+kualitas1+'&kualitas2='+kualitas2+'&kualitas3='+kualitas3+'&kualitas4='+kualitas4;
	param+=strUrl;
	tujuan='pmn_slave_scr.php';
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
					loadData();
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

function deletescr(notransaksi)
{
	param='proses=deletescr&notransaksi='+notransaksi;
	tujuan='pmn_slave_scr.php';
	if(confirm("Are you sure delete this item?"))
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

function editscr(notransaksi,kodeorg,tanggal,buyer,scn,berikat,komoditi,kuantitas,harga,ppn,paymentdate,bayarke,kualitas1,kualitas2,kualitas3,kualitas4,nocontrak,perid)
{
	document.getElementById('listData').style.display = 'none';
    document.getElementById('formInput').style.display = 'block';
    document.getElementById('formUpload').style.display = 'none';
    
	document.getElementById('proses').value = 'update';
	
	document.getElementById('notransaksi').value=notransaksi;
	document.getElementById('pt').value=kodeorg;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('buyer').value=buyer;
	document.getElementById('scn').value=scn;
	if(berikat=='1')
	{
		document.getElementById('berikat').checked=true;
	}
	else
	{
		document.getElementById('berikat').checked=false;
	}
	document.getElementById('komoditi').value=komoditi;
	document.getElementById('kuantitas').value=kuantitas;
	document.getElementById('harga').value=harga;
	document.getElementById('ppn').value=ppn;
	document.getElementById('tanggalbayar').value=paymentdate;
	//document.getElementById('bayarke').value=bayarke;
	l=document.getElementById('bayarke');
    for(a=0;a<l.length;a++){
            if(l.options[a].value==bayarke)
                {
                    l.options[a].selected=true;
                }
    }
	document.getElementById('pt').disabled=true;
	 
	getapproval(kodeorg,notransaksi,komoditi,kualitas1,kualitas2,kualitas3,kualitas4,bayarke,nocontrak,perid);
}

function postingscr(notransaksi)
{
	param='proses=postingscr&notransaksi='+notransaksi;
	tujuan='pmn_slave_scr.php';
	if(confirm("Are you sure submitted this item?"))
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

function printpdf(notransaksi,ev)
{
	param = "proses=printpdf&notransaksi="+notransaksi;
	 
	showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:395px' src='pmn_slave_scr.php?"+param+"'></iframe>",'','',ev);
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
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
	caritanggal=document.getElementById('caritanggal').value;
	carinotransaksi=document.getElementById('carinotransaksi').value;
	
    param='proses=loadData'+'&page='+page;
	
	if(caript!=''){
		param+='&caript='+caript;
	}
	if(caritanggal!=''){
		param+='&caritanggal='+caritanggal;
	}
	if(carinotransaksi!=''){
		param+='&carinotransaksi='+carinotransaksi;
	}
    
	tujuan='pmn_slave_scr.php';
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
					document.getElementById('formInput').style.display='none';
                    document.getElementById('formUpload').style.display='none';
                    document.getElementById('listData').style.display='block';
                    document.getElementById('continerlist').innerHTML=con.responseText;
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
	loadData(pg);
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