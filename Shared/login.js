
// const contact = document.getElementById("contact");
// const password = document.getElementById("password");
// const form = document.getElementById("form");


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
    
    
    // let popup = document.getElementById("popup");
    // let Timeout;
    // popup.classList.add("openPopup");
    // Timeout = setTimeout(closePopup, 3000);
}
function closePopup() {
    popup.classList.remove("openPopup");
}


// form.addEventListener('submit' , e=>{
//     console.log( e.preventDefault());
   

//     // validateInputs();
// });

// const setError = (element , message) =>{
//     const inputcontrl = element.parentElement;
//     const errord = inputcontrl.querySelector('.error');
//     errord.innertext = message;
//     inputcontrl.classList.add('.error');
// }

// function validateInputs(){
//     const passwordvalue = password.value.trim();
//     const contactvalue = contact.value.trim();
//     console.log(`validate function ${passwordvalue} and ${contactvalue}`);

//     if(contactvalue === ''){
//         setError(contact,"contact is required");
//     }
//     if(passwordvalue === ''){
//         setError(password,"[assword is required");
//     }
// }

