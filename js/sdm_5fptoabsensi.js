function getnoakun(sumber, kodeorg, subbagian, noakun, stat){
	if(kodeorg==undefined){		
		kodeorg  = document.getElementById('kodeorg').value;
	}
	if(subbagian==undefined){		
		subbagian= document.getElementById('subbagian').value;
	}
	if(noakun==undefined){		
		noakun   = document.getElementById('noakun').value;
	}
	if(stat==undefined){		
		stat     = document.getElementById('status').value;
	}
	
	param = 'method=getnoakun';
	param += '&kodeorg=' + kodeorg;
	param += '&subbagian=' + subbagian;
	param += '&noakun=' + noakun;
	param += '&status=' + stat;
	
	tujuan='sdm_slave_5fptoabsensi.php';
	post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					data=con.responseText.split("####");
					document.getElementById('noakun').innerHTML = data[0];
					document.getElementById('status').innerHTML = data[1];
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getsubbagian(sumber, kodeorg, subbagian, noakun, stat){
	if(kodeorg==undefined){		
		kodeorg  = document.getElementById('kodeorg').value;
	}
	if(subbagian==undefined){		
		subbagian= document.getElementById('subbagian').value;
	}
	if(noakun==undefined){		
		noakun   = document.getElementById('noakun').value;
	}
	if(stat==undefined){		
		stat     = document.getElementById('status').value;
	}
	
	param = 'method=getsubbagian';
	param += '&kodeorg=' + kodeorg;
	param += '&subbagian=' + subbagian;
	param += '&noakun=' + noakun;
	param += '&status=' + stat;
	
	tujuan='sdm_slave_5fptoabsensi.php';
	post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					if(sumber=='kodeorg'){
						document.getElementById('subbagian').innerHTML = con.responseText;
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
	
	tujuan='sdm_slave_5fptoabsensi.php';
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


function simpan(){
	id       = document.getElementById('id').value;
	kodeorg  = document.getElementById('kodeorg').value;
	subbagian= document.getElementById('subbagian').value;
	noakun   = document.getElementById('noakun').value;
	status   = document.getElementById('status').value;
	method   = document.getElementById('method').value;
	
	validate([
        ["kodeorg","Kode Orga tidak boleh kosong"]
	]);
	
	param  = '';
	param += '&id=' + id;
	param += '&kodeorg=' + kodeorg;
	param += '&subbagian=' + subbagian;
	param += '&noakun=' + noakun;
	param += '&status=' + status;
	param += '&method=' + method;
	
	tujuan = 'sdm_slave_5fptoabsensi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(method=='update'){
						alertify.popup().destroy();
					}
					alertify.alert("Done");
					document.getElementById('method').value='insert';
					document.getElementById('id').value='';
					loaddata();
					alertify.popup().destroy();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddata() {
    param = 'method=loaddata';
    tujuan = 'sdm_slave_5fptoabsensi.php';
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
							ordering: false,
							fixedHeader: true,
							paging: true,
							"iDisplayLength": 10,
							scrollY: '60vh',
							scrollX: true,
							scrollCollapse: true,
							dom: 'Bfrtip',
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
	
	tujuan = 'sdm_slave_5fptoabsensi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('800px','400px');
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

function editdata(jenis,kodeorg,subbagian,stat,noakun,id){
	param  = '';
	param += '&mode=update';
	param += '&method=addnew';
	
	tujuan = 'sdm_slave_5fptoabsensi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('800px','400px');
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
					setValue2('kodeorg',kodeorg);
					setValue2('subbagian',subbagian);
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
