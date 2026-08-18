var con = createXMLHttpRequest();

function datee() {       
    let date = new Date();
    let dd = String(date.getDate()).padStart(2, '0');
    let mm = String(date.getMonth() + 1).padStart(2, '0'); //January is 0!
    let yyyy = date.getFullYear();

    var month = new Array();
    month[0] = "Januari";
    month[1] = "Februari";
    month[2] = "Maret";
    month[3] = "April";
    month[4] = "Mei";
    month[5] = "Juni";
    month[6] = "Juli";
    month[7] = "Agustus";
    month[8] = "September";
    month[9] = "Oktober";
    month[10] = "November";
    month[11] = "December";
    var myDays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum&#39;at', 'Sabtu'];
    var thisDay = date.getDay();

    thisDay = myDays[thisDay];

    let n = month[date.getMonth()];
    let today = mm + ' ' + dd + ' ' + yyyy;
    //document.getElementById("tanggalNow").innerHTML = '<strong>'+thisDay.toUpperCase()+'</strong>' + ' ' + dd +' ' + n + ' '+ yyyy;
    let arr=[mm,yyyy,dd,thisDay,n];
    return arr;
}

function convertToRupiah(number){
    return number.toLocaleString('id-ID');
}

function createXMLHttpRequest() {
    try { return new ActiveXObject("Msxml2.XMLHTTP"); } 
    catch (e) {}
    try { return new ActiveXObject("Microsoft.XMLHTTP"); } 
    catch (e) {}
    try { return new XMLHttpRequest(); } 
    catch(e) {}
    alert("XMLHttpRequest Tidak didukung oleh browser");
    return null;
}

function get_response_text(tujuan, funct) {   
    con.open("GET", tujuan, true);
    //con.setRequestHeader("Connection", "close");
    con.onreadystatechange= eval(funct);
    con.send(null);
}

function showProgress() {
    obj = document.getElementsByClassName('progress1');
    if (obj.length > 0) {
        obj[0].style.display = 'flex';
        obj[0].setAttribute('style','display: flex;justify-content: center;align-items: center;');
    }
}

function hideProgress() {
    obj = document.getElementsByClassName('progress1');
    if (obj.length > 0) {
        obj[0].style.display = 'none';
        obj[0].setAttribute("class", "progress1");
    }
}

function getDivisi(unit) {
	param 	= 'unit=' + unit +  '&method=getDivisi';
	tujuan 	= 'keu_slave_2printqrcode.php';

	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					document.getElementById('divisi').disabled 	= false;
					document.getElementById('divisi').innerHTML = con.responseText;

					if (divisi == 'all') {
						getBlok('all');
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}