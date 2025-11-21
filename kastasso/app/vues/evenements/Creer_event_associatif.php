<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un événement associatif</title>
	<link rel="stylesheet" href="/kastasso/public/css/formulaire_event.css">
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet"> 
	<!--Pour charger le font "Montserrat"-->

</head>
<body>
    <nav class="nav">
    <ul class="nav-links">
	  <strong>
	  
	  <div id="membre"><!--L'espacement entre "inscription" et "connexion" n'est pas le meme que "moncompte" et "accueil" par exemple, donc on crée ce div pour le changement -->
	  </div>
	  
	  <li><a href="#">Mon Compte</a></li>
      <li><a href="#">Accueil</a></li> 

	  <div id="logo">
	  <li id="KASTA"><a>KASTA</a></li>
	  <li id="CROSSFIT"><a>CROSSFIT</a></li>
	  </div>
	  </strong>
    </ul>
  </nav>
    <h1>Créer un événement associatif</h1>
    <form action="/kastasso/public/index.php?path=/evenement/creer" method="post"> 
        
        <label>Titre :
            <input type="text" name="titre" required>
        </label>

        <label for="photo-upload" class="upload-label">Ajouter une illustration pour l'événement</label>
        <input type="file" id="photo-upload" name="photo-upload" accept="image/*" style="display:none;">

        <label>Adresse :
            <input type="text" name="lieu_texte" required>
        </label>

        <label>Lien Maps :
            <input type="text" name="lieu_maps" required>
        </label>

        <label for="date">Date de visibilité :
            <input type="date" id="date" name="date_visible" required>
        </label>

        <label for="date">Date de clôture :
            <input type="date" id="date" name="date_cloture" required>
        </label>

        <label for="date">Date de l'événement :
            <input type="date" id="date" name="date_event_asso" required>
        </label>
    
        <label>Tarif :
            <input type="number" name="tarif" required>
        </label>

        <label>Lien de paiement :
            <input type="text" name="url_helloasso" required>
        </label>

        <label for="prive">Événement privé :
            <select name="prive" id="prive">
            <option value="0">Non</option>
            <option value="1">Oui</option>
            </select>
        </label>

        <label>Descriptif :
            <textarea name="descriptif" rows="4" cols="50"></textarea>
        </label>       
        
        <input type="reset" value="Vider">
        <input type="submit" value="Confirmer la création de l'événement">
    </form>

    <script src="/kastasso/public/js/ev.js"></script>
    </body>

