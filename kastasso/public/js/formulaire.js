// creation des variables
const champNom = document.getElementById('nom');
const champPrenom = document.getElementById('prenom');
const champEmail = document.getElementById('email');
const champTelephone = document.getElementById('telephone');
const mdp = document.getElementById('mdp');
const confmdp = document.getElementById('confmdp');
const submitBtn = document.getElementById('submit');
const message_mdp_conf = document.getElementById('message_mdp_conf');
const message_mdp = document.getElementById('message_mdp');

// vibilite du mdp
document.getElementById('voirmdp').addEventListener('click', function() {
  visibilite_mdp(mdp, this);
});
document.getElementById('voirmdp2').addEventListener('click', function() {
  visibilite_mdp(confmdp, this);
});
function visibilite_mdp(input, button) {
  if (input.type === "password") {
    input.type = "text";
    button.textContent = '🚫️';
  } else {
    input.type = "password";
    button.textContent = '👀️';
  }
}

// validation nom et prenom
function validerNomPrenom(input, messageId) {
  const valeur = input.value;
  const message = document.getElementById(messageId);
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
  const message = document.getElementById('message_telephone');
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



// verifie si le mdp est fort
function verifier_mdp_fort() {
  if (mdp.value === "") {
    message_mdp.textContent = "Mot de passe invalide.";
    message_mdp.className = 'error-texte';
    mdp.classList.add('invalid');
    mdp.classList.remove('valid');
    return false;
  }
  if (!/[a-z]/.test(mdp.value)) {
    message_mdp.textContent = 'Le mot de passe doit contenir au moins une lettre minuscule.';
    message_mdp.className = 'error-texte';
    mdp.classList.add('invalid');
    mdp.classList.remove('valid');
    return false;
  }
  if (!/[A-Z]/.test(mdp.value)) {
    message_mdp.textContent = 'Le mot de passe doit contenir au moins une lettre majuscule.';
    message_mdp.className = 'error-texte';
    mdp.classList.add('invalid');
    mdp.classList.remove('valid');
    return false;
  }
  if (!/[0-9]/.test(mdp.value)) {
    message_mdp.textContent = 'Le mot de passe doit contenir au moins un chiffre.';
    message_mdp.className = 'error-texte';
    mdp.classList.add('invalid');
    mdp.classList.remove('valid');
    return false;
  }
  if (!/[^A-Za-z0-9]/.test(mdp.value)) {
    message_mdp.textContent = "Le mot de passe doit contenir au moins un caractère spécial.";
    message_mdp.className = 'error-texte';
    mdp.classList.add('invalid');
    mdp.classList.remove('valid');
    return false;
  }
  if (mdp.value.length < 8) {
    message_mdp.textContent = 'Le mot de passe doit contenir au moins 8 caractères.';
    message_mdp.className = 'error-texte';
    mdp.classList.add('invalid');
    mdp.classList.remove('valid');
    return false;
  }
  message_mdp.textContent = '';
  message_mdp.className = 'valid-texte';
  mdp.classList.remove('invalid');
  mdp.classList.add('valid');
  return true;
}
// verification du mdp (confirmation)
function validerMdp() {
  // Affiche l'erreur si l'un ou l'autre est vide 
  if (!mdp.value ) {
    message_mdp_conf.textContent = 'Mot de passe invalide.';
    message_mdp_conf.className = 'error-texte';
    confmdp.classList.add('invalid');
    confmdp.classList.remove('valid');
    mdp.classList.add('invalid');
    mdp.classList.remove('valid');
    return false;
  }
  if (!confmdp.value) {
    message_mdp_conf.textContent = 'Mot de passe invalide.';
    message_mdp_conf.className = 'error-texte';
    confmdp.classList.add('invalid');
    confmdp.classList.remove('valid');
    mdp.classList.remove('invalid');
    mdp.classList.add('valid');
    return false;
  }
  if (mdp.value !== confmdp.value) {
    message_mdp_conf.textContent = 'Mot de passe différent';
    message_mdp_conf.className = 'error-texte';
    confmdp.classList.add('invalid');
    confmdp.classList.remove('valid');
    mdp.classList.add('invalid');
    mdp.classList.remove('valid');
    return false;
  } else {
    message_mdp_conf.textContent = 'Les mots de passe correspondent';
    message_mdp_conf.className = 'valid-texte';
    confmdp.classList.remove('invalid');
    confmdp.classList.add('valid');
    mdp.classList.remove('invalid');
    mdp.classList.add('valid');
    return true;
  }
}

// verification du formulaire et activation du bouton
function verifierFormulaireValid() {
  const allValid =
    validerNomPrenom(champNom, "message_nom") &&
    validerNomPrenom(champPrenom, "message_prenom") &&
    validerEmail() &&
    verifier_mdp_fort() &&
    validerMdp() &&
    validerTelephone();
  submitBtn.disabled = !allValid;
}

// listeners sur tous les champs
champNom.addEventListener('input', () => validerNomPrenom(champNom, "message_nom"));
champPrenom.addEventListener('input', () => validerNomPrenom(champPrenom, "message_prenom"));
champEmail.addEventListener('input', validerEmail);
champTelephone.addEventListener('input', validerTelephone);
mdp.addEventListener('input', verifier_mdp_fort);
confmdp.addEventListener('input', validerMdp);

// validation à la soumission (montre bien les erreurs sur submit si oubli)
document.querySelector('form').addEventListener('submit', function(event) {
  let valid = true;
  if (!validerNomPrenom(champNom, "message_nom")) valid = false;
  if (!validerNomPrenom(champPrenom, "message_prenom")) valid = false;
  if (!validerTelephone()) valid = false;
  if (!validerEmail()) valid = false;
  if (!verifier_mdp_fort()) valid = false;
  if (!validerMdp()) valid = false;


  if (!valid) {
    event.preventDefault();
  }
});

document.getElementById('photo-upload').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        var img = document.querySelector('.avatar');
        img.src = URL.createObjectURL(e.target.files[0]);
    }
});





