function loaddata() {
	//cari= trim(document.getElementById('cari').value);

    param = 'method=loaddata';
    //param += '&cari=' + cari;
    tujuan = 'master_grading_slave.php';
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
							paging: false,
							// columnDefs: [
								// {"className": "dt-body-nowrap", "targets": [0,1,2,3,4,5,6,7,8,9,10,11,12,13]}
							// ],
							// drag kolom
							//colReorder: true,
							// jumlah per page
							// "iDisplayLength": 10,
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
								text: '<i class="fa fa-refresh"></i>',
								titleAttr: 'Refresh',
								action: function (e, dt, node, config){
									refreshdata('grading');
								}
							}]
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
					
					$(document).ready(function(){
						$(".up,.down").click(function(){
							var row = $(this).parents("tr:first");
							currentValue = row.children("td").eq(1).text();
							prevRow = row.prev("tr").children("td").eq(1).text();
							nextRow = row.next("tr").children("td").eq(1).text();
							if ($(this).is(".up")) {
								row.insertBefore(row.prev());
								changeurut(currentValue,prevRow,'up');
							} else {
								row.insertAfter(row.next());
								changeurut(currentValue,nextRow,'down');
							}
						});
					});

					// leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function changeurut(kode,nextkode,updown){
	param  = '';
	param += '&updown=' + updown;
	param += '&kode=' + kode;
	param += '&nextkode=' + nextkode;
	param += '&method=changeurut';
	
	tujuan = 'master_grading_slave.php';
	
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getvalstt(no){
	kode=document.getElementById('kodeform_'+no).innerHTML;
	actstt = document.getElementById('actstt_'+no);
	if(actstt.checked == true){
		chk='1';
	}else{
		chk='0';
	}
	
	param  = '';
	param += '&status=' + chk;
	param += '&kode=' + kode;
	param += '&method=getvalstt';
	
	tujuan = 'master_grading_slave.php';
	
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.set('notifier','position', 'top-right');
					alertify.success('Success').delay(3);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getvaltipe(no){
	kode=document.getElementById('kodeform_'+no).innerHTML;
	actipe = document.getElementById('actipe_'+no);
	if(actipe.checked == true){
		chk='1';
	}else{
		chk='0';
	}
	
	param  = '';
	param += '&status=' + chk;
	param += '&kode=' + kode;
	param += '&method=getvaltipe';
	
	tujuan = 'master_grading_slave.php';
	
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.set('notifier','position', 'top-right');
					alertify.success('Success').delay(3);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}