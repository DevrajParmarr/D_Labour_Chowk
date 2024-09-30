let popup = document.getElementById("popup");
// let btn = document.getElementById("login");
let Timeout;
function openPopup(){
    popup.classList.add("openPopup");
    // Timeout = setTimeout(closePopup , 3000);
    // btn.classList.add("btn btn-warning mt-3 w-25");
}
function closePopup(){
    popup.classList.remove("openPopup");
}
// let popup = document.getElementById("popup");
// let timeout;
// function openPopup(){
//     popup.classList.add("openPopup");
//     // timeout = setTimeout(closePopup,3000);
// }
// function closePopup(){
//     popup.classList.remove("openPopup");
// }