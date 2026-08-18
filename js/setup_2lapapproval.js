function postPreview(val) {
	param = '';
	var e = val.split('##');
	for (i = 1; i < e.length; i++) {
		var tmp = document.getElementById(e[i]);
		if (i == 1) {
			param += e[i] + "=" + getValue(e[i]);
		} else {
			param += "&" + e[i] + "=" + getValue(e[i]);
		}
	}
	
	param += '&method=preview';
	
	tujuan = 'setup_slave_2lapapproval.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					
					dt = con.responseText.split("####");
					document.getElementById('output').innerHTML=dt[0];
					
					$(document).ready(function() {
						var table = $('#mytable').DataTable({
							//sumber jika pakai json
							data : JSON.parse(dt[1]),
							// supaya tidak ada overflow horisontal
							responsive: true,
							// fixedColumns:   {
								// leftColumns: 3
								// //rightColumns: 2
							// },
							fixedHeader: true,
							// pake paging atau tidak
							paging: true,
							// ordering: false,
							columnDefs: [
								{"className": "dt-body-center", "targets": [0,5,6]}
								//{className: "dt-body-nowrap",targets:-1}
							],
							// drag kolom
							//colReorder: true,
							// jumlah per page
							"iDisplayLength": 50,
							// tinggi / height
							scrollX: true,
							scrollY: '45vh',
							scrollCollapse: true,
							
							//popup pencarian / filter
							// dom: 'Bfrtip',
							// buttons: [
								// {
									// extend: 'searchPanes',
									// config: {
										// cascadePanes: true
									// }
								// }
							// ]
							//end popup pencarian / filter
							
							//<!--popup pencarian / filter / like sql search-->
							language: {
								searchBuilder: {
									button: 'Filter',
								}
							},
							buttons:[
								'searchBuilder','csv', 'excel', 'print'
							]
							,
							dom: 'Bfrtip'
							
							//select: true
							//buttons: ['colvis']
							
							//tanpa popup
							// dom: 'QBfrtip',
							// buttons:['csv', 'excel', 'print'],
							//tanpa popup
							
							//<!--popup pencarian / filter / like sql search-->
							
						});
						
						//buat nomor urut
						table.on( 'order.dt search.dt', function () {
							table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
								cell.innerHTML = i+1;
							} );
						} ).draw();
						//buat nomor urut
					} );
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}