<?php

require_once('class.php');

class PokemonTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        $this->pikachu = new Pikachu('TestPika');
        $this->salameche = new Salameche('TestSala');
        $this->carapuce = new Carapuce('TestCara');
        $this->combat = new Combat($this->salameche, $this->carapuce);
    }

    public function testConstructPokemon() {
        $this->assertEquals('TestPika', $this->pikachu->nom);
        $this->assertEquals(1, $this->pikachu->niveau);
    }

    public function testConstructCombat() {
        $this->assertContains($this->salameche, $this->combat->adversaires);
        $this->assertCount(2, $this->combat->adversaires);
        $this->assertEquals(Combat::STATUT_DEBUT, $this->combat->statut);
    }

    public function testRound() {
        $this->salameche->precision = 100;
        $this->carapuce->precision = 100;
        $this->combat->round();
        $this->assertCount(6, $this->combat->log);
        $this->assertEquals(Combat::STATUT_ENCOURS, $this->combat->statut);
    }

    public function testPrecision() {
        $sala_coups = $cara_coups = 0;
        foreach(range(1, 1000) as $count) {
            $this->setUp();
            $this->combat->round();
            if($this->salameche->dernier_coup != Pokemon::COUP_RATE) {
                $sala_coups++;
            }

            if($this->carapuce->dernier_coup != Pokemon::COUP_RATE) {
                $cara_coups++;
            }
        }
        $this->assertLessThanOrEqual(10, abs($this->salameche->precision - ($sala_coups / 10)));
        $this->assertLessThanOrEqual(10, abs($this->carapuce->precision - ($cara_coups / 10)));
    }

    public function testMort() {
        $this->salameche->precision = 100;
        $this->carapuce->precision = 100;
        foreach(range(1,8) as $rounds) {
            $this->combat->round();
        }
        $this->assertEquals(0, $this->salameche->pv);
        $this->assertEquals(344, $this->carapuce->pv);
        $this->assertEquals(Combat::STATUT_FIN, $this->combat->statut);
        return $this->combat;
    }

    /**
     * @depends testMort
     * @expectedException Exception
     */
    public function testFin($combat) {
        $combat->round();
    }


}
