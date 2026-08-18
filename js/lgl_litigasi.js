function excel(ev,tujuan){
    unitexp = document.getElementById('unitexp').value;
    perexp = document.getElementById('perexp').value;
	if(unitexp==''||perexp==''){
		alert('Lengkapi unit dan periode.');
		return;
	}
    judul='Report Ms.Excel';	
    param = 'method=excel' + '&unitexp=' + unitexp + '&perexp=' + perexp;
    printFile(param,tujuan,judul,ev);	
}


function add_new_data(){
    document.getElementById('header').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
    cleardetail();  
}

function viewexcel(pt,unit,notransaksi,tipe){
	ev = 'event';
	param = 'method=html' + '&pt=' + pt + '&tipe=' + tipe+ '&unit=' + unit+ '&notransaksi=' + notransaksi;
	tujuan = 'lgl_slave_litigasi.php' + "?" + param;
	width = '';
	height = '';
	title = "Excel";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	showDialog1(title, content, width, height, ev);
}

function html(pt,unit,notransaksi,tipe){
    width = '';
    height = '';
    content = "<fieldset><div id=contviewx style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "View";
    showDialog1(title, content, width, height, ev);
	
    param = 'method=html' + '&pt=' + pt + '&tipe=' + tipe+ '&unit=' + unit+ '&notransaksi=' + notransaksi;
    tujuan = 'lgl_slave_litigasi.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('contviewx').innerHTML = con.responseText;
					loadfiles(notransaksi);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function displayList(){
    document.getElementById('divsch').value='';
    document.getElementById('listData').style.display = 'block';
    document.getElementById('header').style.display = 'none';
    loaddata(0);
}
function getunit(){
	pt= document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	param='pt='+pt+'&method=getunit';
	tujuan='lgl_slave_litigasi.php';  
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('unit').innerHTML=trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function edit(notransaksi,pt,unit,lokasipengadilan,jenispengadilan,penggugat,tergugat,jenisperkara,advokat,tanggalterdaftar,ringkasan,tanggalputusan,amarputusan){
    
	document.getElementById('listData').style.display='none';
    document.getElementById('header').style.display='block';
    document.getElementById('pt').disabled=true;
    document.getElementById('unit').disabled=true;
    document.getElementById('notransaksi').disabled=true;
	document.getElementById('pt').value=pt;
	document.getElementById('unit').value=unit;
	document.getElementById('notransaksi').value=notransaksi;
	document.getElementById('lokasipengadilan').value=lokasipengadilan;
	document.getElementById('jenispengadilan').value=jenispengadilan;
	document.getElementById('penggugat').value=penggugat;
	document.getElementById('tergugat').value=tergugat;
	document.getElementById('jenisperkara').value=jenisperkara;
	document.getElementById('advokat').value=advokat;
	document.getElementById('tanggalterdaftar').value=tanggalterdaftar;
	document.getElementById('ringkasan').value=ringkasan;
	document.getElementById('tanggalputusan').value=tanggalputusan;
	document.getElementById('amarputusan').value=amarputusan;
    document.getElementById('method').value='update';
	loadfiles(notransaksi);
}

function del(pt,unit,notransaksi){
    param='method=delete'+'&pt='+pt;
	param += "&unit="+unit;
	param += "&notransaksi="+notransaksi;
    tujuan='lgl_slave_litigasi.php';
    if(confirm('Anda yakin ???')){
        post_response_text(tujuan, param, respog);	
    }
    function respog(){
	  if(con.readyState==4){
		if (con.status == 200) {
			busy_off();
			if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
			}else {
			   loaddata();
			   
			}
		}else {
				busy_off();
				error_catch(con.status);
		}
	  }	
    }
}

function save(){
    pt=document.getElementById('pt').value;
    unit=document.getElementById('unit').value;
	notransaksi=document.getElementById('notransaksi').value;
	lokasipengadilan=document.getElementById('lokasipengadilan').value;
	jenispengadilan=document.getElementById('jenispengadilan').value;
	penggugat=document.getElementById('penggugat').value;
	tergugat=document.getElementById('tergugat').value;	
	jenisperkara=document.getElementById('jenisperkara').value;
	advokat=document.getElementById('advokat').value;
	tanggalterdaftar=document.getElementById('tanggalterdaftar').value;
	ringkasan=trim(document.getElementById('ringkasan').value);
	tanggalputusan=document.getElementById('tanggalputusan').value;
	amarputusan=trim(document.getElementById('amarputusan').value);
    method=document.getElementById('method').value;
    
    if(pt=='' || unit==''|| notransaksi==''){
        alert('Lengkapi Pengisian.');
        return;
    }

    param='pt='+pt;
    param+='&unit='+unit;
	param+='&notransaksi='+notransaksi;
	param+='&lokasipengadilan='+lokasipengadilan;
	param+='&jenispengadilan='+jenispengadilan;
	param+='&penggugat='+penggugat;
	param+='&tergugat='+tergugat;
	param+='&jenisperkara='+jenisperkara;
	param+='&advokat='+advokat;
	param+='&tanggalterdaftar='+tanggalterdaftar;
	param+='&ringkasan='+ringkasan;
	param+='&tanggalputusan='+tanggalputusan;
	param+='&amarputusan='+amarputusan;
    param+='&method='+method;
    tujuan='lgl_slave_litigasi.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					if(confirm("Ingin upload dokument ???")){
						document.getElementById('pt').disabled=true;
						document.getElementById('unit').disabled=true;
						document.getElementById('notransaksi').disabled=true;
					}else{
						cleardetail();
					}
						loaddata(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cleardetail(){
	document.getElementById('pt').value='';
    document.getElementById('pt').disabled=false;
	document.getElementById('unit').value='';
    document.getElementById('unit').disabled=false;
	document.getElementById('notransaksi').value='';
    document.getElementById('notransaksi').disabled=false;
	document.getElementById('lokasipengadilan').value='';
	document.getElementById('jenispengadilan').value='';
	document.getElementById('penggugat').value='';
	document.getElementById('tergugat').value='';	
	document.getElementById('jenisperkara').value='';
	document.getElementById('advokat').value='';
	document.getElementById('tanggalterdaftar').value='';
	document.getElementById('ringkasan').value='';
	document.getElementById('tanggalputusan').value='';
	document.getElementById('amarputusan').value='';
	document.getElementById('listfiles').innerHTML='';
    document.getElementById('method').value='insert';
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}

function loaddata(page){
    divsch=document.getElementById('divsch').value;
    jenissch=document.getElementById('jenissch').value;
    unitsch=document.getElementById('unitsch').value;
    nohaksch=document.getElementById('nohaksch').value;
	param = 'method=loaddata&page=' + page;
    if (divsch != '') {
        param += '&divsch=' + divsch;
    }
	if (jenissch != '') {
        param += '&jenissch=' + jenissch;
    }
	if (unitsch != '') {
        param += '&unitsch=' + unitsch;
    }
	if (nohaksch != '') {
        param += '&nohaksch=' + nohaksch;
    }
	
    tujuan = 'lgl_slave_litigasi.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else {
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function form(){
    width = '';
    height = '';
    content = "<fieldset style=\"width:97%;\"><div id=contview style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "View";
    showDialog5(title, content, width, height, ev);
}

function viewfile(ev,namafile){
	ext=namafile.split(".");
	if(trim(ext[1]) == 'jpg' || trim(ext[1]) == 'jpeg' || trim(ext[1]) == 'png'){
		form();
		param = 'method=viewfile&namafile='+namafile;
		tujuan = 'lgl_slave_litigasi.php';
		post_response_text(tujuan, param, respog);
	}else{
		alert('File tidak dapat di tampilkan, silahkan download untuk melihat isi file.'); return;	
	}
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('contview').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function showformupload(ev){
	title="UPLOAD FILES";
	width='';
	height='';
    content="<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
    showDialog2(title,content,width,height,ev);	
	
	pos = new Array();
	pos = getMouseP(ev);
	
	document.getElementById('dynamic2').style.top = pos[1]+'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 500) +'px';
	document.getElementById('dynamic2').style.display='';
}

function submitfile(){
	var file = document.getElementById("upload").files[0];
	var	pt = document.getElementById('pt').value;
	var	notransaksi = document.getElementById('notransaksi').value;
	var	unit = document.getElementById('unit').value;
	var formdata = new FormData();
		formdata.append("fileupload", getValue('upload'));
		formdata.append("file", file);
		formdata.append("pt", pt);
		formdata.append("notransaksi", notransaksi);
		formdata.append("unit", unit);
	
	if(getValue('upload')==""){
		alert("warning : Upload file has been empty.");
		return false;
	}
	if(pt=="" || unit=="" || notransaksi==""){
		alert("warning : Silahkan isikan detail sertipikat terlebih dahulu !");
		return false;
	}
	
	var con = createXMLHttpRequest();
	con.open("POST", "lgl_slave_litigasi.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
    
    function respon() {
        if (con.readyState == 4) {
			if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					//=== Success Response
                    alert('Uploaded Success.');
					document.getElementById("upload").value = "";
                    loadfiles(notransaksi);
                }
            } else {
				busy_off();
                error_catch(con.status);
            }
        }
    }
}

function viewlistfile(notransaksi){
	width = '';
    height = '';
    content = "<fieldset style=\"width:97%;\"><div id=contviewz style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "View";
    showDialog4(title, content, width, height, ev);

	param='method=viewlistfile&notransaksi='+notransaksi;
	tujuan='lgl_slave_litigasi.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
					document.getElementById('contviewz').innerHTML=con.responseText;
					 loadfiles(notransaksi);
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function loadfiles(notransaksi){
	param='method=loadfiles&notransaksi='+notransaksi;
	tujuan='lgl_slave_litigasi.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
					if(document.getElementById('listfiles')!==null){
						document.getElementById('listfiles').innerHTML=con.responseText;
					}
					if(document.getElementById('loadfilesdetail')!==null){
						document.getElementById('loadfilesdetail').innerHTML=con.responseText;
					}
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function deletefile(notransaksi,namafile){
	param  = "method=deletefile";
	param += "&notransaksi="+notransaksi;
	param += "&namafile="+namafile;
	
	tujuan='lgl_slave_litigasi.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
                    loadfiles(notransaksi);
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}
