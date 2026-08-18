/**
 * @author repindra.ginting
 */

 
function deltran(notransaksi,per){
    param='notransaksi='+notransaksi+'&method=del'; //alert(param);
    tujuan='sdm_slave_savePengobatan.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					window.location='?periode='+per;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }			
} 
 
 
function selesai(){
	document.location.reload();
}
function posting(notransaksi,karyawanid,per){
    param='notransaksi='+notransaksi+'&karyawanid='+karyawanid+'&method=posting'; //alert(param);
    tujuan='sdm_slave_savePengobatan.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					window.location='?periode='+per;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }			
} 
 
 
 
function getTrxNumber(thn)
{
		document.getElementById('karyawanid').selectedIndex=0;
		document.getElementById('plafon').value=0;
		document.getElementById('satuanPlafon').innerHTML='';
		
		param='tahun='+thn;
                tujuan='sdm_slave_getPengobatanNumber.php';
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
                                                        document.getElementById('notransaksi').value=trim(con.responseText);
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         }
}

function getFamily()
{		
		karid = document.getElementById('karyawanid').options[document.getElementById('karyawanid').selectedIndex].value;
        thnplafon	=document.getElementById('thnplafon').options[document.getElementById('thnplafon').selectedIndex].value;
        lokasitugas	=document.getElementById('lokasitugas').options[document.getElementById('lokasitugas').selectedIndex].value;
		
		if(thnplafon=='')
        {
                alert('Transaction number is obligatory');
                document.getElementById('thnplafon').focus();
                document.getElementById('karyawanid').selectedIndex=0;
				exit(0);
        }
		
		jenisbiaya	= document.getElementById('jenisbiaya').options[document.getElementById('jenisbiaya').selectedIndex].value;
        param='karyawanid='+karid;
        param+='&thnplafon='+thnplafon;
        param+='&jenisbiaya='+jenisbiaya;
		param+='&lokasitugas='+lokasitugas;
//        alert(param);
        tujuan='sdm_slave_getKeluargaOpt.php';
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
												pisah=con.responseText.split('###');
                                                document.getElementById('ygberobat').innerHTML=pisah[0];
                                                document.getElementById('gaji').value=pisah[1];
                                                document.getElementById('tipekaryawan').value=pisah[2];
                                                document.getElementById('sudahbayar').value=pisah[3];
                                                document.getElementById('blmbayar').value=pisah[4];
                                                if(karid==''){
													document.getElementById('plafon').value='0';
													document.getElementById('satuanPlafon').innerHTML='';
												}else{
													document.getElementById('plafon').value=pisah[5];
													document.getElementById('satuanPlafon').innerHTML=pisah[6];
												}
												
												//if(pisah[2]==0 && jenisbiaya=='RWJLN'){
												if(jenisbiaya=='RWJLN'){	
													document.getElementById('plafon').disabled=false;
												}else{
													document.getElementById('plafon').disabled=true;
												}
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
              }	
         }

}

function calculateTotal()
{
        bylab	=remove_comma(document.getElementById('bylab'));
        byadmin	=remove_comma(document.getElementById('byadmin'));
        byobat	=remove_comma(document.getElementById('byobat'));
        bydr	=remove_comma(document.getElementById('bydr'));
        byrs	=remove_comma(document.getElementById('byrs'));
        bebankaryawan	=remove_comma(document.getElementById('bebankaryawan'));
        bebanjamsostek	=remove_comma(document.getElementById('bebanjamsostek'));
        
        ttl=parseFloat(bylab)+parseFloat(byadmin)+parseFloat(byobat)+parseFloat(bydr)+parseFloat(byrs);
        document.getElementById('total').value=ttl;
        change_number(document.getElementById('total'));
        beban();
}
function beban()
{       
        a=parseFloat(remove_comma(document.getElementById('total')));
        b=parseFloat(remove_comma(document.getElementById('gaji')));
        c=parseFloat(remove_comma(document.getElementById('sudahbayar')));
        d=parseFloat(remove_comma(document.getElementById('blmbayar')));
        obt=parseFloat(remove_comma(document.getElementById('byobat')));
        bebankaryawan=parseFloat(remove_comma(document.getElementById('bebankaryawan')));
        bebanjamsostek=parseFloat(remove_comma(document.getElementById('bebanjamsostek')));
        
        tipekaryawan    =document.getElementById('tipekaryawan').value;
        jenisbiaya	=document.getElementById('jenisbiaya');
        jenisbiaya	=jenisbiaya.options[jenisbiaya.selectedIndex].value;
        totalklaim=a+c+d;
        
        //RWJLN
        perusahaan=b-c;
        sisaklaim=b-totalklaim;
        if(sisaklaim < 0){
            sisaklaim=sisaklaim*-1;
        }
        else if(totalklaim>b){
            sisaklaim=totalklaim;
        }
        // RWINP        
        gajiinap=b*4;
        perusahaaninap=gajiinap-c;
        sisaklaiminap=gajiinap-totalklaim;
        if(sisaklaiminap < 0){
            sisaklaiminap=sisaklaiminap*-1;
        }
        else if(totalklaim>gajiinap){
            sisaklaiminap=totalklaim;
        }
        
        
        // if(jenisbiaya == 'RWJLN'){
            // if(tipekaryawan !=0){
                // if(totalklaim > b){    
                    // alert('Gaji: '+b+'\nPengobatan yang sudah dibayar: '+c+'\nPengobatan yang belum dibayar: '+d+'\nPengobatan yang akan diinput: '+a+'\nOver: '+sisaklaim);
                    // document.getElementById('bebanperusahaan').value=perusahaan;
                    // if(c==b){
                        // document.getElementById('bebankaryawan').value=a;
                        // change_number(document.getElementById('bebanperusahaan'));
                        // change_number(document.getElementById('bebankaryawan'));
                    // }
                    // else{
                        // document.getElementById('bebankaryawan').value=sisaklaim;
                        // change_number(document.getElementById('bebanperusahaan'));
                        // change_number(document.getElementById('bebankaryawan'));
                    // }                    
                // }
                // else{
                    // document.getElementById('bebanperusahaan').value=a;
                    // document.getElementById('bebankaryawan').value=0;
                    // change_number(document.getElementById('total'));
                    // change_number(document.getElementById('bebanperusahaan'));
                    // change_number(document.getElementById('bebankaryawan'));
                // }
               
            // }
            // else { 
                // document.getElementById('bebanperusahaan').value=a;
                // document.getElementById('bebankaryawan').value=0;
                // change_number(document.getElementById('total'));
                // change_number(document.getElementById('bebanperusahaan'));
                // change_number(document.getElementById('bebankaryawan'));
            // }
        // }
        // else if(jenisbiaya == 'RWINP'){
            // if(tipekaryawan !=0){
                // if(totalklaim > gajiinap){
                    // if(tipekaryawan==1){
                        // plafonobat=450000;
                        // if(obt >=plafonobat){
                            // lebih=obt-plafonobat;
                        // }
                        // else{
                            // lebih=0;
                        // }
                    // }
                    // else if(tipekaryawan==3){
                        // plafonobat=400000;
                        // if(obt >=plafonobat){
                            // lebih=obt-plafonobat;
                        // }
                        // else{
                            // lebih=0;
                        // }
                    // }
                    // else{
                        // lebih=0;
                    // }
                    // alert('Gaji: '+b+'\nPengobatan yang sudah dibayar: '+c+'\nPengobatan yang belum dibayar: '+d+'\nPengobatan yang akan diinput: '+a+'\nOver: '+sisaklaiminap);
                    // document.getElementById('bebanperusahaan').value=perusahaaninap;
                    // document.getElementById('bebankaryawan').value=sisaklaiminap;
                    // change_number(document.getElementById('bebanperusahaan'));
                    // change_number(document.getElementById('bebankaryawan'));
                // }
                // else{
                    // if(tipekaryawan==1){
                        // plafonobat=450000;
                        // if(obt >=plafonobat){
                            // lebih=obt-plafonobat;
                        // }
                        // else{
                            // lebih=0;
                        // }
                    // }
                    // else if(tipekaryawan==3){
                        // plafonobat=400000;
                        // if(obt >=plafonobat){
                            // lebih=obt-plafonobat;
                        // }
                        // else{
                            // lebih=0;
                        // }
                    // }
                    // else{
                        // lebih=0;
                    // }
                    // document.getElementById('bebanperusahaan').value=a-lebih;
                    // document.getElementById('bebankaryawan').value=lebih;
                    // change_number(document.getElementById('total'));
                    // change_number(document.getElementById('bebanperusahaan'));
                    // change_number(document.getElementById('bebankaryawan'));
                    
                // }
            // }
            // else{  
                // document.getElementById('bebanperusahaan').value=a;
                // document.getElementById('bebankaryawan').value=0;
                // change_number(document.getElementById('total'));
                // change_number(document.getElementById('bebanperusahaan'));
                // change_number(document.getElementById('bebankaryawan'));
            // }
        // }
        // else {
            document.getElementById('bebanperusahaan').value=a-bebankaryawan-bebanjamsostek;
            // document.getElementById('bebankaryawan').value=0;
            change_number(document.getElementById('bebanperusahaan'));
            // change_number(document.getElementById('bebankaryawan'));
            // change_number(document.getElementById('bebanjamsostek'));
        // }
}
function kurangkanTotal(obj){
         a=parseFloat(remove_comma(document.getElementById('total')));
         b=parseFloat(remove_comma(document.getElementById('bebankaryawan')));
         c=parseFloat(remove_comma(document.getElementById('bebanjamsostek')));
         pangurang=b+c;
         document.getElementById('bebanperusahaan').value=a-pangurang;
        change_number(document.getElementById('bebanperusahaan'));
        change_number(document.getElementById('bebankaryawan'));
        change_number(document.getElementById('bebanjamsostek')); 
}
function savePengobatan(){
        thnplafon	=document.getElementById('thnplafon');
        thnplafon	=thnplafon.options[thnplafon.selectedIndex].value;	
        periode	=document.getElementById('periode');
        periode	=periode.options[periode.selectedIndex].value;	
        jenisbiaya	=document.getElementById('jenisbiaya');
        jenisbiaya	=jenisbiaya.options[jenisbiaya.selectedIndex].value;	
        karyawanid	=document.getElementById('karyawanid');
        karyawanid	=karyawanid.options[karyawanid.selectedIndex].value;		
        ygberobat	=document.getElementById('ygberobat');
        ygberobat	=ygberobat.options[ygberobat.selectedIndex].value;	
        rs		=document.getElementById('rs');
		rs		=rs.options[rs.selectedIndex].value;	
        diagnosa	=document.getElementById('diagnosa');
        diagnosa	=diagnosa.options[diagnosa.selectedIndex].value;	
        klaim		=document.getElementById('klaim');
        klaim	=klaim.options[klaim.selectedIndex].value;

        method	=document.getElementById('method').value;
        notransaksi	=document.getElementById('notransaksi').value;
        hariistirahat	=document.getElementById('hariistirahat').value;
		if(hariistirahat=='')
          hariistirahat=0;
        tanggal		=document.getElementById('tanggal').value;
        tanggalkwitansi		=document.getElementById('tanggalkwitansi').value;
        tanggalpengajuan		=document.getElementById('tanggalpengajuan').value;
        keterangan		=document.getElementById('keterangan').value;
        byrs			=parseFloat(remove_comma(document.getElementById('byrs')));
        byadmin		=parseFloat(remove_comma(document.getElementById('byadmin')));
        bylab			=parseFloat(remove_comma(document.getElementById('bylab')));
        byobat		=parseFloat(remove_comma(document.getElementById('byobat')));
        bydr			=parseFloat(remove_comma(document.getElementById('bydr')));
        bylab			=parseFloat(remove_comma(document.getElementById('bylab')));
        total			=parseFloat(remove_comma(document.getElementById('total')));
        bebanperusahaan		=parseFloat(remove_comma(document.getElementById('bebanperusahaan')));
        bebankaryawan		=parseFloat(remove_comma(document.getElementById('bebankaryawan')));        
        bebanjamsostek		=parseFloat(remove_comma(document.getElementById('bebanjamsostek')));
        
		plafon		=parseFloat(remove_comma(document.getElementById('plafon')));
		
		
		
        
        if(notransaksi=='')
        {
                alert('Tahun Plafond harus dipilih');
                document.getElementById('thnplafon').focus();
        }else if(jenisbiaya=='RWINP' && (hariistirahat=='' || hariistirahat==0)){
				alert('Jumlah hari inap harus diisi');
                document.getElementById('hariistirahat').focus();
		}
        else if(total<0.1)
        {
                alert('Biaya masih belum diisi');
                document.getElementById('byrs').focus();		
        }
        else if(karyawanid=='')
        {
                alert('Karyawan harus dipilih');
                document.getElementById('karyawanid').focus();		
        }
		else if (rs==''){
			alert('Rumah sakit/klinik/apotik masih kosong');
			return;
		}
		else if (diagnosa==''){
			alert('Diagnosa masih kosong');
			return;
		}
        else if(tanggal=='') {
			alert('Date is obligatory');
			document.getElementById('tangal').focus();			
        }else if(bebanperusahaan > plafon && (jenisbiaya == 'FRAME' || jenisbiaya =='RWCAE' || jenisbiaya =='RWSAL') ){
				alert('Beban perusahaan harus lebih kecil atau sama dengan dari nilai plafond');
                document.getElementById('byrs').focus();
		}else if(bebanperusahaan <= 0){
				alert('Beban perusahaan harus lebih besar dari nilai 0');
                document.getElementById('byrs').focus();
		}else if(klaim==1 && keterangan==''){
			alert('Untuk claim RS, harus mengisikan no.invoice di keterangan');return;
		}else {
			
				if(jenisbiaya == 'RWINP'){
					totalPlaf=hariistirahat*plafon;
					if(byrs > totalPlaf){
						alert('Biaya rumah sakit harus lebih kecil atau sama dengan dari total nilai plafond : '+totalPlaf);
						document.getElementById('bebankaryawan').focus();
						return false;
					}
				}
				
				if(confirm('Saving, are you sure..?'))
				{
				   param='tahunplafon='+thnplafon+'&periode='+periode+'&jenisbiaya='+jenisbiaya;
				   param+='&karyawanid='+karyawanid+'&method='+method+'&ygberobat='+ygberobat;
				   param+='&rs='+rs+'&diagnosa='+diagnosa+'&klaim='+klaim+'&notransaksi='+notransaksi;
				   param+='&hariistirahat='+hariistirahat+'&tanggal='+tanggal+'&keterangan='+keterangan;		   
				   param+='&byrs='+byrs+'&byadmin='+byadmin+'&bydr='+bydr;
				   param+='&byobat='+byobat+'&total='+total+'&bylab='+bylab;
				   param+='&bebanperusahaan='+bebanperusahaan+'&bebankaryawan='+bebankaryawan+'&bebanjamsostek='+bebanjamsostek;
				   param+='&tanggalkwitansi='+tanggalkwitansi+'&tanggalpengajuan='+tanggalpengajuan;
				   tujuan='sdm_slave_savePengobatan.php';
				   post_response_text(tujuan, param, respog);
				}
        }		

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
                            document.getElementById('container').innerHTML=con.responseText;
                            document.getElementById('mainsavebtn').disabled=true;
                            alert('Done');
                            tabAction(document.getElementById('tabFRM1'),1,'FRM',0);
                        }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
              }	
         }	
}

function formatDateNow(){
	d = new Date(Date.now());
	tanggal=''+d.getDate();
	bulan=''+(d.getMonth()+1);
	tahun=d.getFullYear();
	if(tanggal.length<2){
		tanggal='0'+tanggal;
	}
	if(bulan.length<2)bulan='0'+bulan;
	return [tanggal,bulan,tahun].join('-');
}

function clearForm()
{
        document.getElementById('tanggalkwitansi').value=formatDateNow();
        document.getElementById('tanggalpengajuan').value=formatDateNow();
        document.getElementById('tanggal').value=formatDateNow();
        document.getElementById('notransaksi').value='';
        document.getElementById('hariistirahat').value='0';
        document.getElementById('keterangan').value='';
        document.getElementById('byrs').value='0';
        document.getElementById('plafon').value='0';
        document.getElementById('byadmin').value='0';
        document.getElementById('bylab').value='0';
        document.getElementById('byobat').value='0';
        document.getElementById('bydr').value='0';
        document.getElementById('bylab').value='0';
        document.getElementById('total').value='0';
        document.getElementById('bebanperusahaan').value='0';
        document.getElementById('bebankaryawan').value='0';
        document.getElementById('bebanjamsostek').value='0';
        document.getElementById('lokasitugas').value='';
        thnplafon		=document.getElementById('thnplafon');
                thnplafon	=thnplafon.options[0].selected=true;	
        periode			=document.getElementById('periode');
                periode		=periode.options[0].selected=true;
        jenisbiaya		=document.getElementById('jenisbiaya');
                jenisbiaya	=jenisbiaya.options[0].selected=true;	
        karyawanid		=document.getElementById('karyawanid');
                karyawanid	=karyawanid.options[0].selected=true;	
        ygberobat		=document.getElementById('ygberobat');
                ygberobat	=ygberobat.options[0].selected=true;	
        rs				=document.getElementById('rs');
                rs			=rs.options[0].selected=true;
        diagnosa		=document.getElementById('diagnosa');
                diagnosa	=diagnosa.options[0].selected=true;
        klaim			=document.getElementById('klaim');
                klaim		=klaim.options[0].selected=true;
   document.getElementById('mainsavebtn').disabled=false;
}

function saveObat()
{
        nodok=document.getElementById('notransaksi').value;
        namaobat=document.getElementById('namaobat').value;
                    jenisobat=document.getElementById('jenisobat');
                    jenisobat=jenisobat.options[jenisobat.selectedIndex].value;

        param='notransaksi='+nodok+'&namaobat='+namaobat+'&jenisobat='+jenisobat;
        tujuan='sdm_slave_saveObat.php';	
        if(nodok=='' || namaobat=='')
         alert('Document Not Valid');
        else
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
                                                   document.getElementById('container1').innerHTML=con.responseText;
                                                   document.getElementById('namaobat').value='';
												   alert('Done');
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         }		
}

function deleteObat(id,notransaksi)
{
    param='id='+id+'&del=true&notransaksi='+notransaksi;
        tujuan='sdm_slave_saveObat.php';
        if(confirm('Deleting are you sure..?'))	
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
                                                   document.getElementById('container1').innerHTML=con.responseText;
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         } 
}

function deletePengobatan(notransaksi)
{

        param='notransaksi='+notransaksi+'&method=del';
        tujuan='sdm_slave_savePengobatan.php';
        if(confirm('You are deleting '+notransaksi+', are you sure?'))
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
                                                        document.getElementById('container').innerHTML=con.responseText;
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         }		
}

function loadPengobatan(thn)
{
        param='tahunplafon='+thn+'&method=none';
        tujuan='sdm_slave_savePengobatan.php';
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
                                                        document.getElementById('container').innerHTML=con.responseText;
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                      }	
         }		
}

function loadPengobatanPrint()
{
    per=document.getElementById('optplafon').options[document.getElementById('optplafon').selectedIndex].value;
    org=document.getElementById('optkodeorg').options[document.getElementById('optkodeorg').selectedIndex].value;
    rs=document.getElementById('optrs').options[document.getElementById('optrs').selectedIndex].value;
    kary=document.getElementById('optkary').options[document.getElementById('optkary').selectedIndex].value;
    
    param='periode='+per+'&kodeorg='+org+'&rs='+rs+'&kary='+kary+'&method=1'; //alert(param);
    tujuan='sdm_slave_getPengobatanList.php';
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
                    document.getElementById('container').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }			
}

function loadPengobatanPrint1()
{
    per=document.getElementById('optplafon1').options[document.getElementById('optplafon1').selectedIndex].value;
    org=document.getElementById('optkodeorg1').options[document.getElementById('optkodeorg1').selectedIndex].value;

    param='periode='+per+'&kodeorg='+org+'&method=2'; //alert(param);
    tujuan='sdm_slave_getPengobatanList.php';
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
                    document.getElementById('container1').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }			
}

function loadPengobatanPrint2()
{
    per=document.getElementById('optplafon2').options[document.getElementById('optplafon2').selectedIndex].value;
    org=document.getElementById('optkodeorg2').options[document.getElementById('optkodeorg2').selectedIndex].value;

    param='periode='+per+'&kodeorg='+org+'&method=3'; //alert(param);
    tujuan='sdm_slave_getPengobatanList.php';
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
                    document.getElementById('container2').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }			
}

function loadPengobatanPrint3()
{
    per=document.getElementById('optplafon3').options[document.getElementById('optplafon3').selectedIndex].value;
    org=document.getElementById('optkodeorg3').options[document.getElementById('optkodeorg3').selectedIndex].value;

    param='periode='+per+'&kodeorg='+org+'&method=4'; //alert(param);
    tujuan='sdm_slave_getPengobatanList.php';
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
                    document.getElementById('container3').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }			
}

function loadPengobatanPrint4()
{
    per=document.getElementById('optplafon4').options[document.getElementById('optplafon4').selectedIndex].value;
    org=document.getElementById('optkodeorg4').options[document.getElementById('optkodeorg4').selectedIndex].value;

    param='periode='+per+'&kodeorg='+org+'&method=5'; //alert(param);
    tujuan='sdm_slave_getPengobatanList.php';
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
                    document.getElementById('container4').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }			
}

function printKlaim()
{
    per=document.getElementById('optplafon').options[document.getElementById('optplafon').selectedIndex].value;
    org=document.getElementById('optkodeorg').options[document.getElementById('optkodeorg').selectedIndex].value;
    rs=document.getElementById('optrs').options[document.getElementById('optrs').selectedIndex].value;
    kary=document.getElementById('optkary').options[document.getElementById('optkary').selectedIndex].value;
    document.getElementById('frmku').src='sdm_2laporanKlaimToExcel.php?periode='+per+'&kodeorg='+org+'&rs='+rs+'&kary='+kary;	
//    alert(document.getElementById('frmku').src);
}

function printKlaim1()
{
    per=document.getElementById('optplafon1').options[document.getElementById('optplafon1').selectedIndex].value;
    org=document.getElementById('optkodeorg1').options[document.getElementById('optkodeorg1').selectedIndex].value;
    document.getElementById('frmku1').src='sdm_2laporanKlaimToExcel1.php?periode='+per+'&kodeorg='+org;	
}

function printKlaim2()
{
    per=document.getElementById('optplafon2').options[document.getElementById('optplafon2').selectedIndex].value;
    org=document.getElementById('optkodeorg2').options[document.getElementById('optkodeorg2').selectedIndex].value;
    document.getElementById('frmku2').src='sdm_2laporanKlaimToExcel2.php?periode='+per+'&kodeorg='+org;	
}

function printKlaim3()
{
    per=document.getElementById('optplafon3').options[document.getElementById('optplafon3').selectedIndex].value;
    org=document.getElementById('optkodeorg3').options[document.getElementById('optkodeorg3').selectedIndex].value;
    document.getElementById('frmku3').src='sdm_2laporanKlaimToExcel3.php?periode='+per+'&kodeorg='+org;	
//    alert(org);
}
function printKlaim4()
{
     per=document.getElementById('optplafon4').options[document.getElementById('optplafon4').selectedIndex].value;
    org=document.getElementById('optkodeorg4').options[document.getElementById('optkodeorg4').selectedIndex].value;
    document.getElementById('frmku3').src='sdm_2laporanKlaimToExcel4.php?periode='+per+'&kodeorg='+org;   
}

function previewPengobatan(notransaksi,ev)
{
    param='notransaksi='+notransaksi;
    tujuan='sdm_slave_previewPengobatan.php';
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
                                                       title=notransaksi;
                                                       width='500';
                                                       height='400';
                                                       content="<div style='height:390px;width:490px;overflow:auto;'>"+con.responseText+"</div>";
                                                       showDialog1(title,content,width,height,ev);
                                            }
                                    }
                                    else {
                                            busy_off();
                                            error_catch(con.status);
                                    }
                  }	
     }			
}

function savePClaim(no,notransaksi,bebanperusahaan,gapok,bayar)
{
    bayar=remove_comma(document.getElementById('bayar'+no));
    tglbayar=remove_comma(document.getElementById('tglbayar'+no));

    if(notransaksi=='' || bayar=='' || tglbayar.length!=10){
            alert('Nilai dibayar harus diisi');
    }
    else if(bayar==0.00)
    {
            alert('Nilai dibayar harus lebih besar dari 0');
    }else if(bebanperusahaan > bayar){
			alert('Nilai dibayar harus lebih kecil atau sama dengan beban perusahaan');
	}else
    {
            param='notransaksi='+notransaksi+'&bayar='+bayar+'&tglbayar='+tglbayar;
            if(confirm('Saving payment '+notransaksi+', Are you sure..?'))
            tujuan='sdm_simpanPembayaranKlaim.php';
            post_response_text(tujuan, param, respog);
    }
    function respog()
    {
                  if(con.readyState==4)
                  {
                            if (con.status == 200) {
                                            busy_off();
                                            if (!isSaveResponse(con.responseText)) {
                                                    document.getElementById('bayar'+no).style.backgroundColor='red';
                                                    alert(con.responseText);
                                            }
                                            else {
                                                    //document.getElementById('bayar'+no).style.backgroundColor='#C3DAF9';
													document.getElementById('bayar'+no).disabled=true;
													document.getElementById('btnsavePClaim'+no).style.display='none';
													document.getElementById('tglbayar'+no).disabled=true;
													
                                            }
                                    }
                                    else {
                                            busy_off();
                                            error_catch(con.status);
                                    }
                  }	
     }	
}

function loadOptkar(lokasitugas){
    param='kodeorganisasi='+lokasitugas;
    tujuan='sdm_slaveGetKaryawanPengobatan.php';
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
                                    document.getElementById('karyawanid').innerHTML=con.responseText;
                            }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
            }	
         }    
}

function previewPerorang(karyawanid,ev)
{
        tahun=document.getElementById('optplafon2').options[document.getElementById('optplafon2').selectedIndex].value;
        param='karyawanid='+karyawanid+'&tahun='+tahun;
        tujuan='sdm_slave_previewPengobatanPerorang.php';
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
                                                   title='Medical detail : '+karyawanid+' Period : '+tahun;
                                                   width='820';
                                                   height='300';
                                                   content="<fieldset><div style='height:285px;width:800px;overflow:auto;'>"+con.responseText+"</div></fieldset>";
                                                   showDialog1(title,content,width,height,ev);
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                    }	
         }			
}

function loadPengobatanPrint5()
{
    per=document.getElementById('optplafon5').options[document.getElementById('optplafon5').selectedIndex].value;
    karyawanid=document.getElementById('karyawanid').options[document.getElementById('karyawanid').selectedIndex].value;

    param='periode='+per+'&karyawanid='+karyawanid+'&method=6'; //alert(param);
    tujuan='sdm_slave_getPengobatanList.php';
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
                    document.getElementById('container5').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }			
}

function printKlaim5()
{
     per=document.getElementById('optplafon5').options[document.getElementById('optplafon5').selectedIndex].value;
    karyawanid=document.getElementById('karyawanid').options[document.getElementById('karyawanid').selectedIndex].value;
    nama=document.getElementById('karyawanid').options[document.getElementById('karyawanid').selectedIndex].text;
    document.getElementById('frmku5').src='sdm_2laporanKlaimToExcel5.php?periode='+per+'&karyawanid='+karyawanid+'&nama='+nama;   
}

function loadPengobatanPrint6()
{
    per=document.getElementById('optplafon6').options[document.getElementById('optplafon6').selectedIndex].value;
    org=document.getElementById('optkodeorg6').options[document.getElementById('optkodeorg6').selectedIndex].value;

    param='periode='+per+'&kodeorg='+org+'&method=7'; //alert(param);
    tujuan='sdm_slave_getPengobatanList.php';
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
                    document.getElementById('container6').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }			
}

function loadPengobatanPrint8()
{
    per=document.getElementById('optplafon8').options[document.getElementById('optplafon8').selectedIndex].value;
//    org=document.getElementById('optkodeorg').options[document.getElementById('optkodeorg').selectedIndex].value;
//    rs=document.getElementById('optrs').options[document.getElementById('optrs').selectedIndex].value;
    kary=document.getElementById('optkary8').options[document.getElementById('optkary8').selectedIndex].value;
    
    param='periode='+per+'&kary='+kary+'&method=8'; //alert(param);
    tujuan='sdm_slave_getPengobatanList.php';
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
                    document.getElementById('container8').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }			
}

function printKlaim6()
{
     per=document.getElementById('optplafon6').options[document.getElementById('optplafon6').selectedIndex].value;
    org=document.getElementById('optkodeorg6').options[document.getElementById('optkodeorg6').selectedIndex].value;
    document.getElementById('frmku6').src='sdm_2laporanKlaimToExcel6.php?periode='+per+'&kodeorg='+org;   
}

function printKlaim8()
{
    per=document.getElementById('optplafon8').options[document.getElementById('optplafon8').selectedIndex].value;
    kary=document.getElementById('optkary8').options[document.getElementById('optkary8').selectedIndex].value;
    document.getElementById('frmku8').src='sdm_2laporanKlaimToExcel8.php?periode='+per+'&kary='+kary;   
}

function getDaftar(){
   per=document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
   
   unit=document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
   jenisbiaya=document.getElementById('jenisbiaya').options[document.getElementById('jenisbiaya').selectedIndex].value;
   tpkar=document.getElementById('tpkar').options[document.getElementById('tpkar').selectedIndex].value;
   
 
   window.location='?periode='+per+'&unit='+unit+'&jenisbiaya='+jenisbiaya+'&tpkar='+tpkar;
}







function printRekapKlaim(){
   per=document.getElementById('optplafon').options[document.getElementById('optplafon').selectedIndex].value;
  document.getElementById('frmku').src='sdm_2laporanKlaimRekapExcel.php?periode='+per;
}

function editPengobatan(notransaksi,karyawanid,jenisbiaya,lokasitugas,thnplafon,periode,rs,byrs,bydr,bylab,byobat,byadmin,ygberobat,diagnosa,tanggal,total,hariistirahat,bebankaryawan,bebanjamsostek,bebanperusahaan,klaim,keterangan,tanggalkwitansi,tanggalpengajuan,plafon,satuan)
{                    
	// alert(plafon);
    document.getElementById('notransaksi').value=notransaksi;
    document.getElementById('karyawanid').value=karyawanid;
    document.getElementById('thnplafon').value=thnplafon;
    document.getElementById('thnplafon').disabled=true
    document.getElementById('periode').value=periode;
    document.getElementById('lokasitugas').value=lokasitugas;
    document.getElementById('jenisbiaya').value=jenisbiaya;
    document.getElementById('plafon').value=plafon;
    document.getElementById('rs').value=rs;
    document.getElementById('byrs').value=byrs;
    document.getElementById('bydr').value=bydr;
    document.getElementById('bylab').value=bylab;
    document.getElementById('byobat').value=byobat;
    document.getElementById('byadmin').value=byadmin;
    document.getElementById('ygberobat').value=ygberobat;
    document.getElementById('diagnosa').value=diagnosa;
    document.getElementById('tanggal').value=tanggal;
    document.getElementById('total').value=total;
    document.getElementById('hariistirahat').value=hariistirahat;
    document.getElementById('bebankaryawan').value=bebankaryawan;
    document.getElementById('bebanjamsostek').value=bebanjamsostek;
    document.getElementById('bebanperusahaan').value=bebanperusahaan;
    document.getElementById('klaim').value=klaim;
    document.getElementById('tanggalkwitansi').value=tanggalkwitansi;
    document.getElementById('tanggalpengajuan').value=tanggalpengajuan;
    document.getElementById('keterangan').value=keterangan.replace(/%20/g, " ");
	if(satuan==1){
		satuanPlafon="/ per tahun";
	}else if(satuan==2){
		satuanPlafon="/ per hari";
	}else if(satuan==3){
		satuanPlafon="/ 1 tahun sekali";
	}else if(satuan==4){
		satuanPlafon="/ 3 tahun sekali";
	}else{
		satuanPlafon="";
	}
    document.getElementById('satuanPlafon').innerHTML=satuanPlafon;
    document.getElementById('method').value="update";

    param='notransaksi='+notransaksi+'&karyawanid='+karyawanid+'&jenisbiaya='+jenisbiaya;
    param+='&lokasitugas='+lokasitugas+'&thnplafon='+thnplafon;
    param+='&periode='+periode+'&rs='+rs+'&byrs='+byrs+'&bydr='+bydr+'&bylab='+bylab;
    param+='&byobat='+byobat+'&byadmin='+byadmin+'&ygberobat='+ygberobat+'&diagnosa='+diagnosa;
    param+='&tanggal='+tanggal+'&total='+total+'&klaim='+klaim+'&keterangan='+keterangan;
    param+='&hariistirahat='+hariistirahat+'&bebankaryawan='+bebankaryawan+'&bebanjamsostek='+bebanjamsostek+'&bebanperusahaan='+bebanperusahaan+'&plafon='+plafon;
//    alert(param);
	tujuan='sdm_slave_savePengobatan.php';
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
                                                       tabAction(document.getElementById('tabFRM0'),0,'FRM',2);
                                                       document.getElementById('mainsavebtn').disabled=false;
                                                    }
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }	
}
