export interface Utilisateurs {
  id: number;
  nomComplet: string;
  telephone: string;
  code_pin: string;
  email: string;
  etat: number;
  id_role: number;
  password: string;
  solde: number;
}

export interface DepotsRetrait {
  id: number;
  utilisateur_id: number;
  agent_id: number;
  type: string;
  montant: string;
  date: Date;
  status: string;
}

export interface Transaction {
  id: number;
  expediteur_id: number;
  destinataire_id: number;
  montant: number;
  date: Date;
  status: string;
}
