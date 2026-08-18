// JavaScript Document
// JavaScript Document

/*buka tambahan*/


function getsuprph(){
	nodph = trim(document.getElementById('nodph').value);
	param='proses=getsuprph'+'&nodph='+nodph;
	tujuan = 'log_slave_save_po.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('suprph').innerHTML=con.responseText;			
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function getbank(rek){
        id_sup=document.getElementById('supplier_id').options[document.getElementById('supplier_id').selectedIndex].value;
        param='supplier_id='+id_sup;
		 param+='&rek='+rek;
        param+='&proses=cek_supplier_bank';
        tujuan='log_slave_save_po.php';
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
												 document.getElementById('bank_acc').innerHTML=con.responseText;			
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                                  }	
                         } 
                         post_response_text(tujuan, param, respog);

}

function numberFormat(number,digit) {
      number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
      //Seperates the components of the number
      var components = (parseFloat(number).toFixed(digit)).split(".");
      //Comma-fies the first part
      components [0] = components [0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      //Combines the two sections
      return components.join(".");
}

function clearfile(){
	document.getElementById('fileupload').value='';
}


function deletefile(nopo){
	param='proses=deletefile'+'&nopo='+nopo;
	tujuan = 'log_slave_save_po.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					displayList();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function savefile(){
	var fileup = document.getElementById("fileupload").files[0];
	var formdata = new FormData();	
	formdata.append("fileup", fileup);
	formdata.append("nopo", getValue('no_po'));
	formdata.append("fileupload", getValue('fileupload'));
	var con = createXMLHttpRequest();
	con.open("POST", "log_slave_save_po_lokal.php?proses=savefile", true);
	con.onreadystatechange = eval(respon);
	con.send(formdata);
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					save_headher();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function adddph(){
	kode_pt=trim(document.getElementById('kode_pt').value);
	nodph = trim(document.getElementById('nodph').value);
	suprph = trim(document.getElementById('suprph').value);
	param='proses=adddph'+'&nodph='+nodph+'&kode_pt='+kode_pt+'&suprph='+suprph;
	tujuan = 'log_slave_save_po_lokal.php';
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//document.getElementById('dataAtas').style.display='block';
					//displayList();
					// alert(con.responseText);
					ar=con.responseText.split(",");
					fillField(ar[0],ar[1],ar[2],ar[3],ar[4],
								ar[5],ar[6],ar[7],ar[8],ar[9],
								ar[10],ar[11],ar[12],ar[13],ar[14],
								ar[15],ar[16],ar[17],ar[18],ar[19],ar[20],ar[21],ar[22],ar[23]);
					document.getElementById('nodph').value='';			
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function lihatfile(doc,ev){
    param = 'proses=lihatfile' + '&doc=' + doc;
    title="Data Detail";
     showDialog2(title,"<iframe frameborder=0 style='width:795px;height:395px'"+
    " src='log_slave_save_po_lokal.php?"+param+"'></iframe>",'800','400',ev);	
    var dialog = document.getElementById('dynamic2');
    dialog.style.top = '50px';
    dialog.style.left = '15%';
}

/*tutup tambahan*/

function addCommas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}

function clear_all_data()
{
        status_inputan=0;
        document.getElementById('no_po').value='';
        document.getElementById('supplier_id').value='';
        //document.getElementById('tgl_krm').value='';
        //document.getElementById('tmpt_krm').value='';
        document.getElementById('bank_acc').value='';
        document.getElementById('npwp_sup').value='';
        document.getElementById('txtsearch').value='';
        document.getElementById('tgl_cari').value='';
        document.getElementById('proses').value='insert';
        document.getElementById('tgl_krm').value='';
        document.getElementById('term_pay').value='';
        document.getElementById('ketUraian').value='';
        document.getElementById('mtUang').value='';
        document.getElementById('persetujuan_id').value='';
		document.getElementById('fileupload').value='';
        document.getElementById('delivtime').value='';
		// document.getElementById('npwporg').value='';
}
function show_list_pp()
{
        clear_all_data();

        document.getElementById('container_pp').innerHTML='';
        document.getElementById('list_po').style.display='none';
    document.getElementById('list_pp').style.display='block';
        document.getElementById('form_po').style.display='none';
        document.getElementById('kode_pt').value='';

                var tbl = document.getElementById("list_pp_table");
        var row = tbl.rows.length;
        row=row-2;
        for(i=1;i<=row;i++)
        {
         document.getElementById('plh_pp_'+i).checked=false;
        }

}


function cek_pp_pt(kdpt)
{
       // clear_all_data();

   if(kdpt=='')
   {

    kode_pt=document.getElementById('kode_pt').options[document.getElementById('kode_pt').selectedIndex].value;
   }
   else
   {
           show_list_pp();
           kode_pt=kdpt;
           document.getElementById('kode_pt').disabled=true;
           document.getElementById('kode_pt').value=kdpt;
   }
    user_id=trim(document.getElementById('user_id').value);
    param='kodept='+kode_pt+'&id_user='+user_id;
    param+="&proses=listPp";
  // alert(param);
//    return;
     tujuan='log_slave_po_lokal_detail.php';

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
                        //show_form_po();
                        document.getElementById('container_pp').innerHTML=con.responseText;
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
      }
     }
     post_response_text(tujuan, param, respog);
}
function display_number(id)
{
        if(id!='')
        {
            sat=document.getElementById('harga_satuan_'+id);
            if(document.getElementById('mtUang').options[document.getElementById('mtUang').selectedIndex].value=='IDR'){
                change_number(sat);
            }                
                grnd_total();       
        }
        else
        {
                nilDis=document.getElementById('angDiskon');
                change_number(nilDis);
        }
}
function normal_number(id)
{
                satu=document.getElementById('harga_satuan_'+id);
                satu.value=remove_comma_var(satu.value);
}

function calculate(id)
{
	//alert(row);
    defult_tot=document.getElementById('realisasi_'+id).value;
    jmlh_brg=document.getElementById('jmlhDiminta_'+id).value;
    harga=document.getElementById('harga_satuan_'+id).value;
	document.getElementById('hidden_harga_satuan_'+id).value=harga;
	
	if((parseFloat(jmlh_brg))>=(parseFloat(defult_tot)+1)) {
		alert('Quantity must equal or lower then total requested');
		document.getElementById('jmlhDiminta_'+id).value='';
		return;		
	} else {
		if(jmlh_brg==''||harga=='') {
			a=document.getElementById('total_'+id);
			a.value='';
			a=parseFloat(a.value);
		} else {
			harg=document.getElementById('harga_satuan_'+id);
			harg.value=remove_comma_var(harg.value);
			jmlh_sub=jmlh_brg*harg.value;
			
			if(jmlh_sub==0) {
				document.getElementById('total_'+id).value=0;
			} else {
				as=document.getElementById('total_'+id);
				as.value=jmlh_sub
				if(document.getElementById('mtUang').options[document.getElementById('mtUang').selectedIndex].value=='IDR'){
					change_number(as);
				}
			}
		}
    }
    grnd_total();
}

function grnd_total() {
    var tbl = document.getElementById("detailBody");
    var row = tbl.rows.length;
    row=row-8;
    total=0;
	for(i=0;i<row;i++) {
		b=document.getElementById('total_'+i);
		b.value=remove_comma_var(b.value);
		total+=parseFloat(b.value);
		if(document.getElementById('mtUang').options[document.getElementById('mtUang').selectedIndex].value=='IDR'){
			change_number(b);
		}
        if(isNaN(total)) {
            total=0;
        }
    }
	
	tot=document.getElementById('total_harga_po');
	tot.value=total;
	if(document.getElementById('mtUang').options[document.getElementById('mtUang').selectedIndex].value=='IDR') {
        change_number(tot);
    }
    grandTotal();
}

function plusAll(id)
{
        isiData = document.getElementById("detailBody");
        barisIsi = isiData.rows.length;
        barisIsi=barisIsi-7;
        total=0;
        for(i=0;i<barisIsi;i++)
        {
                b=document.getElementById('total_'+i);
                b.value=remove_comma_var(b.value);
                total+=parseFloat(b.value);
                change_number(b);
                // alert(b+"------"+total);
                //alert(b.value);
                //change_number(b);
                if(isNaN(total))
                   {
                           total=0;
                   }
        }
        document.getElementById('total_harga_po').value=total;
        tot=document.getElementById('total_harga_po');
        tot.value=total;
        change_number(tot);


        //hitung diskon
        //nilPpn=document.getElementById('ppn').value;
        nil_dis=document.getElementById('diskon').value;
        angk=document.getElementById('angDiskon').value;
        if(nil_dis!="")
        {
        disc=(nil_dis*total)/100;
                nilaiDis=document.getElementById('angDiskon');
                nilaiDis.value=disc;
                change_number(nilaiDis);
                document.getElementById('nilai_diskon').value=disc;		
        }
        else
        {
                document.getElementById('diskon').value=0;
                disc=(nil_dis*total)/100;
                nilaiDis=document.getElementById('angDiskon');
                nilaiDis.value=disc;
                change_number(nilaiDis);
                document.getElementById('nilai_diskon').value=disc;	
                /*document.getElementById('ppN').value=0;
                document.getElementById('ppn').value=0;
                nilPpn=0;*/
        }

        //ppn
        nPPn=document.getElementById('ppN').value;
        if(nPPn!="")
        {
                //nilP=document.getElementById('ppN').value;
                //dis=document.getElementById('nilai_diskon');
                //subTot=document.getElementById('total_harga_po');
                //dis.value=remove_comma(dis);
                //subTot.value=remove_comma(subTot);
                nilPpn=(parseFloat((total-disc))*nPPn)/100;	
                document.getElementById('hslPPn').innerHTML=nilPpn;
                document.getElementById('ppn').value=nilPpn;
        }
        else
        {
                document.getElementById('ppN').value=0;
                document.getElementById('ppn').value=0;
                nilPpn=0;
        }
        //alert(total+"__"+disc+"___"+nilPpn);
        grnd_tot=parseFloat((total-disc))+parseFloat(nilPpn);
    test=document.getElementById('grand_total');
        test.value=grnd_tot;
        change_number(sb_tot);
        change_number(nilPpn);
        change_number(total);

}
function getZero()
{
        dis=document.getElementById('diskon');
        if(dis.value=="")
        {
                dis.value=0;
        }
        nPpn=document.getElementById('ppN');
        if(nPpn.value=="")
        {
                nPpn.value=0;
        }
        angdis=document.getElementById('angDiskon');
        //angdis.value=remove_comma(angdis);
        if(angdis.value=="")
        {
                angdis.value=0;
        }
}
function periksa_isi(obj)
{
        if(trim(obj.value)=='')	
        {
                alert('Please complete the form');
                obj.focus();
                return;
        }
}
function cek_isi(obj)
{
        if(trim(obj.value)!='')	
        {
                change_number(obj.value);
        }
        else
        {
                change_number(obj.value);
        }
}
function calculate_diskon()
{
        sb_tot=document.getElementById('total_harga_po');
        sb_tot.value=remove_comma_var(sb_tot.value);
        nil_dis=document.getElementById('diskon').value;
        angk=document.getElementById('angDiskon').value;
        if((nil_dis==0)||(angk==0))
        {
                document.getElementById('angDiskon').disabled=false;
                document.getElementById('diskon').disabled=false;
        }
        if((nil_dis!=0)||(angk!=0))
        {
                document.getElementById('angDiskon').disabled=true;
                if(nil_dis>100)
                {	
                        alert('Discount must lower than 100%');
                        document.getElementById('diskon').value='';
                        document.getElementById('angDiskon').disabled=false;
                }
                else
                {
                        disc=(nil_dis*sb_tot.value)/100;
                }
                 //  	grnd_tot=(sb_tot.value-disc)+pn;
                        //document.getElementById('angDiskon').value=disc;
                        nilaiDis=document.getElementById('angDiskon');
                        nilaiDis.value=disc;
                        document.getElementById('nilai_diskon').value=disc;
                        change_number(nilaiDis);
                        calculatePpn();
                        grandTotal();
        }


/*	document.getElementById('ppn').value=pn;
        pn=document.getElementById('ppn');
        change_number(pn);
*/	
                /*document.getElementById('grand_total').value=grnd_tot;
        total=document.getElementById('grand_total');
        change_number(total);
*/        

}


function calculate_angDiskon()
{
        nilDis=document.getElementById('angDiskon');
        nilDis.value=remove_comma(nilDis);
        if(nilDis.value!=0)
        {
                document.getElementById('diskon').disabled=true;
                subTot=document.getElementById('total_harga_po');
                subTot.value=remove_comma(subTot);
                if(nilDis.value!=subTot.value)
                {
                        persenDis=parseFloat(nilDis.value/subTot.value)*100;
                }
                if(persenDis<100)
                {
                        persen=(persenDis);
						// alert(persen);
                        document.getElementById('nilai_diskon').value=nilDis.value;
                        document.getElementById('diskon').value=persen;
                        //sbTot=document.getElementById('total_harga_po').value
                }
                else 
                {
                        alert("Discount value is wrong");
                        document.getElementById('angDiskon').value='';
                        document.getElementById('diskon').value='';
                        document.getElementById('nilai_diskon').value='';
                        document.getElementById('diskon').disabled=false;
                }

                //nilDiskon=document.getElementById('angDiskon').value;
			calculatePpn();
			grandTotal();
        }
        else if(nilDis.value==0)
        {
                document.getElementById('diskon').disabled=false;
				document.getElementById('diskon').value=0;
        }
}





function calculatePbbkb()
{		
		NilPbbkb = document.getElementById('pbbkb').value;
		if(NilPbbkb == ""){
			document.getElementById('pbbkb').value=0;
		}
		grandTotal();
}
function checkChkPpn(){
	
	chkPpn=document.getElementById('chkPpn').checked;
	ppN=document.getElementById('ppN');
	var tbl2 = document.getElementById("detailBody");
    var rowChk = tbl2.rows.length;
    rowChk=rowChk-8;
	
	for(j=0;j<rowChk;j++){
		b=document.getElementById('harga_satuan_'+j);
		c=document.getElementById('hidden_harga_satuan_'+j).value;
		if(chkPpn!=true){
			ppN.disabled = false;
			ppN.value = 0;
			b.disabled = false;
			if(b.value==c)
				b.value=(remove_comma(b)*1.1);
			else
				b.value=c;
			change_number(b);
			//tambahan
			document.getElementById('ppN').value=10;
			calculatePpn();
			//tutup tambahan
		}else{
			ppN.disabled = true;
			ppN.value = 10;
			b.disabled = true;
			b.value=(remove_comma(b)/1.1);
			change_number(b);
		}
	}
	
	var tbl3 = document.getElementById("detailBody");
    var rowChk3 = tbl3.rows.length;
    rowChk3=rowChk3-8;
	for(k=0;k<rowChk3;k++){
		// calculate(k);
		
		defult_tot=document.getElementById('realisasi_'+k).value;
		jmlh_brg=document.getElementById('jmlhDiminta_'+k).value;
		harga=document.getElementById('harga_satuan_'+k).value;

		if((parseFloat(jmlh_brg))>=(parseFloat(defult_tot)+1)){
			alert('Quantity must equal or lower then total requested');
			document.getElementById('jmlhDiminta_'+k).value='';
			return;		
		}else{
			if(jmlh_brg==''||harga==''){
				a=document.getElementById('total_'+k);
				a.value='';
				a=parseFloat(a.value);
			}else{
				harg=document.getElementById('harga_satuan_'+k);
				harg.value=remove_comma_var(harg.value);
				jmlh_sub=jmlh_brg*harg.value;

				if(jmlh_sub==0){
					document.getElementById('total_'+k).value=0;
				}else{
					as=document.getElementById('total_'+k);
					as.value=jmlh_sub
					if(document.getElementById('mtUang').options[document.getElementById('mtUang').selectedIndex].value=='IDR'){
						change_number(as);
					}
				}
			}
		}
	}
	
	var tbl4 = document.getElementById("detailBody");
    var row4 = tbl4.rows.length;
    row4=row4-8;
	total=0;
   
	for(l=0;l<row4;l++){
		b=document.getElementById('total_'+l);
		b.value=remove_comma_var(b.value);
		total+=parseFloat(b.value);
		if(document.getElementById('mtUang').options[document.getElementById('mtUang').selectedIndex].value=='IDR'){
			change_number(b);
		}
		if(isNaN(total)){
			total=0;
		}
	}
	
	tot=document.getElementById('total_harga_po');
	tot.value=total;
	
	if(document.getElementById('mtUang').options[document.getElementById('mtUang').selectedIndex].value=='IDR'){
		// change_number(tot.value);
		tot.value=total;
	}
	calculatePpn();
}


function calculatePpn()
{
        var reg = /^[0-9]{1,2}$/;
        nilP=document.getElementById('ppN').value;
        dis=document.getElementById('nilai_diskon');
        subTot=document.getElementById('total_harga_po');
		//alert(reg);
        // if(reg.test(nilP))
        // {
                // if(nilP==10)
                // {
						// dis.value=remove_comma(dis);
                        // subTot.value=remove_comma(subTot);
                        // pn=(parseFloat((subTot.value-dis.value))*10)/100;	
                        // document.getElementById('hslPPn').innerHTML=pn;
                        // document.getElementById('ppn').value=pn;
                // }

                // else if(nilP==0)
                // {
                        // dis.value=remove_comma(dis);
                        // subTot.value=remove_comma(subTot);
                        // pn=(parseFloat((subTot.value-dis.value))*nilP)/100;	
                        // document.getElementById('hslPPn').innerHTML=nilP;
                        // document.getElementById('ppn').value=pn;
                // }	
                // else if(nilP==2)
                // {
                        // dis.value=remove_comma(dis);
                        // subTot.value=remove_comma(subTot);
                        // pn=(parseFloat((subTot.value-dis.value))*nilP)/100;	
                        // document.getElementById('hslPPn').innerHTML=nilP;
                        // document.getElementById('ppn').value=pn;
                // }	
        // }
        // else
        // {
                // // alert("Please provide number between 0 and 10");
                // document.getElementById('hslPPn').value='';
                // document.getElementById('ppN').value='';
                // return;

        // }
		
		if(nilP > 100){
			alert('PPN must lower than 100%');
			document.getElementById('ppN').value='0';
			document.getElementById('ppn').value='0';
			document.getElementById('hslPPn').innerHTML='0';
		}

                grandTotal();
}
nilPpn=0;
function calculatePph()
{
        var reg = /^[0-9]{1,2}$/;
        nilP=document.getElementById('ppH').value;
        dis=document.getElementById('nilai_diskon');
        subTot=document.getElementById('total_harga_po');
        //alert(reg);
        if(reg.test(nilP))
        {
                if(nilP==10)
                {
                        dis.value=remove_comma(dis);
                        subTot.value=remove_comma(subTot);
                        pn=(parseFloat((subTot.value-dis.value))*nilP)/100;	
                        document.getElementById('hslPPh').innerHTML=pn;
                        document.getElementById('pph').value=pn;
                }

                else if(nilP==0)
                {
                        dis.value=remove_comma(dis);
                        subTot.value=remove_comma(subTot);
                        pn=(parseFloat((subTot.value-dis.value))*nilP)/100;	
                        document.getElementById('hslPPh').innerHTML=nilP;
                        document.getElementById('pph').value=pn;
                }	
                else if(nilP==2)
                {
                        dis.value=remove_comma(dis);
                        subTot.value=remove_comma(subTot);
                        pn=(parseFloat((subTot.value-dis.value))*nilP)/100;	
                        document.getElementById('hslPPh').innerHTML=nilP;
                        document.getElementById('pph').value=pn;
                }	
        }
        else
        {
                // alert("Please provide number between 0 and 10");
                document.getElementById('hslPPh').value='';
                document.getElementById('pph').value='';
                return;

        }

                grandTotal();
}
nilPph=0;
function grandTotal()
{
        sb_tot=document.getElementById('total_harga_po');
        sb_tot.value=remove_comma(sb_tot);
        nilDiskon=document.getElementById('angDiskon');
		nilPbbkb=document.getElementById('pbbkb').value;
        pph=document.getElementById('ppH');
        ppn=document.getElementById('ppN');
        if(nilDiskon.value!=""||nilDiskon.value!=0)
        {
                nilDiskon.value=remove_comma(nilDiskon);
                //nilPpn=document.getElementById('ppn').value;
        }
        else
        {
                document.getElementById('diskon').value=0;
                nilDiskon.value=0;
//		document.getElementById('ppN').value=0;
//		document.getElementById('ppn').value=0;
//		nilPpn=0;
        }
		
		if(ppn.value!=0||ppn.value!='')
        {
            nilPpn=(parseFloat((sb_tot.value-nilDiskon.value))*ppn.value)/100;	
            // document.getElementById('hslPPn').innerHTML=nilPpn;
            // document.getElementById('ppn').value=nilPpn;   
			document.getElementById('hslPPn').innerHTML=numberFormat(nilPpn,2);
            document.getElementById('ppn').value=numberFormat(nilPpn,2);   
			
        }
        else
        {
            document.getElementById('ppN').value=0;
            document.getElementById('ppn').value=0;
            document.getElementById('hslPPn').innerHTML=0;
            nilPpn=0;
        }
		
		
		if(pph.value!=0||pph.value!='')
        {
            nilPph=(parseFloat((sb_tot.value-nilDiskon.value))*pph.value)/100;	
            // document.getElementById('hslPPh').innerHTML=nilPph;
            // document.getElementById('pph').value=nilPph;   
			document.getElementById('hslPPh').innerHTML=numberFormat(nilPph,2);
            document.getElementById('pph').value=numberFormat(nilPph,2);   
        }
        else
        {
            document.getElementById('ppH').value=0;
            document.getElementById('ppH').value=0;
            document.getElementById('hslPPh').innerHTML=0;
            nilPph=0;
        }

        grnd_tot=parseFloat((sb_tot.value-nilDiskon.value))+parseFloat(nilPpn)+parseFloat(nilPbbkb)-parseFloat(nilPph);
       
	   addcost=document.getElementById('addcost').value;//tambahan
	   if(addcost=='' || addcost=='-'){
		   document.getElementById('addcost').value=0;
		   addcost=0;
	   }
	   
		grnd_tot=parseFloat(grnd_tot)+parseFloat(addcost);//tambahan
		if(isNaN(grnd_tot)) {
            grnd_tot=0;
        }
	   total=document.getElementById('grand_total');
        total.value=grnd_tot;

        if(document.getElementById('mtUang').options[document.getElementById('mtUang').selectedIndex].value=='IDR'){
			change_number(sb_tot);
			// change_number(ppn);
			change_number(total);
        }		
}
function process()
{
    clear_all_data();
	document.getElementById('btncancel').value = "hapus";
    var tbl = document.getElementById("container_pp");
    var row = tbl.rows.length;
    row=row-1;
        //alert(row);
        strUrl = '';
    for(i=1;i<=row;i++)
        {
                  ar=document.getElementById('plh_pp_'+i);
           if(ar.checked==true)
                   {
            //alert(i);           
                                try{
                                        if(strUrl != '')
                                        {
                                                strUrl += '&nopp[]='+trim(document.getElementById('nopp_x'+i).innerHTML)
                                                       +'&kdbrg[]='+trim(document.getElementById('kdbrg_'+i).innerHTML);
                                        }
                                        else
                                        {
                                                strUrl += '&nopp[]='+trim(document.getElementById('nopp_x'+i).innerHTML)
                                                       +'&kdbrg[]='+trim(document.getElementById('kdbrg_'+i).innerHTML);
                                        }
                                }
                                catch(e){}

                        }
        }

                //return;
                if(strUrl=='')
                {
                        alert('Choose one');
                        return;
                }
                else
                {
                    kodePt=document.getElementById('kode_pt').options[document.getElementById('kode_pt').selectedIndex].value;
                    param="proses=createTable"+"&baris="+row+'&kode_pt='+kodePt;
                    param+=strUrl;
                    //alert(param);
                    tujuan='log_slave_po_lokal_detail.php';
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

                                  // document.getElementById('detail_content').innerHTML=con.responseText;
                                   //generate_nopo();
                                   document.getElementById('dataAtas').style.display='none';
                                    show_form_po();
                 var a=con.responseText.split("###");
                                                // window.alert(a[0] + " " + a[1]);

                 document.getElementById('no_po').value=a[0];
                                                  //  alert(con.responseText);
                  document.getElementById('ppDetailTable').innerHTML=a[1];
                  getnpwp();
                 // loadNotifikasi2();
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
                                          }	
                         } 
                         post_response_text(tujuan, param, respog);
                }
                //alert(strUrl);
}

function getnpwp(npwp)
{
    nopo=document.getElementById('no_po').value;
    param='nopo='+nopo;
    param+='&npwporg='+npwp;
    param+='&proses=getnpwp';
    tujuan='log_slave_save_po.php';
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
                 document.getElementById('npwporg').innerHTML=con.responseText;            
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        } 
     } 
     post_response_text(tujuan, param, respog);

}

function show_form_po()
{
    document.getElementById('list_po').style.display='none';
    document.getElementById('list_pp').style.display='none';
    document.getElementById('form_po').style.display='block';
}

function displayList()
{
    document.getElementById('dataAtas').style.display='block';
    document.getElementById('list_po').style.display='block';
    document.getElementById('list_pp').style.display='none';
    document.getElementById('form_po').style.display='none';
    load_new_data();
    clear_all_data();
}

function get_supplier()
{
        id_sup=document.getElementById('supplier_id').options[document.getElementById('supplier_id').selectedIndex].value;
        param='supplier_id='+id_sup;
        param+='&proses=cek_supplier';
        tujuan='log_slave_save_po_lokal.php';
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
                                                                // var a=con.responseText.split(",");
                                                                        // // window.alert(a[0] + " " + a[1]);
                                                                // document.getElementById('bank_acc').value=a[0];
                                                                // //  alert(con.responseText);
                                                                // document.getElementById('npwp_sup').value=a[1];
																 document.getElementById('npwp_sup').value=con.responseText;
																 getbank();
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                                  }	
                         } 
                         post_response_text(tujuan, param, respog);

}
function save_headher()
{
                var tbl = document.getElementById("ppDetailTable");
                var row = tbl.rows.length;
                row=row-6;
                strUrl2 = '';
                for(i=0;i<row;i++){
					try{
						if(strUrl2 != ''){					
							strUrl2 +='&nopp[]='+trim(document.getElementById('rnopp_'+i).value)
							+'&kdbrg[]='+encodeURIComponent(trim(document.getElementById('rkdbrg_'+i).value))
							+'&spekBrg[]='+encodeURIComponent(trim(document.getElementById('spek_brg_'+i).value))
							+'&rjmlh_psn[]='+encodeURIComponent(trim(document.getElementById('jmlhDiminta_'+i).value))
							+'&rhrg_sat[]='+document.getElementById('harga_satuan_'+i).value
							//+'&rmat_uang[]='+encodeURIComponent(trim(document.getElementById('kurs_'+i).value))
							+'&rsatuan_unit[]='+encodeURIComponent(trim(document.getElementById('sat_'+i).value));
						}else{
							strUrl2 +='&nopp[]='+trim(document.getElementById('rnopp_'+i).value)
							+'&kdbrg[]='+encodeURIComponent(trim(document.getElementById('rkdbrg_'+i).value))
							+'&spekBrg[]='+encodeURIComponent(trim(document.getElementById('spek_brg_'+i).value))
							+'&rjmlh_psn[]='+encodeURIComponent(trim(document.getElementById('jmlhDiminta_'+i).value))
							+'&rhrg_sat[]='+document.getElementById('harga_satuan_'+i).value
							//+'&rmat_uang[]='+encodeURIComponent(trim(document.getElementById('kurs_'+i).value))
							+'&rsatuan_unit[]='+encodeURIComponent(trim(document.getElementById('sat_'+i).value));
						}
					}
					catch(e){}
                }
                //alert(document.getElementById('nopp_1').value);
                //alert(strUrl2);
                //grandTotal();
                nopo=document.getElementById('no_po').value;
                tgl_po=document.getElementById('tgl_po').value;
                npwporg=document.getElementById('npwporg').options[document.getElementById('npwporg').selectedIndex].value;
                supplier_id=document.getElementById('supplier_id').options[document.getElementById('supplier_id').selectedIndex].value;
                sub_tot=document.getElementById('total_harga_po');
                sub_tot.value=remove_comma(sub_tot);
                sub_tot=sub_tot.value;
                disc=document.getElementById('diskon').value;
				nil_pbbkb=document.getElementById('pbbkb');
				nil_pbbkb.value=remove_comma(nil_pbbkb);
                nil_pbbkb=nil_pbbkb.value;
				nil_pph=document.getElementById('pph');
                nil_pph.value=remove_comma(nil_pph);
                nil_pph=nil_pph.value;
				nil_ppn=document.getElementById('ppn');
                nil_ppn.value=remove_comma(nil_ppn);
                nil_ppn=nil_ppn.value;
				chkPpn=document.getElementById('chkPpn').checked;
                tgl_deliver=document.getElementById('tgl_krm').value;
                delivery_loc=document.getElementById('tmpt_krm').value;
                cara_pem=document.getElementById('term_pay').value;
                grnd_tot=document.getElementById('grand_total');
                grnd_tot.value=remove_comma(grnd_tot);
                grnd_tot=grnd_tot.value;
                purchs=document.getElementById('user_id').value;
                lokasi_peng=document.getElementById('tmpt_krm').value;
                
				nil_diskon=document.getElementById('nilai_diskon');
				nil_diskon.value=remove_comma(nil_diskon);
                nil_diskon=nil_diskon.value;
				
                rproses=document.getElementById('proses').value;
                rek=document.getElementById('bank_acc').value;
                npwp=document.getElementById('npwp_sup').value;
                ketUrai=trim(document.getElementById('ketUraian').value);
                mataUang=document.getElementById('mtUang').options[document.getElementById('mtUang').selectedIndex].value;
                krs=trim(document.getElementById('Kurs').value);
                ttd=document.getElementById('persetujuan_id').options[document.getElementById('persetujuan_id').selectedIndex].value;
                ttd2=document.getElementById('persetujuan_id2').options[document.getElementById('persetujuan_id2').selectedIndex].value;
				addcost=document.getElementById('addcost').value;
				
				delivtime=document.getElementById('delivtime').value;
				
                if(lokasi_peng=="")
                {
                        lokasi_peng=document.getElementById('tmpt_krm1').options[document.getElementById('tmpt_krm1').selectedIndex].value;
                        lokasi_peng=parseInt(lokasi_peng);
                }
				if(chkPpn==true){
					valChkPpn = 1;
				}else{
					valChkPpn = 0;
				}

                //alert(row);
				if(ttd=='' || ttd2=='' || mataUang=='' || supplier_id==''){
					alert('Lengkapi Pengisian');return;
				}
			
				if(krs=='' || krs=='0'){
					alert('kurs belum terisi');return;
				}
                param='nopo='+nopo+'&tglpo='+tgl_po+'&supplier_id='+supplier_id+'&subtot='+sub_tot+'&grand_total='+grnd_tot+'&purchser_id='+purchs+'&lokasi_krm='+lokasi_peng;
                param+='&diskon='+disc+'&pbbkb='+nil_pbbkb+'&pph='+nil_pph+'&ppn='+nil_ppn+'&chkppn='+valChkPpn+'&tgl_krm='+tgl_deliver+'&lok_kirim='+delivery_loc+'&cara_pembayarn='+cara_pem+'&nildiskon='+nil_diskon;
                param+='&proses='+rproses+'&rek='+rek+'&npwp='+npwp+'&ketUraian='+ketUrai;
                param+='&mtUang='+mataUang+'&Kurs='+krs+'&id_user='+ttd+'&ttd2='+ttd2+'&addcost='+addcost+'&delivtime='+delivtime+'&npwporg='+npwporg;
                param+=strUrl2;
                //alert(param);
        //	return;
                tujuan='log_slave_save_po_lokal.php';
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
                                                        /*alert(con.responseText);
                                                         return;*/
                                                        //document.getElementById('contain').innerHTML=con.responseText;
                                                        document.getElementById('dataAtas').style.display='block';
                                                        displayList();
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                                  }	
                         } 
                         if(confirm("Saving on :"+mataUang+' currency, are you sure?'))
                         {
                             post_response_text(tujuan, param, respog);	
                         }

}
function load_new_data()
{
        param='proses=update_data';
        tujuan='log_slave_save_po_lokal.php';
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
                                                                //  alert(con.responseText);
                                                                document.getElementById('contain').innerHTML=con.responseText;
                                                                loadNotifikasi();
                                                        //displayList();
                                                }
                                        }
                                        else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                                  }	
                         } 
        post_response_text(tujuan, param, respog);	
}
// function fillField(nopo,tgl_po,supplier_id,sub_tot,disc,nil_pbbkb,nil_pph,chkppn,nil_ppn,grnd_tot,rek,npwp,diskon_nilai,stat,tglKrm,matauang,angKurs,ttd,loKrm,tandtgn2)
// {
	
	
	
	
	
function fillField(nopo,tgl_po,supplier_id,sub_tot,disc,nil_pbbkb,nil_pph,chkppn,nil_ppn,grnd_tot,rek,npwp,diskon_nilai,stat,tglKrm,matauang,angKurs,ttd,loKrm,tandtgn2,addcost,delivtime,npwporg)
{	
		document.getElementById('btncancel').value = "batal";

        if(stat==3){
                alert("This PO has been released");
                exit();
        }
        else
        {
                        status_inputan=1;
                        document.getElementById('dataAtas').style.display='none';
                        document.getElementById('no_po').value=nopo;
                        document.getElementById('tgl_po').value=tgl_po;
                        document.getElementById('npwporg').value=npwporg;
                        document.getElementById('supplier_id').value=supplier_id;
                        //document.getElementById('tmpt_krm').value=delivery_loc;
                        //document.getElementById('term_pay').value=cara_pem;
                        //document.getElementById('user_id').value=purchs;
                        document.getElementById('bank_acc').value=rek;
                        document.getElementById('npwp_sup').value=npwp;
                        rproses=document.getElementById('proses').value='edit_po';
                        dnopp=document.getElementById('no_po').value=nopo;
                        param='nopo='+dnopp+'&proses='+rproses;
                        //tujuan='log_slave_po_lokal_detail.php';
                        tujuan='log_slave_po_lokal_detail.php';
                        /*alert(param);
                        return;*/
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

                                                                        show_form_po();
                                                                        ar=con.responseText.split("###");
                                                                        document.getElementById('ppDetailTable').innerHTML=ar[0];
                                                                        document.getElementById('total_harga_po').value=numberFormat(sub_tot,2);
																		test=document.getElementById('total_harga_po');
																		document.getElementById('diskon').value=numberFormat(disc,2);
                                                                        document.getElementById('pbbkb').value=numberFormat(nil_pbbkb,2);
																		document.getElementById('pph').value=numberFormat(nil_pph,2);
                                                                        document.getElementById('ppn').value=numberFormat(nil_ppn,2);
                                                                        /*nppn=document.getElementById('ppn');
                                                                        change_number(nppn);*/
                                                                        document.getElementById('grand_total').value=numberFormat(grnd_tot,2);
             /*                       gr_total=document.getElementById('grand_total');
                                    change_number(gr_total);
                        */						document.getElementById('tgl_krm').value=tglKrm;
                                                                        document.getElementById('nilai_diskon').value=numberFormat(diskon_nilai,2);
                                                                        document.getElementById('angDiskon').value=numberFormat(diskon_nilai,2);
																		document.getElementById('hslPPh').innerHTML=numberFormat(nil_pph,2);
                                                                        if(nil_pph!=0)
                                                                        {
                                                                                document.getElementById('ppH').value=10;
                                                                        }
                                                                        else
                                                                        {
                                                                                document.getElementById('ppH').value=0;
                                                                        }
                                                                        document.getElementById('hslPPn').innerHTML=numberFormat(nil_ppn,2);
																		//alert(chkppn);
																		if(chkppn==0){
																			document.getElementById('chkPpn').checked = false;
																		}else{
																			document.getElementById('chkPpn').checked = true;
																		}
                                                                        if(nil_ppn!=0)
                                                                        {
                                                                                document.getElementById('ppN').value=10;
                                                                        }
                                                                        else
                                                                        {
                                                                                document.getElementById('ppN').value=0;
                                                                        }
                                                                        document.getElementById('mtUang').value=matauang;
																			if(document.getElementById('mtUang').options[document.getElementById('mtUang').selectedIndex].value=='IDR'){
																				change_number(test);
																			}
																		document.getElementById('Kurs').value=angKurs;
                                                                        document.getElementById('persetujuan_id').value=ttd;
                                                                        document.getElementById('persetujuan_id2').value=tandtgn2;
                                                                        document.getElementById('tmpt_krm').value=loKrm;
                                                                        document.getElementById('term_pay').value=ar[1];
                                                                        document.getElementById('ketUraian').value=ar[2];
																		document.getElementById('addcost').value=numberFormat(addcost,2);	
                                                                        //document.getElementById('proses').value="editHeader";
																		document.getElementById('delivtime').value=delivtime;
																		getbank(rek);
                                                                        getnpwp(npwporg);
																		
                                                                }
                                                        }
                                                        else {
                                                                busy_off();
                                                                error_catch(con.status);
                                                        }
                                                  }	
                                         } 
                        post_response_text(tujuan, param, respog);	
        }
        //}
}
function loadNotifikasi()
{
        proses="getNotifikasi";
        param="proses="+proses;
        tujuan="log_slave_save_po_lokal.php";
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
                                                        //displayList();
                                                        document.getElementById('notifikasiKerja').innerHTML=con.responseText;
                                        }
                        }
                        else {
                                        busy_off();
                                        error_catch(con.status);
                        }
                }
        }

}


function cancel_headher()
{
		status_batal = document.getElementById('btncancel').value;
		if(status_batal=="batal"){
			displayList();
		}else{
				nopo=document.getElementById('no_po').value;
				// alert(nopo);
                document.getElementById('proses').value='';
                ar=document.getElementById('proses');
                ar.value='delete_all';
                /*alert(document.getElementById('proses').value);
                return;*/
                ar=ar.value;
                param='nopo='+nopo+'&proses='+ar;
                /*alert(param);
                return;*/
                tujuan='log_slave_save_po_lokal.php';
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
                                                                displayList();
                                                                //document.getElementById('contain').innerHTML=con.responseText;

                                                        }
                                                }
                                                else {
                                                        busy_off();
                                                        error_catch(con.status);
                                                }
                                          }
                                 }
                post_response_text(tujuan, param, respog);
		}
		
}
function delPoDetail(nopo,stat,StatIns)
{
        if(stat==1)
        {
                alert('Waiting Approval');
                return;
        }
        else
        {
                if(StatIns==0)
                {
                        if(confirm("Deleting, Are you sure?"))
                        { 
                          // alert("berhasil");
                                displayList();
                        }
                        else
                        {
                           return;
                        }		
                }
                else
                {
                                document.getElementById('proses').value='';
                                ar=document.getElementById('proses');
                                ar.value='delete_all';
                                /*alert(document.getElementById('proses').value);
                                return;*/
                                ar=ar.value;
                                param='nopo='+nopo+'&proses='+ar;
                                /*alert(param);
                                return;*/
                                tujuan='log_slave_save_po_lokal.php';

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
                                                                                //document.getElementById('contain').innerHTML=con.responseText;
                                                                                displayList();
                                                                        }
                                                                }
                                                                else {
                                                                        busy_off();
                                                                        error_catch(con.status);
                                                                }
                                                          }	
                                                 }
                                if(confirm("Deleting, are you sure"))
                                 { 
                                        post_response_text(tujuan, param, respog);	
                                 }
                                 else
                                 {
                                         return;
                                 }
                }
        }
}

function alasan_batal(nopo,stat)
{
    param='nopo='+nopo+'&stat='+stat+'&proses=get_alasan_batal';
//    alert(param);
    
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
                    width='400';
                    height='200';
                    content="<div id=form_batal></div>";
                    ev='event';
                    title="Form Alasan Pembatalan PO";
                    showDialog1(title,content,width,height,ev);
//                    alert(con.responseText);
                    document.getElementById('form_batal').innerHTML=con.responseText;
                    return con.responseText;
                  
//                    displayList();
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }	
    }
    
    tujuan='log_slave_save_po_lokal.php';
    post_response_text(tujuan, param, respog);
}

function delPo(nopo,stat,batal)
{
    batal=document.getElementById('batal').value;
/*	if(stat==1)
        {
                alert('Menunggu Persetujuan');
                return;
        }
        else if(stat==2)
        {
                alert("Porses Persetujuan Sudah Selesai");
                return;
        }
*/	
        if(stat==2)
        {
                alert('Being on correction progress');
                return;
        }
        else
        {
                        document.getElementById('proses').value='';
                        ar=document.getElementById('proses');
                        ar.value='delete_all';
                //	alert(document.getElementById('proses').value);
//			return;
                        ar=ar.value;
                        param='nopo='+nopo+'&batal='+batal+'&proses='+ar;
                        //alert(param);
//			return;
                        tujuan='log_slave_save_po_lokal.php';
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
                                                    
                                                    displayList();
                                                    //document.getElementById('contain').innerHTML=con.responseText;

                                            }
                                    }
                                    else {
                                            busy_off();
                                            error_catch(con.status);
                                    }
                              }	
                         }
                         
                        if(confirm('Are you sure delete this PO and it`s items'))
                        {
                                post_response_text(tujuan, param, respog);
                                closeDialog();
                        }
                        else
                        {
                                return;
                        }
        }
}
function agree_po()
{
        width='400';
        height='200';
        //nopp=document.getElementById('nopp_'+id).value;
        content="<div id=container></div>";
        ev='event';
        title="Persetujuan Atau Penolakan Form";
        showDialog1(title,content,width,height,ev);
        //get_data_pp();	
}
function koreksiForm(npo)
{
        width='400';
        height='160';
        //nopp=document.getElementById('nopp_'+id).value;
        content="<div id=isi></div>";
        ev='event';
        title=" Koreksi No PO  :"+npo;
        showDialog1(title,content,width,height,ev);
        //get_data_pp();	
}
function getKoreksi(npo)
{
        //met=document.getElementById('proses').value;
        //rnopo=document.getElementById('no_po').value;
        rnopo=npo;
        met='getKoreksi';
        param='proses='+met+'&nopo='+rnopo;
        tujuan='log_slave_save_po_lokal.php';
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
                                                                        /*alert(con.responseText);
                                                                        return;*/
                                                                        koreksiForm(npo);
                                                                        document.getElementById('isi').innerHTML=con.responseText;
                                                                        return con.responseText;
                                                                }
                                                        }
                                                        else {
                                                                busy_off();
                                                                error_catch(con.status);
                                                        }
                                          }	
                         }
        post_response_text(tujuan, param, respog);
}
function get_data_pp(npo)
{
        /*tbl=document.getElementById('ppDetailTable');
        row=tbl.rows.length;
        row=row-6;
       //alert(row);
        for(i=0;i<row;i++)
            {
                jmlh=document.getElementById('jmlhDiminta_'+i).value;
                harg_satuan=document.getElementById('harga_satuan_'+i).value;
                disk=document.getElementById('diskon').value;
                supp_id=document.getElementById('supplier_id').value;
                tgl_krm=document.getElementById('tlg_krm').value;
                loc_kirim=document.getElementById('tmpt_krm').value;
                paym_term=document.getElementById('term_pay').value;
                realis=document.getElementById('realisasi_'+i).value;
                kd_brg=document.getElementById('rkdbrg_'+i).value;
                if((jmlh=='')||(harg_satuan=='')||(disk=='')||(supp_id=='')||(tgl_kirim='')||(paym_term==''))
                    {
                        alert('Please Complete The Form First');
                        return;
                    }
            }*/

        met=document.getElementById('proses').value;
        //rnopo=document.getElementById('no_po').value;
        rnopo=npo;
        met='get_form_approval';
        param='proses='+met+'&nopo='+rnopo;
        tujuan='log_slave_save_po_lokal.php';
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
                                                                        /*alert(con.responseText);
                                                                        return;*/
                                        agree_po();
                                                                                document.getElementById('container').innerHTML=con.responseText;
                                                                                return con.responseText;
                                                                }
                                                        }
                                                        else {
                                                                busy_off();
                                                                error_catch(con.status);
                                                        }
                                          }	
                         }
        post_response_text(tujuan, param, respog);


}
function forward_po()
{
        nik=document.getElementById('persetujuan_id').value;
        snopo=document.getElementById('rnopp').value;
        met=document.getElementById('proses');
        met=met.value='insert_forward_po';
        param='id_user='+nik+'&proses='+met+'&nopo='+snopo;
        tujuan='log_slave_save_po_lokal.php';
        //alert(param);
        //return;
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
                                                                closeDialog();
                                                                displayList();

                                                        }
                                                }
                                                else {
                                                        busy_off();
                                                        error_catch(con.status);
                                                }
                                  }	
                 } 	
                 post_response_text(tujuan, param, respog);	
}
function close_po_a()
{
        document.getElementById('close_po').style.display='block';
        document.getElementById('test').style.display='none';

}
function proses_release_po()
{
        //document.getElementById('snopo').value=nopo;
        rnopo=document.getElementById('snopo').value;
        param='nopo='+rnopo+'&proses=proses_release_po';
        tujuan='log_slave_save_po_lokal.php';
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
                                                                //document.getElementById('close_container').innerHTML=con.responseText;	
                                                                displayList();							
                                                        }
                                                }
                                                else {
                                                        busy_off();
                                                        error_catch(con.status);
                                                }
                                  }	
                 } 	
                 post_response_text(tujuan, param, respog);	


}
function cancel_po()
{
        closeDialog();
}
function clearRow(id)
{
        /*document.getElementById('harga_satuan_'+id).value=0;
        document.getElementById('total_'+id).value=0;	
        document.getElementById('rnopp_'+id).value='';
        document.getElementById('rkdbrg_'+id).value='';
        document.getElementById('jmlhDiminta_'+id).value='';*/
        //alert(id);
        tabel=document.getElementById("detailBody");
        tabel.removeChild(tabel.rows[id]);

}
/* Function deleteDelete(id)
 * Fungsi untuk menghapus data Detail
 * I : id row (urutan row pada table Detail)
 * P : Menghapus data pada tabel Detail
 * O : Menghapus baris pada tabel Detail
 */
 pengurang=7;
function deleteDetail(id) {
        var tbl = document.getElementById("detailBody");
                var baris = tbl.rows.length;
                baris=baris-7;
        //	alert(baris);
                //return;
                if(baris==1)
                {
                        nopo=document.getElementById('no_po').value;
                stat=0;
                        StatIns=1;
                        delPoDetail(nopo,stat,StatIns);
                }
        else if(baris>1)
                {
                        //alert(baris);

                        //alert(tabel.rows[id]);

                        //tabel.removeChild(tabel.rows[id]);
                        //elem.parentNode.removeChild(elem);
                        var detKode = document.getElementById('no_po');
                        var rkd_brg = document.getElementById('rkdbrg_'+id);
                        var nopp = document.getElementById('rnopp_'+id);
                        var purchas= document.getElementById('user_id');

                        param = "proses=detail_delete";
                        param += "&nopo="+detKode.value;
                        param += "&kd_brg="+rkd_brg.value;
                        param += "&nopp="+nopp.value;
                        param += "&purchaser="+purchas.value;

                        function respon(){
                                if (con.readyState == 4) {
                                        if (con.status == 200) {
                                                busy_off();
                                                if (!isSaveResponse(con.responseText)) {
                                                        alert(con.responseText);
                                                } else {
                                                        // Success Response
                                        //alert(id);
                                        //baris=row;
                                        //tabel=document.getElementById("detailBody");
                                        //tabel.removeChild(tabel.rows[id]);

                                        row = document.getElementById("detail_tr_"+id);
                                        if(row) 
                                        {
                                                //
                                                document.getElementById('harga_satuan_'+id).value=0;
                                                document.getElementById('total_'+id).value=0;	
                                                document.getElementById('dtNopp_'+id).innerHTML="";
                                                document.getElementById('dtKdbrg_'+id).innerHTML="";
                                                document.getElementById('jmlhDiminta_'+id).value="";
                                                row.style.display="none";
                                                //pengurang+=1;
                                                plusAll();
                                        } 
                                        else 
                                        {
                                                alert("Row undetected");
                                        }

                                        }
                                        } else {
                                                busy_off();
                                                error_catch(con.status);
                                        }
                                }
                        }

                                if(confirm('Deleting, are you sure?'))
                                {
                                        post_response_text('log_slave_po_lokal_detail.php', param, respon);	
                                }
                                else
                                {
                                        return;
                                }
                }
}
function cariNopo()
{
        txtSearch=trim(document.getElementById('txtsearch').value);
        tglCari=trim(document.getElementById('tgl_cari').value);
        met=document.getElementById('proses');
        met=met.value='update_data';
        met=trim(met);

        param='txtSearch='+txtSearch+'&tglCari='+tglCari+'&proses='+met;
        tujuan='log_slave_save_po_lokal.php';
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
                                                                document.getElementById('contain').innerHTML=con.responseText;							
                                                        }
                                                }
                                                else {
                                                        busy_off();
                                                        error_catch(con.status);
                                                }
                                  }	
                 }
                 post_response_text(tujuan, param, respog);

}
function cek_pembuat(nopo)
{
        rnop=nopo;
        //alert(rnop);
        param='nopo='+rnop+'&proses=cek_pembuat_po';
        tujuan='log_slave_save_po_lokal.php';
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

                                                                        }
                                                                }
                                                                else {
                                                                        busy_off();
                                                                        error_catch(con.status);
                                                                }
                                                  }	
                                 }
                                post_response_text(tujuan, param, respog);
}
function cariBast(num)
{
                param='proses=update_data';
                param+='&page='+num;
                tujuan = 'log_slave_save_po_lokal.php';
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
function getKurs()
{
        mtung=document.getElementById('mtUang').options[document.getElementById('mtUang').selectedIndex].value;
        tgl=document.getElementById('tgl_po').value;
        param='mtUang='+mtung+'&proses=getKurs'+'&tglpo='+tgl;
        tujuan='log_slave_save_po_lokal.php';
        post_response_text(tujuan, param, respog);			
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alert(con.responseText);
                                        }
                                        else {
											if(con.responseText > 0){
												//alert('ERROR TRANSACTION,\n test');
												document.getElementById('Kurs').value=con.responseText;
												document.getElementById('btnSaveHeader').disabled=false;
											}else{
												document.getElementById('Kurs').value=0;
												document.getElementById('btnSaveHeader').disabled=true;
												alert('ERROR TRANSACTION,\n Kurs ' + mtung + ' untuk tanggal ' + tgl +' belum ada');
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

function checkIt(idid,count,nopp)
{}

/*
function checkIt(idid,count,nopp)
{
	// Check jika ada nopp yang masih tercentang
	clean = true;
	for(a=0;a<count;a++){
		var valueA = a + 1,
			checkbox = document.getElementById('plh_pp_'+valueA).checked;
		if(checkbox){
			clean = false;
		}
	}
	
	for(a=0;a<count;a++){
		var valueA = a + 1,
			valueNoPP = document.getElementById('hiddennopp'+valueA).value;
		
		if(clean) {
			document.getElementById('tr_'+valueA).style.display = '';
		} else if(valueNoPP==nopp){
			document.getElementById('tr_'+valueA).style.display = '';
		} else {
			document.getElementById('tr_'+valueA).style.display = 'none';
		}
	}
}
*/
/*function checkStat(id)
{
        ar=document.getElementById('plh_pp_'+id);
        if(ar.checked==true)
        {
                ar.checked==true;
        }
        else if(ar.checked!=true)
        {
                ar.checked==false;
        }
}
*/
function doneKoreksi()
{
        rnopo=document.getElementById('rnopp').value;
        param='nopo='+rnopo+'&proses=updateKoreksi';
        tujuan='log_slave_save_po_lokal.php';
        if(confirm("Correction confirmation?"))
        {
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
                                                displayList();
                                                closeDialog();
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }	

}
function searchSupplier(title,content,ev)
{
        width='';
        height='';
        showDialog1(title,content,width,height,ev);
        //alert('asdasd');
}
function findSupplier()
{
    nmSupplier=document.getElementById('nmSupplier').value;
    param='proses=getSupplierNm'+'&nmSupplier='+nmSupplier;
    tujuan='log_slave_save_po_lokal.php';
    post_response_text(tujuan, param, respog);			

    function respog(){
            if (con.readyState == 4) {
                    if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                                    alert(con.responseText);
                            }
                            else {
                                  document.getElementById('containerSupplier').innerHTML=con.responseText;
                        }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
            }
    }	
}
function setData(kdSupp)
{
    l=document.getElementById('supplier_id');

    for(a=0;a<l.length;a++)
        {
            if(l.options[a].value==kdSupp)
                {
                    l.options[a].selected=true;
                }
        }

       closeDialog();
           get_supplier();
}

function ajukan(nopo)
{
    param='proses=ajukan'+'&nopo='+nopo;
    tujuan='log_slave_save_po_lokal.php';
    if(confirm('Anda yakin ingin mengajukan transaksi ini ??'))
    {
        post_response_text(tujuan, param, respog);  
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
                                    else 
                                    {
                                        load_new_data();
                                    }
                            }
                            else {
                                    busy_off();
                                    error_catch(con.status);
                            }
              } 
    }
}