// JavaScript Document

function previewPdf(tgl,karywn,ev)
{
        tglijin=tgl;
        krywnId=karywn;
        param='proses=prevPdf'+'&tglijin='+tglijin+'&krywnId='+krywnId;
        tujuan = 'sdm_slave_laporan_ijin_meninggalkan_kantor.php?'+param;	
 //display window
   title='Print PDF';
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev);

}

function cancelForm()
{
	document.getElementById('tglIzin').disabled=true;
    // document.getElementById('tglIzin').value='';
    document.getElementById('jam1').value=00;
         q=document.getElementById('jam1');
        for(a=0;a<q.length;a++)
        {
        if(q.options[a].value==00)
            {
                q.options[a].selected=true;
            }
        }
        q2=document.getElementById('mnt1');
        for(a2=0;a2<q2.length;a2++)
        {
        if(q2.options[a2].value==00)
            {
                q2.options[a2].selected=true;
            }
        }
         qjm2=document.getElementById('jam2');
        for(aqjm2=0;aqjm2<qjm2.length;aqjm2++)
        {
        if(qjm2.options[aqjm2].value==00)
            {
                qjm2.options[aqjm2].selected=true;
            }
        }
        qmnt2=document.getElementById('mnt2');
        for(aqmnt2=0;aqmnt2<qmnt2.length;aqmnt2++)
        {
        if(qmnt2.options[aqmnt2].value==00)
            {
                qmnt2.options[aqmnt2].selected=true;
            }
        }
        document.getElementById('jnsIjin').value='';
        document.getElementById('tglAwal').value='';
        document.getElementById('tglEnd').value='';
        document.getElementById('keperluan').value='';
        document.getElementById('ket').value='';
        document.getElementById('atsSblm').value='';
        document.getElementById('atsSblm2').value='';
        document.getElementById('atasan').value='';
        document.getElementById('atasan2').value='';
        document.getElementById('hrd').value='';
        document.getElementById('jumlahhk').value='';
		
		document.getElementById('tanggalkerja').value='';
		document.getElementById('alamatcuti').value='';
		document.getElementById('pengganti').value='';
		document.getElementById('nohp').value='';
		
		cb = document.getElementById('hometrip');
		cb.checked = false;
		cb.disabled = false;
		checkhometrip(cb);
		
		document.getElementById("tdkanan").rowSpan = "8";
}

function saveForm()
{
    pros=document.getElementById('proses').value;
	param = "proses="+pros;
	
	tglijin=document.getElementById('tglIzin').value;
    tglAwal=document.getElementById('tglAwal').value;
    tglEnd=document.getElementById('tglEnd').value;
    jnsIjin=document.getElementById('jnsIjin').options[document.getElementById('jnsIjin').selectedIndex].value;
    jam1=document.getElementById('jam1').options[document.getElementById('jam1').selectedIndex].value;
    mnt1=document.getElementById('mnt1').options[document.getElementById('mnt1').selectedIndex].value;
    jam2=document.getElementById('jam2').options[document.getElementById('jam2').selectedIndex].value;
    mnt2=document.getElementById('mnt2').options[document.getElementById('mnt2').selectedIndex].value;
    keperluan=document.getElementById('keperluan').value;
    ket=document.getElementById('ket').value;
    atasan=document.getElementById('atasan').options[document.getElementById('atasan').selectedIndex].value;
    atasan2=document.getElementById('atasan2').options[document.getElementById('atasan2').selectedIndex].value;
    jamDr=jam1+":"+mnt1;
    jamSmp=jam2+":"+mnt2;
    hk=document.getElementById('jumlahhk').value;
    hrd=document.getElementById('hrd').options[document.getElementById('hrd').selectedIndex].value;
    periodec=document.getElementById('periodec').options[document.getElementById('periodec').selectedIndex].value;
    
	cb = document.getElementById('hometrip');
	nohp=document.getElementById('nohp').value;
	tglberangkat=document.getElementById('tglberangkat').value;
	rutekeberangkatan=document.getElementById('rutekeberangkatan').value;
	tglpulang=document.getElementById('tglpulang').value;
	rutekepulangan=document.getElementById('rutekepulangan').value;
	
	param += "&hometrip="+cb.checked;
	param += "&nohp="+nohp;
	param += "&tglberangkat="+tglberangkat;
	param += "&rutekeberangkatan="+rutekeberangkatan;
	param += "&tglpulang="+tglpulang;
	param += "&rutekepulangan="+rutekepulangan;
    
    if(pros=='update')
    {
		atsSblm=document.getElementById('atsSblm').value;
        atsSblm2=document.getElementById('atsSblm2').value;
        param+="&atsSblm="+atsSblm+"&atsSblm2="+atsSblm2;
	}
	
	alamatcuti=document.getElementById('alamatcuti').value;
	tanggalkerja=document.getElementById('tanggalkerja').value;
	pengganti=document.getElementById('pengganti').value;
	
	param += "&alamatcuti="+alamatcuti;
	param += "&tanggalkerja="+tanggalkerja;
	param += "&pengganti="+pengganti;
	param += "&tglijin="+tglijin;
	param += "&tglberangkat="+tglberangkat;
    param += "&jnsIjin="+jnsIjin;
    param += "&jamDr="+jamDr;
    param += "&jamSmp="+jamSmp;
    param += "&keperluan="+keperluan;
    param += "&ket="+ket;
    param += "&atasan="+atasan;
    param += "&atasan2="+atasan2;
    param += "&tglAwal="+tglAwal;
    param += "&tglEnd="+tglEnd;
	param += "&jumlahhk="+hk;
	param += "&hrd="+hrd;
	param += "&periodec="+periodec;
	
	if((jnsIjin=='CUTI' || jnsIjin=='MELAHIRKAN' || jnsIjin=='KAWIN/SUNATAN/WISUDA' || jnsIjin=='CUTIPOTONGGAJI' || jnsIjin=='TERLAMBATPOTONGGAJI' || jnsIjin=='PULANGAWALPOTONGGAJI') && (hk=='0' || hk==''))
	{
		alert('Number of day(s) required');
	}
	else
	{
		tujuan='sdm_slave_cuti_nonstaff.php';
        post_response_text(tujuan, param, respog);
	}
	
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
					cancelForm();
                    loadNData();
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


function loadNData()
{
        param='proses=loadData';
        tujuan='sdm_slave_cuti_nonstaff.php';
        //alert(tujuan);
        function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    // Success Response
                                        //alert(con.responseText);
                                        document.getElementById('contain').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
        post_response_text(tujuan, param, respon);
}
function cariBast(num)
{
                param='proses=loadData';
                param+='&page='+num;
                tujuan = 'sdm_slave_cuti_nonstaff.php';
                post_response_text(tujuan, param, respog);			
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
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
function fillField(keprlan,tanggal,jnsijin,perstjan,statPrstjn,perstjan2,statPrstjn2,drjam,smpjam,hrd,hk,periodec,keterangan,alamatcuti,tanggalkerja,pengganti,nohp,hometrip,tanggalberangkat,rutekeberangkatan,tglpulang,rutekepulangan) 
{

                // param='proses=getKet'+'&tglijin='+tanggal;
                // tujuan = 'sdm_slave_cuti_nonstaff.php';
                // post_response_text(tujuan, param, respog);			
                // function respog(){
                        // if (con.readyState == 4) {
                                // if (con.status == 200) {
                                        // busy_off();
                                        // if (!isSaveResponse(con.responseText)) {
                                                // alert(con.responseText);
                                        // }
                                        // else {
                                                jm1=drjam.split(" ");
                                                tlgAwal=jm1[0].split("-");
                                                tglAwal1=tlgAwal[2]+"-"+tlgAwal[1]+"-"+tlgAwal[0];
                                                jmDari=jm1[1].split(":");


                                                jm2=smpjam.split(" ");
                                                tlgAkhir=jm2[0].split("-");
                                                tlgAkhir1=tlgAkhir[2]+"-"+tlgAkhir[1]+"-"+tlgAkhir[0];
                                                jamSmp=jm2[1].split(":");
                                                document.getElementById('tglIzin').disabled=true;
                                                document.getElementById('tglIzin').value=tanggal;
                                                document.getElementById('keperluan').value=keprlan;
                                                document.getElementById('proses').value='update';
                                                document.getElementById('tglAwal').value=tglAwal1;
                                                document.getElementById('tglEnd').value=tlgAkhir1;
                                                document.getElementById('jumlahhk').value=hk;
                                                 q=document.getElementById('jam1');
                                                  for(a=0;a<q.length;a++)
                                                  {
                                                        if(q.options[a].value==jmDari[0])
                                                            {
                                                                q.options[a].selected=true;
                                                            }
                                                   }
                                                   q2=document.getElementById('mnt1');
                                                  for(a2=0;a2<q2.length;a2++)
                                                  {
                                                        if(q2.options[a2].value==jmDari[1])
                                                            {
                                                                q2.options[a2].selected=true;
                                                            }
                                                   }

                                                   q3=document.getElementById('jam2');
                                                  for(a3=0;a3<q3.length;a3++)
                                                  {
                                                        if(q.options[a3].value==jamSmp[0])
                                                            {
                                                                q3.options[a3].selected=true;
                                                            }
                                                   }
                                                   qakr=document.getElementById('mnt2');
                                                  for(a5=0;a5<qakr.length;a5++)
                                                  {
                                                        if(qakr.options[a5].value==jamSmp[1])
                                                            {
                                                                qakr.options[a5].selected=true;
                                                            }
                                                   }
                                                   jns=document.getElementById('jnsIjin');
                                                  for(ajns=0;ajns<jns.length;ajns++)
                                                  {
                                                        if(jns.options[ajns].value==jnsijin)
                                                            {
                                                                jns.options[ajns].selected=true;
                                                            }
                                                   }
                                                   atsn=document.getElementById('atasan');
                                                  for(aatsn=0;aatsn<atsn.length;aatsn++)
                                                  {
                                                        if(atsn.options[aatsn].value==perstjan)
                                                            {
                                                                atsn.options[aatsn].selected=true;
                                                            }
                                                   }
												   atsn=document.getElementById('atasan2');
                                                  for(aatsn=0;aatsn<atsn.length;aatsn++)
                                                  {
                                                        if(atsn.options[aatsn].value==perstjan2)
                                                            {
                                                                atsn.options[aatsn].selected=true;
                                                            }
                                                   }
                                                  x=document.getElementById('hrd');
                                                  for(j=0;j<x.length;j++)
                                                  {
                                                        if(x.options[j].value==hrd)
                                                            {
                                                                x.options[j].selected=true;
                                                            }
                                                   }   
                                                  x=document.getElementById('periodec');
                                                  for(j=0;j<x.length;j++)
                                                  {
                                                        if(x.options[j].value==periodec)
                                                            {
                                                                x.options[j].selected=true;
                                                            }
                                                   }                                                     
                                                   document.getElementById('atsSblm').value='';
                                                   document.getElementById('atsSblm').value=perstjan;
												   document.getElementById('atsSblm2').value='';
                                                   document.getElementById('atsSblm2').value=perstjan2;
                                                   document.getElementById('ket').value=keterangan;
												   
												   document.getElementById('alamatcuti').value=alamatcuti;
												   document.getElementById('tanggalkerja').value=tanggalkerja;
												   document.getElementById('pengganti').value=pengganti;
												   
												   document.getElementById('nohp').value=nohp;
												   
												   cb = document.getElementById('hometrip');
													cb.disabled = true;
												   if(hometrip==1)
												   {
														cb.checked = true;
														checkhometrip(cb);
														document.getElementById('tglberangkat').value=tanggalberangkat;
														document.getElementById('rutekeberangkatan').value=rutekeberangkatan;
														document.getElementById('tglpulang').value=tglpulang;
														document.getElementById('rutekepulangan').value=rutekepulangan;
												   }
												   
												   
												   
                                        // }
                                // }
                                // else {
                                        // busy_off();
                                        // error_catch(con.status);
                                // }
                        // }
                // }	
// 
}
function cariTransaksi()
{
        txtSearch=document.getElementById('txtsearch').value;
        txtTgl=document.getElementById('tgl_cari').value;

        param='txtSearch='+txtSearch+'&txtTgl='+txtTgl+'&proses=cariTransaksi';
        //alert(param);
        tujuan='sdm_slave_cuti_nonstaff.php';
        post_response_text(tujuan, param, respog);			
        function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
                                                document.getElementById('list_ganti').style.display='block';
                                                document.getElementById('headher').style.display='none';
                                                document.getElementById('detail_ganti').style.display='none';
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
function dataKePDF(notrans,ev)
{
        noTrans	= notrans;
        tujuan='vhc_DetailPenggantianKomponen_pdf.php';
        judul= noTrans;		
        param='noTrans='+noTrans;
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
function delData(tgl)
{
        tglijin=tgl;
        param='tglijin='+tglijin+'&proses=deleteData';
        tujuan='sdm_slave_cuti_nonstaff.php';
        if(confirm("Deleting, are you sure !!"))
        post_response_text(tujuan, param, respog);

        function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
                                                        loadNData();
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }	



}

function loadSisaCuti(karyawanid)
{
	jnsIjin=document.getElementById('jnsIjin').value;
	periode=document.getElementById('periodec').value;
    param='periode='+periode+'&karyawanid='+karyawanid+'&jnsIjin='+jnsIjin;
    tujuan='sdm_slave_ijin_getSisaCuti.php';
    post_response_text(tujuan, param, respog);
    function respog(){
            if (con.readyState == 4) {
                if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                        }
                        else {
                            document.getElementById('sis').innerHTML=con.responseText;
                        }
                }
                else {
                        busy_off();
                        error_catch(con.status);
                }
            }
       }    
}

function checkhometrip(cb)
{
	document.getElementById('tglberangkat').value = '';
	document.getElementById('rutekeberangkatan').value = '';
	document.getElementById('tglpulang').value = '';
	document.getElementById('rutekepulangan').value = '';
	
	if(cb.checked==true)
	{
		document.getElementById('trtanggalberangkat').style.display = '';
		document.getElementById('trrutekeberangkatan').style.display = '';
		document.getElementById('trtanggalpulang').style.display = '';
		document.getElementById('trrutekepulangan').style.display = '';
		document.getElementById("tdkanan").rowSpan = "12";
	}
	else
	{
		document.getElementById('trtanggalberangkat').style.display = 'none';
		document.getElementById('trrutekeberangkatan').style.display = 'none';
		document.getElementById('trtanggalpulang').style.display = 'none';
		document.getElementById('trrutekepulangan').style.display = 'none';
		document.getElementById("tdkanan").rowSpan = "8";
	}
}