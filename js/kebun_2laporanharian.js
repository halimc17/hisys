function getDivisi(kebunValue)
{
	pt=document.getElementById('kebun').value;
	
		
		param='kebun='+pt+'&method=getdivisi';
		
		tujuan='kebun_slave_2laporanharian.php';
		post_response_text(tujuan, param, respog);  
    	
	function respog()
	{
		      if(con.readyState==4)
				{	
			        if (con.status == 200) 
					{
						busy_off();
						if (!isSaveResponse(con.responseText)) 
						{
							alert('ERROR TRANSACTION,\n' + con.responseText);
						}
						else 
						{
							document.getElementById('divisi').innerHTML=con.responseText;
						}
					}
						else 
						{
							busy_off();
							error_catch(con.status);
						}
				}				
	}
}
function viewDataDetail(kegiatan,param){
	var parameter = "";
	if(typeof param !== 'undefined'){
		parameter = "&"+param;
	}
	param='kegiatan='+kegiatan+parameter+'&method=datadetail';
	tujuan='kebun_slave_2laporanharian.php';
	post_response_text(tujuan, param, respog);  
	
	function respog()
	{
		if(con.readyState==4)
		{	
			if (con.status == 200) 
			{
				busy_off();
				if (!isSaveResponse(con.responseText)) 
				{
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else {
					title = '';
					width = 1000;
					height = 500;
					ev = 'event';
					content = con.responseText;
					showDialog2(title, content, width, height, ev);
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}				
	}
}
function viewDataDetailKontrak(kegiatan,param){
	var parameter = "";
	if(typeof param !== 'undefined'){
		parameter = "&"+param;
	}
	param='kegiatan=KONTRAK&"+kodekegiatan='+kegiatan+parameter+'&method=datadetail';
	tujuan='kebun_slave_2laporanharian.php';
	post_response_text(tujuan, param, respog);  
	
	function respog()
	{
		if(con.readyState==4)
		{	
			if (con.status == 200) 
			{
				busy_off();
				if (!isSaveResponse(con.responseText)) 
				{
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}else {
					title = '';
					width = 1000;
					height = 500;
					ev = 'event';
					content = con.responseText;
					showDialog2(title, content, width, height, ev);
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}				
	}
}
function ApproveAlltransaction(){
	karyawanid = document.getElementById('idapprover').value;
	levelapprover = document.getElementById('levelapprover').value;
	jenispersetujuan = document.getElementById('jenispersetujuan').value;
	allTransaction = document.getElementsByName('selectivemode');
	var arrTrans = [];
	if(allTransaction.length > 0){
		var arrTrans = new Array();
		for(i=0; i<allTransaction.length; i++){
			if(allTransaction[i].checked === true){
				arrTrans.push(allTransaction[i].value);
			}
		}
		continueExec(arrTrans,0);
	}
	
	
	function continueExec(arrTrans,num){
		function respon(){
			if(con.readyState==4){	
			        if (con.status == 200){
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert('ERROR TRANSACTION,\n' + con.responseText);
						}else{
							//Success
							stat = document.getElementById('stat_'+arrTrans[num]);
							if(stat){
								stat.style.color = "green";
								stat.innerHTML 	 = "Checked";
							}
							num++;
							continueExec(arrTrans,num);
						}
					}else {
						busy_off();
						error_catch(con.status);
					}
				}	
		}
	
		if(num != arrTrans.length){
			xtipe=arrTrans[num].substr(14,3);
			//param = "";
			param = "notransaksi="+arrTrans[num]+"&karyawanid="+karyawanid+"&level="+levelapprover+"&jenispersetujuan="+jenispersetujuan;
			if(xtipe=='PNN'){
				post_response_text('kebun_slave_panenx.php?method=approvement', param, respon);
			}else{
				post_response_text('kebun_slave_operasional.php?proses=approvement', param, respon);
			}
		}
	}
	
}
function postingAllabsen(){
	allTransaction = document.getElementsByName('selectivemode');
	var arrTrans = [];
	if(allTransaction.length > 0){
		var arrTrans = new Array();
		for(i=0; i<allTransaction.length; i++){
			if(allTransaction[i].checked === true){
				arrTrans.push(allTransaction[i].value);
			}
		}
		continueExec(arrTrans,0);
	}
	
	
	function continueExec(arrTrans,num){
		function respon(){
			if(con.readyState==4){	
			        if (con.status == 200){
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert('ERROR TRANSACTION,\n' + con.responseText);
						}else{
							//Success
							stat = document.getElementById('stat_'+arrTrans[num]);
							if(stat){
								stat.style.color = "green";
								stat.innerHTML 	 = "Posted";
							}
							console.log(con.responseText);
							num++;
							continueExec(arrTrans,num);
						}
					}else {
						busy_off();
						error_catch(con.status);
					}
				}	
		}
	
		if(num != arrTrans.length){
			param = "proses=posting&notransaksi="+arrTrans[num];
			post_response_text('sdm_slave_absensi.php', param, respon);
	
		}
	}
	
}
function ApproveAllbaspk(){
	karyawanid = document.getElementById('idapprover').value;
	levelapprover = document.getElementById('levelapprover').value;
	jenispersetujuan = document.getElementById('jenispersetujuan').value;
	allTransaction = document.getElementsByName('selectivemode');
	var arrTrans = [];
	if(allTransaction.length > 0){
		var arrTrans = new Array();
		for(i=0; i<allTransaction.length; i++){
			if(allTransaction[i].checked === true){
				arrTrans.push(allTransaction[i].value);
			}
		}
		continueExec(arrTrans,0);
	}
	
	
	function continueExec(arrTrans,num){
		function respon(){
			if(con.readyState==4){	
			        if (con.status == 200){
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert('ERROR TRANSACTION,\n' + con.responseText);
						}else{
							//Success
							stat = document.getElementById('stat_'+arrTrans[num]);
							if(stat){
								stat.style.color = "green";
								stat.innerHTML 	 = "Posted";
							}
							console.log(con.responseText);
							num++;
							continueExec(arrTrans,num);
						}
					}else {
						busy_off();
						error_catch(con.status);
					}
				}	
		}
	
		if(num != arrTrans.length){
			param = "method=approvement&notransaksi="+arrTrans[num]+"&karyawanid="+karyawanid+"&level="+levelapprover+"&jenispersetujuan="+jenispersetujuan;
			post_response_text('kebun_slave_bapp.php', param, respon);
	
		}
	}
	
}
function postingAllbaspk(){
	allTransaction = document.getElementsByName('selectivemode');
	var arrTrans = [];
	if(allTransaction.length > 0){
		var arrTrans = new Array();
		for(i=0; i<allTransaction.length; i++){
			if(allTransaction[i].checked === true){
				arrTrans.push(allTransaction[i]);
			}
		}
		//console.log(arrTrans);
		continueExec(arrTrans,0);
	}
	/*
	 
	*/
	function continueExec(arrTrans,num){
		function respon(){
			if(con.readyState==4){	
			        if (con.status == 200){
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert('ERROR TRANSACTION,\n' + con.responseText);
						}else{
							//Success
							stat = document.getElementById('stat_'+arrTrans[num].value);
							if(stat){
								stat.style.color = "green";
								stat.innerHTML 	 = "Posted";
							}
							console.log(con.responseText);
							num++;
							continueExec(arrTrans,num);
						}
					}else {
						busy_off();
						error_catch(con.status);
					}
				}	
		}
		
		if(num != arrTrans.length){
			notransaksi 	= arrTrans[num].getAttribute('notransaksi');
			kodekegiatan 	= arrTrans[num].getAttribute('kodekegiatan');
			kodeblok 		= arrTrans[num].getAttribute('kodeblok');
			tanggal 		= arrTrans[num].getAttribute('tanggal');
			kodeorg 		= arrTrans[num].getAttribute('kodeorg');
			koderekanan 	= arrTrans[num].getAttribute('koderekanan');
			kodesegment 	= arrTrans[num].getAttribute('kodesegment');
			blokalokasi 	= arrTrans[num].getAttribute('blokalokasi');
			jumlahrealisasi = arrTrans[num].getAttribute('jumlahrealisasi');
			//param = "notransaksi="+arrTrans[num];
			var param = "kodeorg="+kodeorg+"&koderekanan="+koderekanan+"&kodesegment="+kodesegment;
			param += "&notransaksi="+notransaksi+"&kodeblok="+kodeblok+"&kodekegiatan="+kodekegiatan;
			param += "&blokalokasi="+blokalokasi+"&tanggal="+tanggal+"&jumlahrealisasi="+jumlahrealisasi;
			//console.log(param);
			post_response_text('log_slave_realisasispk_posting.php', param, respon);
		}
	}
}
function postingAlltransaction(){
	allTransaction = document.getElementsByName('selectivemode');
	var arrTrans = [];
	if(allTransaction.length > 0){
		var arrTrans = new Array();
		for(i=0; i<allTransaction.length; i++){
			if(allTransaction[i].checked === true){
				arrTrans.push(allTransaction[i].value);
			}
		}
		//console.log(arrTrans);
		continueExec(arrTrans,0);
	}
	
	
	function continueExec(arrTrans,num){
		function respon(){
			if(con.readyState==4){	
			        if (con.status == 200){
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert('ERROR TRANSACTION,\n' + con.responseText);
						}else{
							//Success
							stat = document.getElementById('stat_'+arrTrans[num]);
							if(stat){
								stat.style.color = "green";
								stat.innerHTML 	 = "Posted";
							}
							console.log(con.responseText);
							num++;
							continueExec(arrTrans,num);
						}
					}else {
						busy_off();
						error_catch(con.status);
					}
				}	
		}
	
		if(num != arrTrans.length){
			xtipe=arrTrans[num].substr(14,3);
			param = "notransaksi="+arrTrans[num];
			if(xtipe=='PNN'){
				post_response_text('kebun_slave_panen_posting.php', param, respon);
			}else{
				post_response_text('kebun_slave_operasional_posting.php', param, respon);
			}
		}
	}
	
}
function checkAll(class_name,e){
	class_obj = document.getElementsByName(class_name);
	if(class_obj.length > 0){
		for(i=0; i<class_obj.length; i++){
			if(e.checked === false){
				if(class_obj[i].checked === true){
					class_obj[i].checked = false;
				}
			}else if(e.checked === true){
				if(class_obj[i].checked === false){
					class_obj[i].checked = true;
				}
			}
		}
	}
	
}
function getKonduktor(){
		pt=document.getElementById('kebun').value;
		divisi=document.getElementById('divisi').value;
		tanggal=document.getElementById('tanggal').value;
	
		param='kebun='+pt+'&divisi='+divisi+'&tanggal='+tanggal+'&method=getkonduktor';
		tujuan='kebun_slave_2laporanharian.php';
		post_response_text(tujuan, param, respog);  
    	
	function respog()
	{
		      if(con.readyState==4)
				{	
			        if (con.status == 200) 
					{
						busy_off();
						if (!isSaveResponse(con.responseText)) 
						{
							alert('ERROR TRANSACTION,\n' + con.responseText);
						}
						else 
						{
							document.getElementById('konduktor').innerHTML=con.responseText;
						}
					}
						else 
						{
							busy_off();
							error_catch(con.status);
						}
				}				
	}
}
function printPDF(notransaksi, kodeorg, periode, tipe){
	kebun=document.getElementById('kebun').value;
	divisi=document.getElementById('divisi').value;
	tanggal=document.getElementById('tanggal').value;
	konduktor=document.getElementById('konduktor').value;
	typereport=document.getElementById('typereport').value;
	
	param='kebun='+kebun+'&divisi='+divisi+'&tanggal='+tanggal+'&konduktor='+konduktor+'&typereport='+typereport+'&method=pdf';
	tujuan='kebun_slave_2laporanharian.php?'+param;
	title = '';
	width = window.innerWidth*0.7;
	height = window.innerHeight*0.7;
	ev = 'event';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	showDialog2(title, content, width, height, ev);
}
function printPDF2(){
	kebun=document.getElementById('kebun').value;
	divisi=document.getElementById('divisi').value;
	tanggal=document.getElementById('tanggal').value;
	konduktor=document.getElementById('konduktor').value;
	typereport=document.getElementById('typereport').value;
    met=document.getElementById('method').value;
    
        param='kebun='+kebun+'&divisi='+divisi+'&tanggal='+tanggal+'&konduktor='+konduktor+'&typereport='+typereport+'&method='+met;
        tujuan='kebun_slave_2laporanharian.php';
        post_response_text(tujuan, param, callback);  
            
    function callback()
    {
	  if(con.readyState==4)
	  {
			if (con.status == 200) 
			{
				busy_off();
				if (!isSaveResponse(con.responseText)) 
				{
					alert('ERROR TRANSACTION,\n' + con.responseText);
				}
				else 
				{
					document.getElementById('container').innerHTML=con.responseText;
				}
			}
			else 
			{
				busy_off();
				error_catch(con.status);
			}
	  } 
    }
}
function preview()
{   
    kebun=document.getElementById('kebun').value;
	divisi=document.getElementById('divisi').value;
	tanggal=document.getElementById('tanggal').value;
	konduktor=document.getElementById('konduktor').value;
	typereport=document.getElementById('typereport').value;
    met=document.getElementById('method').value;
    
        param='kebun='+kebun+'&divisi='+divisi+'&tanggal='+tanggal+'&konduktor='+konduktor+'&typereport='+typereport+'&method='+met;
        tujuan='kebun_slave_2laporanharian.php';
        post_response_text(tujuan, param, callback);  
            
    function callback()
    {
              if(con.readyState==4)
              {
                    if (con.status == 200) 
					{
                        busy_off();
                        if (!isSaveResponse(con.responseText)) 
						{
                            alert('ERROR TRANSACTION,\n' + con.responseText);
                        }
                        else 
						{
                            document.getElementById('container').innerHTML=con.responseText;
							leftFixedTable();
                        }
                    }
                    else 
					{
                        busy_off();
                        error_catch(con.status);
                    }
              } 
    }
}

function excel(ev,tujuan) 
{
	kebun=document.getElementById('kebun').value;
	divisi=document.getElementById('divisi').value;
	tanggal=document.getElementById('tanggal').value;
    
	judul = 'Report Ms.Excel';
    param='kebun='+kebun+'&divisi='+divisi+'&tanggal='+tanggal+'&method=excel';
    printFile(param,tujuan,judul,ev);	
}

function printFile(param,tujuan,title,ev)
{ 
   tujuan=tujuan+"?"+param;  
    width='600';
    height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog2(title,content,width,height,ev);  
}

function cancel()
{
    document.getElementById('kebun').value='';
    document.getElementById('divisi').value='';
    document.getElementById('tanggal').value='';
}