
function loaddata() {
	param='method=loaddata';
	tujuan='vhc_slave_5premikegiatan.php';
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
							"iDisplayLength": 20,
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

function editdata(jenis,pt,keg,basis,basis2,basis3,premibasis2,premibasis3,premilebihbasis,vhc,jenishari,jenisbasis,posisi,unit,unitname,divisi,penanda,penanda2,penanda3,statuspremi,pengurangprestasi,pengurangprestasi2,pengurangprestasi3,upahkontanan){
    param  = '';
	param += '&jenis=' + jenis;
	param += '&method=addnew';
	
	tujuan = 'vhc_slave_5premikegiatan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':true}).resizeTo('90%','68%');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:false
						});
						$('.select2-selection--single').height(25).css({
							cursor: "auto"
						});
						$('.select2-selection__arrow b').css({
							top: "70%"
						});
						$('.select2-selection__rendered').css({
							'line-height': '28px'
						});
					});
                    
                    setValue2('pt',pt);
                    setValue2('keg',keg);    
                    setValue2('basis',basis);
                    setValue2('basis2',basis2);
                    setValue2('basis3',basis3);
                    setValue2('premilebihbasis',premilebihbasis);
                    setValue2('premibasis2',premibasis2);
                    setValue2('premibasis3',premibasis3);
                    setValue2('vhc',vhc);
                    setValue2('jenishari',jenishari);
                    setValue2('jenisbasis',jenisbasis);
                    setValue2('posisi',posisi);
                    setValue2('penanda',penanda);
                    setValue2('penanda2',penanda2);
                    setValue2('penanda3',penanda3);
                    setValue2('pengurangprestasi',pengurangprestasi);
                    setValue2('pengurangprestasi2',pengurangprestasi2);
                    setValue2('pengurangprestasi3',pengurangprestasi3);
                    setValue2('upahkontanan',upahkontanan);
					if(statuspremi == 1){
						document.getElementById('statuspremi').checked = true;
					}else{
						document.getElementById('statuspremi').checked = false;
					}
                    getkebun(pt,unit);
                    setTimeout(function () {
                        setValue2('unit',unit);
                        getdivisi(unit);
                        setTimeout(function () {
                            setValue2('divisi',divisi);
                        },850);
                    },600);
                    setValue2('method','update');
                    
                    document.getElementById('pt').disabled=true;
                    document.getElementById('unit').disabled=true;
                    document.getElementById('keg').disabled=true;
                    document.getElementById('posisi').disabled=true;
                    document.getElementById('divisi').disabled=true;
                    document.getElementById('vhc').disabled=true;
                    document.getElementById('jenishari').disabled=true;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function del(pt,keg,vhc,posisi,unit,divisi,jenishari){
    param='method=delete'+'&pt='+pt+'&keg='+keg+'&vhc='+vhc+'&posisi='+posisi+'&divisi='+divisi+'&unit='+unit+'&jenishari='+jenishari;
    tujuan='vhc_slave_5premikegiatan.php';
    if(confirm("Apakah anda yakin ingin hapus data?")){
            post_response_text(tujuan, param, respog);	
    }
    function respog(){
        if(con.readyState==4) {
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

function newdata(jenis){
	param  = '';
	param += '&jenis=' + jenis;
	param += '&method=addnew';
	
	tujuan = 'vhc_slave_5premikegiatan.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':true}).resizeTo('90%','68%');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:false
						});
						$('.select2-selection--single').height(25).css({
							cursor: "auto"
						});
						$('.select2-selection__arrow b').css({
							top: "70%"
						});
						$('.select2-selection__rendered').css({
							'line-height': '28px'
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

function getkebun(pt,unit)
{
	if(pt == undefined){
		pt      =document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	}
    param   ='pt='+pt+'&method=getkebun';
	param += '&unit='+unit;
    tujuan  ='vhc_slave_5premikegiatan.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    document.getElementById('unit').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }    
}

function simpan(){
    method          	=document.getElementById('method').value;
    pt              	=document.getElementById('pt').value;
    unit            	=document.getElementById('unit').value;
    divisi          	=document.getElementById('divisi').value;
    penanda 			=document.getElementById('penanda').value;
    penanda2 			=document.getElementById('penanda2').value;
    penanda3 			=document.getElementById('penanda3').value;
	keg             	=document.getElementById('keg').value;
	jenishari       	=document.getElementById('jenishari').value;
	jenisbasis     		=document.getElementById('jenisbasis').value;
	posisi          	=document.getElementById('posisi').value;
	statuspremi   		=document.getElementById('statuspremi').checked;
	basis           	=remove_comma_var(document.getElementById('basis').value);
	basis2          	=remove_comma_var(document.getElementById('basis2').value);
	basis3          	=remove_comma_var(document.getElementById('basis3').value);
	premibasis2     	=remove_comma_var(document.getElementById('premibasis2').value);
	premibasis3     	=remove_comma_var(document.getElementById('premibasis3').value);
	premilebihbasis 	=remove_comma_var(document.getElementById('premilebihbasis').value);
	pengurangprestasi   =remove_comma_var(document.getElementById('pengurangprestasi').value);
	pengurangprestasi2  =remove_comma_var(document.getElementById('pengurangprestasi2').value);
	pengurangprestasi3  =remove_comma_var(document.getElementById('pengurangprestasi3').value);
	upahkontanan  		=remove_comma_var(document.getElementById('upahkontanan').value);
	vhc             	=document.getElementById('vhc').value;
    if(unit=='' || keg=='' || vhc=='' || jenishari==''|| posisi==''||premilebihbasis==''){
        alert('Harap lengkapi form isian Unit, Jenis kendaraan, kode kegiatan, jenis hari, posisi, premi lebih basis');return;
    }
    param='pt='+pt+'&keg='+keg+'&vhc='+vhc+'&divisi='+divisi+'&penanda='+penanda+'&penanda2='+penanda2+'&penanda3='+penanda3;
	param+='&basis='+basis+'&basis2='+basis2+'&basis3='+basis3+'&premibasis2='+premibasis2+'&premibasis3='+premibasis3+'&premilebihbasis='+premilebihbasis+'&jenishari='+jenishari+'&jenisbasis='+jenisbasis+'&posisi='+posisi+'&unit='+unit;
	param+='&pengurangprestasi='+pengurangprestasi+'&pengurangprestasi2='+pengurangprestasi2+'&pengurangprestasi3='+pengurangprestasi3+'&upahkontanan='+upahkontanan;
    param+='&method='+method;
	if(statuspremi == true){
		statuspremi = 1;
	}else{
		statuspremi = 0;
	}
    param+='&statuspremi='+statuspremi;
    tujuan='vhc_slave_5premikegiatan.php';
    post_response_text(tujuan, param, respog);		
    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
                    hapus();
                    alertify.popup().destroy();
                    alertify.alert("Data Berhasil disimpan.");
                    setTimeout(function() {
						loaddata();
					},100);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }
}

function getdivisi(unit) {
    if(unit == undefined){
        unit    =document.getElementById('unit').value;
    }
    param   ='unit='+unit+'&method=getdivisi';
    tujuan  ='vhc_slave_5premikegiatan.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                } else {
                    document.getElementById('divisi').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }    
}

function hapus(){
    document.getElementById('method').value='insert';
    setValue2('pt',null);
    setValue2('keg',null);
    setValue2('vhc',null);
    setValue2('jenishari',null);
    setValue2('posisi',null);
    setValue2('jenisbasis',null);
    setValue2('penanda','');
    setValue2('penanda2','');
    setValue2('penanda3','');
    document.getElementById('pt').disabled=false;
    document.getElementById('unit').disabled=false;
    document.getElementById('divisi').disabled=false;
	document.getElementById('vhc').disabled=false;
	document.getElementById('jenishari').disabled=false;
	document.getElementById('keg').disabled=false;
	document.getElementById('posisi').disabled=false;
	document.getElementById('posisi').value='';
	document.getElementById('basis').value='0';
	document.getElementById('basis2').value='0';
	document.getElementById('basis3').value='0';
	document.getElementById('premibasis2').value='0';
	document.getElementById('premibasis3').value='0';
	document.getElementById('premilebihbasis').value='0';		
	document.getElementById('pengurangprestasi').value='0';
	document.getElementById('pengurangprestasi2').value='0';
	document.getElementById('pengurangprestasi3').value='0';
	document.getElementById('upahkontanan').value='0';
	document.getElementById('divisi').innerHTML='';		
}