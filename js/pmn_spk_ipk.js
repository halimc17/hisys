function delet(nospk,jenis) {
	param='method=delete';
	param+='&nospk='+nospk+'&jenis='+jenis;
    tujuan='pmn_spk_ipk_slave.php';
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
    	kuantitaskemasan= document.getElementById('kuantitaskemasan').value;
			kuantitaskemasan=remove_comma_var(kuantitaskemasan);
			
		pelabuhanmuat= document.getElementById('pelabuhanmuat').value;
    	pelabuhantujuan= document.getElementById('pelabuhantujuan').value;
    	tanggalmuat1= document.getElementById('tanggalmuat1').value;
    	tanggalmuat2= document.getElementById('tanggalmuat2').value;
    	namakapal= document.getElementById('namakapal').value;
    	namaponton= document.getElementById('namaponton').value;
		
    	tandatangan= document.getElementById('tandatangan').value;
    	rupiah= document.getElementById('rupiah').value;
   
		method=document.getElementById('method').value;
		kota=document.getElementById('kota').value;
		if(tanggal==''|| transportir=='' || tanggalmuat1=='' || tanggalmuat2=='' || kuantitas=='' || kota==''){
			alert('Field Was Empty');
			return false;
		}
	
		if(namakapal.substr(0,3)=='TRK' && namaponton!=''){
			alert('Jenis Angkutan yang Anda pilih adalah Truck, Nama ponton tidak perlu dilengkapi / The type of transportation you choose is a truck, barge name is required');
			return false;
		}
		
		param+='nokontrak='+nokontrak+'&kodept='+kodept+'&tanggalkontrak='+tanggalkontrak+'&kodecustomer='+kodecustomer+'&kodebarang='+kodebarang;
		param+='&nospk='+nospk+'&jenis='+jenis+'&tanggal='+tanggal+'&transportir='+transportir+'&kuantitas='+kuantitas+'&kuantitaskemasan='+kuantitaskemasan;
		param+='&pelabuhanmuat='+pelabuhanmuat+'&pelabuhantujuan='+pelabuhantujuan+'&tanggalmuat1='+tanggalmuat1+'&tanggalmuat2='+tanggalmuat2+'&namakapal='+namakapal+'&namaponton='+namaponton;
		param+='&tandatangan='+tandatangan+'&rupiah='+rupiah+'&kota='+kota+'&method='+method;
		
		tujuan='pmn_spk_ipk_slave.php';
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
    tujuan='pmn_spk_ipk_slave.php';
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
	nospk, jenis, tanggal, transportir, kuantitas, kuantitaskemasan,
	pelabuhanmuat, pelabuhantujuan, tanggalmuat1, tanggalmuat2, namakapal,
	tandatangan,rupiah,kota,namaponton) {
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
    document.getElementById('namaponton').value=namaponton;
	document.getElementById('method').value='update';
	// getkapalponton(namakapal,namaponton);
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
    document.getElementById('namaponton').value='';
}



