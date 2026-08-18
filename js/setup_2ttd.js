//JS
function simpan() {
	id        = document.getElementById("id").value;
	menuid    = document.getElementById("menuid").value;
	judul     = document.getElementById("judul").value;
	karyawanid= document.getElementById("karyawanid").value;
	kodeunit  = document.getElementById("kodeunit").value;
	jabatan   = document.getElementById("jabatan").value;
	method    = document.getElementById("method").value;

	param = "menuid=" + menuid +"&karyawanid="+karyawanid+"&kodeunit="+kodeunit+"&jabatan="+jabatan+"&judul="+judul+"&method="+method+"&id="+id;

	tujuan = "setup_2ttd_slave.php";
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
		  if (con.status == 200) {
			busy_off();
			if (!isSaveResponse(con.responseText)) {
			  alertify.alert("Informasi", con.responseText);
			} else {
			  alertify.popup().destroy();
			  loaddata(0);
			}
		  } else {
			busy_off();
			error_catch(con.status);
		  }
		}
	}
}

function cancel() {
	document.getElementById("id").value = "";
	document.getElementById("karyawanid").value = "";
	document.getElementById("kodeunit").value = "";
	document.getElementById("menuid").value = "";
	document.getElementById("jabatan").value = "";
	document.getElementById("judul").value = "";
	document.getElementById("method").value = "insert";
}


function loaddata() {
	//cari= trim(document.getElementById('cari').value);

    param = 'method=loaddata';
    //param += '&cari=' + cari;
    tujuan = 'setup_2ttd_slave.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML = con.responseText;
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
							buttons: ['csv', 'excel', 'print',{
									text: 'New',
									action: function () {
										newdata('new');
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


// function loaddata(page) {
	// kodeunit = document.getElementById('kodeunitsch').value;
	// menuid = document.getElementById('menuidsch').value;
	// method='loaddata';
	// param = "menuid=" + menuid +"&page="+page+"&kodeunit="+kodeunit+"&method="+method;
	// tujuan  = "setup_2ttd_slave.php";
	// post_response_text(tujuan, param, respog);
	// function respog() {
		// if (con.readyState == 4) {
			// if (con.status == 200) {
				// busy_off();
				// if (!isSaveResponse(con.responseText)) {
				  // alertify.alert("Informasi", con.responseText);
				// } else {
				  // // alert(con.responseText);
				  // document.getElementById("container").innerHTML = con.responseText;
				  // leftFixedTable();
				// }
			// } else {
				// busy_off();
				// error_catch(con.status);
			// }
		// }
	// }
// }




function newdata(jenis){
	param  = '';
	param += '&jenis=' + jenis;
	param += '&method=addnew';
	
	tujuan = 'setup_2ttd_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('55%','400px');
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
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editdata(jenis,tahun,jabatan,dept,kpi,bobot,porsiatasan,porsisendiri,stat,id){
	param  = '';
	param += '&mode=update';
	param += '&method=addnew';
	
	tujuan = 'setup_2ttd_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('55%','400px');
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
					
					setValue2('idkpi',id);
					setValue2('tahun',tahun);
					setValue2('jabatan',jabatan);
					setValue2('dept',dept);
					setValue2('kpi',kpi);
					setValue2('bobot',bobot);
					setValue2('porsiatasan',porsiatasan);
					setValue2('porsisendiri',porsisendiri);
					setValue2('status',stat);
					setValue2('method','update');
					document.getElementById('tahun').disabled=true;
					document.getElementById('jabatan').disabled=true;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function edit(id) {
    param  = "id=" + id;
    param += '&mode=update';
    param += '&method=addnew';
    tujuan = "setup_2ttd_slave.php";
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alertify.popup().destroy();
					alertify.popup("update","<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('55%','400px');
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
					setValue2('method','update');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function del(id) {
  param = "method=delete" + "&id=" + id;
  tujuan = "setup_2ttd_slave.php";
   alertify.confirm("Informasi","Anda yakin ingin menghapus data ini?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
  
  function respog() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alert(con.responseText);
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
