function delet(nospk,jenis) {
	param='method=delete';
	param+='&nospk='+nospk+'&jenis='+jenis;
    tujuan='pmn_spknonsales_ipkd_slave.php';
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
		
	
    	kodept= document.getElementById('kodept').value;
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
		
    	tandatangan1= document.getElementById('tandatangan1').value;
    	tandatangan2= document.getElementById('tandatangan2').value;
    	rupiah= document.getElementById('rupiah').value;
		
    	transportirdarat= document.getElementById('transportirdarat').value;
    	harga= document.getElementById('harga').value;
    	lain= document.getElementById('lain').value;
    	rpkg= document.getElementById('rpkg').value;
    	toleransi= document.getElementById('toleransi').value;
    	kgtoleransi= document.getElementById('kgtoleransi').value;
    	noakun= document.getElementById('noakun').value;
   
		method=document.getElementById('method').value;
		kota=document.getElementById('kota').value;
		debet=document.getElementById('debet').value;
		if(tanggal==''|| transportir=='' || tanggalmuat1=='' || tanggalmuat2=='' || kuantitas=='' || kota=='' || debet==''){
			alert('Field Was Empty (tanggal, transportir, tanggal muat, kuantitas, kota, akun debet)');
			return false;
		}
		
		param+='kodept='+kodept+'&kodebarang='+kodebarang;
		param+='&nospk='+nospk+'&jenis='+jenis+'&tanggal='+tanggal+'&transportir='+transportir+'&kuantitas='+kuantitas+'&kuantitaskemasan='+kuantitaskemasan;
		param+='&pelabuhanmuat='+pelabuhanmuat+'&pelabuhantujuan='+pelabuhantujuan+'&tanggalmuat1='+tanggalmuat1+'&tanggalmuat2='+tanggalmuat2+'&namakapal='+namakapal+'&namaponton='+namaponton;
		param+='&tandatangan1='+tandatangan1+'&rupiah='+rupiah+'&kota='+kota+'&method='+method;
		param+='&transportirdarat='+transportirdarat+'&harga='+harga+'&lain='+lain+'&tandatangan2='+tandatangan2+'&debet='+debet;
		param+='&rpkg='+rpkg+'&toleransi='+toleransi+'&kgtoleransi='+kgtoleransi+'&noakun='+noakun;
		
		tujuan='pmn_spknonsales_ipkd_slave.php';
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

function getpage() {
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
    tujuan='pmn_spknonsales_ipkd_slave.php';
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




/*
function edit(nokontrak, kodept, tanggalkontrak, kodecustomer, kodebarang,
	nospk, jenis, tanggal, transportir, kuantitas, kuantitaskemasan,
	pelabuhanmuat, pelabuhantujuan, tanggalmuat1, tanggalmuat2, namakapal,
	tandatangan,rupiah,kota,namaponton,transportirdarat,harga,lain) {
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
    document.getElementById('transportirdarat').value=transportirdarat;
    document.getElementById('harga').value=harga;
    document.getElementById('lain').value=lain;
	document.getElementById('method').value='update';
	getkapalponton(namakapal,namaponton);
}
*/







function fillField(nospk,jenis) {
	nospk = nospk;
	param = 'method=getEditData' + '&nospk=' + nospk+ '&jenis=' + jenis;
	tujuan = 'pmn_spknonsales_ipkd_slave.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('method').value = 'update';
					ar = con.responseText.split("###");
					document.getElementById('kodept').value=ar[0];
					document.getElementById('kodebarang').value=ar[3];
					document.getElementById('nospk').value=ar[4];
					document.getElementById('tanggal').value=ar[6];
					document.getElementById('transportir').value=ar[7];
					document.getElementById('kuantitas').value=ar[8];
					document.getElementById('kuantitaskemasan').value=ar[9];
					document.getElementById('pelabuhanmuat').value=ar[10];
					document.getElementById('pelabuhantujuan').value=ar[11];
					document.getElementById('tanggalmuat1').value=ar[12];
					document.getElementById('tanggalmuat2').value=ar[13];
					document.getElementById('namakapal').value=ar[14];
					document.getElementById('tandatangan1').value=ar[15];
					document.getElementById('rupiah').value=ar[16];
					document.getElementById('kota').value=ar[17];
					document.getElementById('namaponton').value=ar[18];
					document.getElementById('transportirdarat').value=ar[19];
					document.getElementById('harga').value=ar[20];
					document.getElementById('lain').value=ar[21];
					document.getElementById('tandatangan2').value=ar[22];
					document.getElementById('debet').value=ar[23];
					document.getElementById('rpkg').value=ar[24];
					document.getElementById('toleransi').value=ar[25];
					document.getElementById('kgtoleransi').value=ar[26];
					document.getElementById('noakun').value=ar[27];
					document.getElementById('method').value='update';
					// getkapalponton(ar[14],ar[18]);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respog);
}










function cancel(){
	document.getElementById('method').value='insert';
	document.getElementById('nospk').value='';
	document.getElementById('kodebarang').value='';
    document.getElementById('tanggal').value='';
    document.getElementById('transportir').value='';
    document.getElementById('kuantitas').value='';
    document.getElementById('kuantitaskemasan').value='';
    document.getElementById('pelabuhanmuat').value='';
    document.getElementById('pelabuhantujuan').value='';
    document.getElementById('tanggalmuat1').value='';
    document.getElementById('tanggalmuat2').value='';
    document.getElementById('namakapal').value='';
    document.getElementById('tandatangan1').value='';
    document.getElementById('tandatangan2').value='';
    document.getElementById('rupiah').value='';
    document.getElementById('kota').value='';
    document.getElementById('transportirdarat').value='';
    document.getElementById('harga').value='Rp    ,- per Kg (Diluar PPN, termasuk PPH)';
    document.getElementById('lain').value='';
    document.getElementById('debet').value='';
    document.getElementById('rpkg').value='';
    document.getElementById('toleransi').value='';
    document.getElementById('kgtoleransi').value='';
    document.getElementById('namaponton').value='';
    document.getElementById('noakun').value='';
}


function empty1(){
	document.getElementById('kgtoleransi').value = 0;
}

function empty2(){
	document.getElementById('toleransi').value = 0;
}

