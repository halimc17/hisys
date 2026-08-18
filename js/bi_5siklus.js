function display_number(id,evt){
	txb = document.getElementById(id);
	if(txb.value == ''){
		txb.value = 0;
	}     
}

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

    tujuan = 'bi_slave_5siklus.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    document.getElementById('listwarna').innerHTML = con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function movewarna(warna,jenis){
	document.getElementById('kodefill').value = warna;
	document.getElementById('kodefill').style.background = warna;
	closeDialog();
}

function batal(){
	document.getElementById('id').value = '';
	document.getElementById('noakun').selectedIndex = 0;
	document.getElementById('noakun').disabled = false;
	document.getElementById('kegiatan').selectedIndex = 0;
	document.getElementById('kegiatan').disabled = false;
	document.getElementById('kodefill').value = '';
	document.getElementById('kodefill').style.background = '#FFFFFF';
	document.getElementById('operationawal').selectedIndex = 0;
	document.getElementById('nilaiawal').value = 0;
	document.getElementById('operationakhir').selectedIndex = 0;
	document.getElementById('nilaiakhir').value = 0;
	document.getElementById('keterangan').value = '';
	document.getElementById('method').value = 'insert';
	
	param='proses=batal';
	tujuan='bi_slave_5siklus.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('newmaster').innerHTML = '';
					getkegiatan();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function getkegiatan(kegiatan){
	noakun=document.getElementById('noakun');
    noakun=noakun.options[noakun.selectedIndex].value;
	
	param = 'noakun='+noakun+'&kegiatan='+kegiatan+'&proses=getkegiatan';
	tujuan = 'bi_slave_5siklus.php';

	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('kegiatan').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function addwarna(){
	kodefill = document.getElementById('kodefill').value;
	operationawal = document.getElementById('operationawal').options[document.getElementById('operationawal').selectedIndex].value;
	nilaiawal = document.getElementById('nilaiawal').value;
	operationakhir = document.getElementById('operationakhir').options[document.getElementById('operationakhir').selectedIndex].value;
	nilaiakhir = document.getElementById('nilaiakhir').value;
	keterangan = document.getElementById('keterangan').value;
	
	param='proses=addwarna&kodefill='+kodefill+'&operationawal='+operationawal+'&nilaiawal='+nilaiawal+'&operationakhir='+operationakhir+'&nilaiakhir='+nilaiakhir+'&keterangan='+keterangan;
	tujuan='bi_slave_5siklus.php';
	
	newRow = document.createElement("tr");
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					vsplit = con.responseText.split("####");
					tabBody = document.getElementById('newmaster');
					tabBody.appendChild(newRow);
					newRow.setAttribute("id","tr_"+vsplit[0]+"_"+vsplit[1]+"_"+vsplit[2]);
					newRow.setAttribute("class","rowcontent");
					newRow.innerHTML += "<td bgcolor="+vsplit[0]+">"+vsplit[0]+"</td>";
					newRow.innerHTML += "<td style='text-align:center;'>"+vsplit[1]+"</td>";
					newRow.innerHTML += "<td style='text-align:right'>"+vsplit[2]+"</td>";
					newRow.innerHTML += "<td style='text-align:center;'>"+vsplit[3]+"</td>";
					newRow.innerHTML += "<td style='text-align:right'>"+vsplit[4]+"</td>";
					newRow.innerHTML += "<td>"+vsplit[5]+"</td>";
					newRow.innerHTML += "<td style='text-align:center'><img title='Hapus' class=resicon onclick=\"deletewarna(this,'"+con.responseText+"')\" src='images/delete_32.png'/></td>";
					document.getElementById('kodefill').value = '';
					document.getElementById('kodefill').style.background = '#FFFFFF';
					document.getElementById('operationawal').selectedIndex = 0;
					document.getElementById('nilaiawal').value = 0;
					document.getElementById('operationakhir').selectedIndex = 0;
					document.getElementById('nilaiakhir').value = 0;
					document.getElementById('keterangan').value = '';
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function deletewarna(btn,arrVal){
	vsplit = arrVal.split("####");
	param='proses=deletewarna&kodefill='+vsplit[0]+'&operationawal='+vsplit[1]+'&nilaiawal='+vsplit[2]+'&operationakhir='+vsplit[3]+'&nilaiakhir='+vsplit[4];
	tujuan='bi_slave_5siklus.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
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

function simpan(){
	idlap = document.getElementById('id').value;
	noakun=document.getElementById('noakun');
    noakun=noakun.options[noakun.selectedIndex].value;
	kegiatan=document.getElementById('kegiatan');
    kegiatan=kegiatan.options[kegiatan.selectedIndex].value;
	method = document.getElementById('method').value;
	
	param='proses=simpan&noakun='+noakun+'&kegiatan='+kegiatan+'&idlap='+idlap+'&method='+method;
	tujuan='bi_slave_5siklus.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					alert("Input data berhasil.");
					loaddata();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function loaddata(){
	param='proses=loaddata';
	tujuan='bi_slave_5siklus.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
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
	post_response_text(tujuan, param, respog);
}

function fillfield(idlap,noakun,kegiatan){
	document.getElementById('id').value = idlap;
	lnoakun=document.getElementById('noakun');
    for(ard=0;ard<lnoakun.length;ard++){
		if(lnoakun.options[ard].value==noakun){
			lnoakun.options[ard].selected=true;
		}
    }
	document.getElementById('noakun').disabled = true;
	document.getElementById('kegiatan').disabled = true;
	document.getElementById('method').value = 'update';
	
	param='proses=fillfield&idlap='+idlap;
	tujuan='bi_slave_5siklus.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					document.getElementById('newmaster').innerHTML = con.responseText;
					getkegiatan(kegiatan);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function deletefield(idlap){
	param='proses=deletefield&idlap='+idlap;
	tujuan='bi_slave_5siklus.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else{
					loaddata();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	if(confirm('Anda yakin hapus item ini : '+idlap+'?'))
		post_response_text(tujuan, param, respog);
}

function detailfield(idlap,ev){
    content = "<div id=detfield style=\"height:300px;width:700px;overflow:auto;\" ></div>";
    title =' Detail : '+idlap;
    width = '700';
    height = '300';
    showDialog1(title, content, width, height, ev);
	getdetailfield(idlap);
}

function getdetailfield(idlap){
    param = 'proses=getdetailfield&idlap='+idlap;
	tujuan = 'bi_slave_5siklus.php';
    
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }else{
                    document.getElementById('detfield').innerHTML = con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
	post_response_text(tujuan, param, respog);
}