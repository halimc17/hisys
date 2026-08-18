function btldendapanen() {
    document.getElementById('method').value = 'insert';
    document.getElementById("kd_org").selectedIndex = "0";
    document.getElementById('tipetrans').selectedIndex = '0';
    document.getElementById('kolom').selectedIndex = '0';
    document.getElementById('jabatan').value = '';
    document.getElementById('id').value = '';
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);
}

function loadData(page) {
	// kodeorg  = document.getElementById('kebuncari').value;
	// tipetrans= trim(document.getElementById('tipecari').value);
	// kolom    = trim(document.getElementById('kolomcari').value);
	
    param = 'method=loaddata';
	// param+='&page=' + page;
	// param+='&kodeorg=' + kodeorg;
	// param+='&tipetrans=' + tipetrans;
	// param+='&kolom=' + kolom;
    tujuan = 'kebun_slave_5jabatanbkm.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
                } else {
                    document.getElementById('output').innerHTML = con.responseText;
					
					$(document).ready(function() {
						var table = $('#pvtTable').DataTable({
							fixedHeader: true,
							paging: true,
							colReorder: true,
							"iDisplayLength": 10,
							scrollX: true,
							scrollY: '45vh',
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
					
                    btldendapanen();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillfield(kodeorg,tipetrans,kolom,jabatan,id) {
    document.getElementById('kd_org').value = kodeorg;
    document.getElementById('tipetrans').value = tipetrans;
    document.getElementById('kolom').value = kolom;
    document.getElementById('jabatan').value = jabatan;
    document.getElementById('id').value = id;
}

function simpan(){
	kodeorg  = document.getElementById('kd_org').value;
	tipetrans= trim(document.getElementById('tipetrans').value);
	kolom    = trim(document.getElementById('kolom').value);
	jabatan  = trim(document.getElementById('jabatan').value);
	method   = trim(document.getElementById('method').value);
	id       = trim(document.getElementById('id').value);

    param = 'kodeorg=' + kodeorg + '&jabatan=' + jabatan + '&kolom=' + kolom+'&method=' + method;
	param+='&tipetrans=' + tipetrans;
	param+='&id=' + id;
    tujuan = 'kebun_slave_5jabatanbkm.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
                } else {
                    //document.getElementById('container').innerHTML = con.responseText;
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefield(id) {
    param = 'id=' + id + '&method=delete';
    tujuan = 'kebun_slave_5jabatanbkm.php';
    if (confirm('Anda yakin hapus item ini?'))
        post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
                } else {
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getjabatan() {
	// width = '';
    // height = '';
    // content = "<fieldset><div id=containerd style=\"max-width:700px;max-height:500px;overflow:auto;\"></div></fieldset>";
    // ev = 'event';
    // title = "Jabatan";
    // showDialog1(title, content, width, height, ev);
	
	kodeorg  = document.getElementById('kd_org').value;
	tipetrans  = document.getElementById('tipetrans').value;
	kolom  = document.getElementById('kolom').value;
	
    param = 'method=getjabatan';
	param+='&kodeorg=' + kodeorg;
	param+='&tipetrans=' + tipetrans;
	param+='&kolom=' + kolom;
    tujuan = 'kebun_slave_5jabatanbkm.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
                } else {
                    //document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('40%','70%'); 
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function addjab(row){
	i = document.getElementsByName("jabatan[]");
	e = document.getElementsByName("check[]");
	data="";
	for(n=0;n<e.length;n++){
		if(e[n].checked==true){
			data+=i[n].innerHTML+",";
		}
	}
	document.getElementById('jabatan').value = data.substr(0,data.length-1);
	alertify.popup().destroy();
}

function getkary(jabatan,kodeorg) {
    param = 'method=getkary';
	param+='&kodeorg=' + kodeorg;
	param+='&jabatan=' + jabatan;
    tujuan = 'kebun_slave_5jabatanbkm.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
                } else {
                    //document.getElementById('containerd').innerHTML = con.responseText;
					alertify.popup2("Detail Karyawan",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('40%','70%'); 
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}