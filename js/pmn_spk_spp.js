function kembalispk(dest,nokontrak,kodept,tanggal,kodecustomer,kodebarang) {
	if(dest=='BI') {
		window.open("main_bi.html","OWLBI","status=0,toolbar=0,resizable=1,status=0,location=no,menubar=0,directories=0");       
	} else { 
		dest=dest.replace(".php","");
		dest=dest.replace(".html","");
		dest=dest.replace(".phtml","");
		dest=dest.replace(".php3","");
		// window.location=dest+'.php?nokontrak='+nokontrak;
		window.location=dest+'.php?nokontrak='+nokontrak+'&kodept='+kodept+'&tanggal='+tanggal+'&kodecustomer='+kodecustomer+'&kodebarang='+kodebarang;
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
    	
		tarifkapal= document.getElementById('tarifkapal').value;tarifkapal = remove_comma_var(tarifkapal);
    	tarifponton= document.getElementById('tarifponton').value;tarifponton = remove_comma_var(tarifponton);
    	namakapal= document.getElementById('namakapal').value;
		
    	tandatangan= document.getElementById('tandatangan').value;
    	
		rupiah= document.getElementById('rupiah').value;
		namaponton= document.getElementById('namaponton').value;
		
   
		method=document.getElementById('method').value;
		kota=document.getElementById('kota').value;
		keperluan=document.getElementById('keperluan').value;
		tandatangan2=document.getElementById('tandatangan2').value;
		if(tanggal==''|| transportir=='' || kuantitas==''){
			alert('Field Was Empty');
			return false;
		}
		if(namakapal.substr(0,3)=='TRK' && namaponton!=''){
			alert('Jenis Angkutan yang Anda pilih adalah Truck, Nama ponton tidak perlu dilengkapi / The type of transportation you choose is a truck, barge name is required');
			return false;
		}
		
    	tanggalberlaku= document.getElementById('tanggalberlaku').value;
		
		param+='nokontrak='+nokontrak+'&kodept='+kodept+'&tanggalkontrak='+tanggalkontrak+'&kodecustomer='+kodecustomer+'&kodebarang='+kodebarang;
		param+='&nospk='+nospk+'&jenis='+jenis+'&tanggal='+tanggal+'&transportir='+transportir+'&kuantitas='+kuantitas;
		param+='&tarifkapal='+tarifkapal+'&tarifponton='+tarifponton+'&namakapal='+namakapal;
		param+='&tandatangan='+tandatangan+'&namaponton='+namaponton+'&rupiah='+rupiah+'&kota='+kota+'&method='+method;
		param+='&keperluan='+keperluan+'&tandatangan2='+tandatangan2+'&tanggalberlaku='+tanggalberlaku;
		
		tujuan='pmn_spk_spp_slave.php';
		post_response_text(tujuan, param, respog);      
    
		function respog(){
			if(con.readyState==4){
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert('ERROR TRANSACTION,\n' + con.responseText);
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
    tujuan='pmn_spk_spp_slave.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n' + con.responseText);
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
	tarifkapal, tarifponton, namakapal,tandatangan,namaponton,rupiah,kota,keperluan,tandatangan2,tanggalberlaku) {
	document.getElementById('nospk').value=nospk;
    document.getElementById('tanggal').value=tanggal;
    document.getElementById('transportir').value=transportir;
    document.getElementById('kuantitas').value=kuantitas;
    document.getElementById('tarifkapal').value=tarifkapal;
    document.getElementById('tarifponton').value=tarifponton;
    document.getElementById('namaponton').value=namaponton;
    document.getElementById('namakapal').value=namakapal;
    document.getElementById('tandatangan').value=tandatangan;
    document.getElementById('kota').value=kota;
    document.getElementById('rupiah').value=rupiah;
    document.getElementById('keperluan').value=keperluan;
    document.getElementById('tandatangan2').value=tandatangan2;
    document.getElementById('tanggalberlaku').value=tanggalberlaku;
	document.getElementById('method').value='update';
}

function cancel(){
	document.getElementById('method').value='insert';
	document.getElementById('nospk').value='';
    document.getElementById('tanggal').value='';
    document.getElementById('transportir').value='';
    document.getElementById('kuantitas').value='';
    // document.getElementById('kuantitaskemasan').value='';
    document.getElementById('tarifkapal').value='';
    document.getElementById('tarifponton').value='';
    document.getElementById('namaponton').value='';
    document.getElementById('namakapal').value='';
    document.getElementById('tandatangan').value='';
    document.getElementById('kota').value='';
    document.getElementById('rupiah').value='';
    document.getElementById('tanggalberlaku').value='';
}



