function simpanassign(){
	namauser= document.getElementById('namauser').value;
	id      = document.getElementById('idassign').value;
	tipe      = document.getElementById('tipeassign').value;
	param  = '';
	param += '&id=' + id;
	param += '&tipe=' + tipe;
	param += '&namauser=' + namauser;
	param += '&method=simpanassign';
	
	tujuan = 'report_pivottable.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					setformuser(id,tipe);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function setformuser(id,tipe){
	param  = '';
	param += '&id=' + id;
	param += '&tipe=' + tipe;
	param += '&method=setformuser';
	
	tujuan = 'report_pivottable.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup2("Assign","<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':true}).resizeTo('500px','70%');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:true
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
function deletefield(id,table,assignto){
	param  = '';
	param += '&id=' + id;
	param += '&table=' + table;
	param += '&assignto=' + assignto;
	param += '&method=deletefield';
	
	tujuan = 'report_pivottable.php';
	alertify.confirm("Delete ?","Anda yakin ???.",
		function(){
			post_response_text(tujuan, param, respog);
		},
		function(){
			return;
		}
	).set('resizable',false);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					if(assignto!=undefined){
						setformuser(id,tipe);
					}else{						
						loadformfav();
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function pdftosave(){
	if(document.getElementById('tipe')!=undefined){		
		var nama = document.getElementById('tipe');
		var tipe = nama.options[nama.selectedIndex].text; 
		var namafile=tipe;
	}else{
		var namafile='pivot';
	}
	
	
	$("#pvtTable").find('.pvtRowLabel').removeClass('pvtRowLabel');
	$("#pvtTable th").css("background-color","white");
	$("#pvtTable tbody tr th").css("background-color","white");
    $("#pvtTable th").css("border","#adadad solid 1px");
    $("#pvtTable tbody tr th").css("border","#adadad solid 1px");
    $("#pvtTable td").css("border","#adadad solid 1px");
	
	var doc = new jsPDF('l','pt','A4')
	var totalPagesExp = '{total_pages_count_string}';
	doc.autoTable({ 
		html: '#pvtTable',
		theme: 'grid',
		useCss: true,
		didDrawPage: function (data) {
			// Header
			doc.setFontSize(15)
			doc.setTextColor(40)
			doc.text(namafile, data.settings.margin.left, 22)

			// Footer
			var str = 'Page ' + doc.internal.getNumberOfPages()
			// Total page number plugin only available in jspdf v1.0+
			if (typeof doc.putTotalPages === 'function') {
				str = str + ' of ' + totalPagesExp
			}
			doc.setFontSize(10)

			// jsPDF 1.4+ uses getWidth, <1.4 uses .width
			var pageSize = doc.internal.pageSize
			var pageHeight = pageSize.height ? pageSize.height : pageSize.getHeight()
			doc.text(str, data.settings.margin.left, pageHeight - 10)
		}
	})
	
	if (typeof doc.putTotalPages === 'function') {
		doc.putTotalPages(totalPagesExp);
	}
	
	var pdf = doc.output(); //returns raw body of resulting PDF returned as a string as per the plugin documentation.
	var data = new FormData();
	data.append("data", pdf);
	data.append("namafile", namafile);
	
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "report_pivottable.php?method=upload", true);
	con.onreadystatechange = eval(respon);
	con.send(data);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					selectkirim();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function selectkirim(){
	alertify.popup().destroy();
	alertify.popup("Kirim ???","<center><label>Kirim laporan (.pdf) melalui :<br></label><button style='height:30px;width:150px;' class=mybutton onclick=popupkirim('telegram')>Telegram</button><button style='height:30px;width:150px;' class=mybutton onclick=popupkirim('email')>E-Mail</button></center>").set({'resizable':false,'maximizable':false}); 
}

function popupkirim(sumber) {
	if(document.getElementById('tipe')!=undefined){		
		var nama = document.getElementById('tipe');
		var tipe = nama.options[nama.selectedIndex].text; 
		var namafile=tipe;
	}else{
		var namafile='pivot';
	}
	
	param  = '';
	param += '&sumber=' + sumber;
	param += '&namalaporan=' + namafile;
	param += '&jenis=popupkirim';
	param += '&method=popupkirim';
	
	alertify.popup().destroy();
	tujuan = 'report_pivottable.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					//alertify.popup2(sumber,con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%'); 
					alertify.popup2(sumber,con.responseText).set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
					listcari();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function listcari(){
	nama       = document.getElementById('nama').value;
	lokasi     = document.getElementById('lokasi').value;
	jabatan    = document.getElementById('jabatan').value;
	sumber     = document.getElementById('sumber').value;
	namalaporan= document.getElementById('namalaporan').value;
	
	param  = '';
	param += '&nama=' + nama;
	param += '&lokasi=' + lokasi;
	param += '&jabatan=' + jabatan;
	param += '&sumber=' + sumber;
	param += '&namalaporan=' + namalaporan;
	param += '&method=listcari';
	param += '&jenis=popupkirim';
	
	tujuan = 'report_pivottable.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById("listcari").innerHTML=con.responseText;
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
							scrollY: '45vh',
							scrollCollapse: true
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


function kirimkan(){
	i = document.getElementsByName("mail[]");
	e = document.getElementsByName("check[]");
	param="";
	jlhuser=0;
	for(n=0;n<e.length;n++){
		if(e[n].checked==true){			
			param+="&email["+n+"]="+i[n].innerHTML;
			jlhuser=jlhuser+1;
		}
	}
	if(param==""){		
		alertify.alert("Silahkan check terlebih dahulu"); return;
	}
	sumber = document.getElementById('sumber').value;
	namalaporan = document.getElementById('namalaporan').value;
	namamenu = document.getElementById('pathmenu').innerHTML;
	
	param += '&method=kirimkan';
	param += '&sumber='+ sumber;
	param += '&namalaporan=' + namalaporan;
	param += '&namamenu=' + namamenu;
	param += '&jenis=popupkirim';
	
	
	document.getElementById('tombolkirimkan').style.display="none";
	
	tujuan = 'report_pivottable.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
					document.getElementById('tombolkirimkan').style.display="";
				} else {
					document.getElementById('tombolkirimkan').style.display="";
					if(sumber=='telegram'){						
						alertify.alert("Data sudah dikirimkan kepada "+jlhuser+" user melalui "+sumber);
					}else{
						alertify.alert("Data sudah dikirimkan kepada "+jlhuser+" user melalui "+sumber+".<br>Jika pesan tidak ada di inbox silahkan cek folder spam.");
					}
					
					for(n=0;n<e.length;n++){
						if(e[n].checked==true){			
							e[n].checked=false;
							e[n].disabled=true;
							i[n].style.backgroundColor='cyan';
						}
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function infodaftar(){
	alertify.alert("Mendaftar Telegram","Berikut cara untuk mendaftar telegram :<br><br>1. Buka aplikasi telegram kemudian cari user : @owlksp_robot<br>2. Kirim pesan : reg spasi user_owl spasi password_owl<br>3. contoh : reg administrator 123456").set({'resizable':true}).resizeTo('250px'); 
}

function clickall(){
	e = document.getElementsByName("check[]");
	h = document.getElementById('checkall');
	for(i=0;i<e.length;i++){
		if(e[i].disabled==false){			
			if(h.checked==true){
				e[i].checked=true;
			}else{
				e[i].checked=false;
			}
		}
	}
}