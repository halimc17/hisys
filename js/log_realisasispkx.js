function htmlspk(notransaksi, tipe) {
	// width = '';
	// height = '';
	// content = "<fieldset style=\"width:98%;\"><div id=contviewx style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "View";
	// showDialog5(title, content, width, height, ev);
	param = 'method=html' +'&tipe=' + tipe + '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('contviewx').innerHTML = con.responseText;
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					loadfilesspk(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function loadfilesspk(notransaksi,jenisupload) {
	param = 'method=loadfiles&notransaksi=' + notransaksi;
	param+='&jenisupload=' + jenisupload;
	tujuan = 'lgl_slave_pengajuanspk.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					if (document.getElementById('loadfilesdetail') !== null) {
						document.getElementById('loadfilesdetail').innerHTML = con.responseText;
						
						
					}
					loaddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function batal(){
	
}

function loaddata(pg){
	document.getElementById('listData').style.display = "";
	var myworkField = document.getElementById('workField');
	if (myworkField !== null) {
		myworkField.style.display = "none";
	}
	
	notransaksicr = document.getElementById('notransaksicr').value;
	unitcr = document.getElementById('unitcr').value;
	koderekanancr = document.getElementById('koderekanancr').value;
	tglcr = document.getElementById('tglcr').value;
	
	param = 'method=loaddata';
	param += '&page='+ pg;
	if (notransaksicr != '') {
		param += '&notransaksicr=' + notransaksicr;
	}
	if(unitcr!=''){
		param += '&unitcr=' + unitcr;
	}
	if(koderekanancr!=''){
		param += '&koderekanancr=' + koderekanancr;
	}
	if(tglcr!=''){
		param += '&tglcr=' + tglcr;
	}
	
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('container').innerHTML = con.responseText;
					batal();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getpage(){
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function viewdetail(notransaksi,kodeorg,tipeview,ev){
	// width = '';
	// height = '';
	// content = "<fieldset><legend>Preview</legend><div id=contRekap style=\"width:100%;max-height:400px;overflow:auto;\"></div></fieldset>";
	// title = "";
	// showDialog1(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);
	// document.getElementById('dynamic1').style.top = pos[1] + 'px';
	// // document.getElementById('dynamic1').style.left = (pos[0]-600) + 'px';
	// document.getElementById('dynamic1').style.display = '';
	
	var param = "notransaksi="+notransaksi+"&kodeorg="+kodeorg+'&tipeview='+tipeview;
	
	param += '&method=rekapbapp';
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//document.getElementById('contRekap').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function showhidedetail(rows,total){
	for(var i = 1; i <= total; i++) {
		key = document.getElementById('tr_dt2_'+rows+'_'+i).style.display;
		if(key=='none'){
			document.getElementById('tr_dt2_'+rows+'_'+i).style.display='';
		}else{
			document.getElementById('tr_dt2_'+rows+'_'+i).style.display='none';
		}
    }
}

function manageDetail(numRow) {
    var detailField = document.getElementById('detail_'+numRow);
    var notrans = document.getElementById('notransaksi').value;
    var matauang = document.getElementById('matauang').value;
    var kodeblok = document.getElementById('kodeblok_'+numRow).getAttribute('value');
    var kodekeg = document.getElementById('kodekegiatan_'+numRow).getAttribute('value');
    var param = "notransaksi="+notrans+"&kodeblok="+kodeblok+"&numRow="+numRow;
    param += "&kodekegiatan="+kodekeg+"&divisi="+getValue('divisi')+"&matauang="+matauang;
	param += "&kebun="+getValue('kodeorg');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    detailField.innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    if(detailField.innerHTML=="") {
        post_response_text('log_slave_realisasispk_detail.php?proses=manageDetail', param, respon);
    } else {
        if(detailField.style.display=='none') {
            detailField.style.display="";
        } else {
            detailField.style.display="none";
        }
    }
}

function previewdt(numrow,ev){
	// width = '';
	// height = '';
	// content = "<fieldset><legend>Detail Realisasi</legend><div id=divpreviewdt style=\"width:100%;max-height:400px;overflow:auto;\"></div></fieldset>";
	// title = "";
	// showDialog1(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);
	// document.getElementById('dynamic1').style.top = pos[1] + 'px';
	// // document.getElementById('dynamic1').style.left = (pos[0]-600) + 'px';
	// document.getElementById('dynamic1').style.display = '';
	
	var notransaksi = document.getElementById('notransaksi').value;
    var matauang = document.getElementById('matauang').value;
    var kodeblok = document.getElementById('kodeblok_'+numrow).getAttribute('value');
    var kodebloktext = document.getElementById('kodeblok_'+numrow).innerHTML;
    var kodekeg = document.getElementById('kodekegiatan_'+numrow).getAttribute('value');
    var kodekegtext = document.getElementById('kodekegiatan_'+numrow).innerHTML;
    var satuan = document.getElementById('satuan_'+numrow).getAttribute('value');
    var hk = document.getElementById('hk_'+numrow).getAttribute('value');
    var hasilkerjajumlah = document.getElementById('hasilkerjajumlah_'+numrow).getAttribute('value');
    var jumlahrp = document.getElementById('jumlahrp_'+numrow).getAttribute('value');
    var param = "notransaksi="+notransaksi+"&kodeblok="+kodeblok+"&numrow="+numrow;
    param += "&kodekegiatan="+kodekeg+"&divisi="+getValue('divisi')+"&matauang="+matauang+'&kodebloktext='+kodebloktext+'&kodekegtext='+kodekegtext;
	param += "&satuan="+satuan+'&hk='+hk+'&hasilkerjajumlah='+hasilkerjajumlah+'&jumlahrp='+jumlahrp;
	param += "&kebun="+getValue('kodeorg');
	
	// var param = "notransaksi="+notransaksi+"&kodeorg="+kodeorg;
	
	param += '&method=previewdt';
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					// document.getElementById('divpreviewdt').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('70%','70%'); 
					loaddatadt(notransaksi,kodeblok,kodekeg);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getnobapp(nospk){
	dttermin = document.getElementById('dttermin').value;
	document.getElementById('nobatermin').value=addZero(dttermin,3)+"/"+nospk;
}
function addZero(num, places) {
  var zero = places - num.toString().length + 1;
  return Array(+(zero > 0 && zero)).join("0") + num;
}
function getapprovaldetail(nopengajuan,kodeorg,ev) {
	// width = '';
	// height = '';
	// content = "<fieldset style=width:96%><legend>Detail Approval</legend><div id=contapp style=\"overflow:auto;width:100%;\"></div></fieldset>";
	// title = "";
	// showDialog4(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);
	// document.getElementById('dynamic4').style.top = pos[1] + 'px';
	// // document.getElementById('dynamic4').style.left = (pos[0]-width) + 'px';
	// document.getElementById('dynamic4').style.display = '';
	param = 'method=getapprovaldetail' + '&nopengajuan=' + nopengajuan + '&kodeorg=' + kodeorg;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('contapp').innerHTML = con.responseText;
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('50%','50%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function view(nopengajuan,notransaksi,kodeorg,tanggal,termin,numRow,ev,tipe,bapp){
	param = "method=preview&tipe="+tipe+"&notransaksi="+notransaksi+"&nopengajuan="+nopengajuan+"&kodeorg="+kodeorg+"&tanggal="+tanggal+"&termin="+termin+"&baspk="+bapp;
    // width = '';
    // height = '';
    // content = "<fieldset><div id=contviewx style=\"height:400px;width:700px;overflow:auto;\"></div></fieldset>";
    // title = "View";
    // showDialog2(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);
	// document.getElementById('dynamic2').style.top = pos[1] + 'px';
	// // document.getElementById('dynamic2').style.right = (80) + 'px';
	// document.getElementById('dynamic2').style.display = '';
	
    tujuan = 'log_slave_realisasispkx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    // document.getElementById('contviewx').innerHTML = con.responseText;
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function UploadFile(notransaksi,tanggal,termin,numRow,ev,nopengajuan) {
	title = "List File";
	//formajukan(title,ev);
	param = 'method=UploadFile' + '&notransaksi=' + notransaksi+ '&tanggal=' + tanggal+ '&termin=' + termin+ '&nopengajuan=' + nopengajuan;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('containervoid').innerHTML = con.responseText;
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('35%','50%'); 
					loadfiles(notransaksi,termin,nopengajuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function formajukan(title,ev) {
	width = '';
	height = '';
	content = "<div id=containervoid ></div>";
	showDialog2(title, content, width, height, ev);
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	// document.getElementById('dynamic2').style.right = (80) + 'px';
	document.getElementById('dynamic2').style.display = '';
}

function loadfiles(notransaksi,termin,nopengajuan) {
	param = 'method=loadfiles&notransaksi=' + notransaksi+ '&termin=' + termin+ '&nopengajuan=' + nopengajuan;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function submitfile() {
	var kriteriaefil = document.getElementById("kriteriaefil").value;
	var file = document.getElementById("upload").files[0];
	var notransaksi = document.getElementById('notransaksi').innerHTML;
	var pengajuanspk = document.getElementById('pengajuanspk').innerHTML;
	var tanggal = document.getElementById('tanggal').innerHTML;
	var termin = document.getElementById('terminup').innerHTML;
	var formdata = new FormData();
	formdata.append("fileupload", getValue('upload'));
	formdata.append("file", file);
	formdata.append("notransaksi", notransaksi);
	formdata.append("pengajuanspk", pengajuanspk);
	formdata.append("kriteriaefil", kriteriaefil);
	formdata.append("termin", termin);
	formdata.append("tanggal", tanggal);
	if (getValue('upload') == "") {
		alert("warning : Upload file has been empty.");
		return false;
	}
	if (notransaksi == "" || pengajuanspk=="") {
		alert("warning : Nomor Transaksi di Perlukan !");
		return false;
	}
	var con = createXMLHttpRequest();
	document.getElementById('btnsubmit').disabled=true;
	busy_on();
	con.open("POST", "log_slave_realisasispkx.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
					document.getElementById('btnsubmit').disabled=false;
				} else {
					//=== Success Response
					alert('Uploaded Success.');
					document.getElementById('btnsubmit').disabled=false;
					document.getElementById("upload").value = "";
					loadfiles(notransaksi,termin,pengajuanspk);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletefile(notransaksi, namafile,termin,nopengajuan) {
	param = "method=deletefile";
	param += "&notransaksi=" + notransaksi;
	param += "&namafile=" + namafile;
	param += "&nopengajuan=" + nopengajuan;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loadfiles(notransaksi,termin,nopengajuan);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function detailPDF2(notransaksi,kodeorg,tipeview,ev) {
	param = "method=rekapbapp&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        "&tipeview="+tipeview;
    
	alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='log_slave_realisasispkx.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	
    // showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        // " src='log_slave_realisasispkx.php?"+param+"'></iframe>",'800','400',ev);
    // var dialog = document.getElementById('dynamic1');
    // dialog.style.top = '50px';
    // dialog.style.left = '15%';
}

function detailPDF(notransaksi,kodeorg,koderekanan,divisi,ev) {
   param = "proses=pdf&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        "&koderekanan="+koderekanan+"&divisi="+divisi;
    
    showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        " src='log_slave_realisasispk_print_detail.php?"+param+"'></iframe>",'800','400',ev);
    var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function showEdit(notransaksi,kodeorg) {
    var workField = document.getElementById('workField');
	document.getElementById('listData').style.display = "none";
	workField.style.display = "";
    var param = "notransaksi="+notransaksi+"&kodeorg="+kodeorg;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    workField.innerHTML = con.responseText;
                    showDetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('log_slave_realisasispkx.php?method=showEdit', param, respon);
}

function showDetail() {
    var detailField = document.getElementById('detailField');
    var notrans = document.getElementById('notransaksi').value;
    var param = "notransaksi="+notrans+"&divisi="+getValue('divisi')
		+"&kebun="+getValue('kodeorg');
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    detailField.innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('log_slave_realisasispkx.php?method=showDetail', param, respon);
}

function getsewahm(notransaksi,kodeblokdt,kodekegdt,termin,ketdt){
	tgldt2 = document.getElementById('tgldt2').value;
	
	var param = "notransaksi="+notransaksi+"&kodeblok="+kodeblokdt+"&kodekegiatan="+kodekegdt;
	param+="&termin="+termin+"&ketdt="+ketdt+"&tgldt2="+tgldt2;
	
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('hasilhkdt2').value=con.responseText;
					calJumlah();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('log_slave_realisasispkx.php?method=getsewahm', param, respon);
}

function simpantermin(notransaksi,kodeblokdt,kodekegdt){
	dttermin = document.getElementById('dttermin').value;
	kodeblokdt2 = document.getElementById('kodeblokdt2').value;
	nobatermin = document.getElementById('nobatermin').value;
	prosestermin = document.getElementById('prosestermin').value;
	
	var param = "notransaksi="+notransaksi+"&kodeblok="+kodeblokdt+"&kodekegiatan="+kodekegdt+'&kodeblokdt2='+kodeblokdt2;
	param+="&dttermin="+dttermin+"&nobatermin="+nobatermin;
	
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    loaddatadt(notransaksi,kodeblokdt,kodekegdt);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('log_slave_realisasispkx.php?method='+prosestermin, param, respon);
}

function loaddatadt(notransaksi,kodeblokdt,kodekegdt){
	param = "notransaksi="+notransaksi+"&kodeblok="+kodeblokdt+"&kodekegiatan="+kodekegdt+'&divisi='+getValue('divisi')+'&kebun='+getValue('kodeorg');
	
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
					document.getElementById('containerdt').innerHTML=con.responseText;
                    bataltermin();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('log_slave_realisasispkx.php?method=loaddatadt', param, respon);
}

function bataltermin(){
	// document.getElementById('dttermin').value='';
	// document.getElementById('nobatermin').value="";
	document.getElementById('prosestermin').value='inserttermin';
}

function adddt2(notransaksi,kodeblokdt,kodekegdt,termin,ketdt,ev) {
	// width = '';
	// height = '';
	// content = "<fieldset><legend>Tambah Detail Termin "+termin+"</legend><div id=contapp style=\"overflow:auto;\"></div></fieldset>";
	// title = "";
	// showDialog4(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);
	// //document.getElementById('dynamic4').style.top = (pos[1]-437) + 'px';
	// document.getElementById('dynamic4').style.top = 50 + '%';
	// document.getElementById('dynamic4').style.left = 40 + '%';
	// document.getElementById('dynamic4').style.display = '';
	
	matauangdt = document.getElementById('matauangdt').value;
	divisi = getValue('divisi');
	kebun = getValue('kodeorg');
	
	param = 'method=adddt2' + '&notransaksi=' + notransaksi + '&kodeblok=' + kodeblokdt+'&kodekegiatan='+kodekegdt+'&dttermin='+termin+'&nobatermin='+ketdt+'&matauang='+matauangdt+'&kebun='+kebun+'&divisi='+divisi;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					alertify.popup2().destroy();
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('500px','70%');
					// document.getElementById('contapp').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletedt2(notransaksi,kodeblokdt,kodekegdt,termin,ketdt) {
	param = 'notransaksi='+notransaksi+'&kodeblok='+kodeblokdt+'&kodekegiatan='+kodekegdt+'&dttermin='+termin+'&nobatermin='+ketdt;
	if(confirm('Anda yakin hapus data ini termin '+termin+'?')) {
		post_response_text('log_slave_realisasispkx.php?method=deletedt2', param, respog);
    }
	
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddatadt(notransaksi,kodeblokdt,kodekegdt);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deletedt(nourut,notransaksi,kodeblokdt,kodekegdt) {
	param = 'nourut='+nourut;
	if(confirm('Anda yakin hapus item ini?')) {
		post_response_text('log_slave_realisasispkx.php?method=deletedt', param, respog);
    }
	
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddatadt(notransaksi,kodeblokdt,kodekegdt);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpandt2(notransaksi,kodeblokdt,kodekegdt,termin,ketdt) {
	kebun = document.getElementById('kodeorg').value;
	divisi = document.getElementById('divisi').value;
	
	kodeblokdt2 = document.getElementById('kodeblokdt2').value;
	tgldt2 = document.getElementById('tgldt2').value;
	keterangandt2 = document.getElementById('keterangandt2').value;
	hkdt2 = remove_comma(document.getElementById('hkdt2'));
	hasilhkdt2 = remove_comma(document.getElementById('hasilhkdt2'));
	jumlahrpdt2 = remove_comma(document.getElementById('jumlahrpdt2'));
	matauangdt = document.getElementById('matauangdt').value;
	
	param = 'notransaksi='+notransaksi+'&kodeblok='+kodeblokdt+'&kodekegiatan='+kodekegdt+'&dttermin='+termin+'&nobatermin='+ketdt+'&kodeblokdt2='+kodeblokdt2;
	param += '&tgldt2='+tgldt2+'&keterangandt2='+keterangandt2+'&hkdt2='+hkdt2+'&hasilhkdt2='+hasilhkdt2+'&jumlahrpdt2='+jumlahrpdt2+'&matauang='+matauangdt;
	param += '&kebun='+kebun+'&divisi='+divisi;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text('log_slave_realisasispkx.php?method=simpandt2', param, respog);
    
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					bataldt2(notransaksi,kodeblokdt,kodekegdt);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function bataldt2(notransaksi,kodeblokdt,kodekegdt){
	document.getElementById('tgldt2').value = "";
	document.getElementById('keterangandt2').value = "";
	document.getElementById('hkdt2').value = "";
	document.getElementById('hasilhkdt2').value = "";
	document.getElementById('jumlahrpdt2').value = "";
	
	loaddatadt(notransaksi,kodeblokdt,kodekegdt);
}

function calJumlah() {
    var hasilH = document.getElementById('tothkjumlah').value;
    var jumlahH = document.getElementById('totjumlahrp').value;
    var hasil = document.getElementById('hasilhkdt2').value;
    var jumlah = document.getElementById('jumlahrpdt2');
    
    if(jumlahH>0 && parseFloat(hasilH)!=0) {
        jumlah.value = (parseFloat(hasil)/parseFloat(hasilH))*parseFloat(jumlahH);        
        jumlah.value = _formatted(jumlah);
    } else {
        jumlah.value = 0;
    }
}

function form_ajukan(kodeorg,notransaksi, tanggal,termin, numrow,jlhrealisasi,nobapp) {
	if(jlhrealisasi==0){
		alert("Gagal, Jumlah Realisasi masih 0");
		return false;
	}
	// width = '300';
	// height = '';
	// content = "<fieldset style=width:280px><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:180px;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "";
	// showDialog5(title, content, width, height, ev);
	param = 'method=form_ajukan' + '&notransaksi=' + notransaksi + '&tanggal=' + tanggal + '&termin=' + termin + '&numrow=' + numrow+ '&kodeorg=' + kodeorg;
	param += "&nobapp="+nobapp;
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('containeraju').innerHTML = con.responseText;
					alertify.popup2("Ajukan",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('15%','40%'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function ajukan(ev) {
	kepada = document.getElementById('kepada').value;
	notransaksi = document.getElementById('notran_aju').innerHTML;
	tanggal = document.getElementById('tgljurnal').value;
	unit = document.getElementById('unitdt2').value;
	termin = document.getElementById('termin_aju').innerHTML;
	nopengajuan = document.getElementById('nopengajuan_aju').innerHTML;
	numrow = document.getElementById('numrow').value;
	bappdt2 = document.getElementById('bappdt2').value;
	param = 'method=ajukan' + '&notransaksi=' + notransaksi + '&kepada=' + kepada;
	param += "&tanggal="+tanggal;
	param += "&termin="+termin;
	param += "&nopengajuan="+nopengajuan;
	param += "&nobapp="+bappdt2;
	
	if (kepada == '') {
		alert('Isikan nama penyetuju.');
		return;
	}
	tujuan = 'log_slave_realisasispkx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					tipeview='viewhtml';
					alertify.popup().destroy();
					alertify.popup2().destroy();
					viewdetail(notransaksi,unit,tipeview,ev)
					//alert('Sucses');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function postingData(subunit,kodeblok,kodekeg,hasilkerjarealisasi,hkrealisasi,jumlahrealisasi,tanggal) {
    var blok = kodeblok;
	var segment = '0000000001';
    var keg = kodekeg;
	
	var param = "kodeorg="+getValue('kodeorg')+"&koderekanan="+getValue('koderekanan');
    param += "&notransaksi="+getValue('notransaksi')+"&kodeblok="+blok+"&kodesegment="+segment+"&kodekegiatan="+keg;
    
	param += "&blokalokasi="+kodeblok;
    param += "&tanggal="+tanggal;
    param += "&jumlahrealisasi="+jumlahrealisasi;
    
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    loaddatadt(getValue('notransaksi'),subunit,kodekeg);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    if(confirm('Akan dilakukan posting untuk sub unit '+kodeblok+
        ' pada tanggal '+tanggal+
        '\nOnces posted the data can not be changed, are you sure?')) {
      //alert(param);
        post_response_text('log_slave_realisasispk_posting.php', param, respon);
    }
}

function getdetailjurnal(notransaksi,nobapp,kodeorg,tanggal){
	param = "method=getdetailjurnal&notransaksi="+notransaksi+"&kodeorg="+kodeorg+"&tanggal="+tanggal+"&nobapp="+nobapp;
    
    tujuan = 'log_slave_realisasispkx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function formpostingDataAll(nopengajuan,notransaksi,nobapp,kodeorg,tanggal,termin,numRow){
	param = "method=formpostingDataAll&notransaksi="+notransaksi+"&nopengajuan="+nopengajuan+"&kodeorg="+kodeorg+"&tanggal="+tanggal+"&termin="+termin+"&nobapp="+nobapp;
    // width = '';
    // height = '';
	// ev = 'event';
    // content = "<fieldset><div id=contviewx style=\"height:400px;width:700px;overflow:auto;\"></div></fieldset>";
    // title = "Posting All";
    // showDialog2(title, content, width, height, ev);
	// pos = new Array();
	// pos = getMouseP(ev);
	// document.getElementById('dynamic2').style.top = pos[1] + 'px';
	// // document.getElementById('dynamic2').style.right = (80) + 'px';
	// document.getElementById('dynamic2').style.display = '';
	
    tujuan = 'log_slave_realisasispkx.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    // document.getElementById('contviewx').innerHTML = con.responseText;
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function postingDataAll(maxRow) {
	if (maxRow == '' || maxRow == 0) {
		alert('Data tidak ditemukan, proses dibatalkan !');
		return;
	}
	if (confirm("Posting semua ???")) {
		savepostingDataAll(1, maxRow);
	}
}
function savepostingDataAll(currRow, maxRow) {
	keg            = document.getElementById('kegpost'+ currRow).innerHTML;
	blok           = document.getElementById('blokpost' + currRow).innerHTML;
	tanggal        = document.getElementById('tglpost' + currRow).innerHTML;
	jumlahrealisasi= document.getElementById('realpost' + currRow).innerHTML;
	termin         = document.getElementById('termin' + currRow).innerHTML;
	notransaksi    = document.getElementById('notrpost' + currRow).value;
	kodeorg        = document.getElementById('kdorgpost' + currRow).value;
	koderekanan    = document.getElementById('kdrekpost' + currRow).value;
	nobapp         = document.getElementById('nobapppost' + currRow).innerHTML;
	nobapppost     = document.getElementById('nobapppost' + currRow).innerHTML;
	
	ev ='event';
	
	var segment = '0000000001';
	var kodeblok = blok;
	var unit = kodeorg;
	
	var param = "kodeorg="+kodeorg+"&koderekanan="+koderekanan+"&termin="+termin;
    param += "&notransaksi="+notransaksi+"&kodeblok="+blok+"&kodesegment="+segment+"&kodekegiatan="+keg;
    
	param += "&nobapppost="+nobapppost;
	param += "&blokalokasi="+kodeblok;
	param += "&nobapp="+nobapp;
    param += "&tanggal="+tanggal;
    param += "&jumlahrealisasi="+jumlahrealisasi;
	
	tujuan = 'log_slave_realisasispk_posting.php';
	post_response_text(tujuan, param, respog);
	document.getElementById('tr_' + currRow).style.backgroundColor = 'cyan';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					// alertify.popup(con.responseText);
					alertify.alert(con.responseText);
					document.getElementById('tr_' + currRow).style.backgroundColor = 'red';
					//unlockScreen();
				} else {
					if (currRow != undefined) {
						document.getElementById('tr_' + currRow).style.backgroundColor = 'cyan';
					}
					currRow += 1;
					if ((currRow > maxRow) || (maxRow == undefined)) {
						//tipeview='viewhtml';
						//viewdetail(notransaksi,unit,tipeview,ev)
						// closeDialog();
						// closeDialog2();
						alertify.popup().destroy();
						alertify.popup2().destroy();
						getpage();
						alert('Done');
					} else {
						savepostingDataAll(currRow, maxRow);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}







// var showPerPage = 25;

// /* Search
 // * Filtering Data
 // */
// function searchTrans() {
    // var notrans = document.getElementById('sNoTrans');
    // var where = '[["notransaksi","'+notrans.value+'"]]';
    
    // goToPages(1,showPerPage,where);
// }

// /* Paging
 // * Paging Data
 // */
// function defaultList() {
    // goToPages(1,showPerPage);
// }

// function goToPages(page,shows,where) {
    // if(typeof where != 'undefined') {
        // var newWhere = where.replace(/'/g,'"');
    // }
    // var workField = document.getElementById('workField');
    // var param = "page="+page;
    // param += "&shows="+shows+"&tipe=KB";
    // if(typeof where != 'undefined') {
        // param+="&where="+newWhere;
    // }
    
    // function respon() {
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                // } else {
                    // //=== Success Response
                    // workField.innerHTML = con.responseText;
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
    
    // post_response_text('log_slave_realisasispk.php?proses=showHeadList', param, respon);
// }

// function choosePage(obj,shows,where) {
    // var pageVal = obj.options[obj.selectedIndex].value;
    // goToPages(pageVal,shows,where);
// }

// function showEdit(num) {
    // var workField = document.getElementById('workField');
    // var trans = document.getElementById('notransaksi_'+num).getAttribute('value');
    // var kodeorg = document.getElementById('kodeorg_'+num).getAttribute('value');
    // var param = "numRow="+num+"&notransaksi="+trans+"&kodeorg="+kodeorg;
    
    // function respon() {
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                // } else {
                    // //=== Success Response
                    // workField.innerHTML = con.responseText;
                    // showDetail();
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
    
    // post_response_text('log_slave_realisasispk.php?proses=showEdit', param, respon);
// }

// /*
 // * Detail
 // */

// function showDetail() {
    // var detailField = document.getElementById('detailField');
    // var notrans = document.getElementById('notransaksi').value;
    // var param = "notransaksi="+notrans+"&divisi="+getValue('divisi')
		// +"&kebun="+getValue('kodeorg');
    
    // function respon() {
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                // } else {
                    // //=== Success Response
                    // detailField.innerHTML = con.responseText;
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
    
    // post_response_text('log_slave_realisasispk_detail.php?proses=showDetail', param, respon);
// }

// function manageDetail(numRow) {
    // var detailField = document.getElementById('detail_'+numRow);
    // var notrans = document.getElementById('notransaksi').value;
    // var matauang = document.getElementById('matauang').value;
    // var kodeblok = document.getElementById('kodeblok_'+numRow).getAttribute('value');
    // var kodekeg = document.getElementById('kodekegiatan_'+numRow).getAttribute('value');
    // var param = "notransaksi="+notrans+"&kodeblok="+kodeblok+"&numRow="+numRow;
    // param += "&kodekegiatan="+kodekeg+"&divisi="+getValue('divisi')+"&matauang="+matauang;
	// param += "&kebun="+getValue('kodeorg');
    
    // function respon() {
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                // } else {
                    // //=== Success Response
                    // detailField.innerHTML = con.responseText;
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
    
    // if(detailField.innerHTML=="") {
        // post_response_text('log_slave_realisasispk_detail.php?proses=manageDetail', param, respon);
    // } else {
        // if(detailField.style.display=='none') {
            // detailField.style.display="";
        // } else {
            // detailField.style.display="none";
        // }
    // }
// }

// function cekKP(numRow1,numRow2){
    // kodeaplikasi = 'kosong';
    // var keg = document.getElementById('kodekegiatan_'+numRow1).getAttribute('value');
    // listkp=document.getElementById("listkp").value; // ada di log_realisasi.php
    // var vba = listkp.split("####"); 
    // for(var i = 0, len = vba.length; i < len; ++i) {
        // if(keg==vba[i])kodeaplikasi='KP';
    // }
    // return kodeaplikasi;    
// }

// function addData(numRow1,numRow2,theme) {
    // if(cekKP(numRow1,numRow2)!='KP')
	// if(cekReal(numRow1,numRow2)==false) {
		// alert('Actual realization larger than contract volume');
		// return;
	// }
	
    // var tbody = document.getElementById('detailBody_'+numRow1);
    // var blok = document.getElementById('kodeblok_'+numRow1).getAttribute('value');
    // var keg = document.getElementById('kodekegiatan_'+numRow1).getAttribute('value');
    // matauang = document.getElementById('matauang').value;
    // jmlkgplus = document.getElementById('jmlkgplus_'+keg).value;
    // var param = "notransaksi="+getValue('notransaksi')+"&kodeblok="+blok+
        // "&kodekegiatan="+keg+'&divisi='+getValue('divisi')+"&kebun="+getValue('kodeorg');
    // param += "&blokalokasi="+getValue('blokalokasi_'+numRow1+'_'+numRow2);
	// param += "&kodesegment="+getValue('kodesegment_'+numRow1+'_'+numRow2);
    // param += "&tanggal="+getValue('tanggal_'+numRow1+'_'+numRow2);
    // param += "&matauang="+matauang;
	// param += "&jmlkgplus="+jmlkgplus;
    // param += "&hasilkerjarealisasi="+getValue('hasilkerjarealisasi_'+numRow1+'_'+numRow2);
    // param += "&hkrealisasi="+getValue('hkrealisasi_'+numRow1+'_'+numRow2);
    // param += "&jumlahrealisasi="+getValue('jumlahrealisasi_'+numRow1+'_'+numRow2);
    // param += "&numRow1="+numRow1+"&numRow2="+numRow2;
    // param += "&jjgkontanan="+getValue('jjgkontanan_'+numRow1+'_'+numRow2);
    // param += "&termin="+getValue('termin_'+numRow1+'_'+numRow2);
    // param += "&keterangan="+getValue('keterangan_'+numRow1+'_'+numRow2);
// //    alert(param);
    // function respon() {
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                // } else {
                    // //=== Success Response
                    // saveToAdd(numRow1,numRow2,theme);
                    // var newRow = document.createElement("tr");
                    // newRow.setAttribute('id','tr_'+numRow1+'_'+(numRow2+1));
                    // newRow.setAttribute('class','rowcontent');
                    // tbody.appendChild(newRow);
                    // newRow.innerHTML = con.responseText;
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
    
    // post_response_text('log_slave_realisasispk_detail.php?proses=add', param, respon);
// }

// function saveData(numRow1,numRow2) {
// if(cekKP(numRow1,numRow2)!='KP')//tambahan dz, cek apakah kontrak plus?
    // if(cekReal(numRow1,numRow2)==false) {
        // alert('Actual realization larger than contract volume');
        // return;
    // }
    
    // var blok = document.getElementById('kodeblok_'+numRow1).getAttribute('value');
    // var keg = document.getElementById('kodekegiatan_'+numRow1).getAttribute('value');
    // var param = "notransaksi="+getValue('notransaksi')+"&kodeblok="+blok+"&kodekegiatan="+keg;
    // param += "&blokalokasi="+getValue('blokalokasi_'+numRow1+'_'+numRow2);
	// param += "&kodesegment="+getValue('kodesegment_'+numRow1+'_'+numRow2);
    // param += "&tanggal="+getValue('tanggal_'+numRow1+'_'+numRow2);
    // param += "&hasilkerjarealisasi="+getValue('hasilkerjarealisasi_'+numRow1+'_'+numRow2);
    // param += "&hkrealisasi="+getValue('hkrealisasi_'+numRow1+'_'+numRow2);
    // param += "&jumlahrealisasi="+getValue('jumlahrealisasi_'+numRow1+'_'+numRow2);
    // param += "&jjgkontanan="+getValue('jjgkontanan_'+numRow1+'_'+numRow2);
	// param += "&termin="+getValue('termin_'+numRow1+'_'+numRow2);
    // param += "&keterangan="+getValue('keterangan_'+numRow1+'_'+numRow2);
    
    // function respon() {
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                // } else {
                    // //=== Success Response
                    // alert('Data changed');
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
    
    // post_response_text('log_slave_realisasispk_detail.php?proses=edit', param, respon);
// }

// function saveToAdd(numRow1,numRow2,theme) {
    // var btn = document.getElementById('btn_'+numRow1+'_'+numRow2),
		// btnDel = document.getElementById('btnDel_'+numRow1+'_'+numRow2),
		// btnPost = document.getElementById('btnPost_'+numRow1+'_'+numRow2),
		// tanggal = document.getElementById('tanggal_'+numRow1+'_'+numRow2),
		// blok = document.getElementById('blokalokasi_'+numRow1+'_'+numRow2),
		// termin = document.getElementById('termin_'+numRow1+'_'+numRow2),
		// segment = document.getElementById('kodesegment_'+numRow1+'_'+numRow2);
    
    // // Change btn
    // btn.removeAttribute('src');
    // btn.removeAttribute('onclick');
    // btn.setAttribute('src','images/'+theme+'/save.png');
    // btn.setAttribute('onclick','saveData('+numRow1+','+numRow2+')');
    // btnDel.style.display = "";
    // btnPost.style.display = "none";
    // tanggal.setAttribute('disabled','disabled');
    // blok.setAttribute('disabled','disabled');
    // termin.setAttribute('disabled','disabled');
	// segment.setAttribute('disabled','disabled');
// }

// function deleteData(numRow1,numRow2) {
    // var tr = document.getElementById('tr_'+numRow1+'_'+numRow2);
    // var blok = document.getElementById('kodeblok_'+numRow1).getAttribute('value');
    // var keg = document.getElementById('kodekegiatan_'+numRow1).getAttribute('value');
    // var param = "notransaksi="+getValue('notransaksi')+"&kodeblok="+blok+"&kodekegiatan="+keg;
    // param += "&tanggal="+getValue('tanggal_'+numRow1+'_'+numRow2);
    // param += "&blokalokasi="+getValue('blokalokasi_'+numRow1+'_'+numRow2);
	// param += "&kodesegment="+getValue('kodesegment_'+numRow1+'_'+numRow2);
    
    // function respon() {
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                // } else {
                    // //=== Success Response
                    // tr.parentNode.removeChild(tr);
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
    
    // post_response_text('log_slave_realisasispk_detail.php?proses=delete', param, respon);
// }

// function printPDF(ev) {
    // // Prep Param
    // param = "proses=pdf";
    
    // showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        // " src='log_slave_spk_print.php?"+param+"'></iframe>",'800','400',ev);
    // var dialog = document.getElementById('dynamic1');
    // dialog.style.top = '50px';
    // dialog.style.left = '15%';
// }

// function detailPDF(numRow,ev) {
    // // Prep Param
    // var notransaksi = document.getElementById('notransaksi_'+numRow).getAttribute('value');
    // var kodeorg = document.getElementById('kodeorg_'+numRow).getAttribute('value');
    // var koderekanan = document.getElementById('koderekanan_'+numRow).getAttribute('value'),
		// divisi = document.getElementById('divisi_'+numRow).getAttribute('value');
    // param = "proses=pdf&notransaksi="+notransaksi+"&kodeorg="+kodeorg+
        // "&koderekanan="+koderekanan+"&divisi="+divisi;
    
    // showDialog1('Print PDF',"<iframe frameborder=0 style='width:795px;height:400px'"+
        // " src='log_slave_realisasispk_print_detail.php?"+param+"'></iframe>",'800','400',ev);
    // var dialog = document.getElementById('dynamic1');
    // dialog.style.top = '50px';
    // dialog.style.left = '15%';
// }

// /* Posting Data
 // */
 
// /* function postingData(numRow1,numRow2,theme) {
    // var blok = document.getElementById('kodeblok_'+numRow1).getAttribute('value');
	// var segment = getValue('kodesegment_'+numRow1+'_'+numRow2);
    // var keg = document.getElementById('kodekegiatan_'+numRow1).getAttribute('value');
    // var hasilkerjarealisasi = document.getElementById('hasilkerjarealisasi_'+numRow1+'_'+numRow2);
    // var hkrealisasi = document.getElementById('hkrealisasi_'+numRow1+'_'+numRow2);
    // var jumlahrealisasi = document.getElementById('jumlahrealisasi_'+numRow1+'_'+numRow2);
    // var keterangan = document.getElementById('keterangan_'+numRow1+'_'+numRow2);
    // var btn = document.getElementById('btn_'+numRow1+'_'+numRow2);
    // var btnDel = document.getElementById('btnDel_'+numRow1+'_'+numRow2);
    // var btnPost = document.getElementById('btnPost_'+numRow1+'_'+numRow2);
	// //var btnRev = document.getElementById('btnRev_'+numRow1+'_'+numRow2);
    
    // var param = "kodeorg="+getValue('kodeorg')+"&koderekanan="+getValue('koderekanan');
    // param += "&notransaksi="+getValue('notransaksi')+"&kodeblok="+blok+"&kodesegment="+segment+"&kodekegiatan="+keg;
    // param += "&blokalokasi="+getValue('blokalokasi_'+numRow1+'_'+numRow2);
    // param += "&tanggal="+getValue('tanggal_'+numRow1+'_'+numRow2);
    // param += "&jumlahrealisasi="+remove_comma(document.getElementById('jumlahrealisasi_'+numRow1+'_'+numRow2));
    
    
    // function respon() {
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                // } else {
                    // //=== Success Response
                    // hasilkerjarealisasi.setAttribute('disabled','disabled');
                    // hkrealisasi.setAttribute('disabled','disabled');
                    // jumlahrealisasi.setAttribute('disabled','disabled');
                    // keterangan.setAttribute('disabled','disabled');
                    // btn.style.display = 'none';
                    // btnDel.style.display = 'none';
					// //btnRev.style.display = '';
                    // btnPost.removeAttribute('onclick');
                    // btnPost.removeAttribute('src');
                    // btnPost.setAttribute('src','images/'+theme+'/posted.png');
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
    
    // if(confirm('Akan dilakukan posting untuk sub unit '+
        // getValue('blokalokasi_'+numRow1+'_'+numRow2)+
        // ' pada tanggal '+getValue('tanggal_'+numRow1+'_'+numRow2)+
        // '\nOnces posted the data can not be changed, are you sure?')) {
		// post_response_text('log_slave_realisasispk_detail.php?proses=posting', param, respon);
    // }
// }
 // */

// function postingData(numRow1,numRow2,theme) {
    // var blok = document.getElementById('kodeblok_'+numRow1).getAttribute('value');
	// var segment = getValue('kodesegment_'+numRow1+'_'+numRow2);
    // var keg = document.getElementById('kodekegiatan_'+numRow1).getAttribute('value');
    // var hasilkerjarealisasi = document.getElementById('hasilkerjarealisasi_'+numRow1+'_'+numRow2);
    // var hkrealisasi = document.getElementById('hkrealisasi_'+numRow1+'_'+numRow2);
    // var jumlahrealisasi = document.getElementById('jumlahrealisasi_'+numRow1+'_'+numRow2);
    // var keterangan = document.getElementById('keterangan_'+numRow1+'_'+numRow2);
    // var btn = document.getElementById('btn_'+numRow1+'_'+numRow2);
    // var btnDel = document.getElementById('btnDel_'+numRow1+'_'+numRow2);
    // var btnPost = document.getElementById('btnPost_'+numRow1+'_'+numRow2);
	// var btnRev = document.getElementById('btnRev_'+numRow1+'_'+numRow2);
    
    // var param = "kodeorg="+getValue('kodeorg')+"&koderekanan="+getValue('koderekanan');
    // param += "&notransaksi="+getValue('notransaksi')+"&kodeblok="+blok+"&kodesegment="+segment+"&kodekegiatan="+keg;
    // param += "&blokalokasi="+getValue('blokalokasi_'+numRow1+'_'+numRow2);
    // param += "&tanggal="+getValue('tanggal_'+numRow1+'_'+numRow2);
    // //param += "&hasilkerjarealisasi="+getValue('hasilkerjarealisasi_'+numRow1+'_'+numRow2);
    // //param += "&hkrealisasi="+getValue('hkrealisasi_'+numRow1+'_'+numRow2);
	// // diedit oleh ginting menambahkan remove comma yang sebelumnya getValue
    // param += "&jumlahrealisasi="+remove_comma(document.getElementById('jumlahrealisasi_'+numRow1+'_'+numRow2));
    
    
    // function respon() {
        // if (con.readyState == 4) {
            // if (con.status == 200) {
                // busy_off();
                // if (!isSaveResponse(con.responseText)) {
                    // alert(con.responseText);
                // } else {
                    // //=== Success Response
                    // hasilkerjarealisasi.setAttribute('disabled','disabled');
                    // hkrealisasi.setAttribute('disabled','disabled');
                    // jumlahrealisasi.setAttribute('disabled','disabled');
                    // keterangan.setAttribute('disabled','disabled');
                    // //btn.style.display = 'none';
                    // //btnDel.style.display = 'none';
					// //btnRev.style.display = '';
                    // btnPost.removeAttribute('onclick');
                    // btnPost.removeAttribute('src');
                    // btnPost.setAttribute('src','images/'+theme+'/posted.png');
                // }
            // } else {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
    
    // if(confirm('Akan dilakukan posting untuk sub unit '+
        // getValue('blokalokasi_'+numRow1+'_'+numRow2)+
        // ' pada tanggal '+getValue('tanggal_'+numRow1+'_'+numRow2)+
        // '\nOnces posted the data can not be changed, are you sure?')) {
      // //alert(param);
        // post_response_text('log_slave_realisasispk_posting.php', param, respon);
    // }
// }


// function viewdetail(num) {
	// width = '';
	// height = '';
	// content = "<fieldset><legend>Preview</legend><div id=contRekap style=\"width:100%;max-height:400px;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "";
	// showDialog1(title, content, width, height, ev);
	
	// var trans = document.getElementById('notransaksi_'+num).getAttribute('value');
    // var kodeorg = document.getElementById('kodeorg_'+num).getAttribute('value');
    // var param = "numRow="+num+"&notransaksi="+trans+"&kodeorg="+kodeorg;
	
	// param += '&proses=rekapbapp';
	// tujuan = 'log_slave_realisasispk_detail.php';
	// post_response_text(tujuan, param, respog);
	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// document.getElementById('contRekap').innerHTML = con.responseText;
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }


// function getapprovaldetail(nopengajuan,kodeorg) {
	// width = '800';
	// height = '';
	// content = "<fieldset><legend>Detail Approval</legend><div id=contapp style=\"width:780px;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "";
	// showDialog4(title, content, width, height, ev);
	// param = 'proses=getapprovaldetail' + '&nopengajuan=' + nopengajuan + '&kodeorg=' + kodeorg;
	// tujuan = 'log_slave_realisasispk_detail.php';
	// post_response_text(tujuan, param, respog);
	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// document.getElementById('contapp').innerHTML = con.responseText;
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }

// function form_ajukan(kodeorg,notransaksi, tanggal,termin, numrow) {
	// width = '300';
	// height = '';
	// content = "<fieldset style=width:280px><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:120px;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "";
	// showDialog5(title, content, width, height, ev);
	// param = 'proses=form_ajukan' + '&notransaksi=' + notransaksi + '&tanggal=' + tanggal + '&termin=' + termin + '&numrow=' + numrow+ '&kodeorg=' + kodeorg;
	// tujuan = 'log_slave_realisasispk_detail.php';
	// post_response_text(tujuan, param, respog);
	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// document.getElementById('containeraju').innerHTML = con.responseText;
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }

// function ajukan() {
	// kepada = document.getElementById('kepada').value;
	// notransaksi = document.getElementById('notran_aju').innerHTML;
	// tanggal = document.getElementById('tanggal_aju').innerHTML;
	// termin = document.getElementById('termin_aju').innerHTML;
	// nopengajuan = document.getElementById('nopengajuan_aju').innerHTML;
	// numrow = document.getElementById('numrow').value;
	// param = 'proses=ajukan' + '&notransaksi=' + notransaksi + '&kepada=' + kepada;
	// param += "&tanggal="+tanggal;
	// param += "&termin="+termin;
	// param += "&nopengajuan="+nopengajuan;
	
	// if (kepada == '') {
		// alert('Isikan nama penyetuju.');
		// return;
	// }
	// tujuan = 'log_slave_realisasispk_detail.php';
	// post_response_text(tujuan, param, respog);
	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// x = document.getElementById('rowdetail_' + numrow);
					// x.cells[6].innerHTML = con.responseText;
					// x.cells[7].innerHTML = "Proses Pengajuan";
					// x.cells[7].style.backgroundColor = "";
					// x.cells[8].innerHTML = 'Menunggu Keputusan';
					// x.cells[8].innerHTML = '';
					// alert('Sucses');
					// closeDialog5();
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }

// function cekReal(numRow1,numRow2) {
    // var tbody = document.getElementById('detailBody_'+numRow1);
    // var hk = document.getElementById('hk_'+numRow1).getAttribute('value');
    // var hasil = document.getElementById('hasilkerjajumlah_'+numRow1).getAttribute('value');
    // var jumlah = document.getElementById('jumlahrp_'+numRow1).getAttribute('value');
    
    // var sumHk=0;var sumHasil=0;var sumJumlah=0;
    // for(i in tbody.childNodes) {
        // if(document.getElementById('hkrealisasi_'+numRow1+'_'+i)) {
            // var tmpHk = document.getElementById('hkrealisasi_'+numRow1+'_'+i).value;
            // var tmpHasil = document.getElementById('hasilkerjarealisasi_'+numRow1+'_'+i).value;
            // var tmpJumlah = document.getElementById('jumlahrealisasi_'+numRow1+'_'+i).value;
            // tmpJumlah = tmpJumlah.replace(",","");
            // sumHk+=parseInt(tmpHk);sumHasil+=parseInt(tmpHasil);sumJumlah+=parseInt(tmpJumlah);
        // }
    // }
    
    // var res = true;
    // if(sumHk>hk) {
        // document.getElementById('hkrealisasi_'+numRow1+'_'+numRow2).value = 0;
        // res = false;
    // }
    // if(sumHasil>hasil) {
        // document.getElementById('hasilkerjarealisasi_'+numRow1+'_'+numRow2).value = 0;
        // res = false;
    // }
    // if(sumJumlah>jumlah) {
        // document.getElementById('jumlahrealisasi_'+numRow1+'_'+numRow2).value = 0;
        // res = false;
    // }
    
    // return res;
// }

// function calJumlah(numRow1,numRow2) {
    // var hasilH = document.getElementById('hasilkerjajumlah_'+numRow1).getAttribute('value');
    // var keg = document.getElementById('kodekegiatan_'+numRow1).getAttribute('value');
    // var jumlahH = document.getElementById('jumlahrp_'+numRow1).getAttribute('value');
    // var hasil = document.getElementById('hasilkerjarealisasi_'+numRow1+'_'+numRow2).value;
    // var jumlah = document.getElementById('jumlahrealisasi_'+numRow1+'_'+numRow2);
    // var jmlkgplus = document.getElementById('jmlkgplus_'+keg).value;
    
    // if(jumlahH>0 && parseFloat(hasilH)!=0) {
        // if (jmlkgplus==0){
            // jumlah.value = (parseFloat(hasil)/parseFloat(hasilH))*parseFloat(jumlahH);
        // }else{
            // jumlah.value = parseFloat(hasil)*parseFloat(jumlahH);
        // }        
        // jumlah.value = _formatted(jumlah);
    // } else {
        // jumlah.value = 0;
    // }
// }

// /**
 // * getSegment
 // * Mengambil Segment sesuai bloknya, lookup ke tabel proporsi segment
 // * Jika tidak ada maka return nilai default '0000000001'
 // * @param {Number} row		Baris pada Daftar SPK
 // * @param {Number} subRow	Baris pada Daftar BAPP yang muncul didalam baris SPK (Sub)
 // */
// function getSegment(row, subRow) {
	// var blok = getValue('blokalokasi_'+row+'_'+subRow),
		// param = "";
	// if(blok!='') {
		// param = "kodeblok="+blok;
		// post_response_text("log_slave_getSegmentBlok.php", param, respog);
	// }
	
	// function respog(){
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// getById('kodesegment_'+row+'_'+subRow).innerHTML=con.responseText;
				// }
			// }
			// else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }

// /**
 // * revisiData
 // * Popup Form Revisi Realisasi
 // */
// function revisiData(numRow1,numRow2,theme,ev) {
	// var blok = document.getElementById('kodeblok_'+numRow1).getAttribute('value'),
		// segment = getValue('kodesegment_'+numRow1+'_'+numRow2),
		// keg = document.getElementById('kodekegiatan_'+numRow1).getAttribute('value'),
		// hasilkerjarealisasi = document.getElementById('hasilkerjarealisasi_'+numRow1+'_'+numRow2),
		// hkrealisasi = document.getElementById('hkrealisasi_'+numRow1+'_'+numRow2),
		// jumlahrealisasi = document.getElementById('jumlahrealisasi_'+numRow1+'_'+numRow2),
		// param = "kodeorg="+getValue('kodeorg')+"&koderekanan="+getValue('koderekanan'),
		// con = "<table><tr><td>HK</td>";
	// param += "&notransaksi="+getValue('notransaksi')+"&kodeblok="+blok+"&kodesegment="+segment+"&kodekegiatan="+keg;
    // param += "&blokalokasi="+getValue('blokalokasi_'+numRow1+'_'+numRow2);
    // param += "&tanggal="+getValue('tanggal_'+numRow1+'_'+numRow2);
	// param += "&hasilkerjarealisasi="+getValue('hasilkerjarealisasi_'+numRow1+'_'+numRow2);
    // param += "&hkrealisasi="+getValue('hkrealisasi_'+numRow1+'_'+numRow2);
	// param += "&jumlahrealisasi="+remove_comma(document.getElementById('jumlahrealisasi_'+numRow1+'_'+numRow2));
	// con += "<td><input id=revHK class=myinputtextnumber onkeypress='return angka_doang(event)' value='"+hkrealisasi.value+"'></td></tr>";
	// con += "<tr><td>Hasil</td><td><input id=revHasil class=myinputtextnumber onkeypress='return angka_doang(event)' value='"+hasilkerjarealisasi.value+"'></td></tr>";
	// con += "<tr><td>Jumlah</td><td><input id=revJumlah class=myinputtextnumber onkeypress='return angka_doang(event)' value='"+remove_comma_var(jumlahrealisasi.value)+"'></td></tr></table>";
	// con += "<button class=mybutton onclick='doRev()'>Revisi</button>";
	// con += "<input id=revParam type=hidden value='"+param+"'>";
	// con += "<input id=btnRevName type=hidden value='btnRev_"+numRow1+"_"+numRow2+"'>";
	// con += "<input id=rowCol type=hidden value='"+numRow1+"_"+numRow2+"'>";
	
	// showDialog1("Revisi BASPK",con,200,100,ev);
	// getById('dynamic1').style.left = null;
	// getById('dynamic1').style.right = "110px";
// }

// /**
 // * doRev
 // * Do Revisi BASPK
 // */
// function doRev() {
	// var param = "",
		// tujuan = "log_slave_realisasispk_revisi.php";
	
	// param += "revHK="+getById('revHK').value+"&revHasil="+getById('revHasil').value;
	// param += "&revJumlah="+getById('revJumlah').value+'&'+getValue('revParam');
	// post_response_text(tujuan, param, respog);
	
	// function respog(){
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// alert("Revisi Berhasil");
					// btnRev = getById(getValue('btnRevName')).removeAttribute('onclick');
					// rowCol = getById('rowCol').value;
					// setValue('hasilkerjarealisasi_'+rowCol,getById('revHasil').value);
					// setValue('hkrealisasi_'+rowCol,getById('revHK').value);
					// setValue('jumlahrealisasi_'+rowCol,getById('revJumlah').value);
					// closeDialog();
				// }
			// }
			// else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }

// function formajukan(title) {
	// width = '';
	// height = '';
	// content = "<div id=containervoid ></div>";
	// ev = 'event';
	// showDialog2(title, content, width, height, ev);
// }
// function UploadFile(notransaksi,tanggal,termin,numRow) {
	// title = "List File";
	// formajukan(title);
	// //var notransaksi = document.getElementById('notransaksi_'+numRow).getAttribute('value');
	// param = 'method=UploadFile' + '&notransaksi=' + notransaksi+ '&tanggal=' + tanggal+ '&termin=' + termin;
	// tujuan = 'log_slave_realisasispk_upload.php';
	// post_response_text(tujuan, param, respog);
	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// document.getElementById('containervoid').innerHTML = con.responseText;
					// loadfiles(notransaksi,termin);
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }


// function submitfile() {
	// var kriteriaefil = document.getElementById("kriteriaefil").value;
	// var file = document.getElementById("upload").files[0];
	// var notransaksi = document.getElementById('notransaksi').innerHTML;
	// var pengajuanspk = document.getElementById('pengajuanspk').innerHTML;
	// var tanggal = document.getElementById('tanggal').innerHTML;
	// var termin = document.getElementById('terminup').innerHTML;
	// var formdata = new FormData();
	// formdata.append("fileupload", getValue('upload'));
	// formdata.append("file", file);
	// formdata.append("notransaksi", notransaksi);
	// formdata.append("pengajuanspk", pengajuanspk);
	// formdata.append("kriteriaefil", kriteriaefil);
	// formdata.append("termin", termin);
	// formdata.append("tanggal", tanggal);
	// if (getValue('upload') == "") {
		// alert("warning : Upload file has been empty.");
		// return false;
	// }
	// if (notransaksi == "" || pengajuanspk=="") {
		// alert("warning : Nomor Transaksi di Perlukan !");
		// return false;
	// }
	// var con = createXMLHttpRequest();
	// document.getElementById('btnsubmit').disabled=true;
	// busy_on();
	// con.open("POST", "log_slave_realisasispk_upload.php?method=submitfile", true);
	// con.onreadystatechange = eval(respon);
	// con.send(formdata);
	// function respon() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
					// document.getElementById('btnsubmit').disabled=false;
				// } else {
					// //=== Success Response
					// alert('Uploaded Success.');
					// document.getElementById('btnsubmit').disabled=false;
					// document.getElementById("upload").value = "";
					// loadfiles(notransaksi,termin);
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }

// function loadfiles(notransaksi,termin) {
	// param = 'method=loadfiles&notransaksi=' + notransaksi+ '&termin=' + termin;
	// tujuan = 'log_slave_realisasispk_upload.php';
	// post_response_text(tujuan, param, respog);
	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// if (document.getElementById('listfiles') !== null) {
						// document.getElementById('listfiles').innerHTML = con.responseText;
					// }
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }

// function form() {
	// width = '';
	// height = '';
	// content = "<fieldset style=\"width:97%;\"><div id=contview style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
	// ev = 'event';
	// title = "View";
	// showDialog5(title, content, width, height, ev);
// }
// function viewfile(ev, namafile) {
	// ext = namafile.split(".");
	// if (trim(ext[1]) == 'jpg' || trim(ext[1]) == 'jpeg' || trim(ext[1]) == 'png') {
		// form();
		// param = 'method=viewfile&namafile=' + namafile;
		// tujuan = 'log_slave_realisasispk_upload.php';
		// post_response_text(tujuan, param, respog);
	// } else {
		// alert('File tidak dapat di tampilkan, silahkan download untuk melihat isi file.');
		// return;
	// }
	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// document.getElementById('contview').innerHTML = con.responseText;
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }

// function deletefile(notransaksi, namafile) {
	// param = "method=deletefile";
	// param += "&notransaksi=" + notransaksi;
	// param += "&namafile=" + namafile;
	// tujuan = 'log_slave_realisasispk_upload.php';
	// post_response_text(tujuan, param, respog);
	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
					// alert(con.responseText);
				// } else {
					// loadfiles(notransaksi);
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }

// function view(nopengajuan,notransaksi,kodeorg,tanggal,termin,numRow,ev,tipe){
	// param = "proses=preview&tipe="+tipe+"&notransaksi="+notransaksi+"&nopengajuan="+nopengajuan+"&kodeorg="+kodeorg+"&tanggal="+tanggal+"&termin="+termin;
    // width = '';
    // height = '';
    // content = "<fieldset><div id=contviewx style=\"width:800px;height:400px;overflow:auto;\"></div></fieldset>";
    // ev = 'event';
    // title = "View";
    // showDialog2(title, content, width, height, ev);
	
    // tujuan = 'log_slave_realisasispk_detail.php';
    // post_response_text(tujuan, param, respog);
    // function respog(){
        // if (con.readyState == 4){
            // if (con.status == 200){
                // busy_off();
                // if (!isSaveResponse(con.responseText)){
                    // alert(con.responseText);
                // }else{
                    // document.getElementById('contviewx').innerHTML = con.responseText;
                // }
            // }else{
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
// }