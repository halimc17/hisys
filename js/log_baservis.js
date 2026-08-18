function posting(id){
    param='proses=posting'+'&id='+id;
    tujuan='log_slave_baservis.php';
    if(confirm('Anda yakin ingin memposting ??'))
    {
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
                                    else 
                                    {
                                        loadData();
                                    }
                            }
                            else {
                                    busy_off();
                                    error_catch(con.status);
                            }
              }	
    }
}

function loadData(page){
    ntrs=document.getElementById('txtsearch').value;
    tglcr=document.getElementById('tgl_cari').value;
    param='proses=loadData'+'&page='+page;
    if(ntrs!=''){
        param+='&noinvoice='+ntrs;
    }
    if(tglcr!=''){
        param+='&tanggalCr='+tglcr;
    }
    tujuan='log_slave_baservis.php';
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
                        document.getElementById('formInput').style.display='none';
                        document.getElementById('listData').style.display='block';
                        document.getElementById('continerlist').innerHTML=isdt[0];
                        document.getElementById('footData').innerHTML=isdt[1];
                        clearData();
                        // closeDialog();
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
document.getElementById('formInput').style.display='none';
document.getElementById('listData').style.display='block';
clearData();
}

function clearData(){
document.getElementById('noso').value='';
document.getElementById('noso').disabled=false;
document.getElementById('noba').value='';
document.getElementById('noba').disabled=false;
document.getElementById('keterangan').value='';
document.getElementById('keterangan').disabled=false;
document.getElementById('tanggal').disabled=false;

}

function searchKontrak(title,status,content,ev){
	width='600';
	height='520';
	showDialog1(title,content,width,height,ev);
    // getFormNosibp(status);
    getFormNoso(status);
}

function getFormNoso(status){
        if (status == 'noso'){
            pros = "getFormNoso";
        }else{
            pros = "getFormNoba";
        }

        param='status='+status+'&proses='+pros;
        tujuan='log_slave_baservis.php';
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

function findNoso(status){
	txt=trim(document.getElementById('nosocr').value);
	param='txtfind='+txt+'&status='+status+'&proses=getnoso';
        tujuan='log_slave_baservis.php';
        if(txt==''){
            alert("Noso is obligatory");
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

function findNoba(status){
    txt=trim(document.getElementById('nosocr').value);
    param='txtfind='+txt+'&status='+status+'&proses=getnoba';
        tujuan='log_slave_baservis.php';
        if(txt==''){
            alert("Noso is obligatory");
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

function setData(nopo){
    document.getElementById('noso').value = nopo;
	
		
		param='proses=gettermin'+'&nopo='+nopo;
		tujuan='log_slave_baservis.php';
		
		function respog()
		{
			if(con.readyState==4)
			{
				if (con.status == 200){
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else {
						document.getElementById('termin').value=con.responseText;
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	post_response_text(tujuan, param, respog);
    closeDialog();
}

function setDataba(noba){
    document.getElementById('noba').value = noba;

        param='proses=getQty'+'&nokontrak='+noba;
        tujuan='log_slave_baservis.php';
        
        function respog()
        {
            if(con.readyState==4)
            {
                if (con.status == 200){
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    }
                    else {
                       document.getElementById('qty').value=con.responseText;
                    }
                }
                else {
                    busy_off();
                    error_catch(con.status);
                }
            }
        }
    
    post_response_text(tujuan, param, respog);
    closeDialog();
}

function simpan(){
    proses = trim(document.getElementById('proses').value);
    noso = trim(document.getElementById('noso').value);
    tanggal = trim(document.getElementById('tanggal').value);
    noba = trim(document.getElementById('noba').value);
    keterangan = trim(document.getElementById('keterangan').value);
    id = trim(document.getElementById('id').value);

    param='proses='+proses+'&noso='+noso+'&tanggal='+tanggal+'&noba='+noba+'&keterangan='+keterangan+'&id='+id;

    tujuan='log_slave_baservis.php';
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
                    loadData();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }   
}

function fillField(id,noso,tanggal,noba,keterangan){
   
		document.getElementById('formInput').style.display='block';
        document.getElementById('listData').style.display='none';
        document.getElementById('id').value=id;
        document.getElementById('noso').value=noso;
        document.getElementById('tanggal').value=tanggal;
        document.getElementById('noba').value=noba;
        document.getElementById('keterangan').value=keterangan;
        document.getElementById('proses').value='update';
        document.getElementById('tanggal').disabled=true;
   
}

function delData(id){
        param='id='+id+'&proses=delData';
        tujuan='log_slave_baservis.php';  
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

function cariData(pg){
    
    nokontrak=document.getElementById('txtsearchkontrak').value;
    
    ntrs=document.getElementById('txtsearch').value;
    tglcr=document.getElementById('tgl_cari').value;
    param='proses=loadData'+'&page='+pg;
    if(ntrs!=''){
        param+='&nodo='+ntrs;
    }
    if(tglcr!=''){
        param+='&tanggalCr='+tglcr;
    }
    if(nokontrak!=''){
        param+='&nokontrak='+nokontrak;
    }
  
    tujuan='log_slave_baservis.php';
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
                        document.getElementById('formInput').style.display='none';
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

function showformupload(ev) {
    title = "UPLOAD FILES";
    width = '';
    height = '';
    content = "<fieldset style=width:96%><legend>Form</legend><div id=contUpload style='overflow:auto;min-width:350px;height:auto;' ></div></fieldset>";
    showDialog2(title, content, width, height, ev);
    pos = new Array();
    pos = getMouseP(ev);
    document.getElementById('dynamic2').style.top = pos[1] + 'px';
    document.getElementById('dynamic2').style.left = (pos[0] - 500) + 'px';
    document.getElementById('dynamic2').style.display = '';
}

function showupload(ev, noso) {
    showformupload(ev);
    param = "";
    param += "noso=" + noso;
    param += '&proses=showupload';
    tujuan = 'log_slave_baservis.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contUpload').innerHTML = con.responseText;
                    loadfiles(noso);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function submitfile() {
    var file = document.getElementById("upload").files[0];
    var noso = document.getElementById('noupload').innerHTML;
    var formdata = new FormData();
    formdata.append("noso", noso);
    formdata.append("proses","submitfile");
    formdata.append("file", file);
    formdata.append("fileupload", getValue('upload'));
    if (getValue('upload') == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }
    var con = createXMLHttpRequest();
    con.open("POST", "log_slave_baservis.php", true);

    busy_on();
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
                    loadfiles(noso);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



function loadfiles(noso) {
    param = 'proses=loadfiles&noso=' + noso;
    tujuan = 'log_slave_baservis.php';
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
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefile(id, noso) {
    param = "proses=deletefile";
    param += "&id=" + id;
    param += "&noso=" + noso;
    tujuan = 'log_slave_baservis.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadfiles(noso);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}