

function loadtransaksi(dest,kdjenis) {
	if(dest=='BI') {
		window.open("main_bi.html","OWLBI","status=0,toolbar=0,resizable=1,status=0,location=no,menubar=0,directories=0");       
	} else { 
		dest=dest.replace(".php","");
		dest=dest.replace(".html","");
		dest=dest.replace(".phtml","");
		dest=dest.replace(".php3","");
		// window.location=dest+'.php?nokontrak='+nokontrak;
		
		window.location=dest+'.php?kdjenis='+kdjenis;
	}
}












