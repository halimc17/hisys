
function loaddata() {
    param = 'method=loaddata';
    tujuan = 'sdm_slave_5manmanagement.php';

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
							ordering: true,
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

function newdata(jenis){
	param  = '';
	param += '&jenis=' + jenis;
	param += '&method=addnew';
	
	tujuan = 'sdm_slave_5manmanagement.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
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

					setValue2('id','');
					setValue2('kriteria','');
					setValue2('penilaian','');
					setValue2('keterangan','');
					setValue2('mode','insert');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan(){
	kt = document.getElementById("kriteria");
	kode = kt.options[kt.selectedIndex].value;
	kriteria = kt.options[kt.selectedIndex].text;
	p = document.getElementById("penilaian");
	penilaian = p.options[p.selectedIndex].value;
	namanilai = p.options[p.selectedIndex].text;
	keterangan = document.getElementById('keterangan').value;
	id  = document.getElementById('id').value;
	method  = document.getElementById('mode').value;
	
	validate([
        ["penilaian","Penilaian tidak boleh kosong."],
        ["kriteria","Kriteria tidak boleh kosong"],
        ["keterangan","Keterangan tidak boleh kosong"]
	]);
	param = 'kode=' + kode;
	param += '&kriteria=' + kriteria;
	param += '&penilaian=' + penilaian;
	param += '&namanilai=' + namanilai;
	param += '&keterangan=' + keterangan;
	param += '&id=' + id;
	param += '&method=' + method;
	tujuan = 'sdm_slave_5manmanagement.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.alert("Done");
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editdata(jenis,id,kriteria,penilaian){
	param = 'method=addnew';
	
	tujuan = 'sdm_slave_5manmanagement.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
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
					
					setValue2('id',id);
					setValue2('kriteria',kriteria);
					setValue2('penilaian',penilaian);
					setValue2('mode','update');

					param = 'method=getKeterangan&id='+id;
					tujuan = 'sdm_slave_5manmanagement.php';
					post_response_text(tujuan, param, respog);
					function respog() {
						if (con.readyState == 4) {
							if (con.status == 200) {
								busy_off();
								if (!isSaveResponse(con.responseText)) {
									alertify.alert(con.responseText);
								} else {
									document.getElementById('keterangan').value = con.responseText;
								}
							} else {
								busy_off();
								error_catch(con.status);
							}
						}
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function del(id){
	param = 'method=delete';
	param += '&id=' + id;	
	tujuan = 'sdm_slave_5manmanagement.php';

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