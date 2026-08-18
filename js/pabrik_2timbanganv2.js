function preview1(type)
{
	kdpabrik=document.getElementById('kdpabrik1').value;
	kdbrg=document.getElementById('kdbrg1').value;
	cust=document.getElementById('cust1').value;
	tgltrans=document.getElementById('tgltrans1').value;
	tgltrans2=document.getElementById('tgltrans2').value;
	nokontrak=document.getElementById('nokontrak').value;
	param='kdpabrik='+kdpabrik+'&kdbrg='+kdbrg+'&tgltrans='+tgltrans+'&nokontrak='+nokontrak+'&cust='+cust+'&proses=preview1&type='+type+'&tgltrans2='+tgltrans2;
	// if(kdbrg == ''){alertify.alert('Informasi','Nama Barang harus dipilih');return false}
	tujuan='pabrik_slave_2timbanganv2.php';
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
					alert(con.responseText);
				}
				else 
				{
					document.getElementById('contain').innerHTML=con.responseText;
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

function preview2(type)
{
	kdpabrik=document.getElementById('kdpabrik2').value;
	kdbrg=document.getElementById('kdbrg2').value;
	tglawal=document.getElementById('tglawal2').value;
	tglakhir=document.getElementById('tglakhir2').value;
	
	param='kdpabrik='+kdpabrik+'&kdbrg='+kdbrg+'&tglawal='+tglawal+'&tglakhir='+tglakhir+'&proses=preview2&type='+type;
	tujuan='pabrik_slave_2timbanganv2.php';
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
					alert(con.responseText);
				}
				else 
				{
					document.getElementById('contain').innerHTML=con.responseText;
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

function printexcel1(tipe)
{
	kdpabrik=document.getElementById('kdpabrik1').value;
	kdbrg=document.getElementById('kdbrg1').value;
	cust=document.getElementById('cust1').value;
	tgltrans=document.getElementById('tgltrans1').value;
	tgltrans2=document.getElementById('tgltrans2').value;
	nokontrak=document.getElementById('nokontrak').value;
	param='kdpabrik='+kdpabrik+'&kdbrg='+kdbrg+'&tgltrans='+tgltrans+'&nokontrak='+nokontrak+'&cust='+cust+'&proses=preview1&type='+tipe+'&tgltrans2='+tgltrans2;
	tujuan='pabrik_slave_2timbanganv2.php';
    if(tipe == 'pdf'){
	    alertify.popuppdf("title","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan+'?'+param+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
    }else{
        printnopopup(tujuan+'?'+param)
    }
	// if(kdbrg == ''){alertify.alert('Informasi','Nama Barang harus dipilih');return false}
	// judul='Report Ms.Excel';	
	// printFile(param,tujuan,judul,ev)	
}

function printexcel2(ev)
{
	kdpabrik=document.getElementById('kdpabrik2').value;
	kdbrg=document.getElementById('kdbrg2').value;
	tglawal=document.getElementById('tglawal2').value;
	tglakhir=document.getElementById('tglakhir2').value;
	
	param='kdpabrik='+kdpabrik+'&kdbrg='+kdbrg+'&tglawal='+tglawal+'&tglakhir='+tglakhir+'&proses=preview2&type=excel';
	tujuan='pabrik_slave_2timbanganv2.php';
	judul='Report Ms.Excel';	
	printFile(param,tujuan,judul,ev)	
}


// JavaScript Document
function savePil()
{
	kdbrg=document.getElementById('kdBrg').value;
	kdPabrik=document.getElementById('kdPbrk').value;
	tgl=document.getElementById('tglTrans').value;
	
	if(tgl=='')
	{
		alert('Tanggal masih kosong');
		return;	
	}
	
	param='kdBrg='+kdbrg+'&kdPbrk='+kdPabrik+'&tgl='+tgl+'&proses=getData';
	tujuan='pabrik_slave_2timbanganv2.php';
	post_response_text(tujuan, param, respog);
	function respog()
	{
		      if(con.readyState==4)
		      {
			        if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alert(con.responseText);
						}
						else {
							//alert(con.responseText);
							document.getElementById('kdBrg').disabled=true;
							document.getElementById('kdPbrk').disabled=true;
							document.getElementById('tglTrans').disabled=true;
							document.getElementById('contain').innerHTML=con.responseText;
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 } 	
	
}
function gantiPil()
{
	document.getElementById('kdBrg').disabled=false;
	document.getElementById('kdPbrk').disabled=false;
	document.getElementById('tglTrans').disabled=false;
	document.getElementById('kdBrg').value='0';
	document.getElementById('kdPbrk').value='';
	document.getElementById('tglTrans').value='';
	document.getElementById('contain').innerHTML='';
}
function dataKeExcel(ev,tujuan)
{
	kdBrg		=document.getElementById('kdBrg').value;
	kdPbrk  =document.getElementById('kdPbrk').value;
	tgl =document.getElementById('tglTrans').value;

	//gudang	=gudang.options[gudang.selectedIndex].value;
	judul='Report Ms.Excel';	
	param='kdBrg='+kdBrg+'&kdPbrk='+kdPbrk+'&tgl='+tgl;
	//alert(param);
	printFile(param,tujuan,judul,ev)	
}
function dataKePDF(ev)
{
	kdBrg	=document.getElementById('kdBrg').value;
	kdPbrk  =document.getElementById('kdPbrk').value;
	tgl =document.getElementById('tglTrans').value;

	tujuan='pabrik_slaveLaporanTimbanganPdf.php';
	judul='Report PDF';		
	param='kdBrg='+kdBrg+'&kdPbrk='+kdPbrk+'&tgl='+tgl;
	//alert(param);
	printFile(param,tujuan,judul,ev)		
}
function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}
