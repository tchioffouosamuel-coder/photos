<?php

if (!function_exists('photo_url')) {
    function photo_url($path) {
        // Retirer /public/ si présent dans l'URL de base
        $baseUrl = rtrim(config('app.url'), '/');
        
        // Construire l'URL correcte
        return $baseUrl . '/storage/' . ltrim($path, '/');
    }
}