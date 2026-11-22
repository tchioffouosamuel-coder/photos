<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

/*
 | Laravel 12 ne fournit plus ces traits par defaut. Sans AuthorizesRequests,
 | $this->authorize() n'existe pas et les controleurs d'administration
 | echoueraient avec une erreur de methode inconnue au lieu de refuser l'acces.
 */
abstract class Controller
{
    use AuthorizesRequests;
    use ValidatesRequests;
}
