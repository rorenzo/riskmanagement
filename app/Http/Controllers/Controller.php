<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Potrebbe variare leggermente
use Illuminate\Foundation\Validation\ValidatesRequests; // Potrebbe variare leggermente
use Illuminate\Routing\Controller as BaseController;    // Assicurati che estenda questo

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}