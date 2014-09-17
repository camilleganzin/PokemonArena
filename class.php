<?php

class PokemonException extends Exception {}
class PokemonInvalidBattleException extends PokemonException {}
class PokemonBattleEndException extends PokemonException {}
class PokemonUnfinishedBattleException extends PokemonException {}

class Database {
    private static $instance;
    const PREFIX = 'rohajobu_';

    private function __construct() {
        }

    static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new Predis\Client();
        }
            return self::$instance;
    }
}

class Pokemon {
    public $id;

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
    public $combattant = false;
    protected $is_saved = true;

    public $affinites = array('Electrik' => array('Electrik' => 1, 'Eau' => 2, 'Feu' => 1, 'Plante' => 1, 'Roche' => 0),
                              'Eau' => array('Electrik' => 0.5, 'Eau' => 0.5, 'Feu' => 2, 'Plante' => 0.5, 'Roche' => 2),
                              'Feu' => array('Electrik' => 1, 'Eau' => 0.5, 'Feu' => 0.5, 'Plante' => 2, 'Roche' => 0.5),
                              'Plante' => array('Electrik' => 1, 'Eau' => 2, 'Feu' => 0,5, 'Plante' => 0.5, 'Roche' => 2),
                              'Roche' => array('Electrik' => 2, 'Eau' => 0.5, 'Feu' => 1, 'Plante' => 2, 'Roche' => 1)
                             );

    const COUP_TOUCHE = 0;
    const COUP_RATE = 1;
    const COUP_CRIT = 2;
    const COUP_FORT = 3;
    const COUP_FAIBLE = 4;
    const COUP_CRIT_FORT = 5;
    const COUP_CRIT_FAIBLE = 6;

    public function __construct($nom) {
        $this->id = self::getUniqueId();
        $this->nom = $nom;
        $this->pvmax = $this->pv;
        $this->affinites = $this->affinites[$this->type];
        $this->is_saved = false;
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

    static function getAll() {
        $client = Database::getInstance();
        $ids = $client->zrange(Database::PREFIX.'pokelist', 0, -1);
        $pokemons = array();
        foreach($ids as $id) {
            $pokemons[$id] = self::get($id);
        }
        return $pokemons;
    }

    /**
     * complexité : O(n)
     * pour n valant le nombre total de pokémons
     */
    static function getFree() {
        $pokemons = self::getAll();
        $free = array();
        foreach($pokemons as $pkmn) {
            if(!$pkmn->combattant) {
                $free[$pkmn->id] = $pkmn;
            }
        }
        return $free;
    }

    static function get($id) {
        $client = Database::getInstance();
        $data = $client->hget(Database::PREFIX.'pokemons', $id);
        if($data) {
            $obj = unserialize($data);
            return $obj;
        } else {
            return false;
        }
    }
    
    public function save() {
        $data = serialize($this);
        $client = Database::getInstance();
        $client->hset(Database::PREFIX.'pokemons', $this->id, $data);
        $client->zadd(Database::PREFIX.'pokelist', $this->id, $this->id);
        if(!$this->is_saved) {
            $client->set(Database::PREFIX.'pokeid', $this->id);
            $this->is_saved = true;
        }
    }

    public function delete() {
        $client = Database::getInstance();
        $client->hdel(Database::PREFIX.'pokemons', $this->id);
        $client->zrem(Database::PREFIX.'pokelist', $this->id);
    }
    
    public function getUniqueId() {
        $client = Database::getInstance();
        $max_id = $client->zrevrange(Database::PREFIX.'pokelist', 0, 0);
        if(!count($max_id)) {
            return 0;
        }
        return $max_id[0]+1;
        $max_id = $client->get(Database::PREFIX.'pokeid');
        if($max_id === null) {
            return 0;
        } else {
            return $max_id + 1;
        }
    }

}

class Pikachu extends Pokemon {
    public $famille = 'Pikachu';
    public $type = 'Electrik';
    public $pv = 350;
    public $force = 55;
    public $defense = 40;
    public $initiative = 90;
    public $precision = 70;
}

class Carapuce extends Pokemon {
    public $famille = 'Carapuce';
    public $type = 'Eau';
    public $pv = 440;
    public $force = 48;
    public $defense = 55;
    public $initiative = 43;
    public $precision = 80;
}

class Salameche extends Pokemon {
    public $famille = 'Salamèche';
    public $type = 'Feu';
    public $pv = 390;
    public $force = 52;
    public $defense = 43;
    public $initiative = 65;
    public $precision = 50;
}

class Combat {

    public $id;

    const STATUT_DEBUT = 0;
    const STATUT_ENCOURS = 1;
    const STATUT_FIN = 2;

    public $adversaires = array();
    public $statut;
    public $log = array();
    public $precedents_logs = array();
    protected $is_saved = true;

    public function __construct(Pokemon $pkmn1, Pokemon $pkmn2) {
        $this->id = $this->getUniqueId();
        if($pkmn1->combattant || $pkmn2->combattant) {
            throw new PokemonInvalidBattleException('Un pokémon en combat ne peut pas en combattre un autre.');
        }
        $this->statut = self::STATUT_DEBUT;
        $this->adversaires[] = $pkmn1;
        $this->adversaires[] = $pkmn2;
        $pkmn1->combattant = true;
        $pkmn2->combattant = true;
        $pkmn1->save();
        $pkmn2->save();
        $this->is_saved = false;
        $this->save();
    }

    public function round() {
        if($this->statut == self::STATUT_FIN) {
            throw new Exception('Le combat est terminé');
        }
        $this->statut = self::STATUT_ENCOURS;
        $this->precedents_logs[] = $this->log;
        $this->log = array();
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
            $pkmn1->combattant = false;
            $pkmn2->combattant = false;
            $this->save();
        }
        $pkmn1->save();
        $pkmn2->save();
    }

    public function getUniqueId() {
        $client = Database::getInstance();
        $max_id = $client->get(Database::PREFIX.'combatid');
        if($max_id === null) {
            return 0;
        } else {
            return $max_id + 1;
        }
    }

    static function getAll() {
        $client = Database::getInstance();
        $ids = $client->zrange(Database::PREFIX.'combatlist', 0, -1);
        $combats = array();
        foreach($ids as $id) {
            $combats[$id] = self::get($id);
        }
        return $combats;
    }

    static function get($id) {
        $client = Database::getInstance();
        $data = $client->hget(Database::PREFIX.'combats', $id);
        if($data) {
            $obj = unserialize($data);
            return $obj;
        } else {
            return false;
        }
    }

    public function save() {
        $data = serialize($this);
        $client = Database::getInstance();
        $client->hset(Database::PREFIX.'combats', $this->id, $data);
        $client->zadd(Database::PREFIX.'combatlist', $this->id, $this->id);
        if(!$this->is_saved) {
            $client->set(Database::PREFIX.'combatid', $this->id);
            $this->is_saved = true;
        }
    }

    public function delete() {
        if($this->statut == self::STATUT_FIN) {
            $client = Database::getInstance();
            $client->hdel(Database::PREFIX.'pokemons', $this->id);
            $client->zrem(Database::PREFIX.'pokelist', $this->id);
        } else {
            throw new PokemonUnfinishedBattleException('Ce combat est en cours !');
        }
    }

}
