function getKrwyn(lksiTgs,krywnId)
{
	if((lksiTgs=='')&&(krywnId==''))
	{
		kdOrg=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
		param='kodeOrg='+kdOrg+'&proses=getKrywan';
	}
	else
	{
		kdOrg=lksiTgs;
		kdKry=krywnId;
		param='kodeOrg='+kdOrg+'&proses=getKrywan'+'&kdKry='+kdKry;
	}
	tujuan='vhc_slave_5operator.php';
	
	post_response_text(tujuan, param, respog);
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
				}
				else {
					document.getElementById('kd_karyawan').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}  	
}

function simpan()
{
	kd_kary=document.getElementById('kd_karyawan').options[document.getElementById('kd_karyawan').selectedIndex].value;
	kdVhc=document.getElementById('kdVhc').options[document.getElementById('kdVhc').selectedIndex].value;
	statu=document.getElementById('status').options[document.getElementById('status').selectedIndex].value;
	jabatan=document.getElementById('jabatan').options[document.getElementById('jabatan').selectedIndex].value;
	sim=document.getElementById('sim').value;
	vhclama=document.getElementById('kdVhclama').value;
	insert=document.getElementById('proses').value;

	validate([
        ["kd_karyawan","Karyawan harus dipilih."],
        ["kdVhc","Kode kendaraan harus dipilih"],
        ["jabatan","Jabatan harus dipilih"],
        ["status","Status harus dipilih"]
    ]);
    
	param='kdKry='+kd_kary+'&status='+statu+'&sim='+sim+'&jabatan='+jabatan+'&proses='+insert+'&kdVhc='+kdVhc+'&vhclama='+vhclama;

	tujuan='vhc_slave_5operator.php';
	post_response_text(tujuan, param, respog);
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
				}
				else {
					document.getElementById('proses').value='insert';
					document.getElementById('kd_karyawan').disabled=false;
					document.getElementById('kdVhc').disabled=false;
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

function loaddata()
{
	param='proses=loaddata';
	tujuan='vhc_slave_5operator.php';
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
	
	tujuan = 'vhc_slave_5operator.php';
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

function editdata(jenis,krywn_id,jabatan,stat,vhc,sim)
{
	param  = '';
	param += '&jenis=' + jenis;
	param += '&proses=addnew';
	
	tujuan = 'vhc_slave_5operator.php';
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
					setValue2('kd_karyawan',krywn_id);
					setValue2('kdVhc',vhc);
					setValue2('jabatan',jabatan);
					setValue2('jabatanlama',jabatan);
					setValue2('status',stat);
					// document.getElementById('kdVhclama').value=vhc;
					document.getElementById('sim').value=sim;
					document.getElementById('kd_karyawan').disabled=true;
					document.getElementById('kdVhc').disabled=true;
					document.getElementById('proses').value='update';
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function batalOpt()
{
    document.getElementById('kd_karyawan').disabled=false;
    document.getElementById('kdVhc').disabled=false;
	setValue2('kd_karyawan',null);
	setValue2('kdVhc',null);
	setValue2('status',null);
	setValue2('jabatan',null);
	setValue2('jabatanlama',null);
    document.getElementById('sim').value='';
    document.getElementById('proses').value='insert';
}

function getnosim(){
	kd_kary=document.getElementById('kd_karyawan').options[document.getElementById('kd_karyawan').selectedIndex].value;
	param='kdKry='+kd_kary+'&proses=getnosim';
	tujuan='vhc_slave_5operator.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
				if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					document.getElementById('sim').value = con.responseText;
				}
			}
			else 
			{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}

function delOpt(noKry,vhc)
{
	nokry=noKry;
	param='kdKry='+nokry+'&kdVhc='+vhc+'&proses=deleteKry';
	tujuan='vhc_slave_5operator.php';
			
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					loaddata();
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	
	alertify.confirm("Delete","Anda yakin ?",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	);
}