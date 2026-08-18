function select2(){
	a = document.getElementById('noakunx2').value;
	b = document.getElementById('noakun22').value;
	
	alert("noakunx2: "+a+", noakun22: "+b);
}

function setvalselect2(){
	// $('#noakun22').val('1160303').trigger('change');
	// $('#noakunx2').val('1160303').trigger('change');
	
	setValue2('noakun22','');
}

function clearselect2(){
	$('#noakun22').val(null).trigger('change');
	$('#noakunx2').val(null).trigger('change');
}
function del(kodeorg){

	param = 'method=delete';
	param += '&kodeorg=' + kodeorg;
	
	
	tujuan='setup_slave_blokx.php';
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
	method  = document.getElementById('method').value;
	
	validate([
        ["kodeorg","Kebun tidak boleh kosong."],
        ["divisi","Divisi tidak boleh kosong"],
        ["blok","Blok tidak boleh kosong"]
	]);
	if(getValue('statusblok') == 'TM'){
		if(getValue('tahunmulaipanen') == '' || getValue('tahunmulaipanen') == '0'){
			alertify.alert('Informasi','Tahun mulai panen harus diisi apabila status blok TM.');
			return false;
		}
		if(getValue('bulanmulaipanen') == '' || getValue('bulanmulaipanen') == '0'){
			alertify.alert('Informasi','Bulan mulai panen harus diisi apabila status blok TM.');
			return false;
		}
	}
	param  = '';
	param += '&kodeorg=' + getValue('kodeorg');
	param += '&divisi=' + getValue('divisi');
	param += '&blok=' + getValue('blok');
	param += '&tahuntanam=' + getValue('tahuntanam');
	param += '&luas=' + getValue('luas');
	param += '&pokok=' + getValue('pokok');
	param += '&statusblok=' + getValue('statusblok');
	param += '&tahunmulaipanen=' + getValue('tahunmulaipanen');
	param += '&bulanmulaipanen=' + getValue('bulanmulaipanen');
	param += '&kodetanah=' + getValue('kodetanah');
	param += '&klasifikasitanah=' + getValue('klasifikasitanah');
	param += '&topografi=' + getValue('topografi');
	param += '&intiplasma=' + getValue('intiplasma');
	param += '&jenisbibit=' + getValue('jenisbibit');
	param += '&luasareanonproduktif=' + getValue('luasareanonproduktif');
	param += '&cadangan=' + getValue('cadangan');
	param += '&konservasi=' + getValue('konservasi');
	param += '&okupasi=' + getValue('okupasi');
	param += '&rendahan=' + getValue('rendahan');
	param += '&sungai=' + getValue('sungai');
	param += '&rumah=' + getValue('rumah');
	param += '&kantor=' + getValue('kantor');
	param += '&pabrik=' + getValue('pabrik');
	param += '&jalan=' + getValue('jalan');
	param += '&kolam=' + getValue('kolam');
	param += '&umum=' + getValue('umum');
	param += '&arealberbatu=' + getValue('arealberbatu');
	param += '&enclave=' + getValue('enclave');
	param += '&method=' + getValue('method');
	param += '&lc=' + getValue('lc');
	param += '&status=' + getValue('status');
	param += '&basisbuah=' + getValue('basisbuah');
	param += '&luasbloking=' + getValue('luasbloking');
	param += '&blokold=' + getValue('blokold');
	param += '&indukblok=' + getValue('indukblok');
	param += '&method=' + method;
	
	tujuan = 'setup_slave_blokx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.set('notifier','position', 'top-center');
					alertify.success("Done.",'3');
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getblok(indukblok){
	param  = '';
	param += '&indukblok=' + indukblok;
	param += '&method=getblok';
	
	tujuan = 'setup_slave_blokx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('blok').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getindukblok(divisi){
	param  = '';
	param += '&divisi=' + divisi;
	param += '&method=getindukblok';
	
	tujuan = 'setup_slave_blokx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('indukblok').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function loaddata() {
	//cari= trim(document.getElementById('cari').value);

    param = 'method=loaddata';
    //param += '&cari=' + cari;
    tujuan = 'setup_slave_blokx.php';
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
							responsive: true,
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
							"iDisplayLength": 25,
							// tinggi / height
							scrollY: '60vh',
							scrollX: true,
							scrollCollapse: true,
							dom: 'Blfrtip',
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
					$('select[name*="mytable_length"]').attr("style", "height:30px;");
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
	
	tujuan = 'setup_slave_blokx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'title':jenis,'message':"<center>"+con.responseText+"</center>"}).resizeTo('1000px','85%').show();
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
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

function editdata(jenis,kodeorg,divisi,blok,tahuntanam,indukblok,luasareaproduktif,jumlahpokok,statusblok,kodetanah,klasifikasitanah,topografi,intiplasma,jenisbibit,luasareanonproduktif,cadangan,okupasi,rendahan,sungai,rumah,kantor,pabrik,jalan,kolam,umum,arealberbatu,konservasi,enclave,stat,lc,tahunmulaipanen,bulanmulaipanen,blokold,basisbuah,luasbloking){
	param  = '';
	param += '&jenis=' + jenis;	
	param += '&mode=update';
	param += '&method=addnew';
	
	tujuan = 'setup_slave_blokx.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'title':jenis,'message':"<center>"+con.responseText+"</center>"}).resizeTo('1000px','85%').show();
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
					
					setValue2('kodeorg',kodeorg);
					setTimeout(function(){
						setValue2('divisi',divisi);
					setTimeout(function(){
						setValue2('indukblok',indukblok);
						setTimeout(function(){
							setValue2('blok',blok);
							setTimeout(function(){
								setValue2('tahuntanam',tahuntanam);
								setValue2('luas',luasareaproduktif);
								setValue2('pokok',jumlahpokok);
								setValue2('statusblok',statusblok);
								setValue2('kodetanah',kodetanah);
								setValue2('klasifikasitanah',klasifikasitanah);
								setValue2('topografi',topografi);
								setValue2('intiplasma',intiplasma);
								setValue2('jenisbibit',jenisbibit);
								setValue2('luasareanonproduktif',luasareanonproduktif);
								setValue2('cadangan',cadangan);
								setValue2('konservasi',konservasi);
								setValue2('okupasi',okupasi);
								setValue2('rendahan',rendahan);
								setValue2('sungai',sungai);
								setValue2('rumah',rumah);
								setValue2('kantor',kantor);
								setValue2('pabrik',pabrik);
								setValue2('jalan',jalan);
								setValue2('kolam',kolam);
								setValue2('umum',umum);
								setValue2('arealberbatu',arealberbatu);
								setValue2('enclave',enclave);
								setValue2('status',stat);
								setValue2('lc',lc);
								setValue2('tahunmulaipanen',tahunmulaipanen);
								setValue2('bulanmulaipanen',bulanmulaipanen);
								setValue2('basisbuah',basisbuah);
								setValue2('luasbloking',luasbloking);
								setValue2('blokold',blokold);
								setValue2('method','update');
								document.getElementById('kodeorg').disabled=true;
								document.getElementById('divisi').disabled=true;
								document.getElementById('blok').disabled=true;
								document.getElementById('indukblok').disabled=true;
								//document.getElementById('statusblok').disabled=true;
								
							}, 250);
						}, 250);
						}, 250);
					}, 250);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function gettotalnonprd(){
	gp=0;
	n = document.getElementsByName("nonprd[]");
	for (i = 0; i < n.length; i++){
		a = n[i].value; 
		if(a==''){a=0;}else{a=remove_comma_var(a);}
		gp = gp+parseFloat(a);
	}
	
	document.getElementById('luasareanonproduktif').value=gp;
}