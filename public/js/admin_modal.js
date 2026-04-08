// modal refus admin

function ouvrirModalRefus(idMembre, nomMembre) {
    document.getElementById('modalIdMembre').value = idMembre;
    document.getElementById('modalMembreNom').innerHTML = 'Vous êtes sur le point de refuser l\'inscription de <strong>' + nomMembre + '</strong>.';
    document.getElementById('motif_refus').value = '';
    document.getElementById('modalRefus').style.display = 'flex';
}

function fermerModalRefus() {
    document.getElementById('modalRefus').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    const modalRefus = document.getElementById('modalRefus');
    if (modalRefus) {
        modalRefus.addEventListener('click', function(e) {
            if (e.target === this) {
                fermerModalRefus();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            fermerModalRefus();
        }
    });
});
