function caripenerima(title,ev)
{
    content= "<div>";
    content+="<fieldset>Search : <input type=text id=txtnama class=myinputtext onkeypress=\"return tanpa_kutip(event);\" maxlength=25><button class=mybutton onclick=gocaripenerima()>Go</button><p>";
    content+="<div id=containercari style=\"max-height:250px;min-height:auto;overflow:auto;\"></div></fieldset></div>";
    
    width='';
    height='';
    showDialog1(title,content,width,height,ev); 
}

function gocaripenerima()
{
    textnama=document.getElementById('txtnama').value;
    kodeorg=document.getElementById('kodeorg').value;
    
    /*if(txtnama.length <= 2)
    {
        alert("No. nama too short text. Min 3 Char.");
        return;
    }*/
    
    param='method=gocaripenerima'+'&txtnama='+textnama+'&kodeorg='+kodeorg;
    //alert(param);
    tujuan='lgl_slave_pengajuanfee.php';
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
                    document.getElementById('containercari').innerHTML=con.responseText;
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

function fillpenerima(textnama)
{
    document.getElementById('penerima').value=textnama;
    closeDialog();
}

function getpenerima(tipe) {
        tipe=document.getElementById('tipe').value;
       
        if (tipe==1) {
          document.getElementById('penerima').disabled=true;
          document.getElementById('cari').hidden=false;
          document.getElementById('penerima').value='';

     
                    }
       else
           {
          document.getElementById('penerima').disabled=false;
           document.getElementById('cari').hidden=true;
           document.getElementById('penerima').value='';
        }

        }

function add_new_data(){
    document.getElementById('header').style.display = 'block';
    document.getElementById('listData').style.display = 'none';
    cancel(); 
}

function form_ajukan(notransaksi,kodeorg,numrow){
	width = '300';
    height = '';
    content = "<fieldset><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:100px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "";
    showDialog1(title, content, width, height, ev);
	
	param = 'method=form_ajukan' + '&notransaksi=' + notransaksi+ '&kodeorg=' + kodeorg+ '&numrow=' + numrow;
    tujuan = 'lgl_slave_pengajuanfee.php';
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

function ajukan(){
	kepada=document.getElementById('kepada').value;
    notransaksi=document.getElementById('notran_aju').innerHTML;
    numrow=document.getElementById('numrow').value;
	param = 'method=ajukan' + '&notransaksi=' + notransaksi+ '&kepada=' + kepada;
    
	if(kepada==''){
		alert('Isikan nama penyetuju.');
		return;
	}
	tujuan = 'lgl_slave_pengajuanfee.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					x = document.getElementById('tr_' + numrow);
					x.cells[9].innerHTML = '';
					x.cells[10].innerHTML = '';
					x.cells[11].innerHTML = '';
					alert('Sucses');
					closeDialog();
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
    content = "<fieldset><div id=containerd style=\"width:100%;max-height:700px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "Preview";
    showDialog1(title, content, width, height, ev); 
}

function previewexcel(notransaksi,kodeorg, periode,tipe){
	param = 'method=html' + '&kodeorg=' + kodeorg + '&periode=' + periode+ '&notransaksi=' + notransaksi+ '&tipe=' + tipe;
	tujuan = 'lgl_slave_pengajuanfee.php' + "?" + param;
	width = '';
	height = '';
	ev = 'event';
	title = "Preview";
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	printFile(param,tujuan,title,ev);
	//showDialog1(title, content, width, height, ev);
	var dialog = document.getElementById('dynamic1');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

function html(notransaksi,kodeorg, periode,tipe){
    width = ''; height = '';
    content = "<div id=containerd style=\"width:100%;max-height:700px;overflow:auto;\"></div>";
    ev = 'event';
    title = "Preview";
    showDialog4(title, content, width, height, ev); 
	
    param = 'method=html' + '&kodeorg=' + kodeorg + '&periode=' + periode+ '&notransaksi=' + notransaksi+ '&tipe=' + tipe;
    tujuan = 'lgl_slave_pengajuanfee.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('containerd').innerHTML = con.responseText;
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
    document.getElementById('periodesch').value='';
    document.getElementById('listData').style.display = 'block';
    document.getElementById('header').style.display = 'none';
    document.getElementById('detail').style.display = 'none';
    loaddata(0);
}

function edit(kodeorg,tanggal,notransaksi,instansi,tipe,penerima,keterangan,uraian,bank,rekening){
    document.getElementById('notransaksi').value=notransaksi;
    document.getElementById('kodeorg').value=kodeorg;
    document.getElementById('tanggal').value=tanggal;
    document.getElementById('instansi').value=instansi;
    document.getElementById('tipe').value=tipe;
   /* if (tipe) {
                document.getElementById('penerima1').value=penerima;
    }
    else
        {
                 document.getElementById('penerima0').value=penerima;
        };*/
    
    document.getElementById('penerima').value=penerima;
    //document.getElementById('penerima1').value=penerima1;
    document.getElementById('keterangan').value=keterangan;
    document.getElementById('uraian').value=uraian;
    document.getElementById('rekening').value=rekening;
    document.getElementById('bank').value=bank;
	
    document.getElementById('listData').style.display='none';
    document.getElementById('header').style.display='block';
    detail(notransaksi,kodeorg,tanggal);
}

function deletedetail(notransaksi,kodeorg,instansi,keterangan,deskripsi,tanggal){
    param='method=deletedetail'+'&notransaksi='+notransaksi+'&kodeorg='+kodeorg+'&instansi='+instansi+'&keterangan='+keterangan+'&deskripsi='+deskripsi+'&tanggal='+tanggal;
 
    tujuan='lgl_slave_pengajuanfee.php';
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
				   loaddatadetail(notransaksi);
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}	
    }
}

function del(kodeorg,tanggal,notransaksi){
    param='method=delete'+'&kodeorg='+kodeorg+'&tanggal='+tanggal+'&notransaksi='+notransaksi;
    tujuan='lgl_slave_pengajuanfee.php';
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
				   document.getElementById('contain').innerHTML=con.responseText;
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
	
    notransaksi=document.getElementById('notransaksi').value;
    kodeorg=document.getElementById('kodeorg').value;
    tanggal=document.getElementById('tanggal').value;
    keterangan=document.getElementById('keterangan').value;
    uraian=document.getElementById('uraian').value;
    instansi=document.getElementById('instansi').value;
    tipe=document.getElementById('tipe').value;
/*
     if (tipe) {
                penerima=document.getElementById('penerima0').value;
                }
    else
        {
                 penerima=document.getElementById('penerima1').value;
        };*/

    penerima=document.getElementById('penerima').value;
    //penerima=document.getElementById('penerima1').value;
    bank=document.getElementById('bank').value;
    rekening=document.getElementById('rekening').value;
    
	if(notransaksi==''||kodeorg==''||tanggal==''||instansi==''||keterangan==''){
        alert('Lengkapi Pengisian');
        return;
    }
	
	if((bank!=''&& rekening=='') || (bank==''&& rekening!='')){
        alert('Bank dan Nomor Rekening wajib terisi keduanya !');
        return;
    }
	
	document.getElementById('kodeorg').disabled=true;
    document.getElementById('tanggal').disabled=true;
    document.getElementById('instansi').disabled=true;
	
    param  = 'method=detail';
    param += '&tanggal=' + tanggal+'&kodeorg=' + kodeorg+'&notransaksi=' + notransaksi;
    param += '&keterangan=' + keterangan+'&uraian=' + uraian+'&instansi=' + instansi+'&tipe=' + tipe;
    param += '&penerima=' + penerima;
    param += '&bank=' + bank;
    param += '&rekening=' + rekening;
    tujuan = 'lgl_slave_pengajuanfee.php';
    //  alert(penerima1);
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
                    loaddatadetail(notransaksi);
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
    periodesch=document.getElementById('periodesch').value;
	param = 'method=loaddata&page=' + page;
    if (divsch != '') {
        param += '&divsch=' + divsch;
    }
    if (periodesch != '') {
        param += '&periodesch=' + periodesch;
    }
 
    tujuan = 'lgl_slave_pengajuanfee.php';
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


function cancel(){
    document.getElementById('detail').style.display = 'none';
    document.getElementById('tomboldetail').disabled=false;
	document.getElementById('notransaksi').value='';
    document.getElementById('kodeorg').value='';
    document.getElementById('kodeorg').disabled=false;
    document.getElementById('tanggal').value='';
    document.getElementById('tanggal').disabled=false;
    document.getElementById('keterangan').value='';
    document.getElementById('instansi').value='';
    document.getElementById('instansi').disabled=false;
    document.getElementById('tipe').value='';
    document.getElementById('penerima').value='';
    document.getElementById('bank').value='';
    document.getElementById('rekening').value='';
}

function loaddatadetail(notransaksi){
    tanggal=document.getElementById('tanggal').value;
    kodeorg=document.getElementById('kodeorg').value;
    notransaksi=document.getElementById('notransaksi').value;

    param = 'method=loaddatadetail';
    param += '&kodeorg=' + kodeorg+'&tanggal=' + tanggal+'&notransaksi=' + notransaksi;
    tujuan = 'lgl_slave_pengajuanfee.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else {
                    document.getElementById('loaddatadetail').innerHTML = con.responseText;
					loadfiles(notransaksi);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function numberFormat(number,digit) {
      number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
      var components = (parseFloat(number).toFixed(digit)).split(".");
      components [0] = components [0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      return components.join(".");
}

function savedetail(){
	notransaksi=document.getElementById('notransaksi').value;
    kodeorg=document.getElementById('kodeorg').value;
    tanggal=document.getElementById('tanggal').value;
    keterangan=document.getElementById('keterangan').value;
    uraian=document.getElementById('uraian').value;
    instansi=document.getElementById('instansi').value;
    tipe=document.getElementById('tipe').value;
    penerima=document.getElementById('penerima').value;
  
    bank=document.getElementById('bank').value;
    rekening=document.getElementById('rekening').value;
    deskripsi=document.getElementById('deskripsi').value;
    rupiah=document.getElementById('rupiah').value;
    noakun=document.getElementById('noakun').value;
    method=document.getElementById('method').value;

    if(deskripsi=='' || rupiah==''){
        alert('Lengkapi Pengisian.');
        return;
    }
    
	param='kodeorg='+kodeorg;
    param+='&tanggal='+tanggal;
	param+='&notransaksi='+notransaksi;
    param+='&keterangan='+keterangan;
	param+='&uraian='+uraian;
	param+='&instansi='+instansi;
	param+='&tipe='+tipe;
	param+='&penerima='+penerima;
	param+='&bank='+bank;
	param+='&deskripsi='+deskripsi;
	param+='&rupiah='+rupiah;
	param+='&rekening='+rekening;
    param+='&noakun='+noakun;
    param+='&method='+method;
   
    
    tujuan='lgl_slave_pengajuanfee.php';
    
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //cleardetail();
                    loaddatadetail(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cleardetail(){
    document.getElementById('deskripsi').value='';
    document.getElementById('rupiah').value='';
    document.getElementById('method').value='insert';
}

function getnotransaksi(){
	kodeorg= document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
	tanggal=document.getElementById('tanggal').value;
	document.getElementById('notransaksi').value='';
	param='tanggal='+tanggal+'&kodeorg='+kodeorg+'&method=getnotransaksi';
	
	tujuan='lgl_slave_pengajuanfee.php';  
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
					document.getElementById('notransaksi').value=trim(con.responseText);
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
		
}

function submitfile(){
	var file = document.getElementById("upload").files[0];
	var	notransaksi = document.getElementById('notransaksi').value;
	var formdata = new FormData();
		formdata.append("fileupload", getValue('upload'));
		formdata.append("file", file);
		formdata.append("notransaksi", notransaksi);
	
	if(getValue('upload')==""){
		alert("warning : Upload file has been empty.");
		return false;
	}
	if(notransaksi==""){
		alert("warning : Silahkan isikan detail terlebih dahulu !");
		return false;
	}
	
	var con = createXMLHttpRequest();
	con.open("POST", "lgl_slave_pengajuanfee.php?method=submitfile", true);
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


function loadfiles(notransaksi){
	param='method=loadfiles&notransaksi='+notransaksi;
	tujuan='lgl_slave_pengajuanfee.php';
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
	
	tujuan='lgl_slave_pengajuanfee.php';
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

function viewfile(ev,namafile){
	ext=namafile.split(".");
	if(trim(ext[1]) == 'jpg' || trim(ext[1]) == 'jpeg' || trim(ext[1]) == 'png'){
		width = ''; height = '';
		content = "<fieldset style=\"width:97%;\"><div id=contviewx style=\"width:100%;height:100%;overflow:auto;\"></div></fieldset>";
		ev = 'event';
		title = "View";
		showDialog2(title, content, width, height, ev);
		
		param = 'method=viewfile&namafile='+namafile;
		tujuan = 'lgl_slave_pengajuanfee.php';
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
					document.getElementById('contviewx').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}