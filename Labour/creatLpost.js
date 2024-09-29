//  JAVASCRIPT FOR THE POPUP MESSAGE 

let popup = document.getElementById("popup");
let timeout;
function openPopup(){
    popup.classList.add("openPopup");
    timeout = setTimeout(closePopup,3000);
}
function closePopup(){
    popup.classList.remove("openPopup");
}