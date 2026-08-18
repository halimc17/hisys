function openPdf(file){
    let currentUrl = window.location.href;
    let parts = currentUrl.split('index.php');
    window.open(`${parts[0]}manualbook/${file}.pdf`, '_blank');
}