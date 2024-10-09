
function openPopup() {
    console.log("function called");
    let Timeout;

    var element = document.getElementById("popup");
    if (element) { // Check if the element exists
        element.classList.add("openPopup"); // Add the class
        Timeout = setTimeout(closePopup, 3000);

    } else {
        console.error("Element not found!");
    }
}
function closePopup() {
    popup.classList.remove("openPopup");
}

function showerror(){
    console.log("show error function called");
    var password = document.getElementById("password");
    password.classList.add("htmlerror");
    
}

