function getKegiatan() {
	pt=document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;


    param = 'proses=getKegiatan'+'&pt='+pt;
    tujuan = 'vhc_slave_5kegiatanmandor.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert('Info',con.responseText);
                } else {
					alertify.popup2("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('40%','70%'); 
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function addKegiatan(row){
	i = document.getElementsByName("kodekegiatan[]");
	e = document.getElementsByName("check[]");
	data="";
	for(n=0;n<e.length;n++){
		if(e[n].checked==true){
			data+=i[n].innerHTML+",";
		}
	}
	document.getElementById('kodekegiatan').value = data.substr(0,data.length-1);
	alertify.popup2().destroy();
}


function simpan()
{
	pt=document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
    kodekegiatan  = trim(document.getElementById('kodekegiatan').value);
    rupiah  = trim(document.getElementById('rupiah').value);
	status_aktif=document.getElementById('status_aktif').options[document.getElementById('status_aktif').selectedIndex].value;
	insert=document.getElementById('proses').value;

	validate([
        ["pt","PT harus dipilih."],
        ["kodekegiatan","Kode kegiatan harus dipilih"],
        ["rupiah","Rupiah harus diisi"],
        ["status_aktif","Status harus dipilih"]
    ]);
    
	param='pt='+pt+'&kodekegiatan='+kodekegiatan+'&status_aktif='+status_aktif+'&proses='+insert+'&rupiah='+rupiah;

	tujuan='vhc_slave_5kegiatanmandor.php';
	post_response_text(tujuan, param, respog);
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert('Informasi : '+con.responseText);
				}
				else {
					document.getElementById('proses').value='insert';
					document.getElementById('pt').disabled=false;
					document.getElementById('kodekegiatan').disabled=false;
					document.getElementById('rupiah').disabled=false;
					document.getElementById('status_aktif').disabled=false;
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
	tujuan='vhc_slave_5kegiatanmandor.php';
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
	
	tujuan = 'vhc_slave_5kegiatanmandor.php';
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

function editdata(jenis,pt,kodekegiatan,rupiah,aktif)
{
	param  = '';
	param += '&jenis=' + jenis;
	param += '&proses=addnew';
	
	tujuan = 'vhc_slave_5kegiatanmandor.php';
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
					setValue2('pt',pt);
					setValue2('kodekegiatan',kodekegiatan);
					setValue2('rupiah',rupiah);
					setValue2('status_aktif',aktif);
					document.getElementById('pt').disabled=true;

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
	setValue2('pt',null);
	setValue2('kodekegiatan',null);
	setValue2('rupiah',null);
	setValue2('status_aktif',null);

    document.getElementById('proses').value='insert';
}
