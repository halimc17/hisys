function delet(nospk,jenis) {
	param='method=delete';
	param+='&nospk='+nospk+'&jenis='+jenis;
    tujuan='pmn_spk_sp_slave.php';
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

function save() {
	
	    param="";
		
		nokontrak	= document.getElementById('nokontrak').value;
    	kodept= document.getElementById('kodept').value;
    	tanggalkontrak= document.getElementById('tanggalkontrak').value;
    	kodecustomer= document.getElementById('kodecustomer').value;
    	kodebarang= document.getElementById('kodebarang').value;
    	
		nospk= document.getElementById('nospk').value;
		jenis= document.getElementById('jenis').value;
    	tanggal= document.getElementById('tanggal').value;
    	transportir= document.getElementById('transportir').value;
    	kuantitas= document.getElementById('kuantitas').value;
			kuantitas=remove_comma_var(kuantitas);
    	
		pelabuhanmuat= document.getElementById('pelabuhanmuat').value;
    	pelabuhanbongkar= document.getElementById('pelabuhanbongkar').value;
    	namakapal= document.getElementById('namakapal').value;
    	namaponton= document.getElementById('namaponton').value;
		
    	tandatangan= document.getElementById('tandatangan').value;
    	
		rupiah= document.getElementById('rupiah').value;
		surveyor= document.getElementById('surveyor').value;
		
   
		method=document.getElementById('method').value;
		kota=document.getElementById('kota').value;
		if(tanggal==''|| transportir=='' || kuantitas==''){
			alert('Field Was Empty');
			return false;
		}
		
		if(namakapal.substr(0,3)=='TRK' && namaponton!=''){
			alert('Jenis Angkutan yang Anda pilih adalah Truck, Nama ponton tidak perlu dilengkapi / The type of transportation you choose is a truck, barge name is required');
			return false;
		}
		
		param+='nokontrak='+nokontrak+'&kodept='+kodept+'&tanggalkontrak='+tanggalkontrak+'&kodecustomer='+kodecustomer+'&kodebarang='+kodebarang;
		param+='&nospk='+nospk+'&jenis='+jenis+'&tanggal='+tanggal+'&transportir='+transportir+'&kuantitas='+kuantitas;
		param+='&pelabuhanmuat='+pelabuhanmuat+'&pelabuhanbongkar='+pelabuhanbongkar+'&namakapal='+namakapal+'&namaponton='+namaponton;
		param+='&tandatangan='+tandatangan+'&surveyor='+surveyor+'&rupiah='+rupiah+'&kota='+kota+'&method='+method;
		
		tujuan='pmn_spk_sp_slave.php';
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



function loaddata() {
	nokontrak=document.getElementById('nokontrak').value;
	jenis=document.getElementById('jenis').value;
	param='method=loaddata';
	param+='&nokontrak='+nokontrak+'&jenis='+jenis;
    tujuan='pmn_spk_sp_slave.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML=con.responseText;
				}
			} else {
				busy_off();
                error_catch(con.status);
			}
		} 
	}  
}

function edit(nokontrak, kodept, tanggalkontrak, kodecustomer, kodebarang,
	nospk, jenis, tanggal, transportir, kuantitas,
	pelabuhanmuat, pelabuhanbongkar, namakapal,tandatangan,surveyor,rupiah,kota,namaponton) {
	document.getElementById('nospk').value=nospk;
	document.getElementById('nospk').disabled=true;
    document.getElementById('tanggal').value=tanggal;
    document.getElementById('transportir').value=transportir;
    document.getElementById('kuantitas').value=kuantitas;
    document.getElementById('pelabuhanmuat').value=pelabuhanmuat;
    document.getElementById('pelabuhanbongkar').value=pelabuhanbongkar;
    document.getElementById('surveyor').value=surveyor;
    document.getElementById('namakapal').value=namakapal;
    document.getElementById('tandatangan').value=tandatangan;
    document.getElementById('kota').value=kota;
    document.getElementById('rupiah').value=rupiah;
	document.getElementById('method').value='update';
	// getkapalponton(namakapal,namaponton);
}

function cancel(){
	document.getElementById('method').value='insert';
	document.getElementById('nospk').value='';
	document.getElementById('nospk').disabled=false;
    document.getElementById('tanggal').value='';
    document.getElementById('transportir').value='';
    document.getElementById('kuantitas').value='';
    // document.getElementById('kuantitaskemasan').value='';
    document.getElementById('pelabuhanmuat').value='';
    document.getElementById('pelabuhanbongkar').value='';
    document.getElementById('surveyor').value='';
    document.getElementById('namakapal').value='';
    document.getElementById('namaponton').value='';
    document.getElementById('tandatangan').value='';
    document.getElementById('kota').value='';
    document.getElementById('rupiah').value='';
}



