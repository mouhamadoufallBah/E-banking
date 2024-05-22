<?php

namespace Moohamad\EBankingApi\Model;

require_once("./src/model/BaseModel.php");

class Transaction extends BaseModel{
    public $table = "transactions";
    public $connexion = null;

    public int $id;
    public int $expediteur_id;
    public int $destinataire_id;
    public int $montant;
    public string $date;
    public string $status;

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
        $sql = "SELECT * FROM $this->table where id = :id";

        $req = $this->connexion->prepare($sql);
        $req->execute([
            "id" => $id
        ]);

        return $req;
    }
    
    
    public function getAllTransactionByIdUser(int $idUser){
        $sql = "SELECT * FROM $this->table WHERE expediteur_id = :idUser OR destinataire_id = :idUser";

        $req = $this->connexion->prepare($sql);
        $req->execute([':idUser' => $idUser]);

        return $req;
    }
    
    public function cancelTransaction(int $idTransaction){
        $sql = "UPDATE $this->table SET status = 0 WHERE id=:idTransaction";

        $req =  $this->connexion->prepare($sql);

        $result = $req->execute([
            "idTransaction" => $idTransaction
        ]);

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

}