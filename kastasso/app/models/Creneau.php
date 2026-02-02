<?php
require_once __DIR__ . '/BaseDeDonnees.php';

class Creneau {

    public static function findByEvent($idEventSport) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            SELECT c.*, COUNT(ab.Id_Membre) as nb_inscrits
            FROM creneau_event c
            LEFT JOIN aide_benevole ab ON c.Id_creneau = ab.Id_creneau
            WHERE c.Id_Event_sportif = ?
            GROUP BY c.Id_creneau
            ORDER BY c.Date_creneau, c.Heure_Debut
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idEventSport]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById($id) {
        $pdo = BaseDeDonnees::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM creneau_event WHERE Id_creneau = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            INSERT INTO creneau_event (Type, Commentaire, Date_creneau, Heure_Debut, Heure_Fin, Id_Event_sportif)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([
            $data['type'],
            $data['commentaire'] ?? null,
            $data['date_creneau'],
            $data['heure_debut'],
            $data['heure_fin'],
            $data['id_event_sportif']
        ]);

        if ($ok) {
            return $pdo->lastInsertId();
        }
        return false;
    }

    public static function createMany(int $eventId, array $creneaux): bool {
        if (empty($creneaux)) {
            return false;
        }

        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            INSERT INTO creneau_event (Type, Commentaire, Date_creneau, Heure_Debut, Heure_Fin, Id_Event_sportif)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $stmt = $pdo->prepare($sql);
        $sqlPoste = "INSERT INTO creneau_poste (Id_creneau, Id_Poste) VALUES (?, ?)";
        $stmtPoste = $pdo->prepare($sqlPoste);

        foreach ($creneaux as $c) {
            $ok = $stmt->execute([
                $c['type'],
                $c['commentaire'] ?? null,
                $c['date_creneau'],
                $c['heure_debut'],
                $c['heure_fin'],
                $eventId
            ]);

            if (!$ok) {
                return false;
            }

            $idCreneau = $pdo->lastInsertId();

            if (!empty($c['postes']) && is_array($c['postes'])) {
                foreach ($c['postes'] as $idPoste) {
                    $okPoste = $stmtPoste->execute([$idCreneau, $idPoste]);
                    if (!$okPoste) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public static function update($id, $data) {
        $pdo = BaseDeDonnees::getConnexion();
        $sql = "
            UPDATE creneau_event
            SET Type = ?, Commentaire = ?, Date_creneau = ?, Heure_Debut = ?, Heure_Fin = ?
            WHERE Id_creneau = ?
        ";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $data['type'],
            $data['commentaire'] ?? null,
            $data['date_creneau'],
            $data['heure_debut'],
            $data['heure_fin'],
            $id
        ]);
    }

    public static function delete($id) {
        $pdo = BaseDeDonnees::getConnexion();
        $stmt = $pdo->prepare("DELETE FROM creneau_event WHERE Id_creneau = ?");
        return $stmt->execute([$id]);
    }

    public static function countInscrits($idCreneau) {
        $pdo = BaseDeDonnees::getConnexion();
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM aide_benevole WHERE Id_creneau = ?");
        $stmt->execute([$idCreneau]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
