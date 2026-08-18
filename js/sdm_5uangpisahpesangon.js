function loaddata() {
	param = 'method=loaddata';
    tujuan = 'sdm_slave_5uangpisahpesangon.php';
    post_response_text(tujuan, param, respog);

    function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alertify.alert(con.responseText);
                }else{
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
							buttons: ['searchBuilder','csv', 'excel', 'print',{
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
	
	tujuan = 'sdm_slave_5uangpisahpesangon.php';
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
							dropdownAutoWidth:true,
							width: 'auto'
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
					setValue2('status','1');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editdata(jenis,kode,kodept,jenispesangon,tipekaryawan,masakerjadari,masakerjasampai,pengali,stat){
	param  = '';
	param += '&mode=update';
	param += '&method=addnew';
	
	tujuan = 'sdm_slave_5uangpisahpesangon.php';
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
							dropdownAutoWidth:true,
							width: 'auto'
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
					
					setValue2('kode',kode);
					setValue2('kodept',kodept);
					setValue2('jenispesangon',jenispesangon);
					setValue2('tipekaryawan',tipekaryawan);
					setValue2('masakerjadari',masakerjadari);
					setValue2('masakerjasampai',masakerjasampai);
					setValue2('pengali',pengali);

					setValue2('status',stat);
					setValue2('method','update');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan(){
	kode=document.getElementById('kode').value;
	kodept=document.getElementById('kodept').value;
	jenispesangon=document.getElementById('jenispesangon').value;
	tipekaryawan=document.getElementById('tipekaryawan').value;
	masakerjadari=document.getElementById('masakerjadari').value;
	masakerjasampai=document.getElementById('masakerjasampai').value;
	pengali=document.getElementById('pengali').value;

	stat=document.getElementById('status').value;
	method=document.getElementById('method').value;
	
	validate([
        ["kodept",bahasa.kodept+" "+bahasa.kodept],
        ["jenispesangon",bahasa.jenispesangon+" "+bahasa.jenispesangon],
        ["tipekaryawan",bahasa.tipekaryawan+" "+bahasa.tipekaryawan],
        ["masakerjadari",bahasa.masakerjadari+" "+bahasa.masakerjadari],
        ["masakerjasampai",bahasa.masakerjasampai+" "+bahasa.masakerjasampai],
        ["pengali",bahasa.pengali+" "+bahasa.pengali]
	]);
	
	param  = '';
	param += '&kode=' + kode;
	param += '&kodept=' + kodept;
	param += '&jenispesangon=' + jenispesangon;
	param += '&tipekaryawan=' + tipekaryawan;
	param += '&masakerjadari=' + masakerjadari;
	param += '&masakerjasampai=' + masakerjasampai;
	param += '&pengali=' + pengali;
	param += '&status=' + stat;
	param += '&method=' + method;
	
	tujuan = 'sdm_slave_5uangpisahpesangon.php';
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