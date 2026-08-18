function batal2() {
    document.getElementById('printContainer2').innerHTML = '';
    var chartCont = document.getElementById('chartContainer');
    if (chartCont) chartCont.style.display = 'none';
}

function showheader() {
    if (document.getElementById('tableheader').style.display == "none") {
        document.getElementById('tableheader').style.display = "block";
        document.getElementById('showhead').innerHTML = "Hide Filter";
        document.getElementById('tombolexport').style.display = "none";
    }
}