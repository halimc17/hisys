function excel(){
	// alokasi=trim(document.getElementById('alokasi').value);
	unit=trim(document.getElementById('unit').value);
	per=trim(document.getElementById('per').value);
	tipe='excel';
	ev='event';
	// param='method=preview'+'&unit='+unit+'&per='+per+'&tipe='+tipe+'&alokasi='+alokasi;
	param='method=preview'+'&unit='+unit+'&per='+per+'&tipe='+tipe;
	ujuan='keu_slave_3hpp.php';
	judul='Report Ms.Excel';	
	printFile(param,tujuan,judul,ev);	
}

function printFile(param,tujuan,title,ev){
   tujuan=tujuan+"?"+param;  
   width='900';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}




function savehpp(){
    unit  = trim(document.getElementById('unit').value);
    per   = trim(document.getElementById('per').value);
	param = 'method=savehpp'+'&unit='+unit+'&per='+per;
	
	akundb =trim(document.getElementById('akundb1').innerHTML);
	akundb =remove_comma_var(akundb);
	akunkr =trim(document.getElementById('akunkr1').innerHTML);
	akunkr =remove_comma_var(akunkr);
	rpdb   =trim(document.getElementById('rpdb1').innerHTML);
	rpdb   =remove_comma_var(rpdb);
	rpkr   =trim(document.getElementById('rpkr1').innerHTML);
	rpkr   =remove_comma_var(rpkr);
	param+='&akundb='+akundb+'&akunkr='+akunkr+'&rpdb='+rpdb+'&rpkr='+rpkr;
	
	akundb2 =trim(document.getElementById('akundb2').innerHTML);
	akundb2 =remove_comma_var(akundb2);
	akunkr2 =trim(document.getElementById('akunkr2').innerHTML);
	akunkr2 =remove_comma_var(akunkr2);
	rpdb2   =trim(document.getElementById('rpdb2').innerHTML);
	rpdb2   =remove_comma_var(rpdb2);
	rpkr2   =trim(document.getElementById('rpkr2').innerHTML);
	rpkr2   =remove_comma_var(rpkr2);
	param+='&akundb2='+akundb2+'&akunkr2='+akunkr2+'&rpdb2='+rpdb2+'&rpkr2='+rpkr2;
	
	akundb3 =trim(document.getElementById('akundb3').innerHTML);
	akundb3 =remove_comma_var(akundb3);
	akunkr3 =trim(document.getElementById('akunkr3').innerHTML);
	akunkr3 =remove_comma_var(akunkr3);
	rpdb3   =trim(document.getElementById('rpdb3').innerHTML);
	rpdb3   =remove_comma_var(rpdb3);
	rpkr3   =trim(document.getElementById('rpkr3').innerHTML);
	rpkr3   =remove_comma_var(rpkr3);
	param+='&akundb3='+akundb3+'&akunkr3='+akunkr3+'&rpdb3='+rpdb3+'&rpkr3='+rpkr3;
	
	e = document.getElementsByName('akundet[]');
	for(i=0;i<e.length;i++){
		param += '&akundetail[' + i + ']=' + e[i].innerHTML;
		param += '&rupiahdetail[' + i + ']=' + document.getElementsByName('rupiahdet[]')[i].innerHTML;
		param += '&ketdetail[' + i + ']=' + document.getElementsByName('keterangan[]')[i].innerHTML;
	}
	
	
	tujuan='keu_3jurnalohtbm_slave.php';
	post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",'\n' + con.responseText);
				} else {	
					alertify.alert("Informasi",'Data tersimpan');
					// preview();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}



function preview(){
    unit=trim(document.getElementById('unit').value);
    per=trim(document.getElementById('per').value);
    jenis=trim(document.getElementById('jenis').value);
    alokasibiaya=trim(document.getElementById('alokasibiaya').value);

	tipe='html';
	if (unit=='' || per=='') {
		alertify.alert("Informasi","Periode atau Unit masih kosong !!!");
		return;
	}
	param='method=preview'+'&unit='+unit+'&per='+per+'&tipe='+tipe+'&jenis='+jenis;

    if(alokasibiaya == 'TBM') {
        tujuan='keu_3jurnalohtbm_slave.php';
    } else {
        tujuan='keu_3alokasiohtm_slave.php';
    }
	
    post_response_text(tujuan, param, respog);	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",'\n' + con.responseText);
				} else {	
					document.getElementById('printContainer').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}	
	}
}


function batal(){
    document.getElementById('unit').value='';	
    document.getElementById('per').value='';	
    // document.getElementById('kom').value='';
    document.getElementById('printContainer').innerHTML='';	
}


