function batal(){
	document.getElementById('unit').selectedIndex=0;
	document.getElementById('periode').selectedIndex=0;
	document.getElementById('idkaryawan').selectedIndex=0;
	document.getElementById('showTable').innerHTML = '';
	document.getElementById('showGraphic').innerHTML = '';
}

function preview(){
	unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	periode = document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
	idkaryawan = document.getElementById('idkaryawan').options[document.getElementById('idkaryawan').selectedIndex].value;
	
	param='proses=preview'+'&unit='+unit+'&periode='+periode+'&idkaryawan='+idkaryawan;
    tujuan='keu_slave_2monitoringuangmuka.php';
    post_response_text(tujuan, param, respog);	
	
	function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
					vsplit = con.responseText.split('####');
                    document.getElementById('showGraphic').innerHTML=vsplit[0];
                    document.getElementById('showTable').innerHTML=vsplit[1];
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fisikKeExcel(ev){
	tahun = document.getElementById('tahun').options[document.getElementById('tahun').selectedIndex].value;
	jenishama = document.getElementById('jenishama').options[document.getElementById('jenishama').selectedIndex].value;
    param='proses=excel&tahun='+tahun+'&jenishama='+jenishama;
	judul='Report Ms.Excel';	
	tujuan='kebun_slave_2monitorhpt.php';
    printFile(param,tujuan,judul,ev)
}

function printFile(param,tujuan,title,ev){
	tujuan=tujuan+"?"+param;  
	width='900';
	height='400';
	content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	showDialog1(title,content,width,height,ev);
}