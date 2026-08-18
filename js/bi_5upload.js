function cariwarna(ev){
    content = "<div id=listwarna style=\"height:400px;width:905px;\"></div>";
    title =' Tabel Warna :';
    width = '904';
    height = '377';
    showDialog1(title, content, width, height, ev);
	getwarna();
}

function getwarna(){
    param = 'proses=cariwarna';

    tujuan = 'bi_slave_5upload.php';
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
                else
                {
                    document.getElementById('listwarna').innerHTML = con.responseText;
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

function movewarna(warna,jenis){
	document.getElementById('kodefill').value=warna;
	document.getElementById('kodefill').style.backgroundColor=warna;
	closeDialog();
}

function loadalldata(page){
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
					document.getElementById('container2').innerHTML = valSplit[2];
					document.getElementById('footData2').innerHTML = valSplit[3];
					document.getElementById('container3').innerHTML = valSplit[4];
					document.getElementById('footData3').innerHTML = valSplit[5];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('bi_slave_5upload.php?proses=loadalldata', param, respon);
}

function showEdit(id,tipepeta,namapeta){
	document.getElementById('id').value = id;
	ltipepeta=document.getElementById('tipepeta');
    for(ard=0;ard<ltipepeta.length;ard++){
        if(ltipepeta.options[ard].value==tipepeta){
			ltipepeta.options[ard].selected=true;
		}
    }
	document.getElementById('namapeta').value = namapeta;
	getNamaPeta();
	document.getElementById("method").value = "update";
}

function showEdit2(id,provinsi,kodept,unit,tipepeta,namapeta){
	document.getElementById('id2').value = id;
	lprovinsi2=document.getElementById('provinsi2');
    for(ard=0;ard<lprovinsi2.length;ard++){
        if(lprovinsi2.options[ard].value==provinsi){
			lprovinsi2.options[ard].selected=true;
		}
    }
	
	lkodept2=document.getElementById('kodept2');
    for(ard=0;ard<lkodept2.length;ard++){
        if(lkodept2.options[ard].value==kodept){
			lkodept2.options[ard].selected=true;
		}
    }
	
	getkebun2(unit);
	
	ltipepeta2=document.getElementById('tipepeta2');
    for(ard=0;ard<ltipepeta2.length;ard++){
        if(ltipepeta2.options[ard].value==tipepeta){
			ltipepeta2.options[ard].selected=true;
		}
    }
	
	document.getElementById('namapeta2').value = namapeta;
	document.getElementById("method2").value = "update";
}

function showEdit3(id,periode,kodept,unit,tipedokumen,kegiatan,fitur,warna,keterangan){
	document.getElementById('id3').value = id;
	
	lperiode3=document.getElementById('periode3');
    for(ard=0;ard<lperiode3.length;ard++){
        if(lperiode3.options[ard].value==periode){
			lperiode3.options[ard].selected=true;
		}
    }
	
	lkodept3=document.getElementById('kodept3');
    for(ard=0;ard<lkodept3.length;ard++){
        if(lkodept3.options[ard].value==kodept){
			lkodept3.options[ard].selected=true;
		}
    }
	
	ltipedokumen3=document.getElementById('tipedokumen3');
    for(ard=0;ard<ltipedokumen3.length;ard++){
        if(ltipedokumen3.options[ard].value==tipedokumen){
			ltipedokumen3.options[ard].selected=true;
		}
    }
	
	getkebun3(unit,kegiatan);
	
	document.getElementById("kodefill").value = warna;
	document.getElementById("kodefill").style.backgroundColor = warna;
	
	var radios = document.getElementsByName("fitur");
	for (var i=0;i<radios.length; i++) {
		if(radios[i].value==fitur){
			radios[i].checked = true;
		}
    }
	
	document.getElementById('keterangan3').value = keterangan;
	
	document.getElementById('periode3').disabled = true;
	document.getElementById('kodept3').disabled = true;
	document.getElementById('kebun3').disabled = true;
	document.getElementById('tipedokumen3').disabled = true;
	document.getElementById('kegiatan3').disabled = true;
	
	document.getElementById("method3").value = "update";
}

function getNamaPeta(){
	tipepeta=document.getElementById('tipepeta').options[document.getElementById('tipepeta').selectedIndex].value;
	
	if(tipepeta == 'MAP001'){
		document.getElementById('provinsi').selectedIndex = '0';
		document.getElementById('provinsi').style.display = '';
		document.getElementById('provinsi_find').style.display = '';
		document.getElementById('namapeta').style.display = 'none';
	}else{
		document.getElementById('provinsi').style.display = 'none';
		document.getElementById('provinsi_find').style.display = 'none';
		document.getElementById('namapeta').style.display = '';
	}
}

function batal(){
	document.getElementById('id').value = "";
	document.getElementById('tipepeta').selectedIndex = 0;
	getNamaPeta();
	document.getElementById('namapeta').value = "";
	document.getElementById("upload").value = "";
	document.getElementById("method").value = "insert";
}

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

function getPage2(){
    pg=document.getElementById('pages2');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata2(paged);	
}

function getPage3(){
    pg=document.getElementById('pages3');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata3(paged);	
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

function loaddata2(page){
	
	param = "page="+page;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					valSplit = con.responseText.split("####");
					document.getElementById('container2').innerHTML = valSplit[0];
					document.getElementById('footData2').innerHTML = valSplit[1];
					batal2();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('bi_slave_5upload.php?proses=loaddata2', param, respon);
}

function simpan(){
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", document.getElementById('upload').value);
	formdata.append("provinsi", document.getElementById('provinsi').value);
	formdata.append("namapeta", document.getElementById('namapeta').value);
	formdata.append("tipepeta", document.getElementById('tipepeta').value);
	formdata.append("id", document.getElementById('id').value);
	formdata.append("method", document.getElementById('method').value);
	
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
					batal();
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

function showsvg(idsvg,typemapshow,ev){
	showDetail(idsvg,ev);
    idsvg = idsvg;
	param='idsvg='+idsvg+'&proses=showsvg&typemapshow='+typemapshow;
	tujuan='bi_slave_5upload.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
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

function showsvg2(idsvg,typemapshow,ev){
	showDetail(idsvg,ev);
    idsvg = idsvg;
	param='idsvg='+idsvg+'&proses=showsvg&typemapshow='+typemapshow;
	tujuan='bi_slave_5upload.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
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

function showsvg3(idsvg,typemapshow,ev){
	showDetail(idsvg,ev);
    idsvg = idsvg;
	param='idsvg='+idsvg+'&proses=showsvg&typemapshow='+typemapshow;
	tujuan='bi_slave_5upload.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
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
	content = "<div id='svgimg' style='width:780px;height:380px;padding:5px;'></div>";
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

function deldata2(idsvg){
	pg=document.getElementById('pages2');
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
					loaddata2(paged);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
	if(confirm('Are you sure delete this item : '+idsvg+'?'))
		post_response_text('bi_slave_5upload.php?proses=deldata2', param, respon);
}

function deldata3(idsvg){
	pg=document.getElementById('pages3');
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
					loaddata3(paged);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
	if(confirm('Are you sure delete this item : '+idsvg+'?'))
		post_response_text('bi_slave_5upload.php?proses=deldata3', param, respon);
}


//====TAB TIPE PT=====
function batal2(){
	document.getElementById('id2').value = "";
	document.getElementById('kodept2').selectedIndex = 0;
	document.getElementById('provinsi2').selectedIndex = 0;
	document.getElementById('tipepeta2').selectedIndex = 0;
	document.getElementById('namapeta2').value = "";
	document.getElementById("upload2").value = "";
	document.getElementById("method2").value = "insert";
	getkebun2();
}

function simpan2(){
	var file = document.getElementById("upload2").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", document.getElementById('upload2').value);
	formdata.append("kodept", document.getElementById('kodept2').value);
	formdata.append("unit", document.getElementById('kebun2').value);
	formdata.append("provinsi", document.getElementById('provinsi2').value);
	formdata.append("tipepeta", document.getElementById('tipepeta2').value);
	formdata.append("namapeta", document.getElementById('namapeta2').value);
	formdata.append("id", document.getElementById('id2').value);
	formdata.append("method", document.getElementById('method2').value);
	
	var con = createXMLHttpRequest();
	con.open("POST", "bi_slave_5upload.php?proses=simpan2", true);
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
					loaddata2(0);
					// alert(con.responseText);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getkebun2(unit){
	kodept2=document.getElementById('kodept2');
    kodept2=kodept3.options[kodept2.selectedIndex].value;
	
	param='proses=getkebun2&kodept='+kodept2;
	tujuan='bi_slave_5upload.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('kebun2').innerHTML=con.responseText;
					if(unit!='undefined'){
						lkebun2=document.getElementById('kebun2');
						for(ard=0;ard<lkebun2.length;ard++){
							if(lkebun2.options[ard].value==unit){
								lkebun2.options[ard].selected=true;
							}
						}
					}
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function getkebun3(unit,kegiatan){
	kodept3=document.getElementById('kodept3');
    kodept3=kodept3.options[kodept3.selectedIndex].value;
	
	param='proses=getkebun3&kodept='+kodept3;
	tujuan='bi_slave_5upload.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('kebun3').innerHTML=con.responseText;
					if(unit!='undefined'){
						lkebun3=document.getElementById('kebun3');
						for(ard=0;ard<lkebun3.length;ard++){
							if(lkebun3.options[ard].value==unit){
								lkebun3.options[ard].selected=true;
							}
						}
					}
					getNoDok3(kegiatan);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function getNoDok3(kegiatan){
	id = document.getElementById('id3').value;
	periode=document.getElementById('periode3');
    periode=periode.options[periode.selectedIndex].value;
	kdorg=document.getElementById('kebun3');
	tipedokumen=document.getElementById('tipedokumen3');
    tipedokumen=tipedokumen.options[tipedokumen.selectedIndex].value;
	kdorg=document.getElementById('kebun3');
    kdorg=kdorg.options[kdorg.selectedIndex].value;
	
	if(tipedokumen==''){
		document.getElementById('trnodok').style.display = 'none';
	}else{
		document.getElementById('trnodok').style.display = '';
	}
	
	param='proses=getNoDok3&tipedokumen='+tipedokumen+'&kdorg='+kdorg+'&periode='+periode;
	tujuan='bi_slave_5upload.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else{
					document.getElementById('newnodok').innerHTML = '';
					document.getElementById('kegiatan3').innerHTML = con.responseText;
					if(kegiatan!='undefined'){
						lkegiatan3=document.getElementById('kegiatan3');
						for(ard=0;ard<lkegiatan3.length;ard++){
							if(lkegiatan3.options[ard].value==kegiatan){
								lkegiatan3.options[ard].selected=true;
							}
						}
						getListDok(id);
					}
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function getListDok(id){
	id = document.getElementById('id3').value;
	
	param='proses=getListDok&id='+id;
	tujuan='bi_slave_5upload.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else{
					document.getElementById('newnodok').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function searchnodok(title,content,ev){
	kebun=document.getElementById('kebun3');
    kebun=kebun.options[kebun.selectedIndex].value;
	tipedokumen=document.getElementById('tipedokumen3');
    tipedokumen=tipedokumen.options[tipedokumen.selectedIndex].value;
	kegiatan=document.getElementById('kegiatan3');
    kegiatan=kegiatan.options[kegiatan.selectedIndex].value;
	
	if(kebun==''||tipedokumen==''||kegiatan==''){
		alert('Kebun, Tipe Dokumen dan Kegiatan harus diisi.');
		return;
	}
	
	width='550';
	height='400';
	showDialog1(title,content,width,height,ev);
	getformnodok();
}

function getformnodok(){
    tipedokumen=document.getElementById('tipedokumen3');
    tipedokumen=tipedokumen.options[tipedokumen.selectedIndex].value;
	
	param='proses=searchnodok';
	tujuan='bi_slave_5upload.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else{
					document.getElementById('formPencariandata').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function findNodok(){
	nodokcr = document.getElementById('nodokcr').value;
	periode=document.getElementById('periode3');
    periode=periode.options[periode.selectedIndex].value;
	kebun=document.getElementById('kebun3');
    kebun=kebun.options[kebun.selectedIndex].value;
	tipedokumen=document.getElementById('tipedokumen3');
    tipedokumen=tipedokumen.options[tipedokumen.selectedIndex].value;
	kegiatan=document.getElementById('kegiatan3');
    kegiatan=kegiatan.options[kegiatan.selectedIndex].value;
	
	param='proses=findNodok&nodokcr='+nodokcr+'&periode='+periode+'&kdorg='+kebun+'&tipedokumen='+tipedokumen+'&kdkegiatan='+kegiatan;
	tujuan='bi_slave_5upload.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else{
					document.getElementById('ctner2').innerHTML=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}

function addNoDok(){
	nodok=document.getElementById('nodok').value;	
	param='proses=addNoDok&nodok='+nodok;
	tujuan='bi_slave_5upload.php';
	
	newRow = document.createElement("tr");
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					tabBody = document.getElementById('newnodok');
					tabBody.appendChild(newRow);
					newRow.setAttribute("id","tr_"+con.responseText);
					newRow.setAttribute("class","rowcontent");
					newRow.innerHTML += "<td>"+con.responseText+"</td>";
					newRow.innerHTML += "<td style='text-align:center'><img title='Hapus' class=resicon onclick=\"deletenodok(this,'"+con.responseText+"')\" src='images/delete_32.png'/></td>";
					document.getElementById('nodok').value = '';
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function setDataNodok(nodok){
	document.getElementById('nodok').value = nodok;
	closeDialog();
}

function deletenodok(btn,nodok){
	param='proses=deletenodok&nodok='+nodok;
	tujuan='bi_slave_5upload.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					var row = btn.parentNode.parentNode;
					row.parentNode.removeChild(row);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

//TAB 3
function batal3(){
	document.getElementById('id3').value = "";
	document.getElementById('periode3').selectedIndex = 0;
	document.getElementById('kodept3').selectedIndex = 0;
	getkebun3();
	document.getElementById('tipedokumen3').selectedIndex = 0;
	document.getElementById("upload3").value = "";
	document.getElementById("keterangan3").value = "";
	document.getElementById("kodefill").value = "";
	document.getElementById("kodefill").style.backgroundColor = "";
	
	var radios = document.getElementsByName("fitur");
	for (var i=0;i<radios.length; i++) {
		if(i==0){
			radios[i].checked = true;
		}
    }
	
	document.getElementById('periode3').disabled = false;
	document.getElementById('kodept3').disabled = false;
	document.getElementById('kebun3').disabled = false;
	document.getElementById('tipedokumen3').disabled = false;
	document.getElementById('kegiatan3').disabled = false;
	
	document.getElementById("method3").value = "insert";
}

function simpan3(){
	var radios = document.getElementsByName("fitur");
	for (var i=0;i<radios.length; i++) {
		if(radios[i].checked==true){
			valRadio = radios[i].value;
		}
    }
	
	var file = document.getElementById("upload3").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", document.getElementById('upload3').value);
	formdata.append("id", document.getElementById('id3').value);
	formdata.append("periode", document.getElementById('periode3').value);
	formdata.append("kdorg", document.getElementById('kebun3').value);
	formdata.append("tipedokumen", document.getElementById('tipedokumen3').value);
	formdata.append("kdkegiatan", document.getElementById('kegiatan3').value);
	formdata.append("fitur", valRadio);
	formdata.append("warna", document.getElementById('kodefill').value);
	formdata.append("keterangan", document.getElementById('keterangan3').value);
	formdata.append("method", document.getElementById('method3').value);
	
	var con = createXMLHttpRequest();
	con.open("POST", "bi_slave_5upload.php?proses=simpan3", true);
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
					alert('Data berhasil disimpan');
					loaddata3(0);
					// alert(con.responseText);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddata3(page){
	
	param = "page="+page;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					valSplit = con.responseText.split("####");
					document.getElementById('container3').innerHTML = valSplit[0];
					document.getElementById('footData3').innerHTML = valSplit[1];
					batal3();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('bi_slave_5upload.php?proses=loaddata3', param, respon);
}

function isifile(namafile,ev){
    param = 'proses=isifile&nodok='+nodok+'&namafile='+namafile;
    title="Data Detail";
	showDialog4(title,"<iframe frameborder=0 style='width:795px;height:395px'"+
    " src='bi_slave_5upload.php?"+param+"'></iframe>",'800','400',ev);	
    var dialog = document.getElementById('dynamic4');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}