<?php

namespace Moohamad\EBankingApi\Model;

require_once("./src/model/BaseModel.php");

class Utilisateurs extends BaseModel
{
    public $table = "utilisateurs";
    public $connexion = null;

    public int $id;
    public string $nomComplet;
    public string $telephone;
    public string $email;
    public string $password;
    public string $code_pin;
    public int $solde;
    public int $id_role;
    public int $etat;

    public function __construct($db)
    {
        if ($this->connexion == null) {
            $this->connexion = $db;
            parent::__construct($this->table, $this->connexion);
        }
    }

    public function getAll()
    {
        $sql = "SELECT * FROM $this->table";

        $req = $this->connexion->query($sql);

        return $req;
    }

    public function get($id)
    {
        $sql = "SELECT 
                u.*, r.nom,
                (SELECT SUM(t1.montant) 
                FROM transactions t1 
                WHERE t1.expediteur_id = u.id) AS total_sortie,
                (SELECT SUM(t2.montant) 
                FROM transactions t2 
                WHERE t2.destinataire_id = u.id) AS total_entre
                FROM utilisateurs u
                left join roles r on r.id = u.id_role
                WHERE u.id = :id;";

        $req = $this->connexion->prepare($sql);
        $req->execute([':id' => $id]);

        return $req;
    }

    public function update()
    {
        $sql = "UPDATE $this->table SET nomComplet=:nomComplet, email=:email, password=:password, id_role=:id_role, etat=:etat WHERE id=:id";

        $req =  $this->connexion->prepare($sql);

        $result = $req->execute([
            ":nomComplet" => $this->nomComplet,
            ":email" => $this->email,
            ":password" => password_hash($this->password, PASSWORD_BCRYPT),
            ":id_role" => $this->id_role,
            ":etat" => $this->etat,
            "id" => $this->id
        ]);

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function updateSolde(int $id, int $solde)
    {
        $sql = "UPDATE $this->table SET solde=:solde WHERE id=:id";

        $req =  $this->connexion->prepare($sql);

        $result = $req->execute([
            ":solde" => $solde,
            "id" => $id
        ]);

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function delete()
    {
        $sql = "DELETE FROM $this->table WHERE id= :id";

        $req = $this->connexion->prepare($sql);

        $result = $req->execute(array(":id" => $this->id));

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function login(string $telephone, string $password)
    {
        $sql = "SELECT u.*, r.nom FROM $this->table u
                left join roles r on r.id = u.id_role
                where telephone = :telephone";

        $stmt = $this->connexion->prepare($sql);
        $stmt->execute(['telephone' => $telephone]);
        $user = $stmt->fetch();
        if (!$user) {
            return false;
        }

        // Vérification du mot de passe
        if (password_verify($password, $user['password'])) {
            return $user;
        } else {
            return false;
        }
    }
}
