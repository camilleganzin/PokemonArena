<?php

/**
*@route /
*@view /views/index.html
*/

function index() {
	return array('nom' => 'PokemonArena');
}

/**
*@route /pokemon/:name
*@view /views/pokemon.html
*/
function pokemon($name) {
	return array('pokemon' => $name);
}

define('APPLICATION_PATH', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

require_once('bottle/bottle.php');