<?php
// Fichier de gestion des permissions et rôles

// Définition des permissions par rôle
$permissions = [
    'visiteur' => [
        'view_planning' => true,
        'view_activities' => true,
        'request_registration' => true
    ],
    'adherent' => [
        'view_planning' => true,
        'view_activities' => true,
        'register_activity' => true,
        'view_own_profile' => true
    ],
    'animateur' => [
        'view_planning' => true,
        'view_activities' => true,
        'manage_sessions' => true,
        'view_participants' => true
    ],
    'membre_bureau' => [
        'view_planning' => true,
        'view_activities' => true,
        'manage_members' => true,
        'manage_activities' => true,
        'manage_planning' => true,
        'manage_payments' => true,
        'manage_instructors' => true,
        'view_statistics' => true
    ]
];

// Vérifier si l'utilisateur a une permission
function hasPermission($role, $permission) {
    global $permissions;
    return isset($permissions[$role][$permission]) && $permissions[$role][$permission];
}

// Vérifier si l'utilisateur connecté est membre du bureau
function isMemberBureau() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'membre_bureau';
}

// Vérifier si l'utilisateur connecté est animateur
function isAnimateur() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'animateur';
}

// Vérifier si l'utilisateur connecté est adhérent
function isAdherent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'adherent';
}

// Rediriger selon le rôle
function redirectByRole($role) {
    switch($role) {
        case 'membre_bureau':
            header("Location: /admin/area.php");
            break;
        case 'animateur':
            header("Location: /animateur/dashboard.php");
            break;
        case 'adherent':
            header("Location: /planning.php");
            break;
        case 'visiteur':
            header("Location: /index.php");
            break;
        default:
            header("Location: /index.php");
    }
    exit();
}

// Obtenir le nom du rôle en français
function getRoleName($role) {
    $roles = [
        'visiteur' => 'Visiteur public',
        'adherent' => 'Adhérent',
        'animateur' => 'Animateur',
        'membre_bureau' => 'Membre du bureau'
    ];
    return $roles[$role] ?? 'Inconnu';
}

// Obtenir la couleur du badge pour chaque rôle
function getRoleColor($role) {
    $colors = [
        'visiteur' => 'secondary',
        'adherent' => 'primary',
        'animateur' => 'warning',
        'membre_bureau' => 'success'
    ];
    return $colors[$role] ?? 'secondary';
}
