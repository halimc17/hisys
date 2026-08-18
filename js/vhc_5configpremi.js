function simpan(){
	kodeOrg=document.getElementById('kodeOrg').options[document.getElementById('kodeOrg').selectedIndex].value;
	setupPremi=document.getElementById('setupPremi').options[document.getElementById('setupPremi').selectedIndex].value;
	status_aktif=document.getElementById('status_aktif').options[document.getElementById('status_aktif').selectedIndex].value;
	insert=document.getElementById('proses').value;

	param='kodeOrg='+kodeOrg+'&status_aktif='+status_aktif+'&setupPremi='+setupPremi+'&proses='+insert;
	tujuan='vhc_slave_5configpremi.php';
	post_response_text(tujuan, param, respog);
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
				}else {
					document.getElementById('proses').value='insert';
					document.getElementById('kodeOrg').disabled=false;
					document.getElementById('setupPremi').disabled=false;
					batalOpt();
                    setTimeout(function() {
						loaddata();
						alertify.popup().destroy();
						alertify.alert("Data Berhasil disimpan.");
					},100);
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}	
	 }  	
}

function loaddata(){
	param='proses=loaddata';
	tujuan='vhc_slave_5configpremi.php';
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
	param += '&proses=addnew';
	
	tujuan = 'vhc_slave_5configpremi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':true}).resizeTo('40%','30%');
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

function editdata(jenis,kodeOrg,setupPremi,status_aktif){
	param  = '';
	param += '&jenis=' + jenis;
	param += '&proses=addnew';
	
	tujuan = 'vhc_slave_5configpremi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':true}).resizeTo('40%','30%');
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
					setValue2('kodeOrg',kodeOrg);
					setValue2('setupPremi',setupPremi);
					setValue2('status_aktif',status_aktif);

					document.getElementById('kodeOrg').disabled=true;
					document.getElementById('setupPremi').disabled=true;
					document.getElementById('proses').value='update';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function batalOpt(){
    document.getElementById('kodeOrg').disabled=false;
    document.getElementById('setupPremi').disabled=false;
	setValue2('kodeOrg',null);
	setValue2('setupPremi',null);
	setValue2('status',null);
    document.getElementById('proses').value='insert';
}