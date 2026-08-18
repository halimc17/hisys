function selesai(){
	alertify.popup().destroy();
}
function addkary(row){
	i = document.getElementsByName("karyawanid[]");
	e = document.getElementsByName("check[]");
    temp = document.getElementById('karyawantemp').value;
    cari = document.getElementById('cari').value;
	h = document.getElementById('checkall');
	data="";
	for(n=0;n<e.length;n++){
		if(e[n].checked==true){
			data+=i[n].innerHTML+",";
		}
        if(i.length != n){
            h.checked = false;
        }else{
            h.checked = true;
        }
	}
    document.getElementById('karyawan').value = data.substr(0,data.length-1);
    document.getElementById('karyawantemp').value = data.substr(0,data.length-1);
    if(temp != '' && cari != ''){
        temporary = temp.split(',');
        var x;
        dataplustemp ='';
        for (x in temporary) {
            if(data.includes(temporary[x]) == false){
                dataplustemp += temporary[x]+',';
            }
        }
        datafix = dataplustemp+data;
        document.getElementById('karyawan').value = datafix.substr(0,datafix.length-1);
        document.getElementById('karyawantemp').value = datafix.substr(0,datafix.length-1);
    }
}

function clickall(){
	e = document.getElementsByName("check[]");
	h = document.getElementById('checkall');
	k = document.getElementsByName("karyawanid[]");
    temp = document.getElementById('karyawantemp').value;
    cari = document.getElementById('cari').value;
	data="";
	for(i=0;i<e.length;i++){
		if(h.checked==true){
			e[i].checked=true;
			data+=k[i].innerHTML+",";
		}else{
			e[i].checked=false;
		}
	}
    document.getElementById('karyawan').value = data.substr(0,data.length-1);
    document.getElementById('karyawantemp').value = data.substr(0,data.length-1);
    if(temp != '' && cari != ''){
        temporary = temp.split(',');
        var x;
        dataplustemp ='';
        for (x in temporary) {
            dataplustemp += temporary[x]+',';
        }
        datafix = dataplustemp+data;
        document.getElementById('karyawan').value = datafix.substr(0,datafix.length-1);
        document.getElementById('karyawantemp').value = datafix.substr(0,datafix.length-1);
    }
}

function getkaryawan(id,sumber) {
	divisidari= document.getElementById('divisidari').value;
	karyawan  = document.getElementById('karyawan').value;
	
	param = 'divisidari=' + divisidari+ '&method=getkaryawan';
	param += '&karyawan=' + karyawan;
	param += '&id=' + id;
	param += '&sumber=' + sumber;
	tujuan = 'kebun_slave_5asistensi.php';
	post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
                } else {
					alertify.popup("Add Karyawan",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('55%','75%'); 
					loadgetkaryawan(id,sumber);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadgetkaryawan(id,sumber){
	divisidari              = document.getElementById('divisidari').value;
	karyawan                = document.getElementById('karyawan').value;
	karyawantemp            = document.getElementById('karyawantemp').value;
	karyawantempsudahtrans  = document.getElementById('karyawantempsudahtrans').value;
	cari                    = document.getElementById('cari').value;
	sudahtrans              = document.getElementById('sudahtrans').value;
	
	param = 'divisidari=' + divisidari+ '&method=loadgetkaryawan';
	param += '&karyawan=' + karyawan;
	param += '&karyawantemp=' + karyawantemp;
	param += '&karyawantempsudahtrans=' + karyawantempsudahtrans;
	param += '&id=' + id;
	param += '&sumber=' + sumber;
	param += '&cari=' + cari;
	param += '&sudahtrans=' + sudahtrans;
	tujuan = 'kebun_slave_5asistensi.php';
	post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
                } else {
					document.getElementById('loadgetkaryawan').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function getdivisiasal() {

	kodeorg   = document.getElementById('kodeorgdari').value;

	param = 'kodeorg=' + kodeorg+ '&method=getdivisiasal';
	tujuan = 'kebun_slave_5asistensi.php';
	post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
                } else {
					document.getElementById('divisidari').innerHTML = con.responseText;
					
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getdivisitujuan() {

    kodeorg = document.getElementById('kodeorgtujuan').value;
	
	
	param = 'kodeorg=' + kodeorg+ '&method=getdivisitujuan';
	tujuan = 'kebun_slave_5asistensi.php';
	post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
                } else {
					
					document.getElementById('divisitujuan').innerHTML = con.responseText;
					
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getdivisicari(sumber) {
	kodeorg   = document.getElementById('kodeorgcari').value;
	
	param = 'kodeorg=' + kodeorg+ '&method=getdivisi';
	tujuan = 'kebun_slave_5asistensi.php';
	post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
                } else {
					document.getElementById('divisicari').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpandetail(id,kodegolongan,tipe) {  
	param = '';
	if(id.checked==true){
		param += '&check=1';
	}else{
		param += '&check=0';
	}
	
	param += '&kodegolongan=' + kodegolongan + '&tipe=' + tipe + '&method=simpandetail';
	tujuan = 'kebun_slave_5asistensi.php';
	post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
                } else {
                    //alertify.alert('Info',con.responseText);
                    //document.getElementById('container').innerHTML = con.responseText;
					if(kodegolongan==''){
						loaddata();
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpan() {
	tipetrans    = document.getElementById('tipetrans').value;
	dari         = document.getElementById('kodeorgdari').value;
	divisidari   = document.getElementById('divisidari').value;
	tujuan       = document.getElementById('kodeorgtujuan').value;
	divisitujuan = document.getElementById('divisitujuan').value;
	tanggal      = document.getElementById('tanggal').value;
	tanggalsampai= document.getElementById('tanggalsampai').value;
	method       = document.getElementById('method').value;
	id           = document.getElementById('id').value;
	sudahtrans   = document.getElementById('sudahtrans').value;
	karyawan     = document.getElementById('karyawan').value;
    if (trim(dari) == '') {
        document.getElementById('kodeorgdari').focus(); alertify.alert('Info',"Kode organisasi dari wajib diisi."); return;
    }
	if (trim(divisidari) == '') {
        document.getElementById('divisidari').focus(); alertify.alert('Info',"Divisi dari wajib diisi."); return;
    }
	if (trim(tujuan) == '') {
        document.getElementById('kodeorgtujuan').focus(); alertify.alert('Info',"Kode organisasi tujuan wajib diisi."); return;
    }
	if (trim(divisitujuan) == '') {
        document.getElementById('divisitujuan').focus(); alertify.alert('Info',"Divisi tujuan wajib diisi."); return;
    }
	if (trim(tanggal) == '') {
        document.getElementById('tanggal').focus(); alertify.alert('Info',"Tanggal wajib diisi."); return;
    }
	if (trim(tanggalsampai) == '') {
        document.getElementById('tanggalsampai').focus(); alertify.alert('Info',"Tanggal Sampai wajib diisi."); return;
    }
        
	param = 'dari=' + dari + '&tujuan=' + tujuan + '&method=' + method + '&tanggal=' + tanggal+ '&id=' + id;
	param += '&divisidari=' + divisidari + '&divisitujuan=' + divisitujuan;
	param += '&tipetrans=' + tipetrans;
	param += '&karyawan=' + karyawan;
	param += '&sudahtrans=' + sudahtrans;
	param += '&tanggalsampai=' + tanggalsampai;
	tujuan = 'kebun_slave_5asistensi.php';
	post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
                } else {
					location.reload();
                   
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function loaddata(page) {    
	tipetrans =  document.getElementById('tipetranscari').value;
	kodeorg =  document.getElementById('kodeorgcari').value;
	divisi =  document.getElementById('divisicari').value;
	
	param  = 'method=loaddata&page=' + page;
	param += '&tipetrans='+tipetrans+'&kodeorg='+kodeorg+'&divisidari='+divisi;
	tujuan = 'kebun_slave_5asistensi.php';
	post_response_text(tujuan, param, respog);
    

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
                } else {
                    document.getElementById('container').innerHTML = con.responseText;
					$(document).ready(function() {
						var table = $('#pvtTable').DataTable({
							fixedHeader: true,
							paging: true,
							colReorder: true,
							"iDisplayLength": 10,
							scrollX: true,
							scrollY: '40vh',
							scrollCollapse: true,
							language: {
								searchBuilder: {
									button: 'Filter',
								}
							},
							buttons:[
								'csv', 'excel', 'print'
							],
							dom: 'Bfrtip',
						});
						
						// //buat nomor urut
						// table.on( 'order.dt search.dt', function () {
							// table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
								// cell.innerHTML = i+1;
							// } );
						// } ).draw();
						// //buat nomor urut
					} );
                    // cancel();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function caridata(page) {   

	param  = 'method=loaddata&page=' + page;
	param += '&tipetrans='+tipetrans+'&kodeorg='+kodeorg+'&divisidari='+divisi;
	tujuan = 'kebun_slave_5asistensi.php';
	post_response_text(tujuan, param, respog);
    
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
                } else {
                    document.getElementById('container').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillField(id,dari,tujuan,tanggal,divasal,divtujuan,tipetrans,tanggalsampai,adatrans) {
    document.getElementById('tipetrans').value = tipetrans;
    document.getElementById('kodeorgdari').value = dari;
	document.getElementById('divisidari').value = divasal;
	document.getElementById('divisitujuan').value = divtujuan;
    document.getElementById('kodeorgtujuan').value = tujuan;

	setValue2('tipetrans',tipetrans);
	setValue2('kodeorgdari',dari);
	setValue2('divisidari',divasal);
	setValue2('divisitujuan',divtujuan);
	setValue2('kodeorgtujuan',tujuan);
	
    document.getElementById('tanggal').value = tanggal;
    document.getElementById('tanggalsampai').value = tanggalsampai;
    document.getElementById('id').value = id;

    if(adatrans == 1){
        document.getElementById('tipetrans').disabled=true;
        document.getElementById('divisidari').disabled=true;
        document.getElementById('divisitujuan').disabled=true;
        document.getElementById('kodeorgtujuan').disabled=true;
        document.getElementById('kodeorgdari').disabled=true;
        document.getElementById('tanggal').disabled=true;
        document.getElementById('tanggalsampai').disabled=true;
    }else{
        document.getElementById('tipetrans').disabled=false;
        document.getElementById('divisidari').disabled=false;
        document.getElementById('divisitujuan').disabled=false;
        document.getElementById('kodeorgtujuan').disabled=false;
        document.getElementById('kodeorgdari').disabled=false;
        document.getElementById('tanggal').disabled=false;
        document.getElementById('tanggalsampai').disabled=false;
    }
    
    document.getElementById('sudahtrans').value = adatrans;
    document.getElementById('method').value = 'update';
	editkary(id);
}


function editkary(id) {    
	param = 'method=editkary';
	param += '&id=' + id;
	tujuan = 'kebun_slave_5asistensi.php';
	post_response_text(tujuan, param, respog);
    

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
                } else {
                    document.getElementById('karyawan').value = con.responseText;
                    document.getElementById('karyawantemp').value = con.responseText;
                    document.getElementById('karyawantempsudahtrans').value = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function cancel() {
	document.getElementById('tipetrans').value = '';
	document.getElementById('divisidari').value = '';
	document.getElementById('divisitujuan').value = '';
	document.getElementById('kodeorgdari').value = '';
    document.getElementById('kodeorgtujuan').value = '';
    document.getElementById('tanggal').value = '';
    document.getElementById('tanggalsampai').value = '';
    document.getElementById('id').value = '';
    document.getElementById('sudahtrans').value = '';
    document.getElementById('karyawan').value = '';
    document.getElementById('karyawantemp').value = '';
    document.getElementById('karyawantempsudahtrans').value = '';
    document.getElementById('method').value = 'insert';
    document.getElementById('tipetrans').disabled=false;
    document.getElementById('divisidari').disabled=false;
    document.getElementById('divisitujuan').disabled=false;
    document.getElementById('kodeorgtujuan').disabled=false;
    document.getElementById('kodeorgdari').disabled=false;
    document.getElementById('tanggal').disabled=false;
    document.getElementById('tanggalsampai').disabled=false;
	
	setValue2('tipetrans',null);
	setValue2('kodeorgdari',null);
	setValue2('divisidari',null);
	setValue2('divisitujuan',null);
	setValue2('kodeorgtujuan',null);
}

function del(id) {
	param = 'id=' + id;
	param += '&method=delete';
	if(confirm("Are you sure ???")){		
		tujuan = 'kebun_slave_5asistensi.php';
		post_response_text(tujuan, param, respog);
	}
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
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