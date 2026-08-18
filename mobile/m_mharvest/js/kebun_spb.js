var winUpdate;

function listAction(getPage) {
    tujuan = $.options.slave + getPage;
    let options = {
        url: tujuan,
        title: '<strong>Detail SPB<strong>',
        success: function (arg) {
            console.log(arg);
        }
    };
    winUpdate = $.openWindow(options);
}

document.addEventListener('DOMContentLoaded', (event) => {
    const toggler = document.getElementsByClassName("caret");
    for (let i = 0; i < toggler.length; i++) {
        toggler[i].addEventListener("click", function () {
            this.parentElement.querySelector(".nested").classList.toggle("active");
            this.classList.toggle("caret-down");
        });
    }
});

function updatetanggal(val) {
    window.tanggal.value = '01-' + val.split('-')[1] + '-' + val.split('-')[0];
}

function updatepriode(val){
    let prd = val.split('-')[2] + '-' + val.split('-')[1];
    if(window.periode.querySelector(`option[value="${prd}"]`)){
        window.periode.value = val.split('-')[2] + '-' + val.split('-')[1];
    }else{
        $.Alert('Periode Tidak Ditemukan')
    }

}