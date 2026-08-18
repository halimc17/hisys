function changeColor(value){
    const title     =document.getElementById('titlecolor').value;
    const strokecolor   =document.getElementById('strokecolor').value;
    const fillcolor     =document.getElementById('fillcolor').value;

    titlecolor.style.fontWeight="bold";
    const previewColor=document.getElementById('previewColor');
    previewColor.style.background=fillcolor;
    previewColor.style.border=`thick solid ${strokecolor}`;

}
function hasil(e){
    console.log(e);
    alert(e.response.message);
    $.refresh();

}