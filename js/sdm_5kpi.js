function del(id){
	param = 'method=delete';
	param += '&id=' + id;
	
	tujuan='sdm_slave_5kpi.php';
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
					getPage();
					
					// var table = $('#mytable').DataTable();
					// var info = table.page.info();
					// loaddata(info.page);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getinduk(){
	tahun       = document.getElementById('tahun').value;
	jabatan     = document.getElementById('jabatan').value;
	karyawanid     = document.getElementById('karyawanid').value;
	kodeorg     = document.getElementById('kodeorg').value;
	jenis     = document.getElementById('jenis').value;
	
	validate([
        ["tahun","Tahun tidak boleh kosong"],
        ["kodeorg","kodeorg tidak boleh kosong"],
        ["jenis","jenis tidak boleh kosong"]
	]);

	param = 'method=getinduk';
	param += '&tahun=' + tahun;
	param += '&jabatan=' + jabatan;
	param += '&kodeorg=' + kodeorg;
	param += '&karyawanid=' + karyawanid;
	tujuan='sdm_slave_5kpi.php';
	
	if(jenis==2 || jenis==1){
		post_response_text(tujuan, param, respog);
	}
	
	
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					document.getElementById('induk').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getskala(){
	tipepenilaian = document.getElementById('tipepenilaian').value;

	param = 'method=getskala';
	param += '&tipepenilaian=' + tipepenilaian;
	tujuan='sdm_slave_5kpi.php';
	
	if(tipepenilaian==1){
		post_response_text(tujuan, param, respog);
	}
	
	
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					document.getElementById('skalapenilaian').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function simpan(){
	idkpi       = document.getElementById('idkpi').value;
	tahun       = document.getElementById('tahun').value;
	jabatan     = document.getElementById('jabatan').value;
	karyawanid     = document.getElementById('karyawanid').value;
	dept        = document.getElementById('dept').value;
	kpi         = document.getElementById('kpi').value;
	bobot       = document.getElementById('bobot').value;
	target 		= document.getElementById('target').value;
	jenis  	= document.getElementById('jenis').value;
	induk  	= document.getElementById('induk').value;
	tipepenilaian  	= document.getElementById('tipepenilaian').value;
	skalapenilaian  	= document.getElementById('skalapenilaian').value;
	method      = document.getElementById('method').value;
	kodeorg     = document.getElementById('kodeorg').value;
	divisi      = document.getElementById('divisi').value;
	
	if(jenis=='0' || jenis=='1')
	{

		validate([
	        ["tahun","Tahun tidak boleh kosong"],
	        ["kpi","KPI tidak boleh kosong"],
	        ["bobot","Bobot tidak boleh kosong"],
	        ["kodeorg","kodeorg tidak boleh kosong"],
	        ["jenis","Jenis tidak boleh kosong"]
		]);
	
	}else{
		validate([
	        ["tahun","Tahun tidak boleh kosong"],
	        ["kpi","KPI tidak boleh kosong"],
	        ["bobot","Bobot tidak boleh kosong"],
	        ["kodeorg","kodeorg tidak boleh kosong"],
	        ["target","target tidak boleh kosong"],
	        ["jenis","Jenis tidak boleh kosong"]
		]);
	}

	param  = '';
	param += '&idkpi=' + idkpi;
	param += '&tahun=' + tahun;
	param += '&jabatan=' + jabatan;
	param += '&dept=' + dept;
	param += '&kpi=' + kpi;
	param += '&bobot=' + bobot;
	param += '&target=' + target;
	param += '&method=' + method;
	param += '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi;
	param += '&karyawanid=' + karyawanid;
	param += '&jenis=' + jenis;
	param += '&induk=' + induk;
	param += '&tipepenilaian=' + tipepenilaian;
	param += '&skalapenilaian=' + skalapenilaian;
	
	tujuan = 'sdm_slave_5kpi.php';
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
					document.getElementById('idkpi').value='';
					getPage();
					// var table = $('#mytable').DataTable();
					// var info = table.page.info();
					// loaddata(info.page);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata2(paged);	
}

function loaddata2(page,tipe){
	cari=document.getElementById('cari').value;
	
	param = 'method=loaddata&page=' + page+'&cari='+cari+'&tipe='+tipe;
    tujuan = 'sdm_slave_5kpi.php';
	if(tipe=='excel'){
		e = tujuan+'?'+param;
		printnopopup(e);
	}else{		
		post_response_text(tujuan, param, respog);
		function respog(){
			if (con.readyState == 4){
				if (con.status == 200){
					busy_off();
					if (!isSaveResponse(con.responseText)){
						alertify.alert(con.responseText);
					} else {
						isdt = con.responseText.split("####");
						document.getElementById('output').innerHTML = isdt[0];
						document.getElementById('footData').innerHTML = isdt[1];
						leftFixedTable();
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}
}


function loaddata(curentpage) {
	//cari= trim(document.getElementById('cari').value);

    param = 'method=loaddata';
    //param += '&cari=' + cari;
    tujuan = 'sdm_slave_5kpi.php';
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
					if(curentpage>0){						
						var table = $('#mytable').DataTable();
							table.page(parseFloat(curentpage)).draw( false );
					}
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
	
	tujuan = 'sdm_slave_5kpi.php';
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

function uploadfile(){
				let xhttp = new XMLHttpRequest();
                let formData = new FormData();
                const file = document.getElementById('filex').files[0];
                const pembatas = document.getElementById('pembatas').value;
                formData.append('files', file);
                formData.append('pembatas', pembatas);
                formData.append('method', 'uploadfile');
                const param = 'method=uploadfile&files='+file+'&pembatas='+pembatas;
                xhttp.open("POST", "sdm_slave_5kpi.php?"+param, true);
				//xhttp.onreadystatechange = eval(respog);
                xhttp.send(formData);
                document.getElementById('butupload').disabled=true;
   				xhttp.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                          let response = this.responseText;
                          console.log(response);
                          alertify.popup().destroy();
                          alertify.alert('Notifikasi',response);
                          loaddata(0);
                        }
                }
}
function uploaddata(){

	param  = '';
	param += '&method=uploaddata';
	
	tujuan = 'sdm_slave_5kpi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup('Upload Data',"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('55%','70%');
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

function editdata(jenis,tahun,jabatan,dept,kpi,bobot,target,id,kodeorg,tipelokasitugasuser,bagianuser,karyawanid,jenis,tipepenilaian){
	param  = '';
	param += '&mode=update';
	param += '&method=addnew';
	param += '&id='+id;
	
	tujuan = 'sdm_slave_5kpi.php';
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
					
					// x=0;
					// if(x==0){
						setValue2('idkpi',id);
						setValue2('tahun',tahun);
						setValue2('jabatan',jabatan);
						setValue2('dept',dept);
						//setValue2('kpi',kpi);
						setValue2('bobot',bobot);
						setValue2('target',target);
						setValue2('kodeorg',kodeorg);
						setValue2('jenis',jenis);
						setValue2('tipepenilaian',tipepenilaian);
						setValue2('method','update');
						document.getElementById('induk').innerHTML=document.getElementById('optindukx'+id).innerHTML;
						document.getElementById('skalapenilaian').innerHTML=document.getElementById('optskalax'+id).innerHTML;
						document.getElementById('tahun').disabled=true;
						document.getElementById('kodeorg').disabled=true;
						document.getElementById('jabatan').disabled=true;
						document.getElementById('karyawanid').disabled=true;

						//x=1;
						//alert('xx'+x);
					//}
					// if(x==1) {
					// 	alert(x);
					if(karyawanid!=undefined){
						getkarid(karyawanid);
					}
					// }
					
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function formajukan(karyawanid,tahun){
    param = 'method=formajukan';
    param += '&karyawanid=' + karyawanid;
    param += '&tahun=' + tahun;
    tujuan = 'sdm_slave_5kpi.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup().destroy();
                    alertify.popup("Ajukan ?","<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('300px','230px');
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

function ajukan(){
    notransaksi     =document.getElementById('notransaksi_ajukan').value;
    tahun     =document.getElementById('notransaksi_ajukan2').value;
    jlh         =document.getElementById('jlh').value;
    var param   = 'method=ajukan';
    param       += '&notransaksi=' + notransaksi;
    param       += '&tahun=' + tahun;
    param       += '&jlh=' + jlh;
    for (i = 1; i <= jlh; i++) {
        param += "&" + 'kepada'+ i + "=" + document.getElementById('kepada'+i).value;
    }
    if(jlh==0){
        alertify.alert("Warning: Approval kosong");
        return;
    }
    tujuan = 'sdm_slave_5kpi.php';
    closeDialog();
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                	alertify.popup().destroy();
                    alert('Sucses');
                    loaddata(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getdivisi(kodeorg,divisi){
	param  = '';
	param += '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi;
	param += '&method=getdivisi';
	
	tujuan = 'sdm_slave_5kpi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('divisi').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getkarid(karyawanid){
	jabatan     = document.getElementById('jabatan').value;
	kodeorg     = document.getElementById('kodeorg').value;
	//alert(karyawanid);
	param  = '';
	param += '&jabatan=' + jabatan;
	param += '&kodeorg=' + kodeorg;
	param += '&karyawanid=' + karyawanid;
	param += '&method=getkarid';
	
	tujuan = 'sdm_slave_5kpi.php';
	post_response_text(tujuan, param, respog);
	
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);

					setValue2('jabatan',"");
				} else {
					document.getElementById('karyawanid').innerHTML=con.responseText;
					setValue2('karyawanid',karyawanid);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
