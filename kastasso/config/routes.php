<?php
// fichier de config des routes
// format : 'url' => ['NomDuControleur', 'nomDeLaMethode']
return [
    'GET' => [
        '/'                        => ['ControleurAuth', 'afficherAccueil'],
        '/accueil'                 => ['ControleurAuth', 'afficherAccueil'],
        '/inscription'             => ['ControleurAuth', 'afficherInscription'],
        '/connexion'               => ['ControleurAuth', 'afficherConnexion'],
        '/contact'                 => ['ControleurMembre', 'afficherContact'],
        '/deconnexion'             => ['ControleurAuth', 'deconnexion'],
        '/admin/dashboard'         => ['ControleurAdmin', 'afficherDashboard'],
        '/admin/tableau_de_bord'   => ['ControleurAdmin', 'afficherDashboard'],
        '/admin/valider'           => ['ControleurAdmin', 'afficherDashboard'],
        '/admin/refuser'           => ['ControleurAdmin', 'afficherDashboard'],
        '/admin/membres'           => ['ControleurAdmin', 'afficherGestionMembres'],
        '/admin/membre/detail'     => ['ControleurAdmin', 'voirMembre'],
        '/admin/membre/historique' => ['ControleurAdmin', 'afficherHistoriqueMembre'],
        '/admin/adhesions'         => ['ControleurAdmin', 'afficherDemandesAdhesion'],
        '/membre/tableau_de_bord'  => ['ControleurMembre', 'afficherTableauDeBord'],
        '/membre/evenements'       => ['ControleurMembre', 'afficherEvenements'],
        '/membre/profil'           => ['ControleurMembre', 'afficherProfil'],
        '/membre/profil/edit'      => ['ControleurMembre', 'afficherEditionProfil'],
        '/membre/securite'         => ['ControleurMembre', 'afficherSecurite'],
        '/membre/inscription/sport' => ['ControleurMembre', 'afficherInscriptionSport'],
        '/membre/inscription/asso'  => ['ControleurMembre', 'afficherInscriptionAsso'],
        '/membre/mes_inscriptions_sport' => ['ControleurMembre', 'afficherMesInscriptionsSport'],
        '/membre/mes_inscriptions_asso'  => ['ControleurMembre', 'afficherMesInscriptionsAsso'],
        '/membre/mes-evenements-passes' => ['ControleurMembre', 'afficherMesEvenementsPasses'],
        '/membre/contact' => ['ControleurMembre', 'afficherContact'],
        
        // routes pour les events (admin)
        '/admin/events'            => ['ControleurEvenement', 'adminIndex'],
        '/admin/events/create'     => ['ControleurEvenement', 'adminCreate'],
        '/admin/events/create-with-slots' => ['ControleurEvenement', 'adminCreateWithSlots'],
        '/admin/events/edit'       => ['ControleurEvenement', 'adminEdit'],
        '/admin/events/benevoles'  => ['ControleurEvenement', 'afficherBenevolesAdmin'],
        '/admin/events/participants' => ['ControleurEvenement', 'afficherParticipantsAdmin'],
        '/admin/events/pdf-participants' => ['ControleurEvenement', 'genererPDFParticipantsAdmin'],
        '/admin/events/pdf-participants-asso' => ['ControleurEvenement', 'genererPDFParticipantsAssoAdmin'],

        '/gestionnaire/tableau_de_bord' => ['ControleurGestionnaire', 'afficherDashboard'],
        '/gestionnaire/events'          => ['ControleurGestionnaire', 'afficherEvenements'],
        '/gestionnaire/events/create'   => ['ControleurGestionnaire', 'creerEvenement'],
        '/gestionnaire/events/create-with-slots' => ['ControleurGestionnaire', 'creerEvenementAvecCreneaux'],
        '/gestionnaire/events/edit'     => ['ControleurGestionnaire', 'modifierEvenement'],
        '/gestionnaire/events/benevoles' => ['ControleurGestionnaire', 'afficherBenevolesGestionnaire'],
        '/gestionnaire/events/participants' => ['ControleurGestionnaire', 'afficherParticipantsGestionnaire'],
        '/gestionnaire/events/pdf-participants' => ['ControleurGestionnaire', 'genererPDFParticipantsGestionnaire'],
        '/gestionnaire/events/pdf-participants-asso' => ['ControleurGestionnaire', 'genererPDFParticipantsAssoGestionnaire'],
        '/gestionnaire/adhesions'       => ['ControleurAdmin', 'afficherDemandesAdhesion'],

        // routes publiques pour les events
        // '/events' => ['ControleurEvenement', 'index'], // on l'a enlevé
        '/events/detail'           => ['ControleurEvenement', 'detail'],

        // routes pour les categories (admin)
        '/admin/categories'        => ['ControleurCategorie', 'index'],
        '/admin/categories/create' => ['ControleurCategorie', 'create'],
        '/admin/categories/edit'   => ['ControleurCategorie', 'edit'],

        // routes pour les postes (admin)
        '/admin/postes'            => ['ControleurPoste', 'index'],
        '/admin/postes/create'     => ['ControleurPoste', 'create'],
        '/admin/postes/edit'       => ['ControleurPoste', 'edit'],

        // routes pour les regimes alimentaires (admin)
        '/admin/regimes-alimentaires' => ['ControleurRegimeAlimentaire', 'afficherGestion'],

        // route pour le template d'adhesion (admin only)
        '/admin/template-adhesion'         => ['ControleurAdmin', 'afficherTemplateAdhesion'],
        '/admin/template-adhesion/preview' => ['ControleurAdmin', 'previewTemplateAdhesion'],

        // routes pour les creneaux (admin)
        '/admin/creneaux'          => ['ControleurCreneau', 'index'],
        '/admin/creneaux/create'   => ['ControleurCreneau', 'create'],
        '/admin/creneaux/edit'     => ['ControleurCreneau', 'edit'],
        '/admin/creneaux/inscrits' => ['ControleurCreneau', 'inscrits'],
        
        // mot de passe oublié
        '/mot_de_passe_oublie'     => ['ControleurAuth', 'afficherMotDePasseOublie'],
        '/reinitialisation_mdp'    => ['ControleurAuth', 'afficherReinitialisation'],

        // documents
        '/documents/formulaire-adhesion' => ['ControleurDocument', 'telechargerFormulaireAdhesion'],
        '/documents/visualiser-adhesion' => ['ControleurDocument', 'visualiserDocumentAdhesion'],
    ],
    'POST' => [
        '/inscription'             => ['ControleurAuth', 'traiterInscription'],
        '/connexion'               => ['ControleurAuth', 'traiterConnexion'],
        
        // mot de passe oublié
        '/mot_de_passe_oublie'     => ['ControleurAuth', 'traiterMotDePasseOublie'],
        '/reinitialisation_mdp'    => ['ControleurAuth', 'traiterReinitialisation'],

        '/admin/valider'           => ['ControleurAdmin', 'validerMembre'],
        '/admin/refuser'           => ['ControleurAdmin', 'refuserMembre'],
        '/admin/rendre_gestionnaire'=> ['ControleurAdmin', 'rendreGestionnaire'],
        '/admin/membre/supprimer'   => ['ControleurAdmin', 'supprimerMembreAdmin'],
        '/admin/membre/modifier-adherent' => ['ControleurAdmin', 'modifierStatutAdherent'],
        '/admin/adhesion/accepter' => ['ControleurAdmin', 'accepterAdhesion'],
        '/admin/adhesion/refuser'  => ['ControleurAdmin', 'refuserAdhesion'],
        '/admin/membres'           => ['ControleurAdmin', 'afficherGestionMembres'],
        '/admin/membre/detail'     => ['ControleurAdmin', 'voirMembre'],
        '/membre/profil/update'    => ['ControleurMembre', 'traiterEditionProfil'],
        '/membre/securite/changer-mdp' => ['ControleurMembre', 'traiterChangementMotDePasse'],
        '/membre/supprimer'        => ['ControleurMembre', 'supprimerCompte'],
        
        // actions sur les events (admin)
        '/admin/events/store'      => ['ControleurEvenement', 'adminStore'],
        '/admin/events/store-with-slots' => ['ControleurEvenement', 'adminStoreWithSlots'],
        '/admin/events/update'     => ['ControleurEvenement', 'adminUpdate'],
        '/admin/events/delete'     => ['ControleurEvenement', 'adminDelete'],
        '/admin/events/paiement'   => ['ControleurEvenement', 'modifierPaiement'],

        '/gestionnaire/events/store'    => ['ControleurGestionnaire', 'enregistrerEvenement'],
        '/gestionnaire/events/store-with-slots' => ['ControleurGestionnaire', 'enregistrerEvenementAvecCreneaux'],
        '/gestionnaire/events/update'   => ['ControleurGestionnaire', 'miseAJourEvenement'],
        '/gestionnaire/events/delete'   => ['ControleurGestionnaire', 'supprimerEvenement'],
        '/gestionnaire/events/paiement' => ['ControleurGestionnaire', 'modifierPaiement'],
        '/gestionnaire/adhesion/accepter' => ['ControleurAdmin', 'accepterAdhesion'],
        '/gestionnaire/adhesion/refuser'  => ['ControleurAdmin', 'refuserAdhesion'],

        // actions sur les categories (admin)
        '/admin/categories/store'  => ['ControleurCategorie', 'store'],
        '/admin/categories/update' => ['ControleurCategorie', 'update'],
        '/admin/categories/delete' => ['ControleurCategorie', 'delete'],

        // actions sur les postes (admin)
        '/admin/postes/store'      => ['ControleurPoste', 'store'],
        '/admin/postes/update'     => ['ControleurPoste', 'update'],
        '/admin/postes/delete'     => ['ControleurPoste', 'delete'],

        // actions sur les creneaux (admin)
        '/admin/creneaux/store'    => ['ControleurCreneau', 'store'],
        '/admin/creneaux/update'   => ['ControleurCreneau', 'update'],
        '/admin/creneaux/delete'   => ['ControleurCreneau', 'delete'],
        '/admin/creneaux/marquer-presences' => ['ControleurCreneau', 'marquerPresences'],
        
        // actions sur les présences (gestionnaire)
        '/gestionnaire/creneaux/marquer-presences' => ['ControleurGestionnaire', 'marquerPresences'],
        
        '/membre/devenir_adherent' => ['ControleurMembre', 'devenirAdherent'],
        '/membre/soumettre-adhesion' => ['ControleurMembre', 'soumettreAdhesion'],
        '/membre/inscription/sport' => ['ControleurMembre', 'traiterInscriptionSport'],
        '/membre/inscription/asso'  => ['ControleurMembre', 'traiterInscriptionAsso'],
        '/membre/desinscription/sport' => ['ControleurMembre', 'traiterDesinscriptionSport'],
        '/membre/desinscription/asso'  => ['ControleurMembre', 'traiterDesinscriptionAsso'],
        '/membre/desinscription/sport/complet' => ['ControleurMembre', 'traiterDesinscriptionSportComplet'],
        // route obsolete : la modification des accompagnateurs se fait maintenant via le formulaire d'inscription en mode edition
        // '/membre/modifier_accompagnateurs' => ['ControleurMembre', 'traiterModificationAccompagnateurs'],
        '/membre/calculer-tarif-asso'  => ['ControleurMembre', 'calculerTarifAsso'],

        // actions sur les regimes alimentaires (admin)
        '/admin/regimes-alimentaires/ajouter' => ['ControleurRegimeAlimentaire', 'ajouter'],
        '/admin/regimes-alimentaires/modifier' => ['ControleurRegimeAlimentaire', 'modifier'],
        '/admin/regimes-alimentaires/supprimer' => ['ControleurRegimeAlimentaire', 'supprimer'],

        // actions sur le template d'adhesion (admin only)
        '/admin/template-adhesion/upload' => ['ControleurAdmin', 'uploadTemplateAdhesion'],
        '/admin/template-adhesion/delete' => ['ControleurAdmin', 'deleteTemplateAdhesion'],

        '/membre/contact/send' => ['ControleurMembre', 'traiterContact'],
        '/contact/send' => ['ControleurMembre', 'traiterContact'],
    ],
];