const dateVisible = document.querySelector('input[name="date_visible"]');
const dateEvenement = document.querySelector('input[name="date_event_asso"]');
const dateCloture = document.querySelector('input[name="date_cloture"]');
const lieu = document.querySelector('input[name="lieu_maps"]');
const payement = document.querySelector('input[name="url_helloasso"]');
const tarifInput = document.querySelector('input[name="tarif"]');

const form = document.querySelector('form');

// Fonction pour afficher une erreur stylisée au lieu d'un alert()
function showError(message) {
  // Supprimer les anciennes alertes d'erreur
  const oldAlerts = document.querySelectorAll('.alert-danger.js-error');
  oldAlerts.forEach(a => a.remove());
  
  // Créer la nouvelle alerte
  const alertDiv = document.createElement('div');
  alertDiv.className = 'alert alert-danger js-error';
  alertDiv.innerHTML = `<ul><li>${message}</li></ul>`;
  
  // Ajouter le bouton de fermeture
  const closeBtn = document.createElement('button');
  closeBtn.className = 'alert-close';
  closeBtn.innerHTML = '&times;';
  closeBtn.setAttribute('aria-label', 'Fermer');
  closeBtn.addEventListener('click', () => {
    alertDiv.classList.add('fade-out');
    setTimeout(() => alertDiv.remove(), 300);
  });
  alertDiv.appendChild(closeBtn);
  
  // Insérer l'alerte avant le formulaire ou après le h1
  const h1 = document.querySelector('h1');
  const container = form?.closest('.container') || document.querySelector('.container');
  if (h1 && h1.nextSibling) {
    h1.parentNode.insertBefore(alertDiv, h1.nextSibling);
  } else if (container && form) {
    container.insertBefore(alertDiv, form);
  } else if (form) {
    form.parentNode.insertBefore(alertDiv, form);
  }
  
  // Scroll vers l'alerte
  alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function verifDate() {
  if (!dateVisible || !dateCloture) return true;

  const visible = window.parseDateFr ? (window.parseDateFr(dateVisible.value) || new Date(dateVisible.value)) : new Date(dateVisible.value);
  const cloture = window.parseDateFr ? (window.parseDateFr(dateCloture.value) || new Date(dateCloture.value)) : new Date(dateCloture.value);
  
  if (isNaN(visible.getTime()) || isNaN(cloture.getTime())) return true;

  if(visible > cloture) {
      showError("La date de visibilité doit être avant ou égale à la date de clôture.");
      return false;
  }

  if (dateEvenement) {
      const evenement = window.parseDatetimeFr ? (window.parseDatetimeFr(dateEvenement.value) || new Date(dateEvenement.value)) : new Date(dateEvenement.value);
      if (!isNaN(evenement.getTime())) {
        if(evenement < cloture) {
            showError("La date de clôture doit être avant ou égale à la date de l'événement.");
            return false;
        }
        if(evenement < visible) {
            showError("La date de visibilité doit être avant ou égale à la date de l'événement.");
            return false;
        }
      }
  }
  return true;
}

function UrlValide(s) {
  if (!s) return true;
  try {
    new URL(s); 
    return true;
  } catch (_) { 
    return false;  
  }
}

if (form) {
  form.addEventListener('submit', function(e) {
    if (!verifDate()) {
      e.preventDefault(); 
      return;
    }
    if (lieu && lieu.value && !UrlValide(lieu.value)) {
      e.preventDefault();
      showError("Le lien Maps n'est pas valide.");
      lieu.focus();
      return;
    }
    // Vérifier que le lien HelloAsso est rempli si tarif > 0
    if (tarifInput && payement) {
      const tarif = parseFloat(tarifInput.value) || 0;
      const urlHelloasso = payement.value.trim();
      if (tarif > 0 && !urlHelloasso) {
        e.preventDefault();
        showError("Le lien HelloAsso est obligatoire lorsque le tarif est supérieur à 0€.");
        payement.focus();
        return;
      }
    }
    if (payement && payement.value && !UrlValide(payement.value)) {
      e.preventDefault();
      showError("L'URL de paiement HelloAsso n'est pas valide.");
      payement.focus();
      return;
    }
  });
}

// Mise à jour dynamique du champ HelloAsso selon le tarif
if (tarifInput && payement) {
  function updateHelloassoRequired() {
    const tarif = parseFloat(tarifInput.value) || 0;
    const label = payement.closest('.form-group')?.querySelector('label');
    if (tarif > 0) {
      payement.required = true;
      if (label && !label.textContent.includes('*')) {
        label.innerHTML = 'Lien HelloAsso <span style="color: red;">*</span> :';
      }
    } else {
      payement.required = false;
      if (label) {
        label.textContent = 'Lien HelloAsso :';
      }
    }
  }
  
  tarifInput.addEventListener('input', updateHelloassoRequired);
  // Initialiser au chargement
  updateHelloassoRequired();
}

