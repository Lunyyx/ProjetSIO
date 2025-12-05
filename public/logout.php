<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/models/Utilisateur.php';

session_destroy();
setMessage('Vous avez été déconnecté avec succès.', 'success');
rediriger('/');
