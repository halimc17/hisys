const tabprint = document.getElementById('tabprint');
if (tabprint) {
	tabprint.addEventListener('click', function () {
	  simpan();
	});
}

function simpan(){
	notiket = document.getElementById('notiket').value;
	param2='ticketno='+notiket;
	tujuan='printblanko.php';
	tujuan2=tujuan+"?"+param2+'&method=printticket';
	
	// printticket(tujuan2);
	printnopopupx(tujuan2);
}