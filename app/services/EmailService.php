<?php

// service pour envoyer des mails
// j'utilise PHPMailer parce que c'est plus simple que mail()
// et ca marche avec gmail
class EmailService
{
    // envoie un email
    // retourne true si ca a marché, false sinon
    public static function envoyer(string $destinataire, string $sujet, string $message, ?string $destinataireNom = null): bool
    {
        try {
            // on charge les variables d'env (mdp gmail, etc)
            require_once __DIR__ . '/../../config/env.php';
            Env::load();

            // on charge phpmailer
            require_once __DIR__ . '/../../vendor/autoload.php';

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            // config du serveur smtp
            $mail->isSMTP();
            $mail->Host       = Env::get('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = Env::get('MAIL_USERNAME');
            $mail->Password   = Env::get('MAIL_PASSWORD');
            $mail->SMTPSecure = Env::get('MAIL_ENCRYPTION', 'tls');
            $mail->Port       = (int) Env::get('MAIL_PORT', 587);
            $mail->CharSet    = 'UTF-8';

            // qui envoie le mail
            $mail->setFrom(
                Env::get('MAIL_FROM_ADDRESS'),
                Env::get('MAIL_FROM_NAME', 'KAST ASSO')
            );

            // a qui on envoie
            $mail->addAddress($destinataire, $destinataireNom ?? '');

            // le contenu du mail
            $mail->isHTML(true);
            $mail->Subject = $sujet;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags($message); // version texte si le html marche pas

            // on envoie
            $mail->send();
            $safeDestinataire = filter_var($destinataire, FILTER_SANITIZE_EMAIL);
            $safeSujet = preg_replace('/[\r\n]/', '', $sujet);
            error_log("Email envoyé avec succès à {$safeDestinataire} - Sujet: {$safeSujet}");
            return true;

        } catch (PHPMailer\PHPMailer\Exception $e) {
            error_log("Erreur PHPMailer lors de l'envoi à {$destinataire}: {$mail->ErrorInfo}");
            return false;
        } catch (Throwable $e) {
            error_log("Erreur générale lors de l'envoi d'email à {$destinataire}: {$e->getMessage()}");
            return false;
        }
    }

    // --- Helpers privés pour factoriser la logique dupliquée ---

    // logique partagée pour les notifications liées à un créneau (inscription / désinscription)
    private static function envoyerNotificationCreneau(int $idCreneau, int $idMembre, string $template, string $sujet): bool
    {
        try {
            require_once __DIR__ . '/../models/Creneau.php';
            require_once __DIR__ . '/../models/Membre.php';
            require_once __DIR__ . '/../models/EvenementSport.php';

            // Récupération des données du créneau
            $creneau = Creneau::findById($idCreneau);
            if (!$creneau) {
                error_log("Créneau introuvable (ID: {$idCreneau}) pour notification");
                return false;
            }

            // Récupération de l'événement sportif
            $evenement = EvenementSport::findById($creneau['Id_Event_sportif']);
            if (!$evenement) {
                error_log("Événement introuvable pour créneau {$idCreneau}");
                return false;
            }

            // Récupération des infos du membre
            $membre = Membre::getMembreParId($idMembre);
            if (!$membre) {
                error_log("Membre introuvable (ID: {$idMembre}) pour notification");
                return false;
            }

            // Préparation des variables pour le template
            // On fournit toutes les variables possibles pour que les deux templates
            // (inscription et désinscription) disposent de tout ce dont ils ont besoin.
            $prenom = $membre['Prenom'];
            $nom = $membre['Nom'];
            $type_inscription = 'creneau';
            $titre_evenement = $evenement['titre'] ?? $evenement['Titre'] ?? 'Événement';
            $type_creneau = $creneau['Type'] ?? $creneau['type'] ?? '';
            $date_creneau = date('d/m/Y', strtotime($creneau['Date_creneau'] ?? $creneau['date_creneau']));
            $heure_debut = date('H:i', strtotime($creneau['Heure_Debut'] ?? $creneau['heure_debut']));
            $heure_fin = date('H:i', strtotime($creneau['Heure_Fin'] ?? $creneau['heure_fin']));
            $commentaire_creneau = $creneau['Commentaire'] ?? $creneau['commentaire'] ?? '';
            $adresse = $evenement['adresse'] ?? $evenement['Adresse'] ?? '';
            $code_postal = $evenement['code_postal'] ?? $evenement['Code_postal'] ?? '';
            $ville = $evenement['ville'] ?? $evenement['Ville'] ?? '';
            $lieu_maps = $evenement['lieu_maps'] ?? $evenement['Lieu_maps'] ?? '';
            $lien_profil = getBaseUrl() . 'index.php?path=/membre/profil';
            $lien_evenements = getBaseUrl() . 'index.php?path=/membre/tableau_de_bord';

            // Génération du contenu HTML
            ob_start();
            require __DIR__ . '/../templates/' . $template;
            $message = ob_get_clean();

            // Envoi de l'email
            return self::envoyer(
                $membre['Mail'],
                "{$sujet} - {$titre_evenement}",
                $message,
                "{$prenom} {$nom}"
            );

        } catch (Throwable $e) {
            error_log("Erreur lors de l'envoi de notification créneau: {$e->getMessage()}");
            return false;
        }
    }

    // logique partagée pour les notifications liées à un événement asso (inscription / désinscription)
    private static function envoyerNotificationEventAsso(int $idEventAsso, int $idMembre, string $template, string $sujet, int $nbInvites = 0): bool
    {
        try {
            require_once __DIR__ . '/../models/Membre.php';
            require_once __DIR__ . '/../models/EvenementAsso.php';

            // Récupération de l'événement
            $evenement = EvenementAsso::findById($idEventAsso);
            if (!$evenement) {
                error_log("Événement associatif introuvable (ID: {$idEventAsso}) pour notification");
                return false;
            }

            // Récupération des infos du membre
            $membre = Membre::getMembreParId($idMembre);
            if (!$membre) {
                error_log("Membre introuvable (ID: {$idMembre}) pour notification");
                return false;
            }

            // Préparation des variables pour le template
            $prenom = $membre['Prenom'];
            $nom = $membre['Nom'];
            $type_inscription = 'evenement';
            $titre_evenement = $evenement['titre'] ?? '';
            $date_evenement = !empty($evenement['date_event_asso']) ? date('d/m/Y à H:i', strtotime($evenement['date_event_asso'])) : 'À confirmer';
            $nb_invites = $nbInvites;
            $adresse = $evenement['adresse'] ?? '';
            $code_postal = $evenement['code_postal'] ?? '';
            $ville = $evenement['ville'] ?? '';
            $lieu_maps = $evenement['lieu_maps'] ?? '';
            $lien_profil = getBaseUrl() . 'index.php?path=/membre/profil';
            $lien_evenements = getBaseUrl() . 'index.php?path=/membre/tableau_de_bord';

            // Génération du contenu HTML
            ob_start();
            require __DIR__ . '/../templates/' . $template;
            $message = ob_get_clean();

            // Envoi de l'email
            return self::envoyer(
                $membre['Mail'],
                "{$sujet} - {$titre_evenement}",
                $message,
                "{$prenom} {$nom}"
            );

        } catch (Throwable $e) {
            error_log("Erreur lors de l'envoi de notification événement: {$e->getMessage()}");
            return false;
        }
    }

    // --- Méthodes publiques (délèguent aux helpers) ---

    // mail confirmation inscription creneau
    public static function envoyerConfirmationInscriptionCreneau(int $idCreneau, int $idMembre): bool
    {
        return self::envoyerNotificationCreneau(
            $idCreneau,
            $idMembre,
            'email_confirmation_inscription.php',
            "Confirmation d'inscription"
        );
    }

    // mail confirmation désinscription créneau
    public static function envoyerConfirmationDesinscriptionCreneau(int $idCreneau, int $idMembre): bool
    {
        return self::envoyerNotificationCreneau(
            $idCreneau,
            $idMembre,
            'email_confirmation_desinscription.php',
            "Confirmation de désinscription"
        );
    }

    // mail confirmation inscription event asso
    public static function envoyerConfirmationInscriptionEventAsso(int $idEventAsso, int $idMembre, int $nbInvites = 0): bool
    {
        return self::envoyerNotificationEventAsso(
            $idEventAsso,
            $idMembre,
            'email_confirmation_inscription.php',
            "Confirmation d'inscription",
            $nbInvites
        );
    }

    // mail confirmation désinscription event asso
    public static function envoyerConfirmationDesinscriptionEventAsso(int $idEventAsso, int $idMembre): bool
    {
        return self::envoyerNotificationEventAsso(
            $idEventAsso,
            $idMembre,
            'email_confirmation_desinscription.php',
            "Confirmation de désinscription"
        );
    }

    // mail rappel creneau 24h avant
    public static function envoyerRappelCreneau(int $idCreneau): int
    {
        try {
            require_once __DIR__ . '/../models/Creneau.php';
            require_once __DIR__ . '/../models/EvenementSport.php';
            require_once __DIR__ . '/../models/Participation.php';

            // Récupération du créneau
            $creneau = Creneau::findById($idCreneau);
            if (!$creneau) {
                error_log("Créneau introuvable (ID: {$idCreneau}) pour rappel");
                return 0;
            }

            // Récupération de l'événement
            $evenement = EvenementSport::findById($creneau['Id_Event_sportif']);
            if (!$evenement) {
                error_log("Événement introuvable pour créneau {$idCreneau}");
                return 0;
            }

            // Récupération des inscrits
            $inscrits = Participation::getInscritsCreneaux($idCreneau);
            if (empty($inscrits)) {
                return 0;
            }

            // Préparation des données communes
            $type_inscription = 'creneau';
            $titre_evenement = $evenement['Titre'];
            $type_creneau = $creneau['Type'];
            $date_creneau = date('d/m/Y', strtotime($creneau['Date_creneau']));
            $heure_debut = date('H:i', strtotime($creneau['Heure_Debut']));
            $heure_fin = date('H:i', strtotime($creneau['Heure_Fin']));
            $commentaire_creneau = $creneau['Commentaire'] ?? '';
            $adresse = $evenement['Adresse'];
            $code_postal = $evenement['Code_postal'];
            $ville = $evenement['Ville'];
            $lieu_maps = $evenement['Lieu_maps'] ?? '';
            $descriptif = $evenement['Descriptif'] ?? '';
            $nb_inscrits = count($inscrits);
            $contact_organisateur = '';
            $lien_profil = getBaseUrl() . 'index.php?path=/membre/profil';

            $nbEnvoyes = 0;

            // Envoi à chaque inscrit
            foreach ($inscrits as $inscrit) {
                $prenom = $inscrit['Prenom'];
                $nom = $inscrit['Nom'];

                // Génération du contenu HTML
                ob_start();
                require __DIR__ . '/../templates/email_rappel_creneau.php';
                $message = ob_get_clean();

                // Envoi de l'email
                if (self::envoyer(
                    $inscrit['Mail'],
                    "Rappel : {$titre_evenement} - Demain !",
                    $message,
                    "{$prenom} {$nom}"
                )) {
                    $nbEnvoyes++;
                }
            }

            return $nbEnvoyes;

        } catch (Throwable $e) {
            error_log("Erreur lors de l'envoi des rappels pour créneau {$idCreneau}: {$e->getMessage()}");
            return 0;
        }
    }

    // notif modification event
    public static function notifierModificationEvent(int $idEvent, string $typeEvent, array $modifications = [], string $messageOrganisateur = ''): int
    {
        try {
            require_once __DIR__ . '/../models/EvenementSport.php';
            require_once __DIR__ . '/../models/EvenementAsso.php';
            require_once __DIR__ . '/../models/Participation.php';
            require_once __DIR__ . '/../models/Creneau.php';

            $inscrits = [];

            if ($typeEvent === 'sport') {
                $evenement = EvenementSport::findById($idEvent);
                if (!$evenement) return 0;

                // Récupérer tous les inscrits de tous les créneaux
                $inscrits = Participation::obtenirInscritsPourEvenement($idEvent);
            } else {
                $evenement = EvenementAsso::findById($idEvent);
                if (!$evenement) return 0;

                $inscrits = Participation::getParticipants($idEvent);
            }

            if (empty($inscrits)) return 0;

            // Préparation des données
            $titre_evenement = $evenement['Titre'];
            $adresse = $evenement['Adresse'];
            $code_postal = $evenement['Code_postal'];
            $ville = $evenement['Ville'];
            $lieu_maps = $evenement['Lieu_maps'] ?? '';
            $descriptif = $evenement['Descriptif'] ?? '';
            $message_organisateur = $messageOrganisateur;
            $lien_profil = getBaseUrl() . 'index.php?path=/membre/profil';

            $nbEnvoyes = 0;

            foreach ($inscrits as $inscrit) {
                $prenom = $inscrit['Prenom'];
                $nom = $inscrit['Nom'];
                $type_inscription = $typeEvent === 'sport' ? 'creneau' : 'evenement';

                if ($typeEvent === 'sport') {
                    $date_evenement = '';
                    $type_creneau = '';
                    $date_creneau = '';
                    $heure_debut = '';
                    $heure_fin = '';
                    $commentaire_creneau = '';
                } else {
                    $date_evenement = date('d/m/Y à H:i', strtotime($evenement['Date_event_asso']));
                }

                ob_start();
                require __DIR__ . '/../templates/email_modification_event.php';
                $message = ob_get_clean();

                if (self::envoyer(
                    $inscrit['Mail'],
                    "Modification - {$titre_evenement}",
                    $message,
                    "{$prenom} {$nom}"
                )) {
                    $nbEnvoyes++;
                }
            }

            return $nbEnvoyes;

        } catch (Throwable $e) {
            error_log("Erreur lors de la notification de modification: {$e->getMessage()}");
            return 0;
        }
    }

    // notif annulation event
    public static function notifierAnnulationEvent(int $idEvent, string $typeEvent, string $raisonAnnulation = ''): int
    {
        try {
            require_once __DIR__ . '/../models/EvenementSport.php';
            require_once __DIR__ . '/../models/EvenementAsso.php';
            require_once __DIR__ . '/../models/Participation.php';

            $inscrits = [];

            if ($typeEvent === 'sport') {
                $evenement = EvenementSport::findById($idEvent);
                if (!$evenement) return 0;

                $inscrits = Participation::obtenirInscritsPourEvenement($idEvent);
            } else {
                $evenement = EvenementAsso::findById($idEvent);
                if (!$evenement) return 0;

                $inscrits = Participation::getParticipants($idEvent);
            }

            if (empty($inscrits)) return 0;

            // Préparation des données
            $titre_evenement = $evenement['titre'] ?? '';
            $adresse = $evenement['adresse'] ?? '';
            $code_postal = $evenement['code_postal'] ?? '';
            $ville = $evenement['ville'] ?? '';
            $raison_annulation = $raisonAnnulation;
            $evenements_similaires = [];
            $lien_evenements = getBaseUrl() . 'index.php?path=/';

            $nbEnvoyes = 0;

            foreach ($inscrits as $inscrit) {
                $prenom = $inscrit['Prenom'] ?? $inscrit['prenom'] ?? '';
                $nom = $inscrit['Nom'] ?? $inscrit['nom'] ?? '';
                $email = $inscrit['Mail'] ?? $inscrit['mail'] ?? '';
                $type_inscription = $typeEvent === 'sport' ? 'creneau' : 'evenement';

                if ($typeEvent === 'sport') {
                    $date_creneau = '';
                    $heure_debut = '';
                    $heure_fin = '';
                    $type_creneau = '';
                    $date_evenement = '';
                } else {
                    $date_evenement = !empty($evenement['date_event_asso'])
                        ? date('d/m/Y à H:i', strtotime($evenement['date_event_asso']))
                        : '';
                }

                ob_start();
                require __DIR__ . '/../templates/email_annulation_event.php';
                $message = ob_get_clean();

                if (self::envoyer(
                    $email,
                    "Annulation - {$titre_evenement}",
                    $message,
                    "{$prenom} {$nom}"
                )) {
                    $nbEnvoyes++;
                }
            }

            return $nbEnvoyes;

        } catch (Throwable $e) {
            error_log("Erreur lors de la notification d'annulation: {$e->getMessage()}");
            return 0;
        }
    }
}
