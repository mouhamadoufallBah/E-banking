<?php

namespace Moohamad\EBankingApi\Model;

class BaseModel
{
    protected $table;
    protected $connexion = null;

    public function __construct($table, $connexion)
    {
        $this->table = $table;
        $this->connexion = $connexion;
    }

    public function dynamicInsert()
    {
        // Récupération des propriétés non nulles de l'objet
        $fields = [];
        $placeholders = [];
        $values = [];

        foreach (get_object_vars($this) as $property => $value) {
            if ($value !== null && $property != 'table' && $property != 'connexion') {
                $fields[] = $property;
                $placeholders[] = ':' . $property;
                if ($property == 'password') {
                    $values[':' . $property] = password_hash($value, PASSWORD_BCRYPT);
                } else {
                    $values[':' . $property] = $value;
                }
            }
        }

        // Construction de la requête SQL dynamique
        $sql = "INSERT INTO $this->table (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";

        // Préparation et exécution de la requête
        $req = $this->connexion->prepare($sql);
        $result = $req->execute($values);

        // Retour du résultat
        return $result ? true : false;
    }

    public function dynamicUpdate($id)
    {
        // Récupération des propriétés non nulles de l'objet
        $fields = [];
        $values = [];

        foreach (get_object_vars($this) as $property => $value) {
            if ($value !== null && $property != 'table' && $property != 'connexion' && $property != 'id') {
                // On vérifie si la propriété a une valeur définie
                if (isset($this->{$property})) {
                    $fields[] = $property . '= :' . $property;
                    if ($property == 'password') {
                        $values[':' . $property] = password_hash($this->{$property}, PASSWORD_BCRYPT);
                    } else {
                        $values[':' . $property] = $this->{$property};
                    }
                }
            }
        }

        // Construction de la requête SQL dynamique
        $sql = "UPDATE $this->table SET " . implode(', ', $fields) . " WHERE id = :id";

        // Ajout de l'ID à la liste des valeurs
        $values[':id'] = $id;

        // Préparation et exécution de la requête
        $req = $this->connexion->prepare($sql);
        $result = $req->execute($values);

        // Retour du résultat
        return $result ? true : false;
    }
}
