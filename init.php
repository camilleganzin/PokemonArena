<?php

require_once('former/former/former.php');

require_once('Predis/Autoloader.php');
Predis\Autoloader::register();

require_once('class.php');
require_once('forms.php');
session_start();
