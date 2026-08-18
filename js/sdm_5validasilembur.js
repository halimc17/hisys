function del(id){

	param = 'method=delete';
	param += '&id=' + id;
	
	
	tujuan='sdm_5validasilembur_slave.php';
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
	stat    = document.getElementById('status').value;
	method  = document.getElementById('method').value;
	
	validate([
        ["tipeorg","Tipe organisasi tidak boleh kosong."],
        ["kodeorg","Kode organisasi tidak boleh kosong."],
        ["mulaiberlaku","Tanggal berlaku tidak boleh kosong."]
	]);
	
	param  = '';
	param += '&id=' + $('#idx').val();		
	param += '&tipeorg=' + $('#tipeorg').val();		
	param += '&mulaiberlaku=' + $('#mulaiberlaku').val();		
	param += '&kodeorg=' + $('#kodeorg').val();		
	param += '&divisi=' + $('#divisi').val();		
	param += '&jabatan=' + $('#jabatan').val();		
	param += '&tipekaryawan=' + $('#tipekaryawan').val();		
	param += '&karyawanid=' + $('#karyawanid').val();		
	param += '&sehari=' + $('#sehari').val();		
	param += '&seminggu=' + $('#seminggu').val();		
	param += '&hmhb=' + $('#hmhb').val();		
	param += '&persengaji=' + $('#persengaji').val();		
	param += '&status=' + stat;
	param += '&method=' + method;
	
	tujuan = 'sdm_5validasilembur_slave.php';
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

function loaddata() {
	//cari= trim(document.getElementById('cari').value);

    param = 'method=loaddata';
    //param += '&cari=' + cari;
    tujuan = 'sdm_5validasilembur_slave.php';
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
							"iDisplayLength": 25,
							// tinggi / height
							scrollY: '65vh',
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
							buttons: ['searchBuilder', 'excel', 'print',{
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
	
	tujuan = 'sdm_5validasilembur_slave.php';
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
						$('.select2').select2({dropdownAutoWidth:true});
						$('.select2-selection--single').height(30).css({cursor: "auto"});
						$('.select2-selection__arrow b').css({top: "70%"});
						$('.select2-selection__rendered').css({'line-height': '31px'});
					});
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editdata(jenis,id,tipeorg,kodeorg,divisi,tipekaryawan,jabatan,karyawanid,mulaiberlaku,stat, sehari, seminggu, hmhb, persengaji){
	param  = '';
	param += '&id=' + id;	
	param += '&jenis=' + jenis;	
	param += '&tipeorg=' + tipeorg;
	param += '&kodeorg=' + kodeorg;
	param += '&divisi=' + divisi;
	param += '&tipekaryawan=' + tipekaryawan;
	param += '&jabatan=' + jabatan;
	param += '&karyawanid=' + karyawanid;
	param += '&mulaiberlaku=' + mulaiberlaku;
	param += '&status=' + stat;
	param += '&sehari=' + sehari;
	param += '&seminggu=' + seminggu;
	param += '&hmhb=' + hmhb;
	param += '&persengaji=' + persengaji;
	param += '&mode=update';
	param += '&method=addnew';
	
	tujuan = 'sdm_5validasilembur_slave.php';
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
						$('.select2').select2({dropdownAutoWidth:true});
						$('.select2-selection--single').height(30).css({cursor: "auto"});
						$('.select2-selection__arrow b').css({top: "70%"});
						$('.select2-selection__rendered').css({'line-height': '31px'});
					});
					
					setValue2('method','update');
					setValue2('idx',id);
					setValue2('tipeorg',tipeorg);
					getData('tipeorg',kodeorg);
					setTimeout(function(){		
						getData('kodeorg',divisi);
						setTimeout(function(){		
							getData('divisi',tipekaryawan);
							setTimeout(function(){		
								getData('tipekaryawan',jabatan);
								setTimeout(function(){
									data = karyawanid.split(",");
									$('#karyawanid').val(data).trigger("change");
									$('#mulaiberlaku').val(mulaiberlaku).trigger("change");
									$('#status').val(stat).trigger("change");
									$('#sehari').val(sehari);
									$('#seminggu').val(seminggu);
									$('#hmhb').val(hmhb);
									$('#persengaji').val(persengaji);
								}, 500);					
							}, 500);
						}, 500);
					}, 500);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getData(e,selected){
	param  = '';
	param += '&tipeorg=' + $('#tipeorg').val();		
	param += '&kodeorg=' + $('#kodeorg').val();		
	param += '&divisi=' + $('#divisi').val();		
	param += '&jabatan=' + $('#jabatan').val();		
	param += '&tipekaryawan=' + $('#tipekaryawan').val();		
	param += '&selected='+selected;
	param += '&sumber='+e;
	param += '&method=getData';
	
	tujuan = 'sdm_5validasilembur_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					data = con.responseText.split("##");
					if(e=='tipeorg'){
						$('#kodeorg').val(null).trigger('change');
						$('#divisi').val(null).trigger('change');
						$('#tipekaryawan').val(null).trigger('change');
						$('#jabatan').val(null).trigger('change');
						$('#karyawanid').val(null).trigger('change');
						
						$('#kodeorg').html(data[0]);
						$('#divisi').html(data[1]);
						$('#tipekaryawan').html(data[3]);
						$('#jabatan').html(data[2]);
						$('#karyawanid').html(data[4]);
					}
					if(e=='kodeorg'){
						$('#divisi').html(data[1]);
						$('#tipekaryawan').html(data[3]);
						$('#jabatan').html(data[2]);
						$('#karyawanid').html(data[4]);
					}
					if(e=='divisi'){
						$('#tipekaryawan').html(data[3]);
						$('#jabatan').html(data[2]);
						$('#karyawanid').html(data[4]);
					}
					if(e=='tipekaryawan'){
						$('#jabatan').html(data[2]);
						$('#karyawanid').html(data[4]);
					}
					if(e=='jabatan'){
						$('#karyawanid').html(data[4]);
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}