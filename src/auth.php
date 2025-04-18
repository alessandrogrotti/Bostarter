<?php
include_once 'connection.php';  // Include la connessione ai database
include_once 'mongodb.php';

// Funzione di login
function login($email, $password) {
    try {
        $mysqlConn = getMySQLConnection(); // Connessione MySQL
        $stmt = $mysqlConn->prepare("SELECT * FROM UTENTE WHERE Email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['Password'])) {
            // Verifica se è un creatore
            $stmtCreator = $mysqlConn->prepare("SELECT 1 FROM CREATORE WHERE Email_Utente = :email");
            $stmtCreator->execute([':email' => $email]);
            $is_creator = $stmtCreator->fetch() ? true : false;

            // Verifica se è un amministratore
            $stmtAdmin = $mysqlConn->prepare("SELECT 1 FROM AMMINISTRATORE WHERE Email_Utente = :email");
            $stmtAdmin->execute([':email' => $email]);
            $is_admin = $stmtAdmin->fetch() ? true : false;

            // Imposta la sessione
            $_SESSION['id'] = $user['Email']; // Email come ID
            $_SESSION['nickname'] = $user['Nickname'];
            $_SESSION['is_admin'] = $is_admin;
            $_SESSION['is_creator'] = $is_creator;

            writeLog('Login', "Utente {$user['Nickname']} ($email) ha effettuato l'accesso");
            return true;
        } else {
            writeLog('Login fallito', "Tentativo di login fallito con email: $email");
            return false;
        }
    } catch (PDOException $e) {
        return false;
    }
}

// Funzioni per verificare lo stato di login
function isLoggedIn() {
    return isset($_SESSION['id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;
}

function isCreator() {
    return isset($_SESSION['is_creator']) && $_SESSION['is_creator'] == true;
}

// Funzioni per forzare login e ruoli
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: index.php");
        exit;
    }
}

function requireCreator() {
    if (!isCreator()) {
        header("Location: index.php");
        exit;
    }
}
?>
