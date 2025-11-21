// creation des variables
var champNom = document.getElementById('nom');
var champPrenom = document.getElementById('prenom');
var champEmail = document.getElementById('email');
var champTelephone = document.getElementById('telephone');
var submitBtn = document.getElementById('submit');
var verif_Tshirt = document.getElementById('t-shirt');
var verif_Pull = document.getElementById('pull');


// validation nom et prenom
function validerNomPrenom(input, messageId) {
  var valeur = input.value;
  var message = document.getElementById(messageId);
  if (valeur.length <= 1) {
    message.textContent = "Ce champ doit contenir au moins 2 lettres.";
    message.className = "error-texte";
    input.classList.add("invalid");
    input.classList.remove("valid");
    return false;
  }
  if (!/^[A-Za-zÀ-ÿ\s'-]+$/.test(valeur)) {
    message.textContent = "Utilisez uniquement des lettres valides (pas de caractères spéciaux ni chiffres).";
    message.className = "error-texte";
    input.classList.add("invalid");
    input.classList.remove("valid");
    return false;
  }
  if (valeur.length > 30) {
    message.textContent = "Ce champ doit contenir moins de 30 caractères.";
    message.className = "error-texte";
    input.classList.add("invalid");
    input.classList.remove("valid");
    return false;
  }
  message.textContent = "";
  message.className = "valid-texte";
  input.classList.remove("invalid");
  input.classList.add("valid");
  return true;
}

// validation mail
function validerEmail() {
  const message = document.getElementById('message_email');
  if (champEmail.value === "") {
    message.textContent = "Adresse e-mail invalide.";
    message.className = "error-texte";
    champEmail.classList.add('invalid');
    champEmail.classList.remove('valid');
    return false;
  }
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(champEmail.value)) {
    message.textContent = "Adresse e-mail invalide.";
    message.className = "error-texte";
    champEmail.classList.add('invalid');
    champEmail.classList.remove('valid');
    return false;
  }
  message.textContent = "";
  message.className = "valid-texte";
  champEmail.classList.remove('invalid');
  champEmail.classList.add('valid');
  return true;
}

// verification du telephone (saisie valide)
function validerTelephone() {
  var message = document.getElementById('message_telephone');
  if (champTelephone.value === "") {
    message.textContent = "Numéro invalide.";
    message.className = "error-texte";
    champTelephone.classList.add('invalid');
    champTelephone.classList.remove('valid');
    return false;
  }
  if (!/^0[1-9][0-9]{8}$/.test(champTelephone.value)) {
    message.textContent = "Numéro non valide (ex: 0612345678).";
    message.className = "error-texte";
    champTelephone.classList.add('invalid');
    champTelephone.classList.remove('valid');
    return false;
  }
  message.textContent = "";
  message.className = "valid-texte";
  champTelephone.classList.remove('invalid');
  champTelephone.classList.add('valid');
  return true;
}


// listeners sur tous les champs
champNom.addEventListener('input', () => validerNomPrenom(champNom, "message_nom"));
champPrenom.addEventListener('input', () => validerNomPrenom(champPrenom, "message_prenom"));
champEmail.addEventListener('input', validerEmail);
champTelephone.addEventListener('input', validerTelephone);

document.getElementById('photo-upload').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        var img = document.querySelector('.avatar');
        img.src = URL.createObjectURL(e.target.files[0]);
    }
});

function valid_Tshirt(){
    if(verif_Tshirt.value == ''){
        verif_Tshirt.classList.remove('valid');
        verif_Tshirt.classList.add('invalid');
        return false
    }
    verif_Tshirt.classList.remove('invalid');
    verif_Tshirt.classList.add('valid');
    return true;
}

function valid_Pull(){
    if(verif_Pull.value == ''){
        verif_Pull.classList.remove('valid');
        verif_Pull.classList.add('invalid');
        return false
    }
    verif_Pull.classList.remove('invalid');
    verif_Pull.classList.add('valid');
    return true;
}

verif_Tshirt.addEventListener('change', valid_Tshirt);
verif_Pull.addEventListener('change', valid_Pull);
var tshirt = document.getElementById('t-shirt')?.value.trim();
var pull = document.getElementById('pull')?.value.trim();

// verification du formulaire et activation du bouton
function verifierFormulaireValid() {
    if (!submitBtn) return;
    var tousValid =
    validerNomPrenom(champNom, "message_nom") &&
    validerNomPrenom(champPrenom, "message_prenom") &&
    validerEmail() &&
    validerTelephone();
  submitBtn.disabled = !tousValid;
}
// validation à la soumission (montre bien les erreurs sur submit si oubli)
document.querySelector('form').addEventListener('submit', function(event) {
  let valid = true;
  if (!validerNomPrenom(champNom, "message_nom")) valid = false;
  if (!validerNomPrenom(champPrenom, "message_prenom")) valid = false;
  if (!validerTelephone()) valid = false;
  if (!validerEmail()) valid = false;
  if (!valid_Tshirt()) valid = false;
  if (!valid_Pull()) valid = false;

 
  if (!valid || !tshirtValue || !pullValue) {
    event.preventDefault();
    return;
}
console.log('Validation passed, submitting form.');
});
document.querySelector('form').addEventListener('submit', verifierFormulaireValid);