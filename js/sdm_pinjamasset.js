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
    cancel();  
}

function viewexcel(kary,tipe){
	ev = 'event';
	param = 'method=html' + '&kary=' + kary + '&tipe=' + tipe;
	tujuan = 'sdm_slave_pinjamasset.php' + "?" + param;
	width = '';
	height = '';
	title = "Excel";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	showDialog1(title, content, width, height, ev);
}

function html(kary,tipe){
    form();
    param = 'method=html' + '&kary=' + kary + '&tipe=' + tipe;
    tujuan = 'sdm_slave_pinjamasset.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('contview').innerHTML = con.responseText;
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
    document.getElementById('karysch').value='';
    document.getElementById('listData').style.display = 'block';
    document.getElementById('header').style.display = 'none';
    document.getElementById('detail').style.display = 'none';
    loaddata(0);
}

function edit(kary,kodeorg,nmkary){
    document.getElementById('kary').value=kary;
    document.getElementById('kodeorg').value=kodeorg;
	document.getElementById('kary').innerHTML="<option value='"+ kary +"'>"+ nmkary +"</option>"
	document.getElementById('kary').disabled=true;
	document.getElementById('kodeorg').disabled=true;
    document.getElementById('listData').style.display='none';
    document.getElementById('header').style.display='block';
    detail(kary,kodeorg);
}

function editdetail(kodeasset,kary,tglpinjam,keterangan,pemilik,posisi,thnperoleh){
	document.getElementById('kodeasset').value=kodeasset;
	document.getElementById('kodeasset').disabled=true;
	document.getElementById('kary').value=kary;
	document.getElementById('kary').disabled=true;
	document.getElementById('tglpinjam').value=tglpinjam;
	document.getElementById('tglpinjamlama').value=tglpinjam;
	document.getElementById('keterangan').value=keterangan;
	document.getElementById('pemilik').value=pemilik;
	document.getElementById('posisi').value=posisi;
	document.getElementById('tahun').value=thnperoleh;
	document.getElementById('showall').checked=true;
	document.getElementById('showall').disabled=true;
	document.getElementById('method').value='update';
}

function deletedetail(kodeasset,kary,tglpinjam){
    param='method=deletedetail'+'&kodeasset='+kodeasset+'&kary='+kary+'&tglpinjam='+tglpinjam;

    tujuan='sdm_slave_pinjamasset.php';
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
			   loaddatadetail(kary);
			   document.getElementById('formloadfilesdetail').style.display = 'none';
			}
		}else {
				busy_off();
				error_catch(con.status);
		}
	  }	
    }
}

function del(kary,kodeorg){
    param='method=delete'+'&kodeorg='+kodeorg+'&kary='+kary;
    tujuan='sdm_slave_pinjamasset.php';
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


function detail(){
    kodeorg=document.getElementById('kodeorg').value;
    kary=document.getElementById('kary').value;
	
	if(kodeorg==''||kary==''){
        alert('Lengkapi Pengisian');
        return;
    }
    param = 'method=detail';
    param += '&kodeorg=' + kodeorg+'&kary=' + kary;
    tujuan = 'sdm_slave_pinjamasset.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else {
                    document.getElementById('detail').style.display = 'block';
                    document.getElementById('detail').innerHTML = con.responseText;
                    loaddatadetail(kary);
                }
            }else{
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
    loaddata(paged);	
}



function loaddata(page){
    divsch=document.getElementById('divsch').value;
    karysch=document.getElementById('karysch').value;
	param = 'method=loaddata&page=' + page;
    if (divsch != '') {
        param += '&divsch=' + divsch;
    }
    if (karysch != '') {
        param += '&karysch=' + karysch;
    }
 
    tujuan = 'sdm_slave_pinjamasset.php';
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


function cancel(){
	// var today = new Date();
	// var dd = today.getDate();
	// var mm = today.getMonth()+1; //January is 0!
	// var yyyy = today.getFullYear();
	// if(dd<10) {
		// dd = '0'+dd
	// } 
	// if(mm<10) {
		// mm = '0'+mm
	// } 
	// today = dd + '-' + mm + '-' + yyyy;
	
    document.getElementById('detail').style.display = 'none';
    document.getElementById('tomboldetail').disabled=false;
	document.getElementById('kodeorg').disabled=false;
	document.getElementById('kary').disabled=false;
    document.getElementById('kodeorg').value='';
    document.getElementById('kary').value='';
}



function loaddatadetail(kary){
    document.getElementById('tomboldetail').disabled=true;
    document.getElementById('kodeorg').disabled=true;
    document.getElementById('kary').disabled=true;
    kodeorg=document.getElementById('kodeorg').value;
    kary=document.getElementById('kary').value;
   
    param = 'method=loaddatadetail';
    param += '&kodeorg=' + kodeorg+'&kary=' + kary;
    tujuan = 'sdm_slave_pinjamasset.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else {
                    document.getElementById('loaddatadetail').innerHTML = con.responseText;
					//loadfiles(kary);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function showall(){
	kodeorg=document.getElementById('kodeorg').value;
	show=document.getElementById('showall');
    if(show.checked==true){
		show=1;
	} else{
		show=0;
	}
    param='method=showall'+'&kodeorg='+kodeorg+'&show='+show;
    tujuan='sdm_slave_pinjamasset.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
                }else {
                    document.getElementById('kodeasset').innerHTML = con.responseText;
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
	}  	
}

function getdata(){
    kodeasset=document.getElementById('kodeasset').value;
    param='method=getdata'+'&kodeasset='+kodeasset;
    tujuan='sdm_slave_pinjamasset.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
					isdt = con.responseText.split("######");
                    document.getElementById('pemilik').value = isdt[0];
                    document.getElementById('posisi').value = isdt[1];
                    document.getElementById('tahun').value = isdt[2];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function savedetail(){
    kodeorg=document.getElementById('kodeorg').value;
    kary=document.getElementById('kary').value;
    kodeasset=document.getElementById('kodeasset').value;
    tglpinjam=document.getElementById('tglpinjam').value;
    tglpinjamlama=document.getElementById('tglpinjamlama').value;
    keterangan=document.getElementById('keterangan').value;
    method=document.getElementById('method').value;
    
    if((kodeorg=='' || kary==''|| kodeasset==''|| tglpinjam=='')){
        alert('Lengkapi Pengisian.');
        return;
    }
    
    param='kodeorg='+kodeorg+'&kary='+kary+'&kodeasset='+kodeasset+'&tglpinjamlama='+tglpinjamlama;
    param+='&tglpinjam='+tglpinjam+'&keterangan='+keterangan;
    param+='&method='+method;
    
    tujuan='sdm_slave_pinjamasset.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    cleardetail();
                    loaddatadetail(kary);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cleardetail(){
    document.getElementById('kodeasset').value='';
    document.getElementById('kodeasset').disabled=false;
    document.getElementById('pemilik').value='';
    document.getElementById('posisi').value='';
    document.getElementById('tahun').value='';
    document.getElementById('tglpinjam').value='';
    document.getElementById('keterangan').value='';
	document.getElementById('showall').checked=false;
	document.getElementById('showall').disabled=false;
	showall();
}

function getkaryawan(){
	kodeorg= document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	param='kodeorg='+kodeorg+'&method=getkaryawan';
	
	tujuan='sdm_slave_pinjamasset.php';  
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('kary').innerHTML=trim(con.responseText);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}

function form(){
    width = '';
    height = '';
    content = "<fieldset style=\"width:98.5%;\"><div id=contview style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "";
    showDialog4(title, content, width, height, ev);
	
	var dialog = document.getElementById('dynamic4');
	dialog.style.top = '40%';
	
}

function kembali(kary,kodeasset,tglpinjam){
	form();
	param='method=kembali&kary='+kary+'&kodeasset='+kodeasset+'&tglpinjam='+tglpinjam;
	tujuan='sdm_slave_pinjamasset.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
                    document.getElementById('contview').innerHTML=con.responseText;
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function savekembali(row){
    kary=document.getElementById('nkary').value;
    kodeasset=document.getElementById('kodeasset'+row).value;
    tglpinjam=document.getElementById('tglpinjam'+row).value;
    tglkembali=document.getElementById('tglkembali'+row).value;
    penerima=document.getElementById('penerima'+row).value;
    ketkembali=document.getElementById('ketkembali'+row).value;
    
    if(tglkembali=='' || penerima==''){
        alert('Lengkapi Pengisian.');
        return;
    }
	
	param='method=savekembali&kary='+kary+'&kodeasset='+kodeasset+'&tglpinjam='+tglpinjam+'&tglkembali='+tglkembali+'&penerima='+penerima+'&ketkembali='+ketkembali;
	tujuan='sdm_slave_pinjamasset.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
					document.getElementById('tglkembali' + row).disabled = true;
					document.getElementById('penerima' + row).disabled = true;
					document.getElementById('ketkembali' + row).disabled = true;
					document.getElementById('row_' + row).cells[11].innerHTML = '';
					loaddatadetail(kary);
					
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}



function viewfile(ev,namafile){
	ext=namafile.split(".");
	if(trim(ext[1]) == 'jpg' || trim(ext[1]) == 'jpeg' || trim(ext[1]) == 'png'){
		form();
		param = 'method=viewfile&namafile='+namafile;
		tujuan = 'sdm_slave_pinjamasset.php';
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
    content="<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;width:auto;height:auto;' ></div></fieldset>";
    showDialog2(title,content,width,height,ev);	
	
	pos = new Array();
	pos = getMouseP(ev);
	
	document.getElementById('dynamic2').style.top = pos[1]+'px';
	document.getElementById('dynamic2').style.left = (pos[0] - 300) +'px';
	document.getElementById('dynamic2').style.display='';
}


function showupload(ev){
	kary = document.getElementById('kary').value;
	kodeasset = document.getElementById('kodeasset').value;
	tglpinjam = document.getElementById('tglpinjam').value;
	if(kary==''||kodeasset==''||tglpinjam==''){
		alert('Isikan terlebih dahulu Nama Karyawan, Nama Barang dan Tanggal Pinjam.'); return;
	}
	showformupload(ev);
	param='method=showupload&kary='+kary+'&kodeasset='+kodeasset+'&tglpinjam='+tglpinjam;
	tujuan='sdm_slave_pinjamasset.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
                    document.getElementById('contUpload').innerHTML=con.responseText;
					loadfiles(kary,kodeasset,tglpinjam);
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}	
}

function submitfile(){
	var	kary = document.getElementById('karyupload').innerHTML;
	var	kodeasset = document.getElementById('kodeassetupload').innerHTML;
	var	tglpinjam = document.getElementById('tglpinjamupload').innerHTML;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("kary", kary);
	formdata.append("kodeasset", kodeasset);
	formdata.append("tglpinjam", tglpinjam);
	
	if(getValue('upload')==""){
		alert("warning : Upload file has been empty.");
		return false;
	}
	savedetail();
	var con = createXMLHttpRequest();
	con.open("POST", "sdm_slave_pinjamasset.php?method=submitfile", true);
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
                    loadfiles(kary,kodeasset,tglpinjam);
                }
            } else {
				busy_off();
                error_catch(con.status);
            }
        }
    }
}

function formloadfilesdetail(kary,kodeasset,tglpinjam){
	document.getElementById('formloadfilesdetail').style.display = 'block';
	loadfiles(kary,kodeasset,tglpinjam);
}

function loadfiles(kary,kodeasset,tglpinjam){
	param='method=loadfiles&kary='+kary+'&kodeasset='+kodeasset+'&tglpinjam='+tglpinjam;
	tujuan='sdm_slave_pinjamasset.php';
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
					loaddatadetail(kary);
				}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}

function deletefile(notransaksi,namafile)
{
	param='method=deletefile&notransaksi='+notransaksi+'&namafile='+namafile;
	tujuan='sdm_slave_pinjamasset.php';
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
                    loadfiles(notransaksi);
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

function deletefileall(notransaksi){
	param='method=deletefileall&notransaksi='+notransaksi;
	tujuan='sdm_slave_pinjamasset.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else{
                    alert('File sudah di hapus');
					loaddata();	
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
}