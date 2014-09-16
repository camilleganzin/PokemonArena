<?php

require_once('class.php');
session_start();

if(!isset($_SESSION['pokemons'])) {
    $_SESSION['pokemons'] = array();
}
