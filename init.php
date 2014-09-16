<?php

require_once('former/former/former.php');
require_once('class.php');
require_once('forms.php');
session_start();

if(!isset($_SESSION['pokemons'])) {
    $_SESSION['pokemons'] = array();
}
