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

    tujuan = 'pabrik_slave_5db_komponen.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
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

function addwarna(){
	tipe = document.getElementById('tipe').value;
	kode = document.getElementById('kode').value;
	kodefill = document.getElementById('kodefill').value;
	operationawal = document.getElementById('operationawal').options[document.getElementById('operationawal').selectedIndex].value;
	nilaiawal = document.getElementById('nilaiawal').value;
	operationakhir = document.getElementById('operationakhir').options[document.getElementById('operationakhir').selectedIndex].value;
	nilaiakhir = document.getElementById('nilaiakhir').value;
	keterangan = document.getElementById('keterangan').value;
	
	param='proses=addwarna&tipe='+tipe+'&kode='+kode+'&kodefill='+kodefill+'&operationawal='+operationawal+'&nilaiawal='+nilaiawal+'&operationakhir='+operationakhir+'&nilaiakhir='+nilaiakhir+'&keterangan='+keterangan;
	tujuan='pabrik_slave_5db_komponen.php';
	
	newRow = document.createElement("tr");
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
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

function simpan(){
	tipe = document.getElementById('tipe').value;
	kode = document.getElementById('kode').value;
	nama = document.getElementById('nama').value;
	method = document.getElementById('method').value;
	
	param='proses=simpan&tipe='+tipe+'&kode='+kode+'&nama='+nama+'&method='+method;
	tujuan='pabrik_slave_5db_komponen.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
 					loadData();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function batal(){
	document.getElementById('tipe').value = '';
	document.getElementById('tipe').disabled = false;
	document.getElementById('kode').value = '';
	document.getElementById('kode').disabled = false;
	document.getElementById('nama').value = '';
	document.getElementById('kodefill').value = '';
	document.getElementById('kodefill').style.background = '#FFFFFF';
	document.getElementById('operationawal').selectedIndex = 0;
	document.getElementById('nilaiawal').value = 0;
	document.getElementById('operationakhir').selectedIndex = 0;
	document.getElementById('nilaiakhir').value = 0;
	document.getElementById('keterangan').value = '';
	document.getElementById('method').value = 'insert';
	
	param='proses=batal';
	tujuan='pabrik_slave_5db_komponen.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('newmaster').innerHTML = '';
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}

function loadData(num){

    param='proses=loadData';
	param+='&page='+num;

	tujuan='pabrik_slave_5db_komponen.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
                if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//alert(con.responseText);
                    //document.getElementById('container').innerHTML=con.responseText;
                    isdt = con.responseText.split("####");
                    document.getElementById('container').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                    batal();
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
    loadData(paged);  
}

function deletefield(tipe,kode){
	param='proses=deletefield&tipe='+tipe+'&kode='+kode;
	tujuan='pabrik_slave_5db_komponen.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					loadData();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	if(confirm('Anda yakin hapus item ini ?'))
		post_response_text(tujuan, param, respog);
}

function detailfield(tipe,kode,ev){
    content = "<div id=detfield style=\"overflow:auto;\" ></div>";
    title =' Detail : '+tipe;
    width = '';
    height = '';
    showDialog1(title, content, width, height, ev);
	getdetailfield(tipe,kode);
}

function getdetailfield(tipe,kode){
    param = 'proses=getdetailfield&tipe='+tipe+'&kode='+kode;
	tujuan = 'pabrik_slave_5db_komponen.php';
    
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
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

function fillfield(tipe,kode,nama){
	document.getElementById('tipe').value = tipe;
	document.getElementById('tipe').disabled = true;
	document.getElementById('kode').value = kode;
	document.getElementById('kode').disabled=true;
	document.getElementById('nama').value = nama;
	document.getElementById('method').value = 'update';
	
	param='proses=fillfield&tipe='+tipe+'&kode='+kode+'&nama='+nama+'&method='+method;
	tujuan='pabrik_slave_5db_komponen.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('newmaster').innerHTML = con.responseText;
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
	tujuan='bi_slave_5laporan.php';
	
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