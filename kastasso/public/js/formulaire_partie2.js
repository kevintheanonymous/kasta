var verif_Tshirt = document.getElementById('t-shirt');
var verif_Pull = document.getElementById('pull');

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
function formulaireValid(event){
  let valid = true;
  if (!valid_Tshirt()) valid = false;
  if (!valid_Pull()) valid = false;
  if (!valid_restriction()) valid = false;
  if (!valid) {
    event.preventDefault();
  }
}
document.querySelector('form').addEventListener('submit', formulaireValid);
verif_Tshirt.addEventListener('change', valid_Tshirt);
verif_Pull.addEventListener('change', valid_Pull);

const form = document.querySelector('form');
if (form) {
  form.addEventListener('submit', (e) => {
    const tshirt = document.getElementById('t-shirt')?.value.trim();
    const pull = document.getElementById('pull')?.value.trim();
    if (!tshirt || !pull) {
      e.preventDefault();
    }
  });
}