<?php

require_once('init.php');

/**
 * @route /
 * @view /views/index.html
 */
function index() {
    global $response;
    if(isset($_SESSION['combat']) && $_SESSION['combat']->statut != Combat::STATUT_FIN) {
        return $response->redirect('combat');
    }
    $pokemons = Pokemon::getFree();
    $context = array('pokemons' => $pokemons, 'selection' => null);
    if(isset($_SESSION['pkmn1'])) {
        $context['selection'] = $_SESSION['pkmn1']->id;
    }
    return $context;
}

 /**
* @route /choix/:id
* @view /views/choix.html
*/
function choix($id) {
    global $response;
    $pokemon = Pokemon::get($id);
    if(!$pokemon || $pokemon->combattant) {
        return array('erreur' => 'Vous ne pouvez pas choisir ce pokémon');
    } else {
        if(isset($_SESSION['pkmn1'])) {
            if($_SESSION['pkmn1']->id == $id) {
            return array('erreur' => 'Vous ne pouvez pas choisir ce pokémon');
            }
            $combat = new Combat($_SESSION['pkmn1'], $pokemon);
            unset($_SESSION['pkmn1']);
            $_SESSION['combat'] = $combat;
            return $response->redirect('combat');
        } else {
            $_SESSION['pkmn1'] = $pokemon;
            return $response->redirect('index');
        }
    }
}

/**
 * @route /pokemon/:id/detail
 * @view /views/detail.html
 */
function detail($id) {
    $pokemon = Pokemon::get($id);
    if($pokemon) {
        return array('pokemon' => $pokemon);
    } else {
        return array('erreur' => 'Pokémon introuvable.');
    }
}

/**
 * @route /form
 * @view /views/former.html
 */
function form() {
    global $request, $response;
    $form = new CreateForm('', 'post');
    if(count($request->getParams()) && $form->validate($request->getParams())) {
        $nom = $form->nom;
        $famille = $form->famille;

        $pokemon = new $form->famille($form->nom);
        $pokemon->save();
        return $response->redirect(array('detail', array('id' => $pokemon->id)));
    }
    return array('form' => $form);
}

/**
 * @route /combat/debut
 */
function combat_debut() {
    global $request;
    $resultat = array();
    $pkmn1 = $request->getParam('attaquant');
    $pkmn2 = $request->getParam('defenseur');
    if(isset($_SESSION['pokemons'][$pkmn1], $_SESSION['pokemons'][$pkmn2])) {
        $attaquant = $_SESSION['pokemons'][$pkmn1];
        $defenseur = $_SESSION['pokemons'][$pkmn2];
        $_SESSION['combat'] = new Combat($attaquant, $defenseur);
        $resultat['statut'] = true;
    } else {
        $resultat['statut'] = false;
    }
    header('Content-Type: application/json');
    return json_encode($resultat);
}

/**
*@route /combat/round
*
*/
/*function combat_round() {
    $_SESSION['combat']->round();
    $log = $_SESSION['combat']['log'];
    header('Content-Type: application/json');
    $resulat= array('log' => $log);
    return json_encode($resultat);

}*/

/**
* @route /supprimer/:id
* @view /views/supprimer.html
*/
function supprimer($id) {
        global $request, $response;
        $pokemon = Pokemon::get($id);
    if($pokemon) {
        $supp = new SuppForm('', 'post', array('id' => $id));
    if(count($request->getParams()) && $supp->validate($request->getParams())) {
        $pokemon->delete();
        return $response->redirect('index');
    }
        return array('form' => $supp, 'pokemon' => $pokemon);
    } else {
        return array('erreur' => 'Le pokémon n’existe pas.');
    }
}

/**
* @route /combat
* @view /views/combat.html
*/
function combat() {
    global $response;
    if(!isset($_SESSION['combat'])) {
        return $response->redirect('index');
    }
    return array('combat' => $_SESSION['combat']);
}

/**
* @route /combat/round
* @view /views/choix.html
*/
function combat_round() {
    global $response;
    if(!isset($_SESSION['combat'])) {
        return $response->redirect('index');
    }
    try {
        $_SESSION['combat']->round();
    } catch(PokemonBattleEndException $e) {
    }
    return $response->redirect('combat');
}

define('APPLICATION_PATH', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

require_once('bottle/bottle.php');
