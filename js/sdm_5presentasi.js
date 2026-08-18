function loaddata() {
	param = 'method=loaddata';
    tujuan = 'sdm_slave_5presentasi.php';
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
	
	tujuan = 'sdm_slave_5presentasi.php';
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

function deletedata(id) {
    param = 'method=deletedata';
    param += '&id=' + id;
    tujuan = 'sdm_slave_5presentasi.php';
    alertify.confirm("Informasi","Anda yakin ???",
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

function editdata(jenis,id,tahun,tipe,nourut,text,tipenilai,kodetipenilai,bobot,nouruttotal,totaloperator,kodetotaloperator){
	param  = '';
	param += '&mode=update';
	param += '&method=addnew';
	
	tujuan = 'sdm_slave_5presentasi.php';
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
					
					setValue2('id',id);
					setValue2('tahun',tahun);
					setValue2('tipe',tipe);
					setValue2('nourut',nourut);
					setValue2('text',text);
					setValue2('tipenilai',tipenilai);
					setValue2('kodetipenilai',kodetipenilai);
					setValue2('bobot',bobot);
					setValue2('nouruttotal',nouruttotal);
					setValue2('totaloperator',totaloperator);
					setValue2('kodetotaloperator',kodetotaloperator);
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
	tahun=document.getElementById('tahun').value;
	tipe=document.getElementById('tipe').value;
	nourut=document.getElementById('nourut').value;
	text=document.getElementById('text').value;
	tipenilai=document.getElementById('tipenilai').value;
	kodetipenilai=document.getElementById('kodetipenilai').value;
	bobot=document.getElementById('bobot').value;
	nouruttotal=document.getElementById('nouruttotal').value;
	totaloperator=document.getElementById('totaloperator').value;
	kodetotaloperator=document.getElementById('kodetotaloperator').value;
	method=document.getElementById('method').value;
	id=document.getElementById('id').value;
	
	validate([
        ["tahun",bahasa.tahun+" "+bahasa.tahun],
        ["tipe",bahasa.tipe+" "+bahasa.tipe],
        ["nourut",bahasa.nourut+" "+bahasa.nourut],
        ["text",bahasa.nourut+" "+bahasa.text]
	]);
	
	param  = '';
	param += '&tahun=' + tahun;
	param += '&tipe=' + tipe;
	param += '&nourut=' + nourut;
	param += '&text=' + text;
	param += '&tipenilai=' + tipenilai;
	param += '&kodetipenilai=' + kodetipenilai;
	param += '&nouruttotal=' + nouruttotal;
	param += '&totaloperator=' + totaloperator;
	param += '&kodetotaloperator=' + kodetotaloperator;
	param += '&id=' + id;
	param += '&method=' + method;
	
	tujuan = 'sdm_slave_5presentasi.php';
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