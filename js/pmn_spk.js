function cancel(){
	document.getElementById('detail').style.display='none';
	document.getElementById('detailhead').style.display='none';
	document.getElementById('nokontrak').value='';
	document.getElementById('tanggal').value='';
	document.getElementById('kodecustomer').value='';
	document.getElementById('kodept').value='';;
	document.getElementById('kodebarang').value='';
	
}

function proses(){
	nokontrak=document.getElementById('nokontrak').value;
	tanggal=document.getElementById('tanggal').value;
	kodecustomer=document.getElementById('kodecustomer').value;
	kodept=document.getElementById('kodept').value;
	kodebarang=document.getElementById('kodebarang').value;
    param='method=detail'+'&nokontrak='+nokontrak+'&kodept='+kodept+'&tanggal='+tanggal+'&kodecustomer='+kodecustomer;+'&kodebarang='+kodebarang;
    tujuan = 'pmn_spk_slave.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
					document.getElementById('detail').style.display='block';
					document.getElementById('detailhead').style.display='block';
                    document.getElementById('detail').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
	
}

function gup( name, url ) {
    if (!url) url = location.href;
    name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
    var regexS = "[\\?&]"+name+"=([^&#]*)";
    var regex = new RegExp( regexS );
    var results = regex.exec( url );
    return results == null ? null : results[1];
}

window.addEventListener('DOMContentLoaded', function(event){
	var url = window.location.href;
	var realUrl = url.split("?")[0];
	var pushObjc = {foo:"spkpenjualan"};
	if(gup('nokontrak', url) && gup('nokontrak', url) !=""){
		edit(gup('nokontrak', url),gup('kodept', url),gup('tanggal', url),gup('kodecustomer', url),gup('kodebarang', url));
		window.history.pushState(pushObjc,"Spk Penjualan",realUrl);
	}else{
		window.history.pushState(pushObjc,"Spk Penjualan",realUrl);
	}
});


function newdata(){
	document.getElementById('header').style.display='block';
	document.getElementById('listdata').style.display='none';
	document.getElementById('detail').style.display='none';
	document.getElementById('detailhead').style.display='none';
	// document.getElementById('method').value='insert'; 
}


function edit(nokontrak,kodept,tanggal,kodecustomer,kodebarang){
	// alert(kodecustomer);
	document.getElementById('listdata').style.display='none';
    document.getElementById('header').style.display='block';
    // document.getElementById('detail').style.display='block';
    // document.getElementById('detailhead').style.display='block';
	document.getElementById('nokontrak').value=nokontrak;
	document.getElementById('tanggal').value=tanggal;
	document.getElementById('kodecustomer').value=kodecustomer;
	document.getElementById('kodept').value=kodept;
	document.getElementById('kodebarang').value=kodebarang;
	proses();
}






function loadtransaksi(dest,kdjenis) {
	if(dest=='BI') {
		window.open("main_bi.html","OWLBI","status=0,toolbar=0,resizable=1,status=0,location=no,menubar=0,directories=0");       
	} else { 
		dest=dest.replace(".php","");
		dest=dest.replace(".html","");
		dest=dest.replace(".phtml","");
		dest=dest.replace(".php3","");
		// window.location=dest+'.php?nokontrak='+nokontrak;
		
		window.location=dest+'.php?nokontrak='+nokontrak+'&kodept='+kodept+'&tanggal='+tanggal+'&kodecustomer='+kodecustomer+'&kodebarang='+kodebarang+'&kdjenis='+kdjenis;
	}
}

function displaylist() {
	document.getElementById('listdata').style.display = 'block';
	document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display = 'none';
	document.getElementById('detailhead').style.display = 'none';
	 document.getElementById('nokontraksch').value='';
    document.getElementById('kodecustomersch').value='';
	loaddata(0);
}
function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}


function carinokontrak(title,ev){
    content= "<div id=formcarinokontrak style=\"max-height:250px;width:max-350;overflow:auto;\"></div>";
    title=title;
    height='';
    width='';
    showDialog1(title,content,width,height,ev);	
    param='method=carinokontrak';
    tujuan = 'pmn_spk_slave.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('formcarinokontrak').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 	
}



function caridaftarnokontrak(){
    nokontrak=document.getElementById('daftarnokontrak').value;
    param='method=carinokontrak'+'&nokontrak='+nokontrak;
    tujuan = 'pmn_spk_slave.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('formcarinokontrak').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}


function movecarinokontrak(nokontrak,kodept,tanggal,kodecustomer,kodebarang){
    document.getElementById('nokontrak').value=nokontrak;
    document.getElementById('tanggal').value=tanggal;
    document.getElementById('kodecustomer').value=kodecustomer;
    document.getElementById('kodebarang').value=kodebarang;
    document.getElementById('kodept').value=kodept;
    closeDialog();
}


function loaddata(num) {
	// thnsch = document.getElementById('thnsch');
	// thnsch = thnsch.options[thnsch.selectedIndex].value;
	
	
	 nokontrak=document.getElementById('nokontraksch').value;
	 kodecustomer=document.getElementById('kodecustomersch').value;
	
	param = 'method=loaddata&page=' + num;

		param += '&nokontrak=' + nokontrak;
		param += '&kodecustomer=' + kodecustomer;
	tujuan = 'pmn_spk_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					isdt = con.responseText.split("####");
					document.getElementById('contain').innerHTML = isdt[0];
					document.getElementById('footData').innerHTML = isdt[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}






























