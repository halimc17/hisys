function delpic(nodok,kodebarang,karyawanid,dept){
	param='method=delpic';
	param+='&nodok='+nodok;
	param+='&kodebarang='+kodebarang;
	param+='&karyawanid='+karyawanid;
	param+='&dept='+dept;
	tujuan = 'log_slave_pemakaianbarang.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					datapic(nodok,kodebarang);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}
function clearpic(){
	document.getElementById('karyawanid').value='';
	document.getElementById('dept').value='';
	document.getElementById('qtypic').value='';
}
function datapic(nodok,kodebarang){
	nodok = document.getElementById('nodok').value;
	kodebarang = document.getElementById('kodebarang').value;
	param='method=datapic';
	param+='&nodok='+nodok;
	param+='&kodebarang='+kodebarang;
	tujuan = 'log_slave_pemakaianbarang.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('datapic').innerHTML=con.responseText;
					loaddatadetail(nodok);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}
function savepic(){
    nodok=document.getElementById('nodok').value;
    kodebarang=document.getElementById('kodebarang').value;
    karyawanid=document.getElementById('karyawanid').value;
    dept=document.getElementById('dept').value;
    qtypic=document.getElementById('qtypic').value;
    qty=document.getElementById('qty').value;
	if(parseFloat(qtypic) > parseFloat(qty)){
		alert("Jumlah alokasi lebih banyak dari jumlah diminta."); return;
	}
	if(nodok==''|| kodebarang==''){
        alert('No Dokument dan Kode Barang wajib terisi.');
        return;
    }
	
    if(qtypic=='' && (karyawanid==''|| dept=='')){
        alert('Lengkapi Pengisian.');
        return;
    }
    
    param='karyawanid='+karyawanid;
	param+='&nodok='+nodok;
	param+='&kodebarang='+kodebarang;
	param+='&dept='+dept;
    param+='&qtypic='+qtypic;
    param+='&qty='+qty;
    param+='&method=insertpic';
		
    tujuan='log_slave_pemakaianbarang.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
						clearpic();
						datapic(nodok,kodebarang);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function disdept(val){
	if(val=='dept'){
		document.getElementById('dept').value='';
	}else{
		document.getElementById('karyawanid').value='';
	}
}

function formpic(){
    width = '';
	height = '';
    content = "<div id=contpic style=\"max-height:700px;overflow:auto;\"></div>";
    ev = 'event';
    title = "Add PIC";
    showDialog1(title, content, width, height, ev); 
}
function getpic(){
	kodebarang=document.getElementById('kodebarang').value;
	qty=document.getElementById('qty').value;
	nodok=document.getElementById('nodok').value;
	if(kodebarang==''||nodok==''){
		alert("Warning : No Dokument dan Kode Barang diperlukan !"); return;
	}
	
    formpic();
    param  = 'method=getpic';
    param += '&kodebarang='+kodebarang;
    param += '&qty='+qty;
    param += '&nodok='+nodok;
	
    tujuan = 'log_slave_pemakaianbarang.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('contpic').innerHTML = con.responseText;
					datapic(nodok,kodebarang);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function enable(row){
	rowplus=row+1;
	if(document.getElementById('kepada'+rowplus)!=null && document.getElementById('kepada'+row).value!=''){
		document.getElementById('kepada'+rowplus).disabled=false;
	} else {
		document.getElementById('kepada'+rowplus).disabled=true;
		document.getElementById('kepada'+rowplus).options[0].selected=true;
	}
}

maxf=0
sekarang=1;
function ajukanall(maxRow){  
	if(maxRow =='' || maxRow ==0){
        alert('Data tidak ditemukan, proses dibatalkan !');
        return;
    }
	maxf=maxRow;
	ajukan(1,maxRow);
}

function ajukan(currRow,maxRow){
	kepada=document.getElementById('kepada'+currRow).value;
    nodok=document.getElementById('notran_aju').innerHTML;
    numrow=document.getElementById('numrow').value;
	param = 'method=ajukan' + '&nodok=' + nodok+ '&kepada=' + kepada+ '&level=' + currRow;
    	
	tujuan = 'log_slave_pemakaianbarang.php';
	post_response_text(tujuan, param, respog);
	
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					currRow+=1;
                    sekarang=currRow;
                    if((currRow>maxRow) || (maxRow == undefined)){
						x = document.getElementById('row_' + numrow);
						x.cells[8].innerHTML = 'diajukan';
						x.cells[9].innerHTML = '';
						x.cells[10].innerHTML = '';
						x.cells[11].innerHTML = '';
						document.getElementById('bastcontainer').innerHTML='';
						document.getElementById('nodok').value='';
						alert('Sucses');
						closeDialog();
					} else {
						ajukan(currRow,maxRow);
                    }
					
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function form_ajukan(nodok,kodeorg,numrow){
	width = '';
    height = '';
    content = "<fieldset><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:100px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "";
    showDialog1(title, content, width, height, ev);
	
	param = 'method=form_ajukan' + '&nodok=' + nodok+'&kodeorg='+kodeorg+'&numrow='+numrow;
    tujuan = 'log_slave_pemakaianbarang.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('containeraju').innerHTML = con.responseText;
					
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function form(){
    width = '920';
    height = '';
    content = "<div id=containerd style=\"width:100%;max-height:700px;overflow:auto;\"></div>";
    ev = 'event';
    title = "View";
    showDialog5(title, content, width, height, ev); 
}


function view(nodok){
    form();
    param = 'method=view' + '&nodok=' + nodok;
    tujuan = 'log_slave_pemakaianbarang.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('containerd').innerHTML = con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function del(nodok,numrow){
	param='method=del';
	param+='&nodok='+nodok;
	tujuan = 'log_slave_pemakaianbarang.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function edit(nodok,kodept,tanggal,untukunit,penerima,catatan,kodegudang,numrow){
	document.getElementById('pemilikbarang').value=kodept;
	document.getElementById('gudang').value=kodegudang;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('nodok').value=nodok;
	document.getElementById('untukunit').value=untukunit;
	document.getElementById('penerima').value=penerima;
	document.getElementById('catatan').value=catatan;
	tabAction(document.getElementById('tabFRM0'),0,'FRM',1);
	loaddatadetail(nodok);
}

function loaddata(){
	gudang = document.getElementById('gudang').value;
	nodoksrc = document.getElementById('txtbabp').value;
	tanggalsrc = document.getElementById('tanggalsrc').value;
	param='method=loaddata';
	param+='&gudang='+gudang+'&nodoksrc='+nodoksrc+'&tanggalsrc='+tanggalsrc;
	tujuan = 'log_slave_pemakaianbarang.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containerlist').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function bastBaru(){
	setSloc('ganti');
	kosongkan();
	cleardetail();
}

function deletedetail(nodok,kodebarang,subunit,blok,mesin,kegiatan,numrow){
	param='method=deletedetail';
	param+='&nodok='+nodok+'&kodebarang='+kodebarang+'&subunit='+subunit+'&blok='+blok+'&mesin='+mesin+'&kegiatan='+kegiatan;
	tujuan = 'log_slave_pemakaianbarang.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}


function loaddatadetail(){
	nodok = document.getElementById('nodok').value;
	param='method=loaddatadetail';
	param+='&nodok='+nodok;
	tujuan = 'log_slave_pemakaianbarang.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('bastcontainer').innerHTML=con.responseText;
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function saveItemBast(){
	gudang = document.getElementById('gudang').options[document.getElementById('gudang').selectedIndex].value;
	tanggal = document.getElementById('tanggal').value;
	pt = document.getElementById('pemilikbarang').value;
	nodok = document.getElementById('nodok').value;
	kodeorg = document.getElementById('untukunit').value;
	kodebarang = document.getElementById('kodebarang').value;
	qty = document.getElementById('qty').value;
	subunit = document.getElementById('subunit').value;
	blok = document.getElementById('blok').value;
	mesin = document.getElementById('mesin').value;
	kegiatan = document.getElementById('kegiatan').value;
	penerima = document.getElementById('penerima').value;
	catatan = document.getElementById('catatan').value;
	satuan = document.getElementById('satuan').value;
	
	param='method=saveItemBast';
	param+='&gudang='+gudang+'&tanggal='+tanggal+'&pt='+pt+'&nodok='+nodok+'&kodeorg='+kodeorg+'&kodebarang='+kodebarang+'&qty='+qty+'&subunit='+subunit+'&blok='+blok+'&mesin='+mesin+'&kegiatan='+kegiatan+'&penerima='+penerima+'&catatan='+catatan+'&satuan='+satuan;
	tujuan = 'log_slave_pemakaianbarang.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if(confirm("Ingin menambahkan penerima ???")){
						document.getElementById('qty').disabled=true;
						getpic();
					}else{
						document.getElementById('qty').disabled=false;
						cleardetail();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function cleardetail(){
	document.getElementById('kodebarang').value='';
	document.getElementById('namabarang').value='';
	document.getElementById('satuan').value='';
	document.getElementById('qty').value='';
	document.getElementById('qty').disabled=false;
	document.getElementById('subunit').value='';
	document.getElementById('blok').value='';
	document.getElementById('mesin').value='';
	document.getElementById('kegiatan').value='';
	loaddatadetail(nodok);
}
function getPT(gudang){
	param='method=getPT';
	param+='&gudang='+gudang;
	tujuan = 'log_slave_pemakaianbarang.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isi=con.responseText.split("####");
					document.getElementById('pemilikbarang').value=trim(isi[0]);
					document.getElementById('subunit').innerHTML=trim(isi[1]);
				
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}


function setSloc(x){
	gudang = document.getElementById('gudang').options[document.getElementById('gudang').selectedIndex].value;
	tanggal = document.getElementById('tanggal').value;
	
	var today = new Date();
	var dd = today.getDate();
	var mm = today.getMonth()+1; //January is 0!
	var yyyy = today.getFullYear();
	if(dd<10) {
		dd = '0'+dd
	} 
	if(mm<10) {
		mm = '0'+mm
	} 
	today = dd + '-' + mm + '-' + yyyy;
	if (gudang != '') {
		if (x == 'simpan') {
			document.getElementById('gudang').disabled = true;
			document.getElementById('btnsloc').disabled = true;
			document.getElementById('pemilikbarang').disabled = true;
			document.getElementById('tanggal').disabled = true;
			tujuan = 'log_slave_pemakaianbarang.php';
			param ='method=getNotransaksi';
			param +='&gudang='+gudang+'&tanggal='+tanggal;
			post_response_text(tujuan, param, respog);
		} else {
			document.getElementById('nodok').value='';
			document.getElementById('kegiatan').value='';
			document.getElementById('gudang').disabled = false;
			document.getElementById('tanggal').value=today;
			document.getElementById('tanggal').disabled = false;
			document.getElementById('pemilikbarang').value = "";
			document.getElementById('containerlist').innerHTML = "";
			document.getElementById('gudang').options[0].selected=true;
			document.getElementById('btnsloc').disabled = false;
			document.getElementById('bastcontainer').innerHTML="";
			kosongkan();
		}	
		
	}	
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					//alert(con.responseText);
					document.getElementById('nodok').value = trim(con.responseText);
					loaddata();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function kosongkan(){
	document.getElementById('kodebarang').value='';
	document.getElementById('olbBlok').value='';
	document.getElementById('namabarang').value='';
	document.getElementById('penerima').value='';
	document.getElementById('catatan').value='';
	document.getElementById('satuan').value='';
	document.getElementById('qty').value=0;
	document.getElementById('blok').innerHTML="<option value=''></option>";
	document.getElementById('mesin').options[0].selected=true;
	document.getElementById('kegiatan').options[0].selected=true;
	document.getElementById('subunit').innerHTML="<option value=''></option>";
	document.getElementById('bastcontainer').innerHTML="";
	enableHeader();	
}

function enableHeader(){
	document.getElementById('tanggal').disabled=false;
	document.getElementById('untukunit').disabled=false;
	document.getElementById('penerima').disabled=false;
	document.getElementById('catatan').disabled=false;
	document.getElementById('subunit').disabled=false;	
}


function showWindowBarang(title,ev){
	content= "<div style='width:100%;'>";
	content+="<fieldset>"+title+"&nbsp; : <input type=text id=txtnamabarang class=myinputtext size=25 onkeypress=\"return enterEuy(event);\" maxlength=35><button class=mybutton onclick=goCariBarang()>Go</button> ";
	content+="<div id=containercari style='overflow:auto;max-height:317px;min-width:250px'></div></fieldset></div>";
	width='auto';
	height='auto';
	showDialog1(title,content,width,height,ev);		
}
function enterEuy(evt){
	key=getKey(evt);
	if(key==13){
		goCariBarang();
	} else{
		return tanpa_kutip(evt);
	}
}

function goCariBarang(){
	gudang = document.getElementById('gudang').options[document.getElementById('gudang').selectedIndex].value;
	nodok=document.getElementById('nodok').value;
	txtnamabarang=document.getElementById('txtnamabarang').value;
	if (nodok == '') {
		alert('Document Number is Obligatory');
	} else if (txtnamabarang.length < 1) {
				alert('material name min. 1 char');
		} else {
			param  = 'txtnamabarang=' + txtnamabarang;
			param += '&gudang='+gudang;
			param += '&method=goCariBarang';
			tujuan = 'log_slave_pemakaianbarang.php';
			post_response_text(tujuan, param, respog);
		}
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('containercari').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}

function loadField(kode,nama,satuan){
	document.getElementById('kodebarang').value=kode;
	document.getElementById('namabarang').value=nama;
	document.getElementById('satuan').value=satuan;
	closeDialog();		
}

function loadBlock(induk){
	document.getElementById('kegiatan').value='';
	kodeorg = document.getElementById('untukunit').value;
	param ='induk='+induk+'&kodeorg='+kodeorg;
	param += '&method=loadBlock';
	document.getElementById('blok').innerHTML='';
	//document.getElementById('mesin').options[0].selected=true;
	tujuan = 'log_slave_pemakaianbarang.php';
	if(induk!='')post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isi=con.responseText.split("####");
					document.getElementById('blok').innerHTML=isi[0];
					document.getElementById('mesin').innerHTML=isi[1];
					getKegiatan(induk);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	   	
}


function getKegiatan(blok,x){
	subunit=document.getElementById('subunit').value;
	kodeorg = document.getElementById('untukunit').value;
	param  = 'blok='+blok+'&jenis='+x+'&subunit='+subunit+'&kodeorg='+kodeorg;
	param += '&method=getKegiatan';
	tujuan = 'log_slave_pemakaianbarang.php';
	if(x == 'TRAKSI'){
		document.getElementById('blok').options[0].selected=true;
	}else if(x == 'BLOK'){
		document.getElementById('mesin').options[0].selected=true;
	}
	
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('kegiatan').innerHTML=con.responseText;
					//getSegment();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}		
}


// ================================================================================================

// function loadSubunit(induk,penerimax,subunitx)
// {
    // penerima=penerimax;
	// param='induk='+induk+'&subunitx='+subunitx;
	// document.getElementById('subunit').innerHTML='';
	// document.getElementById('blok').innerHTML='';
	// tujuan = 'log_slave_getSubUnitOption.php';
	// post_response_text(tujuan, param, respog);
	// function respog(){
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// }
				// else {
					// valSplit = con.responseText.split("####");
					// document.getElementById('subunit').innerHTML=valSplit[0];
					// document.getElementById('blok').innerHTML=valSplit[0];
					// document.getElementById('tipeorg').value=valSplit[1];
				    // //loadMesin(induk);
				    // loadKaryawan(induk,penerima);
				// }
			// }
			// else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }	   	
// }

// function loadKaryawan(induk,penerima)
// {	
    // unit=document.getElementById('untukunit').value;
    // subunit=document.getElementById('subunit').value;
   // param='unit='+unit+'&subunit='+subunit+'&penerima='+penerima;
// //   alert(param);
    // tujuan = 'log_slave_getKaryawanOption.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                // }
                // else {
					// document.getElementById('blok').options[0].selected=true;	
                    // document.getElementById('penerima').innerHTML=con.responseText;
					// getKegiatan(induk);
                // }
            // }
            // else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }		
// }

// function loadMesin(induk)
// {
	
   // param='induk='+induk;
	// tujuan = 'log_slave_getMesinOption.php';
	// post_response_text(tujuan, param, respog);
	// function respog(){
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// }
				// else {
				        // document.getElementById('blok').options[0].selected=true;	
                                                                                        // document.getElementById('mesin').innerHTML=con.responseText;
				// }
			// }
			// else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }	
	
// }
// function getKegiatan(blok,x)
// {
	// subunit=document.getElementById('subunit').value;
	// param='blok='+blok+'&jenis='+x+'&subunit='+subunit;
	// tujuan = 'log_slave_getKegiatanBlok.php';
	// if(x == 'TRAKSI'){
		// document.getElementById('blok').options[0].selected=true;
	// }else if(x == 'BLOK'){
		// document.getElementById('mesin').options[0].selected=true;
	// }
	// post_response_text(tujuan, param, respog);
	// function respog(){
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// }
				// else {
					// document.getElementById('kegiatan').innerHTML=con.responseText;
					// getSegment();
				// }
			// }
			// else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }		
// }






// function disableHeader()
// {
	// document.getElementById('tanggal').disabled=true;
	// document.getElementById('untukunit').disabled=true;
	// document.getElementById('penerima').disabled=true;
	// document.getElementById('catatan').disabled=true;
	// tipeorg = document.getElementById('tipeorg').value;
	// // if(tipeorg == 'KEBUN'){
	// // 	document.getElementById('subunit').disabled=false;
	// // }else{
	// // 	document.getElementById('subunit').disabled=true;
	// // }
// }









// function nextItem()
// {
	// document.getElementById('kodebarang').disabled=false;
	// document.getElementById('satuan').disabled=false;
	// document.getElementById('namabarang').disabled=false;	
	// //document.getElementById('blok').disabled=false;
	// document.getElementById('kodebarang').value='';
	// document.getElementById('namabarang').value='';
	// document.getElementById('satuan').value='';
	// document.getElementById('qty').value=0;	
	// // document.getElementById('subunit').disabled=false;
	// document.getElementById('method').value='insert';
    // document.getElementById('mesin').options[0].selected=true;
    // document.getElementById('kegiatan').options[0].selected=true;
               
// }

// function bastBaru()
// {
  // nextItem();
  // kosongkan();	
  // setSloc('simpan');
  // document.getElementById('untukunit').options[0].selected=true;
  // document.getElementById('bastcontainer').innerHTML='';
// }


// function saveItemBast(){
		// gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
        // tanggal=document.getElementById('tanggal').value;
		// x=tanggal;
		// _start=document.getElementById(gudang+'_start').value;
		// _end=document.getElementById(gudang+'_end').value;
		// while (x.lastIndexOf("-") > -1) {
			// x = x.replace("-", "");
		// }
		// while (x.lastIndexOf("-") > -1) {
		    // x=x.replace("/","");
		// }
		
		// curdateY=x.substr(4,4).toString();
		// curdateM=x.substr(2,2).toString();
		// curdateD=x.substr(0,2).toString();
		// curdate=curdateY+curdateM+curdateD;	
		// curdate=parseInt(curdate);
	// if (curdate < parseInt(_start) || curdate > parseInt(_end)) {
		// alert('Date out of range')
	// }
	// else {
			// nodok		=trim(document.getElementById('nodok').value);
			// tanggal		=trim(document.getElementById('tanggal').value);
			// kodebarang	=trim(document.getElementById('kodebarang').value);
			// penerima	=trim(document.getElementById('penerima').value);
			// catatan		=trim(document.getElementById('catatan').value);			
			// satuan		=trim(document.getElementById('satuan').value);
			// qty			=trim(document.getElementById('qty').value);
			// method		=trim(document.getElementById('method').value);
			
			// blok		=document.getElementById('blok');
				// blok	=trim(blok.options[blok.selectedIndex].value);
			// segment		=document.getElementById('segment');
				// segment	=trim(segment.options[segment.selectedIndex].value);
			// mesin		=document.getElementById('mesin');
				// mesin	=trim(mesin.options[mesin.selectedIndex].value);
			// untukunit	=document.getElementById('untukunit');
				// untukunit=trim(untukunit.options[untukunit.selectedIndex].value);
			// subunit		=document.getElementById('subunit');
				// subunit	=trim(subunit.options[subunit.selectedIndex].value);
			// kegiatan		=document.getElementById('kegiatan');
				// kegiatan	=trim(kegiatan.options[kegiatan.selectedIndex].value);			
	        // gudang 		=trim(document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value);
	        // pemilikbarang =trim(document.getElementById('pemilikbarang').options[document.getElementById('pemilikbarang').selectedIndex].value);
			// olbBlok=document.getElementById('olbBlok').value;
			
			
			// //kegiatanstr=kegiatan.substr(0,1).toString();
			// // alert(kegiatanstr);
			// // alert(mesin);
		
		// if(nodok=='')
		// {
			// alert('Document Number is obligatory');
			// return;
		// }
		// if(untukunit=='')
		// {
			// alert('Bussiness unit(Unit) is obligatory');
			// return;
		// } 
		// if(kegiatan=='')
		// {
			// alert('Activity is obligatory');return;
		// }
		// if(penerima=='')
		// {
			// alert('Recipient name is obligatory');return;
		// }  
		// if(kodebarang=='' || satuan=='' || parseFloat(qty)<0.001)
		// {
			// alert('Material, UOM and volume is obligatory');return;
		// }
		
		// //mengakomodasi pengeluaran PT HAL ke PT SIB
		// /*
		// else if(gudang.substr(3,1)!='M' && (kodebarang.substr(0,3)=='311' || kodebarang.substr(0,3)=='312') )
		// {
			// alert('Pupuk dan Agrocemical tidak diperbolehkan untuk dikeluarkan dari transaksi gudang');
			
		// }
		// */
		
		
		// if(kegiatan.substr(0,1)=='4')
		// {	
			// if(mesin==''){
				// alert('Untuk Kegiatan akun transit harap mengisikan kendaraan');return;
			// }
		// }
	
			// if(confirm('Are you sure?'))
			// {
				// param='nodok='+nodok+'&tanggal='+tanggal+'&kodebarang='+kodebarang;
				// param+='&penerima='+penerima+'&satuan='+satuan+'&qty='+qty;
				// param+='&blok='+blok+'&mesin='+mesin+'&untukunit='+untukunit;
				// param+='&gudang='+gudang+'&pemilikbarang='+pemilikbarang;
				// param+='&catatan='+catatan+'&kegiatan='+kegiatan;
				// param+='&segment='+segment+'&olbBlok='+olbBlok;
				// param+='&subunit='+subunit+'&method='+method;
				// tujuan='log_slave_saveBast.php';
				// //alert(param);
				// post_response_text(tujuan, param, respog);
				// disableHeader();
				// document.getElementById('qty').style.backgroundColor='red';
			// }
		
	// }
		// function respog(){
			// if (con.readyState == 4) {
				// if (con.status == 200) {
					// busy_off();
					// if (!isSaveResponse(con.responseText)) {
						// alert(con.responseText);
					// }
					// else {
						// document.getElementById('qty').style.backgroundColor='#ffffff';
						// nextItem();
						// document.getElementById('bastcontainer').innerHTML=con.responseText;
						// //setelah menyimpan 1 baris yakinkan method adalah insert
						// document.getElementById('method').value='insert';
						// document.getElementById('subunit').value='';
						// document.getElementById('blok').value='';
					// }
				// }
				// else {
					// busy_off();
					// error_catch(con.status);
				// }
			// }
		// }	
// }

// function getBastList(gudang)
// {
		// param='gudang='+gudang;
		// tujuan = 'log_slave_getBastList.php';
		// post_response_text(tujuan, param, respog);
		// function respog(){
			// if (con.readyState == 4) {
				// if (con.status == 200) {
					// busy_off();
					// if (!isSaveResponse(con.responseText)) {
						// alert(con.responseText);
					// }
					// else {
						// document.getElementById('containerlist').innerHTML=con.responseText;
					// }
				// }
				// else {
					// busy_off();
					// error_catch(con.status);
				// }
			// }
		// }	
// }

// function delBast(notransaksi,kodebarang,kodeblok)
// {
        // untukunit	=document.getElementById('untukunit');
		     // untukunit=trim(untukunit.options[untukunit.selectedIndex].value);
		// pemilikbarang = document.getElementById('pemilikbarang');
		     // pemilikbarang=trim(pemilikbarang.options[pemilikbarang.selectedIndex].value);
		// param='nodok='+notransaksi+'&kodebarang='+kodebarang;
		// param+='&delete=true&blok='+kodeblok+'&pemilikbarang='+pemilikbarang;
		// param+='&untukunit='+untukunit;
		// tujuan='log_slave_saveBast.php';
		// if(confirm('Deleting Document '+notransaksi+', are you sure..?'))
		  // post_response_text(tujuan, param, respog);	
		// function respog(){
			// if (con.readyState == 4) {
				// if (con.status == 200) {
					// busy_off();
					// if (!isSaveResponse(con.responseText)) {
						// alert(con.responseText);
					// }
					// else {
						// document.getElementById('bastcontainer').innerHTML=con.responseText;
					// }
				// }
				// else {
					// busy_off();
					// error_catch(con.status);
				// }
			// }
		// }	
// }

// function editBast(kodebarang,namabarang,satuan,jumlah,kodeblok,kodekegiatan,kodemesin,kodesegment)
// {
   // //set blok karena merupakan primary
    // // document.getElementById('blok').innerHTML="<option value=''></option><option value='"+kodeblok+"'>"+kodeblok+"</option>";
  	// //document.getElementById('subunit').innerHTML="<option value='"+kodeblok+"'>"+kodeblok+"</option>";
	// document.getElementById('kodebarang').value=kodebarang;
	// document.getElementById('namabarang').value=namabarang;
	// document.getElementById('satuan').value=satuan;
	// document.getElementById('olbBlok').value=kodeblok;
	// // document.getElementById('subunit').value=kodeblok;
	
	// document.getElementById('kodebarang').disabled=true;
	// document.getElementById('satuan').disabled=true;
	// document.getElementById('namabarang').disabled=true;
	// //document.getElementById('blok').disabled=true;
// //	document.getElementById('subunit').disabled=true;
	
	// document.getElementById('qty').value=jumlah;
	// blk = document.getElementById('blok');
	// for(x=0;x<blk.length;x++)
	// {
		// if(blk.options[x].value==kodeblok)
		// {
			// blk.options[x].selected=true;
		// }
	// }
	// sbdt=kodeblok.substr(0,6);
	// subunit = document.getElementById('subunit');
	// for(x=0;x<subunit.length;x++)
	// {
		// if(subunit.options[x].value==sbdt)
		// {
			// subunit.options[x].selected=true;
		// }
	// }
	
	// keg=document.getElementById('kegiatan');
	// for(x=0;x<keg.length;x++)
	// {
		// if(keg.options[x].value==kodekegiatan)
		// {
			// keg.options[x].selected=true;
		// }
	// }
	// segment=document.getElementById('segment');
	// for(x=0;x<segment.length;x++)
	// {
		// if(segment.options[x].value==kodesegment)
		// {
			// segment.options[x].selected=true;
		// }
	// }
   // document.getElementById('method').value='update';
   // disableHeader();	
// }

// function delXBapb(nodok)
// {
	// if(confirm('Deleting Doc: '+nodok+', Are sure..?'))
	// {
		// param='notransaksi='+nodok;
		// tujuan='log_slave_deleteBapb.php';//file ini berfungsi untuk penerimaan dan pengeluaran
	   // if(confirm('All data in this document will be removed. Continue ?'))
	   // {
	   	 // post_response_text(tujuan, param, respog);
	   // }   
	// }
		// function respog(){
			// if (con.readyState == 4) {
				// if (con.status == 200) {
					// busy_off();
					// if (!isSaveResponse(con.responseText)) {
						// alert(con.responseText);
					// }
					// else {
						// gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
						// setSloc('simpan');
					// }
				// }
				// else {
					// busy_off();
					// error_catch(con.status);
				// }
			// }
		// }		
// }


// function editXBast(notransaksi,untukunit,subunit,sbuni,tanggal,namapenerima,keterangan,tipeorg)
// {

	// nextItem();
	// document.getElementById('nodok').value = notransaksi;
	// document.getElementById('tanggal').value=tanggal;
	// document.getElementById('penerima').value=namapenerima;        
	// document.getElementById('catatan').value=keterangan;
	// document.getElementById('tipeorg').value=tipeorg;
	// subunitx=subunit;
        // if((namapenerima.substr(0,3)=='000')&&(namapenerima.length==10)){
                
        // }else{
            // if(namapenerima!='masyarakat')document.getElementById('catatan').value+=' received by:'+namapenerima;
        // }
	
	// unt=document.getElementById('untukunit');
	// for(x=0;x<unt.length;x++)
	// {
		// if(unt.options[x].value==untukunit)
		// {
			// unt.options[x].selected=true;
		// }
	// }
 	
    // tabAction(document.getElementById('tabFRM0'),0,'FRM',1);//jangan tanya darimana	
	// // loadSubunit(untukunit,'0','0');
	// tujuan='log_slave_saveBast.php';
	// param='nodok='+notransaksi+'&displayonly=true';
    // post_response_text(tujuan, param, respog);
		// function respog(){
			// if (con.readyState == 4) {
				// if (con.status == 200) {
					// busy_off();
					// if (!isSaveResponse(con.responseText)) {
						// alert(con.responseText);
					// }
					// else {
						// document.getElementById('bastcontainer').innerHTML=con.responseText;
						// disableHeader();
					    // editloadSubunit(untukunit,namapenerima,sbuni);
					// }
				// }
				// else {
					// busy_off();
					// error_catch(con.status);
				// }
			// }
		// }	
// }

// function editloadSubunit(induk,penerimax,subunitx){
	// penerima=penerimax;
	// param='induk='+induk+'&subunitx='+subunitx;
	// document.getElementById('subunit').innerHTML='';
	// document.getElementById('blok').innerHTML='';
	// tujuan = 'log_slave_getSubUnitOption.php';
	// post_response_text(tujuan, param, respog);
	// function respog(){
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// }
				// else {
					// document.getElementById('subunit').innerHTML=con.responseText;
				    // editloadKaryawan(induk,penerima);
				// }
			// }
			// else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }	
// }

// function editloadKaryawan(induk,penerima)
// {	
	// unit=document.getElementById('untukunit').value;
    // subunit=document.getElementById('subunit').value;
	// param='unit='+unit+'&subunit='+subunit+'&penerima='+penerima;
    // tujuan = 'log_slave_getKaryawanOption.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                // }
                // else {
					// document.getElementById('penerima').innerHTML=con.responseText;
					// loadBlock(subunit);
					// // getKegiatan(induk);
					// // document.getElementById('blok').options[0].selected=true;
                // }
            // }
            // else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }		
// }

// function cariBast(num)
// {
	// tex=trim(document.getElementById('txtbabp').value);
	// gudang = document.getElementById('sloc').options[document.getElementById('sloc').selectedIndex].value;
    // if(gudang =='')
	// {
		// alert('Storage Location  is obligatory')
	// }
	// else
	// {
		// param='gudang='+gudang;
		// param+='&page='+num;
		// if(tex!='')
			// param+='&tex='+tex;
		// tujuan = 'log_slave_getBastList.php';
		// post_response_text(tujuan, param, respog);			
	// }
		// function respog(){
			// if (con.readyState == 4) {
				// if (con.status == 200) {
					// busy_off();
					// if (!isSaveResponse(con.responseText)) {
						// alert(con.responseText);
					// }
					// else {
						// document.getElementById('containerlist').innerHTML=con.responseText;
					// }
				// }
				// else {
					// busy_off();
					// error_catch(con.status);
				// }
			// }
		// }	
// }


// function previewBast(notransaksi,ev)
// {
   	// param='notransaksi='+notransaksi;
	// tujuan = 'log_slave_print_bast_pdf.php?'+param;	
 // //display window
   // title=notransaksi;
   // width='800';
   // height='400';
   // content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   // showDialog2(title,content,width,height,ev);
   
// }

// /**
 // * getSegment
 // * Mengambil Segment sesuai bloknya, lookup ke tabel proporsi segment
 // * Jika tidak ada maka return nilai default '0000000001'
 // */
// function getSegment() {
	// var blok = getValue('blok');
	// param = "";

	// if(blok!='') {
		// param = "kodeblok="+blok;
		// post_response_text("log_slave_getSegmentBlok.php", param, respog);
	// }else{
		// //getvhc();
	// }
	
	// function respog(){
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// document.getElementById('segment').innerHTML=con.responseText;
				// }
			// }
			// else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }

// function getvhc()
// {
	// var blok = getValue('subunit');
	// param = "kodeblok="+blok;
	// // if(blok!=''){
		// post_response_text("log_slave_getvhc.php", param, respog);
	// // }
	
	// function respog(){
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// document.getElementById('mesin').innerHTML=con.responseText;
				// }
			// }
			// else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }