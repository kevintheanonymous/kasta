const dateVisible = document.querySelector('input[name="date_visible"]');
const dateEvenement = document.querySelector('input[name="date_event_asso"]');
const dateCloture = document.querySelector('input[name="date_cloture"]');
const lieu = document.querySelector('input[name="lieu_maps"]');
const payement = document.querySelector('input[name="url_helloasso"]');

const form = document.querySelector('form');

function verifDate() {
  const visible = new Date(dateVisible.value);
  const evenement = new Date(dateEvenement.value);
  const cloture = new Date(dateCloture.value);
  
  if (visible && cloture) {
        if(visible > cloture) {
            alert("La date de visibilité doit être avant ou égale à la date de clôture.");
            return false;
        }
        if(evenement < cloture) {
            alert("La date de clôture doit être avant ou égale à la date de l'événement.");
            return false;
        }
        if(evenement < visible) {
            alert("La date de visibilité doit être avant ou égale à la date de l'événement.");
            return false;
        }
  }
  return true;
}

function UrlValide(s) {
  try {
    new URL(s); // Essaie de créer un objet URL, si il n'y arrive pas, il renvoie une erreur
    return true;
  } catch (_) { // On attrape l'erreur ici
    return false;  
  }
}

form.addEventListener('submit', function(e) {
  if (!verifDate()) {
    e.preventDefault(); 
  }
  if (!UrlValide(lieu.value)) {
    e.preventDefault();
    alert("Le lien Maps n'est pas valide");
    return;
  }
  if (!UrlValide(payement.value)) {
    e.preventDefault();
    alert("L'URL de paiement n'est pas valide");
    return;
  }
});

;
