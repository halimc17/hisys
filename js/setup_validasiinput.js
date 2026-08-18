function getjumlahhari(tanggal) {
	param = 'method=getjumlahhari';
	param+='&tanggal=' + tanggal;
    tujuan = 'setup_slave_validasiinput.php';
	
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('nilaidt').value = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function setaktifall(stat) {
	param = 'method=setaktifall';
	e = document.getElementsByName('idall[]');
	for(i=0;i<e.length;i++){
		param+='&id['+i+']=' + e[i].value;
	}
	
	param+='&stat=' + stat;
    tujuan = 'setup_slave_validasiinput.php';
	if(stat=='0'){
		n = "Ingin menonaktifkan seluruhnya ?";
	}else{
		n = "Ingin mengaktifkan seluruhnya ?";
	}
	
    if (confirm(n)){
        post_response_text(tujuan, param, respog);
	}

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function excel() {
	tujuan = 'setup_slave_validasiinput.php';
	
	judul = 'Excel';
	param = 'method=excel';
	//alertify.popuppdf("Preview","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='setup_slave_validasiinput.php?"+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('10%','20%');
	
	//printFile(param, tujuan, judul, 'event');
	
	printnopopup(tujuan+"?"+param);
}

function btldendapanen() {
    document.getElementById('method').value = 'insert';
    document.getElementById("kd_org").selectedIndex = "0";
    document.getElementById('kd_org').disabled = false;
    document.getElementById('divisi').disabled = false;
    document.getElementById('tipetrans').disabled = false;
    document.getElementById('divisi').value = '';
    document.getElementById('tipetrans').selectedIndex = '0';
    document.getElementById('harilibur').selectedIndex = '0';
    document.getElementById('nilai').value = '0';
    document.getElementById('status').value = '1';
}

function loadData() {
    param = 'method=loaddata';
    tujuan = 'setup_slave_validasiinput.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('container').innerHTML = con.responseText;
					$(document).ready(function() {
						var table = $('#mytable').DataTable({
							// supaya tidak ada overflow horisontal
							//responsive: true,
							// fixedColumns:   {
								// leftColumns: 1,
								// rightColumns: 2
							// },
							fixedHeader: true,
							// pake paging atau tidak
							paging: false,
							// columnDefs: [
								// {"className": "dt-body-nowrap", "targets": [0,1,2,3,4,5,6,7,8,9,10,11,12,13]}
							// ],
							// drag kolom
							//colReorder: true,
							// jumlah per page
							"iDisplayLength": 50,
							// tinggi / height
							scrollY: '60vh',
							scrollCollapse: true,
							dom: 'Bfrtip',
							buttons: ['csv', 'excel', 'print',{
									text: 'New',
									action: function () {
										newdata('new');
									}
								}
							]
						});
					} );
					
                    //btldendapanen();
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
	
	tujuan = 'setup_slave_validasiinput.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('50%','60%');
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

function fillfielddt(jumlahhari,harilibur,berlakudari,berlakusampai,ket) {
    document.getElementById('nilaidt').value = jumlahhari;
    document.getElementById('hariliburdt').value = harilibur;
    document.getElementById('tglmulai').value = berlakudari;
    document.getElementById('tglmulaiold').value = berlakudari;
    document.getElementById('tglsampai').value = berlakusampai;
    document.getElementById('tglsampaiold').value = berlakusampai;
    document.getElementById('ketdt').value = ket;
    document.getElementById('methoddt').value = 'updatedt';
	
	
}

function deletefielddt(kodeorg,divisi,tipetrans,tglmulai,tglsampai,id,nopengajuan) {
    param = 'kodeorg=' + kodeorg + '&method=deletedt';
	param+='&tipetrans=' + tipetrans;
	param+='&divisi=' + divisi;
	param+='&tglmulai=' + tglmulai;
	param+='&tglsampai=' + tglsampai;
	param+='&nopengajuan=' + nopengajuan;
	param+='&id=' + id;
    tujuan = 'setup_slave_validasiinput.php';
    if (confirm('Anda yakin hapus item ini?'))
        post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loaddatadt(id);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillfield(kodeorg,divisi,tipetrans,hari,harilibur,stat, tglberlaku,id) {
    // document.getElementById('kd_org').value = kodeorg;
    // document.getElementById('divisi').value = divisi;
    // document.getElementById('tipetrans').value = tipetrans;
    // document.getElementById('kd_org').disabled = true;
    // document.getElementById('divisi').disabled = true;
    // document.getElementById('tipetrans').disabled = true;
    // document.getElementById('nilai').value = hari;
    // document.getElementById('harilibur').value = harilibur;
    // document.getElementById('status').value = stat;
    // document.getElementById('tglberlaku').value = tglberlaku;
    // document.getElementById('idht').value = id;
    // document.getElementById('method').value = 'edit';
	
	
	param  = '';
	param+='&kodeorg=' + kodeorg + '&stts=' + stat + '&nilai=' + hari;
	param+='&tglberlaku=' + tglberlaku;
	param+='&divisi=' + divisi;
	param+='&id=' + id;
	param+='&tipetrans=' + tipetrans;
	param+='&harilibur=' + harilibur;
	param+='&mode=update';
	param+='&method=addnew';
	
	tujuan = 'setup_slave_validasiinput.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup("edit","<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('50%','60%');
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
					
					setValue2('idht',id);
					setValue2('tipetrans',tipetrans);
					setValue2('nilai',hari);
					setValue2('harilibur',harilibur);
					setValue2('status',stat);
					setValue2('tglberlaku',tglberlaku);
					setValue2('method','edit');
					
					document.getElementById('kd_org').disabled = true;
					document.getElementById('divisi').disabled = true;
					document.getElementById('tipetrans').disabled = true;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan(){
	id        = document.getElementById('idht').value;
	kodeorg   = document.getElementById('kd_org').value;
	divisi    = trim(document.getElementById('divisi').value);
	tipetrans = trim(document.getElementById('tipetrans').value);
	harilibur = trim(document.getElementById('harilibur').value);
	nilai     = trim(document.getElementById('nilai').value);
	stts      = trim(document.getElementById('status').value);
	method    = trim(document.getElementById('method').value);
	tglberlaku= trim(document.getElementById('tglberlaku').value);

    param = 'kodeorg=' + kodeorg + '&stts=' + stts + '&nilai=' + nilai+'&method=' + method;
	param+='&tglberlaku=' + tglberlaku;
	param+='&divisi=' + divisi;
	param+='&id=' + id;
	param+='&tipetrans=' + tipetrans;
	param+='&harilibur=' + harilibur;
    tujuan = 'setup_slave_validasiinput.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup().destroy();
                    document.getElementById('container').innerHTML = con.responseText;
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefield(id) {
    param = 'id=' + id + '&method=delete';
    tujuan = 'setup_slave_validasiinput.php';
    if (confirm('Anda yakin hapus item ini?'))
        post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function getdivisi(kd_org) {
    param = 'kodeorg=' + kd_org + '&method=getdivisi';
    tujuan = 'setup_slave_validasiinput.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
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

function adddetail(id){
    param = 'method=adddetail&id=' + id;
	
    tujuan = 'setup_slave_validasiinput.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':false,'message':con.responseText}).resizeTo('80%','70%').show();
					
					loaddatadt(id);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddatadt(id) {
    param = 'method=loaddatadt&id=' + id;
    tujuan = 'setup_slave_validasiinput.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('listdatadt').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function savedetail(id){
	harilibur   = trim(document.getElementById('hariliburdt').value);
	nilai       = trim(document.getElementById('nilaidt').value);
	method      = trim(document.getElementById('methoddt').value);
	tglmulai    = trim(document.getElementById('tglmulai').value);
	tglmulaiold = trim(document.getElementById('tglmulaiold').value);
	tglsampai   = trim(document.getElementById('tglsampai').value);
	tglsampaiold= trim(document.getElementById('tglsampaiold').value);
	ketdt       = trim(document.getElementById('ketdt').value);

    param = 'nilai=' + nilai+'&method=insertdt';
	param+='&mode=' + method;
	param+='&tglmulai=' + tglmulai;
	param+='&tglsampai=' + tglsampai;
	param+='&harilibur=' + harilibur;
	param+='&tglmulaiold=' + tglmulaiold;
	param+='&tglsampaiold=' + tglsampaiold;
	param+='&id=' + id;
	param+='&ketdt=' + ketdt;
    tujuan = 'setup_slave_validasiinput.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    loaddatadt(id);
					document.getElementById('tglmulai').value='';
					document.getElementById('tglmulaiold').value='';
					document.getElementById('tglsampaiold').value='';
					document.getElementById('tglsampai').value='';
					document.getElementById('ketdt').value='';
					document.getElementById('methoddt').value='insertdt';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function form_ajukan(nopengajuan,kodeapproval,kodeorg) {
	param = 'method=form_ajukan' + '&nopengajuan=' + nopengajuan+ '&kodeapproval=' + kodeapproval+ '&kodeorg=' + kodeorg;
	tujuan = 'setup_slave_validasiinput.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup2("Approval",con.responseText).set({
						'resizable':true,
						'maximizable':false
					}).resizeTo('300px','300px'); 
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function ajukan(jenispersetujuan){	
	kodeorg    = document.getElementById('unitajukan').value;
	nopengajuan= document.getElementById('nopengajuan').innerHTML;
	kepada     = document.getElementById('kepada').value;
	komentar   = document.getElementById('komentar').value;
	
	param = "";
	param += '&kodeorg=' + kodeorg;
	param += '&jenispersetujuan=' + jenispersetujuan;
	param += '&nopengajuan=' + nopengajuan;
	param += '&kepada=' + kepada;
	param += '&komentar=' + komentar;
	param += '&method=ajukan';
	
	tujuan = 'setup_slave_validasiinput.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.set('notifier','position', 'top-center');
					alertify.warning(con.responseText);
				} else {
					alertify.popup2().destroy();
					alertify.popup().destroy();
					loadData();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}