
function addZero(num, places) {
  var zero = places - num.toString().length + 1;
  return Array(+(zero > 0 && zero)).join("0") + num;
}


function add_new_data(){
	document.getElementById('inputdata').style.display = 'block';
	document.getElementById('contdetail').style.display = 'block';
	document.getElementById('listData').style.display = 'none';
	
	setValue2('periode',null);
	setValue2('kodeorg',null);
	setValue2('tipekary',null);
	document.getElementById('upload').value='';
	document.getElementById('continputdata').innerHTML='';
}

function displayList() {
	document.getElementById('listData').style.display = 'block';
	document.getElementById('contdetail').style.display = 'none';
	document.getElementById('inputdata').style.display = 'none';
	loaddata(0);
}

function formupload(){
	periode = document.getElementById('periode').value;
	kodeorg = document.getElementById('kodeorg').value;
	tipekary= document.getElementById('tipekary').value;
	
	param  = 'method=formupload';
	param += '&periode=' + periode + '&kodeorg=' + kodeorg;
	param += '&tipekary=' + tipekary;
	tujuan = 'sdm_slave_uploadbpjs.php';
	judul = 'excel';
	ev    = 'event';
	printFile(param, tujuan, judul, ev)
}

function fileSelected(jenis){
	periode = document.getElementById('periode').value;
	kodeorg = document.getElementById('kodeorg').value;
	tipekary= document.getElementById('tipekary').value;
	
	var file = document.getElementById('upload').files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("periode", periode);
	formdata.append("kodeorg", kodeorg);
	formdata.append("tipekary", tipekary);
	formdata.append("jenis", jenis);
	
	
	busy_on();
	var con = createXMLHttpRequest();
	con.open("POST", "sdm_slave_uploadbpjs.php?method=fileSelected", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
    
    function respon(){
        if (con.readyState == 4){
			if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                }else{
					if(jenis=='simpan'){
						document.getElementById('continputdata').innerHTML="";
						displayList();
						// alertify.alert("Done");
					}else{						
						document.getElementById('continputdata').innerHTML=con.responseText;
						//leftFixedTable();
					}
                }
            }else{
				busy_off();
                error_catch(con.status);
            }
        }
    }
}


function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}
function loaddata(page) {
	kodeorg = document.getElementById('kodeorgsch').value;
	periode = document.getElementById('periodesch').value;
	
	param  = 'method=loaddata&page=' + page;
	param += '&kodeorg=' + kodeorg;
	param += '&periode=' + periode;
	
	tujuan = 'sdm_slave_uploadbpjs.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('contain').innerHTML =  con.responseText;
					
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
							buttons: ['csv', 'excel', 'print']
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

function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '300';
	height = '100';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	showDialog1(title, content, width, height, ev);
}


function preview(kodeorg,periode,tipekary){
	param  = '';
	param += '&periode=' + periode + '&kodeorg=' + kodeorg;
	param += '&tipekary=' + tipekary;
	param += '&method=preview';
	
	
	tujuan = 'sdm_slave_uploadbpjs.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function del(kodeorg,periode,tipekary){
	param  = '';
	param += '&periode=' + periode + '&kodeorg=' + kodeorg;
	param += '&tipekary=' + tipekary;
	param += '&method=delete';
	tujuan = 'sdm_slave_uploadbpjs.php';
	alertify.confirm("Delete","Anda yakin?",
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
