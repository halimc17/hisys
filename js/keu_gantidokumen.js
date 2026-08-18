function select2(){
	a = document.getElementById('noakunx2').value;
	b = document.getElementById('noakun22').value;
	
	alert("noakunx2: "+a+", noakun22: "+b);
}

function setvalselect2(){
	setValue2('noakun22','');
}

function clearselect2(){
	$('#noakun22').val(null).trigger('change');
	$('#noakunx2').val(null).trigger('change');
}

function loaddata() {
    param = 'method=loaddata';
    tujuan = 'keu_slave_gantidokumen.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('output').innerHTML = con.responseText;
					$(document).ready(function() {
						var table = $('#mytable').DataTable({
							// supaya tidak ada overflow horisontal
							// responsive: true,
							// fixedColumns:   {
								// leftColumns: 1,
								// rightColumns: 2
							// },
							ordering: false,
							fixedHeader: true,
							// pake paging atau tidak
							paging: true,
							// columnDefs: [
								// {"className": "dt-body-nowrap", "targets": [0,1,2,3,4,5,6,7,8,9,10,11,12,13]}
							// ],
							// drag kolom
							//colReorder: true,
							// jumlah per page
							"iDisplayLength": 10,
							// tinggi / height
							scrollY: '60vh',
							scrollX: true,
							scrollCollapse: true,
							dom: 'Bfrtip',
							//select: true,
							
							language: {
								searchBuilder: {
									title: 'Filter',
									button: 'Filter'
								}
							},
							buttons: ['searchBuilder','csv', 'excel', 'print',{
									text: 'New',
									action: function () {
										newdata('Data Baru');
									}
								}
							]
						});
						
						//double click untuk freeze column
						$(table.table().container()).on('dblclick', 'td', function () {
							var row = table.column(this);
								new $.fn.dataTable.FixedColumns(table, {
										leftColumns: row.index()+1
										//   rightColumns: 1
									}); 
							//console.log('Row Index = ' + row.index());
						});
						
						//right click untuk freeze column
						$(table.table().container()).on('dblclick', 'th', function () {
							var row = table.column(this);
								new $.fn.dataTable.FixedColumns(table, {
										leftColumns: row.index()+1
									}); 
							//console.log('Row Index = ' + row.index());
						});	
					} );
					
					

					// leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function newdata(jenis){
	param  = '';
	param += '&jenis=' + jenis;
	param += '&method=addnew';
	
	tujuan = 'keu_slave_gantidokumen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('55%','70%');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:false
						});
						$('.select2-selection--single').height(30).css({
							cursor: "auto"
						});
						$('.select2-selection__arrow b').css({
							top: "70%"
						});
						$('.select2-selection__rendered').css({
							'line-height': '31px'
						});
					});
					
					document.getElementById('detail').style.display = 'none';
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan(){
	notransaksi = document.getElementById('notransaksi').value;
	nodoklama = document.getElementById('nodoklama').value;
	nodokbaru = document.getElementById('nodokbaru').value;
	tanggal = document.getElementById('tanggal').value;
	keterangan = document.getElementById('keterangan').value;
	method  = document.getElementById('method').value;
	
	validate([
        ["nodoklama","No Dokumen Lama tidak boleh kosong."],
        ["nodokbaru","No Dokumen Baru tidak boleh kosong"],
        ["tanggal","Tanggal tidak boleh kosong"],
        ["keterangan","Keterangan tidak boleh kosong"]
	]);
	
	param  = '';
	param += '&notransaksi=' + notransaksi;
	param += '&nodoklama=' + nodoklama;
	param += '&nodokbaru=' + nodokbaru;
	param += '&tanggal=' + tanggal;
	param += '&keterangan=' + keterangan;
	param += '&method=' + method;
	
	tujuan = 'keu_slave_gantidokumen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// alertify.popup().destroy();
					// alertify.alert("Done");
					// loaddata();
					document.getElementById('notransaksi').value=con.responseText;
					document.getElementById('detail').style.display = 'block';
					loadfiles();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function loadfiles() {
    notransaksi = document.getElementById('notransaksi').value;
    param       = 'method=loadfiles&notransaksi='+trim(notransaksi);
	tujuan      = 'keu_slave_gantidokumen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					
					if (document.getElementById('listfiles') !== null) {
						document.getElementById('listfiles').innerHTML = con.responseText;
					}
					// document.getElementById('listfiles').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function submitfile() {
	var notransaksi = document.getElementById("notransaksi").value;
	var kriteriaefil = document.getElementById("kriteriaefil").value;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("notransaksi", notransaksi);
	formdata.append("kriteriaefil", kriteriaefil);
	if (getValue('upload') == "") {
		alertify.alert("Informasi","warning : Upload file has been empty. \n Data upload kosong");
		return false;
	}
	document.getElementsByClassName("mybutton").disabled=true;
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "keu_slave_gantidokumen.php?method=submitfile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					//=== Success Response
					document.getElementsByClassName("mybutton").disabled=false;
					// alertify.alert("Informasi",'Uploaded Success.');
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

function deletefile(notransaksi, namafile) {
	param = 'method=deletefile&notransaksi=' + notransaksi + '&namafile=' + namafile;
	tujuan = 'keu_slave_gantidokumen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					loadfiles(notransaksi);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}








function editdata(notransaksi,nodoklama,nodokbaru,tanggal,keterangan){
	param  = '';
	param += '&nodoklama=' + nodoklama;
	param += '&nodokbaru=' + nodokbaru;
	param += '&tanggal=' + tanggal;
	param += '&keterangan=' + keterangan;
	param += '&notransaksi=' + notransaksi;	
	param += '&mode=update';
	param += '&method=addnew';
	jenis = 'Edit Data ' + notransaksi;
	
	tujuan = 'keu_slave_gantidokumen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('55%','70%');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:false
						});
						$('.select2-selection--single').height(30).css({
							cursor: "auto"
						});
						$('.select2-selection__arrow b').css({
							top: "70%"
						});
						$('.select2-selection__rendered').css({
							'line-height': '31px'
						});
					});
					
					setValue2('nodoklama',nodoklama);
					setValue2('nodokbaru',nodokbaru);
					setValue2('tanggal',tanggal);
					setValue2('keterangan',keterangan);
					setValue2('notransaksi',notransaksi);
					setValue2('method','update');
					
					loadfiles();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function del(notransaksi){

	param = 'method=delete';
	param += '&notransaksi=' + notransaksi;	
	tujuan = 'keu_slave_gantidokumen.php';

	alertify.confirm("Delete","Anda yakin?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
	
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
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

function post(notransaksi){
	param  = '';
	param += '&notransaksi=' + notransaksi;
	param += '&method=formposting';
	
	tujuan = 'keu_slave_gantidokumen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup('Posting Data', "<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('35%','40%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function posting(notransaksi,maxaproval) {
  	param = '';
  	method = 'posting';

  	if(maxaproval=='0'){
    	alert('Belum ada setup persetujuan.');
    	return;
  	}

  	strper = '';
  	for(i=1;i<=maxaproval;i++){
   		strper += '&persetujuan['+i+']='+trim(document.getElementById('persetujuan'+i).value);
   		if(trim(document.getElementById('persetujuan'+i).value)==''){
    		alert('Silakan isi persetujuan');
    		return;
   		}
  	}
  	param += 'notransaksi=' + notransaksi;
  	param += '&maxaproval=' + maxaproval;
  	param += '&method=' + method;
  	param += strper;  
  	tujuan = 'keu_slave_gantidokumen.php';

  	alertify.confirm("Informasi","Yakin ingin memposting ???",
    	function(){
      		post_response_text(tujuan, param, respon);
    	},
    	function(){
      		return;
    	}
  	);  
  
  	function respon() {
    	if (con.readyState == 4) {
      		if (con.status == 200) {
        		busy_off();
        		if (!isSaveResponse(con.responseText)) {
          			alertify.alert('Informasi',con.responseText);
				} else {
		  			alertify.popup().destroy();
          			loaddata();
        		}
      		} else {
       	 		busy_off();
        		error_catch(con.status);
      		}
    	}
  	}  
} 

function viewdetail(notransaksi){
	param  = '';
	param += '&notransaksi=' + notransaksi;
	param += '&method=viewdetail';
	
	tujuan = 'keu_slave_gantidokumen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					// alertify.popup('View Dokumen', "<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('35%','40%');
					// alertify.popup('View Dokumen', "<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
					
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}