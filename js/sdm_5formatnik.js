function loaddata() {
	param = 'method=loaddata';
	tujuan = 'sdm_slave_5formatnik.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('output').innerHTML = con.responseText;
					$(document).ready(function () {
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
							paging: false,
							// "pagingType": "simple_numbers",
							// columnDefs: [
							// {"className": "dt-body-nowrap", "targets": [0,1,2,3,4,5,6,7,8,9,10,11,12,13]}
							// ],
							// drag kolom
							//colReorder: true,
							// jumlah per page
							// "iDisplayLength": 1,
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
							buttons: ['searchBuilder', 'csv', 'excel', 'print', {
								text: 'New',
								action: function () {
									newdata('New Data');
								}
							}
							]
						});

						//double click untuk freeze column
						$(table.table().container()).on('dblclick', 'td', function () {
							var row = table.column(this);
							new $.fn.dataTable.FixedColumns(table, {
								leftColumns: row.index() + 1
								//   rightColumns: 1
							});
							//console.log('Row Index = ' + row.index());
						});

						//right click untuk freeze column
						$(table.table().container()).on('dblclick', 'th', function () {
							var row = table.column(this);
							new $.fn.dataTable.FixedColumns(table, {
								leftColumns: row.index() + 1
							});
							//console.log('Row Index = ' + row.index());
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

function newdata(jenis) {
	param = '';
	param += '&jenis=' + jenis;
	param += '&method=addnew';

	tujuan = 'sdm_slave_5formatnik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis, "<center>" + con.responseText + "</center>").set({ 'resizable': true, 'maximizable': false }).resizeTo('50%', '50%');
					$(document).ready(function () {
						$('.select2').select2({
							dropdownAutoWidth: true
						});
						$('.select2-selection--single').height(30).css({
							cursor: "auto"
						});
						$('.select2-selection__arrow b').css({
							top: "70%"
						});
						$('.select2-selection__rendered').css({
							'line-height': '30px'
						});
					});
					setValue2('status', '1');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getTipekaryawan() {
	param = 'method=getTipekaryawan';
	tujuan = 'sdm_slave_5formatnik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Info', con.responseText);
				} else {
					alertify.popup2("Detail", con.responseText).set({ 'resizable': true, 'maximizable': true }).resizeTo('40%', '70%');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function addtipekaryawan(row) {
	i = document.getElementsByName("id_tipekar[]");
	e = document.getElementsByName("check[]");
	data = "";
	for (n = 0; n < e.length; n++) {
		if (e[n].checked == true) {
			data += i[n].innerHTML + ",";
		}
	}
	document.getElementById('tipekaryawan').value = data.substr(0, data.length - 1);
	alertify.popup2().destroy();
}

function editdata(jenis, id, kodept, tipekaryawan, jumlahcounter, counter, flag) {
	param = '';
	param += '&mode=update';
	param += '&method=addnew';

	tujuan = 'sdm_slave_5formatnik.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis, "<center>" + con.responseText + "</center>").set({ 'resizable': true, 'maximizable': false }).resizeTo('55%', '70%');
					$(document).ready(function () {
						$('.select2').select2({
							dropdownAutoWidth: true,
							// width: 'auto'
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

					setValue2('kodept', kodept);
					setValue('tipekaryawan', tipekaryawan);
					setValue('jumlahcounter', jumlahcounter);
					setValue('counter', counter);
					setValue('id', id);
					setValue2('method', 'update');
					document.getElementById('kodept').disabled = true;

					// if(flag == 1){
					// 	document.getElementById('tipekaryawan').disabled=true;
					// }else{
					// 	document.getElementById('tipekaryawan').disabled=false;
					// }

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan() {
	kodept = document.getElementById('kodept').value;
	tipekaryawan = document.getElementById('tipekaryawan').value;
	jumlahcounter = document.getElementById('jumlahcounter').value;
	counter = document.getElementById('counter').value;
	tmk = document.getElementById('tmk').value;
	tglbulan = document.getElementById('tglbulan').value;
	id = document.getElementById('id').value;

	method = document.getElementById('method').value;

	// validate([
	//     ["kodept",bahasa.kodept+" "+bahasa.kodept],
	//     ["tipekaryawan",bahasa.tipekaryawan+" "+bahasa.tipekaryawan],
	//     ["jumlahcounter",bahasa.jumlahcounter+" "+bahasa.jumlahcounter],
	//     ["tmk",bahasa.tmk+" "+bahasa.tmk],
	// ]);

	param = '';
	param += '&kodept=' + kodept;
	param += '&tipekaryawan=' + tipekaryawan;
	param += '&jumlahcounter=' + jumlahcounter;
	param += '&counter=' + counter;
	param += '&tmk=' + tmk;
	param += '&tglbulan=' + tglbulan;
	param += '&id=' + id;
	param += '&method=' + method;

	tujuan = 'sdm_slave_5formatnik.php';
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