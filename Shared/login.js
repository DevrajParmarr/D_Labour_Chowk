let popup = document.getElementById("popup");

let Timeout;
function openPopup(){
    popup.classList.add("openPopup");
    Timeout = setTimeout(closePopup() , 3000);
}
function closePopup(){
    popup.classList.remove("openPopup");
}
