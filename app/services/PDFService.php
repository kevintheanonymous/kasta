<?php
// genere des PDF pour les listes de participants

require_once __DIR__ . '/../../vendor/autoload.php';

class PDFService
{
    // organise par categories alimentaires
    public static function organiserParCategories($participants)
    {
        $categoriesRestrictions = [];
        $categoriesPreferences = [];
        $sansRegime = [];

        foreach ($participants as $participant) {
            $aRestriction = !empty($participant['restrictions']);
            $aPreference = !empty($participant['preferences']);
            $aRegimeAlimentaire = !empty($participant['regime_alimentaire']);

            // Si le participant a des restrictions, on l'ajoute à chaque catégorie de restriction
            if ($aRestriction) {
                $restrictions = explode(', ', $participant['restrictions']);
                foreach ($restrictions as $restriction) {
                    if (!isset($categoriesRestrictions[$restriction])) {
                        $categoriesRestrictions[$restriction] = [];
                    }
                    $categoriesRestrictions[$restriction][] = $participant;
                }
            }

            // Si le participant a des préférences, on l'ajoute à chaque catégorie de préférence
            if ($aPreference) {
                $preferences = explode(', ', $participant['preferences']);
                foreach ($preferences as $preference) {
                    if (!isset($categoriesPreferences[$preference])) {
                        $categoriesPreferences[$preference] = [];
                    }
                    $categoriesPreferences[$preference][] = $participant;
                }
            }

            // Si le participant n'a ni restriction, ni préférence, ni régime alimentaire
            if (!$aRestriction && !$aPreference && !$aRegimeAlimentaire) {
                $sansRegime[] = $participant;
            }
        }

        // Tri alphabétique des catégories
        ksort($categoriesRestrictions);
        ksort($categoriesPreferences);

        return [
            'restrictions' => $categoriesRestrictions,
            'preferences' => $categoriesPreferences,
            'sans_regime' => $sansRegime
        ];
    }

    // genere PDF participants sport
    public static function genererPDFParticipants($evenement, $participants)
    {
        // Création de l'objet TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Métadonnées du document
        $pdf->SetCreator('Kastasso');
        $pdf->SetAuthor('Admin Kastasso');
        $pdf->SetTitle('Liste participants - ' . $evenement['titre']);
        $pdf->SetSubject('Participants avec régimes alimentaires');

        // Configuration
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Supprimer header et footer par défaut
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Police UTF-8 pour gérer les accents
        $pdf->SetFont('helvetica', '', 10);

        // Ajouter une page
        $pdf->AddPage();

        // Si aucun participant
        if (empty($participants)) {
            $html = '<h1 style="text-align: center; color: #666;">Liste des participants</h1>';
            $html .= '<h2 style="text-align: center; color: #333;">' . htmlspecialchars($evenement['titre']) . '</h2>';
            $html .= '<p style="text-align: center;"><strong>Date de génération:</strong> ' . date('d/m/Y à H:i') . '</p>';
            $html .= '<hr>';
            $html .= '<p style="text-align: center; font-size: 16px; color: #999; margin-top: 50px;">Aucun participant inscrit à cet événement.</p>';

            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Output('participants_evenement_' . $evenement['id_event_sport'] . '_' . date('Y-m-d') . '.pdf', 'D');
            exit;
        }

        // Organiser les participants par catégories
        $categories = self::organiserParCategories($participants);

        // Construction du HTML du PDF
        $html = self::construireHTMLPDF($evenement, $participants, $categories);

        // Écrire le HTML dans le PDF
        $pdf->writeHTML($html, true, false, true, false, '');

        // Générer et envoyer le PDF
        $nomFichier = 'participants_evenement_' . $evenement['id_event_sport'] . '_' . date('Y-m-d') . '.pdf';
        $pdf->Output($nomFichier, 'D'); // 'D' = Download
        exit;
    }

    // construit le HTML du PDF
    private static function construireHTMLPDF($evenement, $participants, $categories)
    {
        $html = '';

        // En-tête du document
        $html .= '<h1 style="text-align: center; color: #2c3e50;">Liste des participants</h1>';
        $html .= '<h2 style="text-align: center; color: #34495e;">' . htmlspecialchars($evenement['titre']) . '</h2>';
        $html .= '<p style="text-align: center;"><strong>Date de génération:</strong> ' . date('d/m/Y à H:i') . '</p>';
        $html .= '<p style="text-align: center;"><strong>Nombre total de participants:</strong> ' . count($participants) . '</p>';
        $html .= '<hr style="border: 1px solid #3498db;">';
        $html .= '<br>';

        // Section recap des regimes alimentaires
        $html .= self::construireRecapitulatifRegimes($participants);
        $html .= '<hr style="border: 1px solid #3498db; margin: 20px 0;">';
        $html .= '<br>';

        // Section 1: Restrictions alimentaires
        if (!empty($categories['restrictions'])) {
            $html .= '<h2 style="color: #e74c3c; background-color: #fadbd8; padding: 8px; border-left: 4px solid #e74c3c;">Restrictions alimentaires</h2>';
            $html .= '<br>';

            foreach ($categories['restrictions'] as $restriction => $participants) {
                $html .= '<h3 style="color: #c0392b; margin-top: 15px;">' . htmlspecialchars($restriction) . ' (' . count($participants) . ' participant' . (count($participants) > 1 ? 's' : '') . ')</h3>';
                $html .= self::construireTableauParticipants($participants);
                $html .= '<br>';
            }
        }

        // Section 2: Préférences alimentaires
        if (!empty($categories['preferences'])) {
            $html .= '<h2 style="color: #27ae60; background-color: #d5f4e6; padding: 8px; border-left: 4px solid #27ae60;">Préférences alimentaires</h2>';
            $html .= '<br>';

            foreach ($categories['preferences'] as $preference => $participants) {
                $html .= '<h3 style="color: #229954; margin-top: 15px;">' . htmlspecialchars($preference) . ' (' . count($participants) . ' participant' . (count($participants) > 1 ? 's' : '') . ')</h3>';
                $html .= self::construireTableauParticipants($participants);
                $html .= '<br>';
            }
        }

        // Section 3: Participants avec régime alimentaire déclaré
        $participantsAvecRegime = array_filter($participants, function($p) {
            return !empty($p['regime_alimentaire']);
        });

        if (!empty($participantsAvecRegime)) {
            $html .= '<h2 style="color: #9b59b6; background-color: #f5eef8; padding: 8px; border-left: 4px solid #9b59b6;">Régimes alimentaires déclarés</h2>';
            $html .= '<br>';
            $html .= '<p><strong>' . count($participantsAvecRegime) . ' participant' . (count($participantsAvecRegime) > 1 ? 's' : '') . '</strong> avec un régime alimentaire déclaré</p>';
            $html .= self::construireTableauParticipantsAvecRegime($participantsAvecRegime);
            $html .= '<br>';
        }

        // Section 4: Sans contrainte alimentaire
        if (!empty($categories['sans_regime'])) {
            $html .= '<h2 style="color: #7f8c8d; background-color: #ecf0f1; padding: 8px; border-left: 4px solid #7f8c8d;">Sans contrainte alimentaire</h2>';
            $html .= '<br>';
            $html .= '<p><strong>' . count($categories['sans_regime']) . ' participant' . (count($categories['sans_regime']) > 1 ? 's' : '') . '</strong> sans restriction ni préférence</p>';
            $html .= self::construireTableauParticipants($categories['sans_regime']);
            $html .= '<br>';
        }

        // Section 5: Commentaires alimentaires spécifiques
        $participantsAvecCommentaires = array_filter($participants, function($p) {
            return !empty($p['commentaire_alimentaire']);
        });

        if (!empty($participantsAvecCommentaires)) {
            $html .= '<h2 style="color: #f39c12; background-color: #fef5e7; padding: 8px; border-left: 4px solid #f39c12;">Commentaires alimentaires spécifiques</h2>';
            $html .= '<br>';
            $html .= self::construireTableauCommentaires($participantsAvecCommentaires);
            $html .= '<br>';
        }

        return $html;
    }

    // tableau HTML participants
    private static function construireTableauParticipants($participants)
    {
        $html = '<table border="1" cellpadding="6" cellspacing="0" width="100%">';
        $html .= '<tr bgcolor="#3498db" style="color: white;">';
        $html .= '<th align="left" width="13%"><strong>Nom</strong></th>';
        $html .= '<th align="left" width="13%"><strong>Prénom</strong></th>';
        $html .= '<th align="left" width="24%"><strong>Email</strong></th>';
        $html .= '<th align="center" width="15%"><strong>Téléphone</strong></th>';
        $html .= '<th align="left" width="35%"><strong>Créneaux</strong></th>';
        $html .= '</tr>';

        foreach ($participants as $participant) {
            $html .= '<tr>';
            $html .= '<td align="left" valign="top">' . htmlspecialchars($participant['nom']) . '</td>';
            $html .= '<td align="left" valign="top">' . htmlspecialchars($participant['prenom']) . '</td>';
            $html .= '<td align="left" valign="top" style="font-size: 8px;">' . htmlspecialchars($participant['mail']) . '</td>';
            $html .= '<td align="center" valign="top">' . htmlspecialchars($participant['telephone'] ?? '-') . '</td>';
            
            // Formater les créneaux pour meilleure lisibilité (un par ligne)
            $creneaux = $participant['creneaux'] ?? '-';
            if ($creneaux !== '-') {
                $creneauxArray = explode(' | ', $creneaux);
                $creneauxFormates = '';
                foreach ($creneauxArray as $creneau) {
                    $creneauxFormates .= '• ' . htmlspecialchars($creneau) . '<br/>';
                }
                $html .= '<td align="left" valign="top" style="font-size: 8px; line-height: 1.5;">' . $creneauxFormates . '</td>';
            } else {
                $html .= '<td align="center" valign="top">-</td>';
            }
            
            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }

    // tableau HTML participants avec colonne régime alimentaire
    private static function construireTableauParticipantsAvecRegime($participants)
    {
        $html = '<table border="1" cellpadding="6" cellspacing="0" width="100%">';
        $html .= '<tr bgcolor="#9b59b6" style="color: white;">';
        $html .= '<th align="left" width="12%"><strong>Nom</strong></th>';
        $html .= '<th align="left" width="12%"><strong>Prénom</strong></th>';
        $html .= '<th align="left" width="22%"><strong>Email</strong></th>';
        $html .= '<th align="center" width="12%"><strong>Téléphone</strong></th>';
        $html .= '<th align="center" width="14%"><strong>Régime</strong></th>';
        $html .= '<th align="left" width="28%"><strong>Créneaux</strong></th>';
        $html .= '</tr>';

        foreach ($participants as $participant) {
            $html .= '<tr>';
            $html .= '<td align="left" valign="top">' . htmlspecialchars($participant['nom']) . '</td>';
            $html .= '<td align="left" valign="top">' . htmlspecialchars($participant['prenom']) . '</td>';
            $html .= '<td align="left" valign="top" style="font-size: 8px;">' . htmlspecialchars($participant['mail']) . '</td>';
            $html .= '<td align="center" valign="top">' . htmlspecialchars($participant['telephone'] ?? '-') . '</td>';
            $html .= '<td align="center" valign="top" style="font-weight: bold; color: #9b59b6;">' . htmlspecialchars($participant['regime_alimentaire']) . '</td>';
            
            $creneaux = $participant['creneaux'] ?? '-';
            if ($creneaux !== '-') {
                $creneauxArray = explode(' | ', $creneaux);
                $creneauxFormates = '';
                foreach ($creneauxArray as $creneau) {
                    $creneauxFormates .= '• ' . htmlspecialchars($creneau) . '<br/>';
                }
                $html .= '<td align="left" valign="top" style="font-size: 8px; line-height: 1.5;">' . $creneauxFormates . '</td>';
            } else {
                $html .= '<td align="center" valign="top">-</td>';
            }
            
            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }

    // tableau HTML participants asso avec colonne régime alimentaire
    private static function construireTableauParticipantsAvecRegimeAsso($participants)
    {
        $html = '<table border="1" cellpadding="6" cellspacing="0" width="100%">';
        $html .= '<tr bgcolor="#9b59b6" style="color: white;">';
        $html .= '<th align="left" width="18%"><strong>Nom</strong></th>';
        $html .= '<th align="left" width="18%"><strong>Prénom</strong></th>';
        $html .= '<th align="left" width="28%"><strong>Email</strong></th>';
        $html .= '<th align="center" width="16%"><strong>Téléphone</strong></th>';
        $html .= '<th align="center" width="20%"><strong>Régime</strong></th>';
        $html .= '</tr>';

        foreach ($participants as $participant) {
            $html .= '<tr>';
            $html .= '<td align="left" valign="top">' . htmlspecialchars($participant['nom']) . '</td>';
            $html .= '<td align="left" valign="top">' . htmlspecialchars($participant['prenom']) . '</td>';
            $html .= '<td align="left" valign="top" style="font-size: 8px;">' . htmlspecialchars($participant['mail']) . '</td>';
            $html .= '<td align="center" valign="top">' . htmlspecialchars($participant['telephone'] ?? '-') . '</td>';
            $html .= '<td align="center" valign="top" style="font-weight: bold; color: #9b59b6;">' . htmlspecialchars($participant['regime_alimentaire']) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }

    // tableau HTML commentaires alimentaires
    private static function construireTableauCommentaires($participants)
    {
        $html = '<table border="1" cellpadding="6" cellspacing="0" width="100%">';
        $html .= '<tr bgcolor="#f39c12" style="color: white;">';
        $html .= '<th align="left" width="20%"><strong>Nom</strong></th>';
        $html .= '<th align="left" width="20%"><strong>Prénom</strong></th>';
        $html .= '<th align="left" width="60%"><strong>Commentaire</strong></th>';
        $html .= '</tr>';

        foreach ($participants as $participant) {
            $html .= '<tr>';
            $html .= '<td align="left" valign="top">' . htmlspecialchars($participant['nom']) . '</td>';
            $html .= '<td align="left" valign="top">' . htmlspecialchars($participant['prenom']) . '</td>';
            $html .= '<td align="left" valign="top" style="font-style: italic;">"' . htmlspecialchars($participant['commentaire_alimentaire']) . '"</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }

    // genere PDF participants asso
    public static function genererPDFParticipantsAsso($evenement, $participants)
    {
        // Création de l'objet TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Métadonnées du document
        $pdf->SetCreator('Kastasso');
        $pdf->SetAuthor('Admin Kastasso');
        $pdf->SetTitle('Liste participants - ' . $evenement['titre']);
        $pdf->SetSubject('Participants avec régimes alimentaires');

        // Configuration
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Supprimer header et footer par défaut
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Police UTF-8 pour gérer les accents
        $pdf->SetFont('helvetica', '', 10);

        // Ajouter une page
        $pdf->AddPage();

        // Si aucun participant
        if (empty($participants)) {
            $html = '<h1 style="text-align: center; color: #666;">Liste des participants</h1>';
            $html .= '<h2 style="text-align: center; color: #333;">' . htmlspecialchars($evenement['titre']) . '</h2>';
            $html .= '<p style="text-align: center;"><strong>Date de génération:</strong> ' . date('d/m/Y à H:i') . '</p>';
            $html .= '<hr>';
            $html .= '<p style="text-align: center; font-size: 16px; color: #999; margin-top: 50px;">Aucun participant inscrit à cet événement.</p>';

            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Output('participants_evenement_asso_' . $evenement['id_event_asso'] . '_' . date('Y-m-d') . '.pdf', 'D');
            exit;
        }

        // Organiser les participants par catégories
        $categories = self::organiserParCategories($participants);

        // Construction du HTML du PDF
        $html = self::construireHTMLPDFAsso($evenement, $participants, $categories);

        // Écrire le HTML dans le PDF
        $pdf->writeHTML($html, true, false, true, false, '');

        // Générer et envoyer le PDF
        $nomFichier = 'participants_evenement_asso_' . $evenement['id_event_asso'] . '_' . date('Y-m-d') . '.pdf';
        $pdf->Output($nomFichier, 'D'); // 'D' = Download
        exit;
    }

    // HTML PDF asso
    private static function construireHTMLPDFAsso($evenement, $participants, $categories)
    {
        $html = '';

        // En-tête du document
        $html .= '<h1 style="text-align: center; color: #2c3e50;">Liste des participants</h1>';
        $html .= '<h2 style="text-align: center; color: #34495e;">' . htmlspecialchars($evenement['titre']) . '</h2>';
        $html .= '<p style="text-align: center;"><strong>Date de génération:</strong> ' . date('d/m/Y à H:i') . '</p>';
        $html .= '<p style="text-align: center;"><strong>Nombre total de participants:</strong> ' . count($participants) . '</p>';
        $html .= '<hr style="border: 1px solid #3498db;">';
        $html .= '<br>';

        // Section recap des regimes alimentaires
        $html .= self::construireRecapitulatifRegimes($participants);
        $html .= '<hr style="border: 1px solid #3498db; margin: 20px 0;">';
        $html .= '<br>';

        // Section 1: Restrictions alimentaires
        if (!empty($categories['restrictions'])) {
            $html .= '<h2 style="color: #e74c3c; background-color: #fadbd8; padding: 8px; border-left: 4px solid #e74c3c;">Restrictions alimentaires</h2>';
            $html .= '<br>';

            foreach ($categories['restrictions'] as $restriction => $participants) {
                $html .= '<h3 style="color: #c0392b; margin-top: 15px;">' . htmlspecialchars($restriction) . ' (' . count($participants) . ' participant' . (count($participants) > 1 ? 's' : '') . ')</h3>';
                $html .= self::construireTableauParticipantsAsso($participants);
                $html .= '<br>';
            }
        }

        // Section 2: Préférences alimentaires
        if (!empty($categories['preferences'])) {
            $html .= '<h2 style="color: #27ae60; background-color: #d5f4e6; padding: 8px; border-left: 4px solid #27ae60;">Préférences alimentaires</h2>';
            $html .= '<br>';

            foreach ($categories['preferences'] as $preference => $participants) {
                $html .= '<h3 style="color: #229954; margin-top: 15px;">' . htmlspecialchars($preference) . ' (' . count($participants) . ' participant' . (count($participants) > 1 ? 's' : '') . ')</h3>';
                $html .= self::construireTableauParticipantsAsso($participants);
                $html .= '<br>';
            }
        }

        // Section 3: Participants avec régime alimentaire déclaré
        $participantsAvecRegime = array_filter($participants, function($p) {
            return !empty($p['regime_alimentaire']);
        });

        if (!empty($participantsAvecRegime)) {
            $html .= '<h2 style="color: #9b59b6; background-color: #f5eef8; padding: 8px; border-left: 4px solid #9b59b6;">Régimes alimentaires déclarés</h2>';
            $html .= '<br>';
            $html .= '<p><strong>' . count($participantsAvecRegime) . ' participant' . (count($participantsAvecRegime) > 1 ? 's' : '') . '</strong> avec un régime alimentaire déclaré</p>';
            $html .= self::construireTableauParticipantsAvecRegimeAsso($participantsAvecRegime);
            $html .= '<br>';
        }

        // Section 4: Sans contrainte alimentaire
        if (!empty($categories['sans_regime'])) {
            $html .= '<h2 style="color: #7f8c8d; background-color: #ecf0f1; padding: 8px; border-left: 4px solid #7f8c8d;">Sans contrainte alimentaire</h2>';
            $html .= '<br>';
            $html .= '<p><strong>' . count($categories['sans_regime']) . ' participant' . (count($categories['sans_regime']) > 1 ? 's' : '') . '</strong> sans restriction ni préférence</p>';
            $html .= self::construireTableauParticipantsAsso($categories['sans_regime']);
            $html .= '<br>';
        }

        // Section 5: Commentaires alimentaires spécifiques
        $participantsAvecCommentaires = array_filter($participants, function($p) {
            return !empty($p['commentaire_alimentaire']);
        });

        if (!empty($participantsAvecCommentaires)) {
            $html .= '<h2 style="color: #f39c12; background-color: #fef5e7; padding: 8px; border-left: 4px solid #f39c12;">Commentaires alimentaires spécifiques</h2>';
            $html .= '<br>';
            $html .= self::construireTableauCommentaires($participantsAvecCommentaires);
            $html .= '<br>';
        }

        return $html;
    }

    // tableau participants asso
    private static function construireTableauParticipantsAsso($participants)
    {
        $html = '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead>';
        $html .= '<tr style="background-color: #3498db; color: white;">';
        $html .= '<th style="width: 20%;"><strong>Nom</strong></th>';
        $html .= '<th style="width: 20%;"><strong>Prénom</strong></th>';
        $html .= '<th style="width: 25%;"><strong>Email</strong></th>';
        $html .= '<th style="width: 18%;"><strong>Téléphone</strong></th>';
        $html .= '<th style="width: 17%;"><strong>Accompagnateurs</strong></th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach ($participants as $participant) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($participant['nom']) . '</td>';
            $html .= '<td>' . htmlspecialchars($participant['prenom']) . '</td>';
            $html .= '<td style="font-size: 9px;">' . htmlspecialchars($participant['mail']) . '</td>';
            $html .= '<td>' . htmlspecialchars($participant['telephone'] ?? '-') . '</td>';
            $nb_invites = $participant['nb_invites'] ?? 0;
            $html .= '<td style="text-align: center;">' . $nb_invites . ' accompagnateur' . ($nb_invites > 1 ? 's' : '') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';

        return $html;
    }

    // verifie si un template personnalise existe
    private static function getCustomTemplatePath(): ?string
    {
        $templateDir = __DIR__ . '/../../uploads/templates/';
        if (!is_dir($templateDir)) {
            return null;
        }

        $files = glob($templateDir . 'adhesion_template_*.pdf');
        if (!empty($files)) {
            return $files[0];
        }

        return null;
    }

    // genere PDF formulaire adhesion vierge (ou retourne le template personnalise)
    public static function genererFormulaireAdhesion()
    {
        // nettoyer buffers sortie
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Verifier si un template personnalise existe
        $customTemplate = self::getCustomTemplatePath();
        if ($customTemplate && file_exists($customTemplate)) {
            // Envoyer le PDF personnalise
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="Formulaire_Adhesion_KASTASSO.pdf"');
            header('Content-Length: ' . filesize($customTemplate));
            readfile($customTemplate);
            exit;
        }

        // Sinon, generer le PDF par defaut
        self::genererFormulaireAdhesionDefault('D');
    }

    // genere PDF formulaire adhesion pour preview (inline au lieu de download)
    public static function genererFormulaireAdhesionPreview()
    {
        // nettoyer buffers sortie
        if (ob_get_level()) {
            ob_end_clean();
        }

        self::genererFormulaireAdhesionDefault('I');
    }

    // methode interne pour generer le PDF par defaut
    private static function genererFormulaireAdhesionDefault($outputMode = 'D')
    {
        // creer PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // config document
        $pdf->SetCreator('KAST\'ASSO');
        $pdf->SetAuthor('KAST\'ASSO');
        $pdf->SetTitle('Formulaire d\'Adhésion KAST\'ASSO');
        $pdf->SetSubject('Adhésion Association');

        // supprimer header footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // marges
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        // ajouter page
        $pdf->AddPage();

        // police
        $pdf->SetFont('helvetica', '', 11);

        // contenu HTML
        $html = self::construireHTMLFormulaireAdhesion();

        // ecrire HTML dans PDF
        $pdf->writeHTML($html, true, false, true, false, '');

        // telecharger ou afficher PDF
        $pdf->Output('Formulaire_Adhesion_KASTASSO.pdf', $outputMode);
        exit;
    }

    // construit le recapitulatif des regimes alimentaires
    private static function construireRecapitulatifRegimes($participants)
    {
        $html = '';

        // comptage des regimes alimentaires
        $regimesComptage = [];
        $participantsSansRegime = 0;

        foreach ($participants as $participant) {
            if (!empty($participant['regime_alimentaire'])) {
                $regime = $participant['regime_alimentaire'];
                if (!isset($regimesComptage[$regime])) {
                    $regimesComptage[$regime] = 0;
                }
                $regimesComptage[$regime]++;
            } else {
                $participantsSansRegime++;
            }
        }

        // tri alphabetique des regimes
        ksort($regimesComptage);

        // affichage du recapitulatif
        $html .= '<h2 style="color: #16a085; background-color: #d5f4e6; padding: 8px; border-left: 4px solid #16a085;">Récapitulatif des régimes alimentaires</h2>';
        $html .= '<br>';

        if (empty($regimesComptage) && $participantsSansRegime === count($participants)) {
            $html .= '<p style="text-align: center; color: #7f8c8d;">Aucun régime alimentaire déclaré pour cet événement.</p>';
        } else {
            $html .= '<table align="center" border="1" cellpadding="8" cellspacing="0" width="50%">';
            $html .= '<tr bgcolor="#16a085" style="color: white;">';
            $html .= '<th align="left" width="60%"><strong>Régime alimentaire</strong></th>';
            $html .= '<th align="center" width="40%"><strong>Nombre de personnes</strong></th>';
            $html .= '</tr>';

            foreach ($regimesComptage as $regime => $nombre) {
                $html .= '<tr>';
                $html .= '<td align="left">' . htmlspecialchars($regime) . '</td>';
                $html .= '<td align="center"><strong>' . $nombre . '</strong></td>';
                $html .= '</tr>';
            }

            if ($participantsSansRegime > 0) {
                $html .= '<tr bgcolor="#f5f5f5">';
                $html .= '<td align="left"><em style="color: #666;">Aucun régime déclaré</em></td>';
                $html .= '<td align="center"><strong>' . $participantsSansRegime . '</strong></td>';
                $html .= '</tr>';
            }

            $html .= '</table>';
        }

        $html .= '<br>';

        return $html;
    }

    // construit HTML formulaire adhesion
    private static function construireHTMLFormulaireAdhesion()
    {
        $html = '<h1 style="text-align: center; color: #0288d1;">KAST\'ASSO</h1>';
        $html .= '<div style="text-align: center; color: #ff9800; font-size: 16px;">Formulaire d\'Adhésion à l\'Association</div>';
        $html .= '<div style="text-align: center; font-size: 12px; margin-bottom: 20px;">Année 2026</div>';
        $html .= '<hr style="border: 1px solid #0288d1;">';

        $html .= '<h2 style="color: #0288d1; font-size: 16px; margin-top: 20px;">Informations du demandeur</h2>';

        $html .= '<div style="margin: 15px 0;">';
        $html .= '<span style="font-weight: bold; display: inline-block; min-width: 180px;">Nom :</span>';
        $html .= '<span style="border-bottom: 1px solid #333; display: inline-block; width: 300px;">&nbsp;</span>';
        $html .= '</div>';

        $html .= '<div style="margin: 15px 0;">';
        $html .= '<span style="font-weight: bold; display: inline-block; min-width: 180px;">Prénom :</span>';
        $html .= '<span style="border-bottom: 1px solid #333; display: inline-block; width: 300px;">&nbsp;</span>';
        $html .= '</div>';

        $html .= '<div style="margin: 15px 0;">';
        $html .= '<span style="font-weight: bold; display: inline-block; min-width: 180px;">Date de naissance :</span>';
        $html .= '<span style="border-bottom: 1px solid #333; display: inline-block; width: 300px;">&nbsp;</span>';
        $html .= '</div>';

        $html .= '<div style="margin: 15px 0;">';
        $html .= '<span style="font-weight: bold; display: inline-block; min-width: 180px;">Adresse complète :</span>';
        $html .= '<span style="border-bottom: 1px solid #333; display: inline-block; width: 300px;">&nbsp;</span>';
        $html .= '</div>';

        $html .= '<div style="margin: 15px 0;">';
        $html .= '<span style="font-weight: bold; display: inline-block; min-width: 180px;">Code postal, Ville :</span>';
        $html .= '<span style="border-bottom: 1px solid #333; display: inline-block; width: 300px;">&nbsp;</span>';
        $html .= '</div>';

        $html .= '<div style="margin: 15px 0;">';
        $html .= '<span style="font-weight: bold; display: inline-block; min-width: 180px;">Téléphone :</span>';
        $html .= '<span style="border-bottom: 1px solid #333; display: inline-block; width: 300px;">&nbsp;</span>';
        $html .= '</div>';

        $html .= '<div style="margin: 15px 0;">';
        $html .= '<span style="font-weight: bold; display: inline-block; min-width: 180px;">Email :</span>';
        $html .= '<span style="border-bottom: 1px solid #333; display: inline-block; width: 300px;">&nbsp;</span>';
        $html .= '</div>';

        $html .= '<div style="background-color: #f5f5f5; padding: 15px; border-left: 4px solid #0288d1; margin: 20px 0;">';
        $html .= '<p style="margin-top: 0;"><strong>DÉCLARATION</strong></p>';
        $html .= '<p>Je soussigné(e), <strong>_______________________________</strong>, ';
        $html .= 'résidant à <strong>_______________________________</strong>, ';
        $html .= 'déclare vouloir adhérer à l\'association <strong>KAST\'ASSO</strong> et souscrire ';
        $html .= 'à l\'assurance couvrant les activités sportives proposées par l\'association pour l\'année 2026.</p>';
        $html .= '<p>Je reconnais avoir pris connaissance du règlement intérieur de l\'association et m\'engage ';
        $html .= 'à le respecter. Je certifie sur l\'honneur l\'exactitude des informations fournies dans ce formulaire.</p>';
        $html .= '<p style="margin-bottom: 0;"><strong>Couverture assurance :</strong> En tant qu\'adhérent, je serai couvert par l\'assurance ';
        $html .= 'de l\'association pour tous les événements sportifs et associatifs organisés par KAST\'ASSO.</p>';
        $html .= '</div>';

        $html .= '<table style="width: 100%; margin-top: 30px;">';
        $html .= '<tr>';
        $html .= '<td style="width: 50%;">';
        $html .= '<div style="margin: 15px 0;">';
        $html .= '<span style="font-weight: bold;">Fait à :</span>';
        $html .= '<span style="border-bottom: 1px solid #333; display: inline-block; width: 150px;">&nbsp;</span>';
        $html .= '</div>';
        $html .= '</td>';
        $html .= '<td style="width: 50%;">';
        $html .= '<div style="margin: 15px 0;">';
        $html .= '<span style="font-weight: bold;">Le :</span>';
        $html .= '<span style="border-bottom: 1px solid #333; display: inline-block; width: 150px;">&nbsp;</span>';
        $html .= '</div>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="width: 50%;">';
        $html .= '<div style="border-top: 1px solid #333; margin-top: 50px; padding-top: 5px; text-align: center; font-size: 10px;">Signature du demandeur</div>';
        $html .= '</td>';
        $html .= '<td style="width: 50%;">';
        $html .= '<div style="border-top: 1px solid #333; margin-top: 50px; padding-top: 5px; text-align: center; font-size: 10px;">Précédée de la mention "Lu et approuvé"</div>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        $html .= '<div style="margin-top: 40px; text-align: center; font-size: 9px; color: #666; border-top: 1px solid #ddd; padding-top: 15px;">';
        $html .= '<p><strong>KAST\'ASSO</strong> - Association Sportive</p>';
        $html .= '<p>Contact : contact@kastasso.fr | Téléphone : 01 23 45 67 89</p>';
        $html .= '<p>Document à retourner complété et signé via le formulaire d\'inscription en ligne</p>';
        $html .= '</div>';

        return $html;
    }
}
