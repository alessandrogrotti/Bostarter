<?php
session_start(); // Avvia la sessione

// Imposta una variabile di sessione
$_SESSION['username'] = 'alegrotti';
$_SESSION['role'] = 'admin';

// Recupera e utilizza la variabile di sessione
echo "Benvenuto, " . $_SESSION['username'] . "! Il tuo ruolo è: " . $_SESSION['role'];

// Rimuovi una variabile di sessione
unset($_SESSION['role']);

// Distruggi la sessione
session_destroy();
?>
