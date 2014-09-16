<?php

class Pokemon {
    public $nom;
    public $famille;
    public $type;
    public $pv;
    public $pvmax;
    public $force;
    public $defense;
    public $initiative;
    public $precision;
    public $niveau = 1;
    public $dernier_coup = null;

    public $affinites = array('electrik' => array('electrik' => 1, 'eau' => 2, 'feu' => 1, 'plante' => 1, 'roche' => 0),
                              'eau' => array('electrik' => 0.5, 'eau' => 0.5, 'feu' => 2, 'plante' => 0.5, 'roche' => 2),
                              'feu' => array('electrik' => 1, 'eau' => 0.5, 'feu' => 0.5, 'plante' => 2, 'roche' => 0.5),
                              'plante' => array('electrik' => 1, 'eau' => 2, 'feu' => 0,5, 'plante' => 0.5, 'roche' => 2),
                              'roche' => array('electrik' => 2, 'eau' => 0.5, 'feu' => 1, 'plante' => 2, 'roche' => 1)
                             );

    const COUP_TOUCHE = 0;
    const COUP_RATE = 1;
    const COUP_CRIT = 2;
    const COUP_FORT = 3;
    const COUP_FAIBLE = 4;
    const COUP_CRIT_FORT = 5;
    const COUP_CRIT_FAIBLE = 6;

    public function __construct($nom) {
        $this->nom = $nom;
        $this->pvmax = $this->pv;
        $this->affinites = $this->affinites[$this->type];
    }

    public function attaque($cible, $contre = false) {
        $log = array();
        // on vérifie si le coup touche
        $log[] = $this->nom.' '.($contre ? 'contre-' : '').'attaque '.$cible->nom.'.';
        if(mt_rand(1, 100) <= $this->precision) { // on touche !
            $this->dernier_coup = self::COUP_TOUCHE;
            // on calcule les dégâts
            $degats = $this->force;
            // gestion des affinités
            $degats *= $this->affinites[$cible->type];
            if($this->affinites[$cible->type] > 1) {
                $this->dernier_coup = self::COUP_FORT;
                $log[] = 'C’est super efficace !';
            } elseif($this->affinites[$cible->type] < 1) {
                $this->dernier_coup = self::COUP_FAIBLE;
                $log[] = 'Ce n’est pas très efficace…';
            }
            $log = array_merge($log, $cible->defense($degats));
            if($cible->pv <= 0) {
                $log[] = $cible->nom.' est KO !';
            }
        } else { // on a raté
            $this->dernier_coup = self::COUP_RATE;
            $log[] = '… mais échoue.';
        }
        return $log;
    }

    public function defense($degats) {
        $log = array();
        // on applique la défense
        $pv_perdus = round($degats * (1-($this->defense / 100)));
        $log[] = $this->nom.' subit '.$pv_perdus.' points de dégats (pour '.$degats.' donnés).';
        $this->pv -= $pv_perdus;
        if($this->pv < 0) $this->pv = 0;
        return $log;
    }
}

class Pikachu extends Pokemon {
    public $famille = 'Pikachu';
    public $type = 'electrik';
    public $pv = 350;
    public $force = 55;
    public $defense = 40;
    public $initiative = 90;
    public $precision = 70;
}

class Carapuce extends Pokemon {
    public $famille = 'Carapuce';
    public $type = 'eau';
    public $pv = 440;
    public $force = 48;
    public $defense = 55;
    public $initiative = 43;
    public $precision = 80;
}

class Salameche extends Pokemon {
    public $famille = 'Salamèche';
    public $type = 'feu';
    public $pv = 390;
    public $force = 52;
    public $defense = 43;
    public $initiative = 65;
    public $precision = 50;
}

class Combat {

    const STATUT_DEBUT = 0;
    const STATUT_ENCOURS = 1;
    const STATUT_FIN = 2;

    public $adversaires = array();
    public $statut;
    public $log = array();
    public $precedents_logs = array();

    public function __construct(Pokemon $pkmn1, Pokemon $pkmn2) {
        $this->statut = self::STATUT_DEBUT;
        $this->adversaires[] = $pkmn1;
        $this->adversaires[] = $pkmn2;
    }

    public function round() {
        if($this->statut == self::STATUT_FIN) {
            throw new Exception('Le combat est terminé');
        }
        $this->statut = self::STATUT_ENCOURS;
        $this->precedents_logs[] = $this->log;
        $pkmn1 = $this->adversaires[0];
        $pkmn2 = $this->adversaires[1];
        if($pkmn1->initiative < $pkmn2->initiative) {
            $this->log = array_merge($this->log, $pkmn2->attaque($pkmn1));
            if($pkmn1->pv > 0) {
                $this->log = array_merge($this->log, $pkmn1->attaque($pkmn2, true));
            }
        } else {
            $this->log = array_merge($this->log, $pkmn1->attaque($pkmn2));
            if($pkmn2->pv > 0) {
                $this->log = array_merge($this->log, $pkmn2->attaque($pkmn1, true));
            }
        }
        if($pkmn1->pv <= 0 || $pkmn2->pv <= 0) {
            $this->statut = self::STATUT_FIN;
        }
    }

}
