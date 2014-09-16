<?php

require_once('init.php');

/**
 * @route /
 * @view /views/index.html
 */
function index() {
    //$pika = new Carapuce('Pikaaa');
    $pokemons = $_SESSION['pokemons'];
    return array('pokemons' => $pokemons);
}

/**
 * @route /pokemon/:id/detail
 * @view /views/detail.html
 */
function detail($id) {
    if(isset($_SESSION['pokemons'][$id])) {
        $pokemon = $_SESSION['pokemons'][$id];
        return array('pokemon' => $pokemon);
    }
}

/**
 * @route /pokemon/creer/:famille/:nom/:id
 * @todo supprimer cette action, elle n’est que temporaire !
 */
function creer($famille, $nom, $id) {
    if(!isset($_SESSION['pokemons'][$id])) {
        $pokemon = new $famille($nom);
        $_SESSION['pokemons'][$id] = $pokemon;
        return 'OK';
    } else {
        return 'DOUBLON';
    }
}

define('APPLICATION_PATH', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

require_once('bottle/bottle.php');
