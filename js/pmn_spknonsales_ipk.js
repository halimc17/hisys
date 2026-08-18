

function save() {
	
	    param="";
		
		// nokontrak	= document.getElementById('nokontrak').value;
    	kodept= document.getElementById('kodept').value;
    	// tanggalkontrak= document.getElementById('tanggalkontrak').value;
    	// kodecustomer= document.getElementById('kodecustomer').value;
    	kodebarang= document.getElementById('kodebarang').value;
    	
		nospk= document.getElementById('nospk').value;
		jenis= document.getElementById('jenis').value;
    	tanggal= document.getElementById('tanggal').value;
    	transportir= document.getElementById('transportir').value;
    	kuantitas= document.getElementById('kuantitas').value;
    	kuantitaskemasan= document.getElementById('kuantitaskemasan').value;
    	
		pelabuhanmuat= document.getElementById('pelabuhanmuat').value;
    	pelabuhantujuan= document.getElementById('pelabuhantujuan').value;
    	tanggalmuat1= document.getElementById('tanggalmuat1').value;
    	tanggalmuat2= document.getElementById('tanggalmuat2').value;
    	namakapal= document.getElementById('namakapal').value;
		
    	tandatangan= document.getElementById('tandatangan').value;
    	rupiah= document.getElementById('rupiah').value;
   
		method=document.getElementById('method').value;
		kota=document.getElementById('kota').value;
		if(tanggal==''|| transportir=='' || tanggalmuat1=='' || tanggalmuat2=='' || kuantitas=='' || namakapal=='' || kota==''){
			alert('Field Was Empty');
			return false;
		}
		
		param+='kodept='+kodept+'&kodebarang='+kodebarang;
		param+='&nospk='+nospk+'&jenis='+jenis+'&tanggal='+tanggal+'&transportir='+transportir+'&kuantitas='+kuantitas+'&kuantitaskemasan='+kuantitaskemasan;
		param+='&pelabuhanmuat='+pelabuhanmuat+'&pelabuhantujuan='+pelabuhantujuan+'&tanggalmuat1='+tanggalmuat1+'&tanggalmuat2='+tanggalmuat2+'&namakapal='+namakapal;
		param+='&tandatangan='+tandatangan+'&rupiah='+rupiah+'&kota='+kota+'&method='+method;
		
		tujuan='pmn_spknonsales_ipk_slave.php';
		post_response_text(tujuan, param, respog);      
    
		function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else  {
						cancel();
						loaddata(0);
					}
				} else  {
					busy_off();
					error_catch(con.status);
				}
			} 
	}
}

function getpage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}

function loaddata(page) {
	nospksch=document.getElementById('nospksch').value;
	kodeptsch=document.getElementById('kodeptsch').value;
	jenis=document.getElementById('jenis').value;
	param='method=loaddata';
	param+='&nospksch='+nospksch+'&kodeptsch='+kodeptsch+'&jenis='+jenis+'&page='+page;
    tujuan='pmn_spknonsales_ipk_slave.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				} else {
					// document.getElementById('container').innerHTML=con.responseText;
					
					isdt=con.responseText.split("####");
					document.getElementById('container').innerHTML=isdt[0];
					document.getElementById('footdata').innerHTML=isdt[1];
					// clearData();
					
					
				}
			} else {
				busy_off();
                error_catch(con.status);
			}
		} 
	}  
}

function cancelsch(){
document.getElementById('nospksch').value='';
document.getElementById('kodeptsch').value='';
loaddata(0);
}


function delet(nospk,jenis) {
	param='method=delete';
	param+='&nospk='+nospk+'&jenis='+jenis;
    tujuan='pmn_spknonsales_ipk_slave.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
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

function edit(nokontrak, kodept, tanggalkontrak, kodecustomer, kodebarang,
	nospk, jenis, tanggal, transportir, kuantitas, kuantitaskemasan,
	pelabuhanmuat, pelabuhantujuan, tanggalmuat1, tanggalmuat2, namakapal,
	tandatangan,rupiah,kota) {
	document.getElementById('nospk').value=nospk;
    document.getElementById('tanggal').value=tanggal;
    document.getElementById('transportir').value=transportir;
    document.getElementById('kuantitas').value=kuantitas;
    document.getElementById('kuantitaskemasan').value=kuantitaskemasan;
    document.getElementById('pelabuhanmuat').value=pelabuhanmuat;
    document.getElementById('pelabuhantujuan').value=pelabuhantujuan;
    document.getElementById('tanggalmuat1').value=tanggalmuat1;
    document.getElementById('tanggalmuat2').value=tanggalmuat2;
    document.getElementById('namakapal').value=namakapal;
    document.getElementById('tandatangan').value=tandatangan;
    document.getElementById('rupiah').value=rupiah;
    document.getElementById('kota').value=kota;
    document.getElementById('kodept').value=kodept;
    document.getElementById('kodebarang').value=kodebarang;
    document.getElementById('kodept').disabled=true;
    document.getElementById('kodebarang').disabled=true;
	document.getElementById('method').value='update';
}

function cancel(){
	document.getElementById('method').value='insert';
	document.getElementById('nospk').value='';
    document.getElementById('tanggal').value='';
    document.getElementById('transportir').value='';
    document.getElementById('kuantitas').value='';
    document.getElementById('kuantitaskemasan').value='';
    document.getElementById('pelabuhanmuat').value='';
    document.getElementById('pelabuhantujuan').value='';
    document.getElementById('tanggalmuat1').value='';
    document.getElementById('tanggalmuat2').value='';
    document.getElementById('namakapal').value='';
    document.getElementById('tandatangan').value='';
    document.getElementById('rupiah').value='';
    document.getElementById('kota').value='';
	document.getElementById('kodept').disabled=false;
	document.getElementById('kodept').value='';	
	document.getElementById('kodebarang').disabled=false;
	document.getElementById('kodebarang').value='';
}



