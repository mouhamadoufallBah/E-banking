<?php

namespace Moohamad\EBankingApi\Model;

require_once("./src/model/BaseModel.php");

class DepotsRetrait extends BaseModel{
    public $table = "depots_retraits";
    public $connexion = null;

    public int $id;
    public int $utilisateur_id;
    public int $agent_id;
    public string $type;
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

    public function getAlldepotRetraitByIdAgent(int $agent_id){
        $sql = "SELECT * FROM $this->table WHERE agent_id = :agent_id";

        $req = $this->connexion->prepare($sql);
        $req->execute([':agent_id' => $agent_id]);

        return $req;
    }
    
    public function canceldepotRetrait(int $idDepotRetrait){
        $sql = "UPDATE $this->table SET status = 0 WHERE id=:idDepotRetrait";

        $req =  $this->connexion->prepare($sql);

        $result = $req->execute([
            "idDepotRetrait" => $idDepotRetrait
        ]);

        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}