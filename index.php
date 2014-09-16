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

/**
 * @route /form
 * @view /views/former.html
 */
function former($id) {
    global $request;
    $form = new CreateForm('', 'post');
    if(count($request->getParams()) && $form->validate($request->getParams())) {
        return 'Vous avez créé '.$form->nom.' de la famille '.$form->famille;
        header('Location: /pokemon/creer/'.$form->famille.'/'.$form->nom.'/'.$id);
        die();
    }
    return array('form' => $form);
}

define('APPLICATION_PATH', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

require_once('bottle/bottle.php');
