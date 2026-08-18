function select2(){
	a = document.getElementById('noakunx2').value;
	b = document.getElementById('noakun22').value;
	
	alert("noakunx2: "+a+", noakun22: "+b);
}

function setvalselect2(){
	// $('#noakun22').val('1160303').trigger('change');
	// $('#noakunx2').val('1160303').trigger('change');
	
	setValue2('noakun22','');
}

function clearselect2(){
	$('#noakun22').val(null).trigger('change');
	$('#noakunx2').val(null).trigger('change');
}
function del(id){
	param = 'method=delete';
	param += '&id=' + id;
	
	tujuan='setup_slave_5basispanen.php';
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
function gettahuntanam(){
	param = 'method=gettahuntanam';
	param += '&kodeorg=' + getValue('kodeorg');
	
	tujuan='setup_slave_5basispanen.php';
	post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					document.getElementById('tt').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function simpan(){
	method  = document.getElementById('method').value;
	
	validate([
        ["kodeorg","Kebun tidak boleh kosong."],
        ["tt","Tahun tanam tidak boleh kosong."],
        ["periode","Periode tidak boleh kosong"],
        ["jenishari","Hari tidak boleh kosong"],
	]);
	
	param  = '';
	param += '&kodeorg=' + getValue('kodeorg');
	param += '&tt=' + getValue('tt');
	param += '&periode=' + getValue('periode');
	param += '&jenishari=' + getValue('jenishari');
	param += '&basisha=' + getValue('basisha');
	param += '&basiskg=' + getValue('basiskg');
	param += '&premilebihbasis=' + getValue('premilebihbasis');
	param += '&premibrondol=' + getValue('premibrondol');
	param += '&premikesulitan=' + getValue('premikesulitan');
	param += '&premikehadiran=' + getValue('premikehadiran');
	param += '&banjir=' + getValue('banjir');
	param += '&id=' + getValue('id');
	param += '&method=' + method;
	
	tujuan = 'setup_slave_5basispanen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.set('notifier','position', 'top-center');
					alertify.success("Done.",'3');
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function loaddata() {
	//cari= trim(document.getElementById('cari').value);

    param = 'method=loaddata';
    //param += '&cari=' + cari;
    tujuan = 'setup_slave_5basispanen.php';
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
							//responsive: true,
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
							"iDisplayLength": 25,
							// tinggi / height
							scrollY: '60vh',
							scrollX: true,
							scrollCollapse: true,
							dom: 'Blfrtip',
							//select: true,
							
							language: {
								searchBuilder: {
									title: 'Filter',
									button: 'Filter'
								}
							},
							buttons: ['searchBuilder', 'excel', 'print',{
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
					$('select[name*="mytable_length"]').attr("style", "height:30px;");
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
	
	tujuan = 'setup_slave_5basispanen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':true}).resizeTo('900px','90%');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
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

function editdata(jenis,id,kodeorg,periode,tt,jenishari,basisha,basiskg,premilebihbasis,premibrondolan,premikesulitan,premikehadiran,banjir){
	param  = '';
	param += '&jenis=' + jenis;	
	param += '&mode=update';
	param += '&id=' + id;
	param += '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	param += '&tt=' + tt;
	param += '&jenishari=' + jenishari;
	param += '&basisha=' + basisha;
	param += '&basiskg=' + basiskg;
	param += '&premilebihbasis=' + premilebihbasis;
	param += '&premibrondolan=' + premibrondolan;
	param += '&premikesulitan=' + premikesulitan;
	param += '&premikehadiran=' + premikehadiran;
	param += '&banjir=' + banjir;
	param += '&method=showEditDialog';

	tujuan = 'setup_slave_5basispanen.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':true}).resizeTo('900px','90%');
					// $(document).ready(function() {
					// 	$('.select2').select2({
					// 		dropdownAutoWidth:false
					// 	});
					// 	$('.select2-selection--single').height(30).css({
					// 		cursor: "auto"
					// 	});
					// 	$('.select2-selection__arrow b').css({
					// 		top: "70%"
					// 	});
					// 	$('.select2-selection__rendered').css({
					// 		'line-height': '31px'
					// 	});
					// });
					
					// setValue2('id',id);
					// setValue2('kodeorg',kodeorg);
					// setTimeout(function(){
					// 	setValue2('tt',tt);
					// 	setValue2('periode',periode);
					// 	setValue2('jenishari',jenishari);
					// 	setValue2('basisha',basisha);
					// 	setValue2('basiskg',basiskg);
					// 	setValue2('premilebihbasis',premilebihbasis);
					// 	setValue2('premibrondol',premibrondolan);
					// 	setValue2('premikesulitan',premikesulitan);
					// 	setValue2('premikehadiran',premikehadiran);
					// 	setValue2('banjir',banjir);
					// 	setValue2('method','update');
					// 	document.getElementById('kodeorg').disabled=true;						
					// 	document.getElementById('periode').disabled=true;						
					// 	document.getElementById('tt').disabled=true;						
					// 	document.getElementById('jenishari').disabled=true;						
					// }, 250);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function gettotalnonprd(){
	gp=0;
	n = document.getElementsByName("nonprd[]");
	for (i = 0; i < n.length; i++){
		a = n[i].value; 
		if(a==''){a=0;}else{a=remove_comma_var(a);}
		gp = gp+parseFloat(a);
	}
	
	document.getElementById('luasareanonproduktif').value=gp;
}