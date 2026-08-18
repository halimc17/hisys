function checkChkTipe(){
	chkDasar = document.getElementById('chkDasar').checked;
	chkLain = document.getElementById('chkLain').checked;
	
	if(chkDasar == false){
		document.getElementById('kdkegiatan_find').style.display = '';
		document.getElementById('kdorg_find').style.display = '';
		document.getElementById('tipedokumen_find').style.display = '';
	}else{
		document.getElementById('kdkegiatan_find').style.display = 'none';
		document.getElementById('kdorg_find').style.display = 'none';
		document.getElementById('tipedokumen_find').style.display = 'none';
	}
	
	kdkegiatan = document.getElementById('kdkegiatan').value;
	kdorg = document.getElementById('kdorg').value;
	tipedokumen = document.getElementById('tipedokumen').value;
	param = "chkDasar="+chkDasar+"&kdkegiatan="+kdkegiatan+"&kdorg="+kdorg+"&tipedokumen="+tipedokumen;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					valSplit = con.responseText.split("####");
                    document.getElementById('tipedokumen').innerHTML = valSplit[0];
                    document.getElementById('kdorg').innerHTML = valSplit[1];
                    document.getElementById('kdkegiatan').innerHTML = valSplit[2];
                    document.getElementById('tipepeta').innerHTML = valSplit[3];
					getNoDokumen();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('bi_slave_5upload.php?proses=checkChkTipe', param, respon);
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}

function loaddata(page){
	
	param = "page="+page;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					valSplit = con.responseText.split("####");
					document.getElementById('container').innerHTML = valSplit[0];
					document.getElementById('footData').innerHTML = valSplit[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('bi_slave_5upload.php?proses=loaddata', param, respon);
}

function simpan(){
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", document.getElementById('upload').value);
	formdata.append("provinsi", document.getElementById('provinsi').value);
	formdata.append("kdkegiatan", document.getElementById('kdkegiatan').value);
	formdata.append("kdorg", document.getElementById('kdorg').value);
	formdata.append("tipedokumen", document.getElementById('tipedokumen').value);
	formdata.append("tanggal", document.getElementById('tanggal').value);
	formdata.append("keterangan", document.getElementById('keterangan').value);
	formdata.append("tipepeta", document.getElementById('tipepeta').value);
	formdata.append("chkDasar", document.getElementById('chkDasar').checked);
	
	var con = createXMLHttpRequest();
	con.open("POST", "bi_slave_5upload.php?proses=simpan", true);
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
					loaddata(0);
					// alert(con.responseText);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getNoDokumen(){
	tipedokumen = document.getElementById('tipedokumen').value;
	// document.getElementById('nodokumen').value = '';
	if(tipedokumen == ''){
		document.getElementById('trnodokumen').style.display = 'none';
	}else{
		document.getElementById('trnodokumen').style.display = '';
		getdivnodokumen();
	}
}

function batal(){
	document.getElementById('id').value = "";
	document.getElementById('provinsi').selectedIndex = 0;
	document.getElementById('chkDasar').checked = true;
	checkChkTipe();
	document.getElementById('tanggal').value = "";
	document.getElementById('container').innerHTML = "";
	document.getElementById("upload").value = "";
	document.getElementById("keterangan").value = "";
	document.getElementById("nodokumen").value = "";
	document.getElementById("method").value = "insert";
}

function addnodok(no){
	nodokumen = document.getElementById('nodokumen_'+no).value;
	if(nodokumen==''){
		alert("No. Dokumen harus diisi");
		return;
	}
	getdivnodokumen(no);
}

function getdivnodokumen(no){
	if(no === undefined){
		nodok = '';
		nourut = '';
	}else{
		nodok = document.getElementById('nodokumen_'+no).value;
		nourut = no;
	}
	
	id = document.getElementById('id').value;
	param = "id="+id+'&nodok='+nodok+'&nourut='+nourut;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('divnodokumen').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('bi_slave_5upload.php?proses=getdivnodokumen', param, respon);
}

function delnodok(no){
	param = 'nourut='+no;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					getdivnodokumen();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('bi_slave_5upload.php?proses=delnodok', param, respon);
}

function showsvg(idsvg,ev){
	showDetail(idsvg,ev);
    idsvg = idsvg;
	param='idsvg='+idsvg+'&proses=showsvg';
	tujuan='bi_slave_5upload.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('svgimg').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function showDetail(nosvg,ev){
	title = nosvg;
	content = "<div id='svgimg' style='width:780px;height:380px;padding:5px;overflow:auto'></div>";
	width = '800';
	height = '400';
	showDialog1(title,content,width,height,ev);	
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic1').style.top = '200px';
	document.getElementById('dynamic1').style.left = (pos[0] - 10 - width) +'px';
	document.getElementById('dynamic1').style.display='';
}

function deldata(idsvg){
	pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
	
	param = "idsvg="+idsvg;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					loaddata(paged);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
	if(confirm('Are you sure delete this item : '+idsvg+'?'))
		post_response_text('bi_slave_5upload.php?proses=deldata', param, respon);
}