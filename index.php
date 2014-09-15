<?php

/**
*@route /
*@view /views/index.html
*/

function index() {
	return array('nom' => 'PokemonArena');
}

define('APPLICATION_PATH', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

require_once('bottle/bottle.php');