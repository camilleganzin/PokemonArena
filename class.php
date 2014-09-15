<?php

class Pokemon {

	public $nom;
	public $famille;
	public $type;
	public $niveau = 1;
	public $pv;
	public $pvmax;
	public $defense;
	public $force;
	public $initiative;
	public $precision;
	public $dernier_coup = null;
	public $affinites = array('electrik' => array('electrik' => 1, 'eau' =>							2, 'feu' => 1, 'plante' => 1, 'roche' => 0						),
                              'eau' => array('electrik' => 0.5, 'eau' => 1, 'feu' => 2, 'plante' => 0.5, 'roche' => 2),
                              'feu' => array('electrik' => 1, 'eau' => 0.5, 'feu' => 1, 'plante' => 2, 'roche' => 0.5),
                              'plante' => array('electrik' => 1, 'eau' => 2.5, 'feu' => 0.5, 'plante' => 0.5, 'roche' => 2),
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
            $log[] = '… mais échoue !';
        }
        return $log;
    }

    public function defense($degats) {
        $log = array();
        // on applique la défense
        //echo 'on attaque avec '.$degats.' points (def '.($this->defense/100).').'.PHP_EOL;
        $pv_perdus = round($degats * (1-($this->defense / 100)));
        //echo 'on subit '.$pv_perdus.' points.'.PHP_EOL;
        $log[] = $this->nom.' subit '.$pv_perdus.' points de dégats (pour '.$degats.' donnés).';
        $this->pv -= $pv_perdus;
        if($this->pv < 0) $this->pv = 0;
        return $log;
    }
}

class pikachu extends Pokemon {

	public $famille = 'Pikachu'; //famille
	public $type = 'electrik'; //type
	public $pv = '350';
	public $defense = '40';//defense
	public $force = '55'; //force
	public $initiative = '90';//initiative
	public $precision = '70'; //precision

}

class carapuce extends Pokemon {

	public $famille = 'Carapuce'; //famille
	public $type = 'eau'; //type
	public $pv = '440';
	public $defense = '55';//defense
	public $force = '48'; //force
	public $initiative = '43';//initiative
	public $precision = '80'; //precision
}

class salameche extends Pokemon {

	public $famille = 'Salameche'; //famille
	public $type = 'feu'; //type
	public $pv = '390';
	public $defense = '43';//defense
	public $force = '52'; //force
	public $initiative = '65';//initiative
	public $precision = '50'; //precision

}

class Combat {

	const STATUT_DEBUT = 0;
	const STATUT_ENCOURS = 1;
	const STATUT_FINI = 2;

	public $adversaires = array();
	public $statut;
	public $log = array();
	public $precedents_logs = array();

	public function __construct(Pokemon $pokemon1, Pokemon $pokemon2) {
		$this->statut = self::STATUT_DEBUT;
		$this->combat->adversaires[] = $pokemon1;
		$this->combat->adversaires[] = $pokemon2; 

	}
		
	public function round() {
		if($this->statut == self::STATUT_FIN) {
			throw new Exception('Le combat est terminé');
		}
		$this->statut = self::STATUT_ENCOURS;
        $this->precedents_logs[] = $this->log;
        $pokemon1 = $this->adversaires[0];
        $pokemon2 = $this->adversaires[1];
        
        if($pokemon1->initiative < $pokemon2->initiative) {
            $this->log = array_merge($this->log, $pokemon2->attaque($pokemon1));
            if($pokemon1->pv > 0) {
                $this->log = array_merge($this->log, $pokemon1->attaque($pokemon2, true));
            }
        } else {
            $this->log = array_merge($this->log, $pokemon1->attaque($pokemon2));
            if($pokemon2->pv > 0) {
                $this->log = array_merge($this->log, $pokemon2->attaque($pokemon1, true));
            }
        }
        if($pokemon1->pv <= 0 || $pokemon2->pv <= 0) {
            $this->statut = self::STATUT_FIN;
        }
	}
}

