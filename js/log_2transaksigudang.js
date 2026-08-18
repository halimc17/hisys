/**
 * @author repindra.ginting
 */

function showWindowBarang(title,ev)
{

          content= "<div style='width:100%;'>";
          content+="<fieldset style=min-width:95%>Search <input placeholder='input text min 3 char' type=text id=txtnamabarang class=myinputtext size=25 onkeypress=\"return enterEuy(event);\" maxlength=35><button class=mybutton onclick=goCariBarang()>Go</button> </fieldset>";
          content+="<fieldset style=min-width:95%><legend><i>Result</i></legend><div id=containercari style='overflow:auto;max-height:300px'></div></fieldset></div>";
     //display window
           width='';
           height='';
           showDialog5(title,content,width,height,ev);		
}

function enterEuy(evt)
{
        key=getKey(evt);
        if(key==13)
        {
                goCariBarang();
        }
        else
        {
                return tanpa_kutip(evt);
        }

}

function goCariBarang()
{

                txtcari = trim(document.getElementById('txtnamabarang').value);
                                if (txtcari.length < 3) {
                                        alert('material name min. 3 char');
                                }
                                else {
                                        param = 'txtcari=' + txtcari;
                                        tujuan = 'log_slave_2transaksigudangcari.php';
                                        post_response_text(tujuan, param, respog);
                                }
        function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
                                                document.getElementById('containercari').innerHTML=con.responseText;
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }		
}

function loadField(kode)
{
        document.getElementById('kodebarang').value=kode;
        closeDialog();		
}

function setAll()
{
        document.getElementById('kodebarang').value='';
}

function ambilPeriode(gudang)
{
        param='gudang='+gudang;
        tujuan='log_slave_getPeriode.php';
        post_response_text(tujuan, param, respog);

                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
                                                document.getElementById('periode').innerHTML=con.responseText;
                                                document.getElementById('periode2').innerHTML=con.responseText;
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }	

}

function getTransaksiGudang()
{ 
        unit =document.getElementById('unit');
        periode =document.getElementById('periode');
        periode2 =document.getElementById('periode2');
        jenis =document.getElementById('jenis');
        kodebarang =document.getElementById('kodebarang');
                
		kodebarang	=kodebarang.options[kodebarang.selectedIndex].value;
		
                unit	=unit.options[unit.selectedIndex].value;
                // periode =periode.options[periode.selectedIndex].value;
                // periode2=periode2.options[periode2.selectedIndex].value;
                periode =periode.value;
                periode2    =periode2.value;
                jenis	=jenis.options[jenis.selectedIndex].value;
        param='unit='+unit+'&periode='+periode+'&jenis='+jenis+'&kodebarang='+kodebarang+'&periode2='+periode2;
        tujuan='log_slave_2transaksigudang.php';

        if(jenis=='9'){
		   
            // if(kodebarang==''){a
                // alert('For searching of all type of transaction, material code is required');
            // }else{
				post_response_text(tujuan, param, respog);
            // }
        }else{
            post_response_text(tujuan, param, respog);
        }


                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
                                                //showById('printPanel');
                                                document.getElementById('printContainer').innerHTML=con.responseText;
												leftFixedTable();
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }		
}

function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}

function viewDetail(ev,kodevhc,tanggalmulai,tanggalsampai,unit,periode)
{
   param='kodevhc='+kodevhc+'&tanggalmulai='+tanggalmulai+'&tanggalsampai='+tanggalsampai+'&unit='+unit+'&periode='+periode;
   tujuan='vhc_slave_2biayatotalperkendaraandetail.php'+"?"+param;  
   width='500';
   height='400';

   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1('Detail Biaya per Kendaraan '+kodevhc,content,width,height,ev); 

}

function detailExcel(ev,tujuan)
{
    width='300';
   height='100';

   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1('Detail Biaya per Kendaraan',content,width,height,ev); 
}

function transaksiGudangKeExcel(ev,tujuan)
{
        unit =document.getElementById('unit');
        periode =document.getElementById('periode');
        periode2 =document.getElementById('periode2');
        jenis =document.getElementById('jenis');
        kodebarang =document.getElementById('kodebarang').value;
                unit	=unit.options[unit.selectedIndex].value;
                jenis	=jenis.options[jenis.selectedIndex].value;
                // periode =periode.options[periode.selectedIndex].value;
                // periode2    =periode2.options[periode2.selectedIndex].value;
                periode =periode.value;
                periode2    =periode2.value;
        judul='Report Ms.Excel';	
        param='unit='+unit+'&periode='+periode+'&jenis='+jenis+'&kodebarang='+kodebarang+'&periode2='+periode2;
		
		printnopopup(tujuan+"?"+param);
        // printFile(param,tujuan,judul,ev)	
}

function ambilPeriode2(unit)
{
        param='unit='+unit;
        tujuan='sdm_slave_getPeriode.php';
        post_response_text(tujuan, param, respog);

                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
                                                document.getElementById('periode').innerHTML=con.responseText;
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }	

}
