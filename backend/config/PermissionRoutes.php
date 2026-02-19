<?php
/**
 * Cartographie des permissions par endpoint
 * Format: [method][endpoint] => permission_code
 */
return [
    'POST' => [
        '/api/utilisateurs' => 'users.create',
        '/api/menus' => 'menus.create',
        '/api/commandes' => 'commandes.create',
        '/api/avis' => 'avis.create',
        '/api/contact' => 'contact.create',
        '/api/plats' => 'plats.create',
        '/api/allergenes' => null, // Pas de permission publique
    ],
    'GET' => [
        '/api/utilisateurs' => 'users.list',
        '/api/utilisateurs/{id}' => 'users.read',
        '/api/menus' => 'menus.list',
        '/api/menus/{id}' => 'menus.read',
        '/api/commandes' => 'commandes.list',
        '/api/commandes/{id}' => 'commandes.read',
        '/api/avis' => 'avis.list',
        '/api/avis/{id}' => 'avis.read',
        '/api/plats' => 'plats.list',
        '/api/plats/{id}' => 'plats.read',
        '/api/allergenes' => null, // Publique
    ],
    'PUT' => [
        '/api/utilisateurs/{id}' => 'users.update',
        '/api/menus/{id}' => 'menus.update',
        '/api/commandes/{id}' => 'commandes.update',
        '/api/plats/{id}' => 'plats.update',
    ],
    'DELETE' => [
        '/api/utilisateurs/{id}' => 'users.delete',
        '/api/menus/{id}' => 'menus.delete',
        '/api/commandes/{id}' => 'commandes.delete',
        '/api/avis/{id}' => 'avis.delete',
        '/api/plats/{id}' => 'plats.delete',
        '/api/allergenes/{id}' => null,
    ]
];
