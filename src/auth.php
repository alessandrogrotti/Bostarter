<?php
session_start();

include_once 'connection.php';
include_once 'mongodb.php';

function login($email, $password) {
    try {
        $mysqlConn = getMySQLConnection();
        $stmt = $mysqlConn->prepare("SELECT * FROM UTENTE WHERE Email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && md5($password) === $user['Password']) {
            $stmtCreator = $mysqlConn->prepare("SELECT 1 FROM CREATORE WHERE Email_Utente = :email LIMIT 1");
            $stmtCreator->execute([':email' => $email]);
            $is_creator = (bool)$stmtCreator->fetch();

            $stmtAdmin = $mysqlConn->prepare("SELECT 1 FROM AMMINISTRATORE WHERE Email_Utente = :email LIMIT 1");
            $stmtAdmin->execute([':email' => $email]);
            $is_admin = (bool)$stmtAdmin->fetch();

            session_regenerate_id(true);
            $_SESSION = [
                'id' => $user['Email'],
                'nickname' => $user['Nickname'],
                'is_admin' => $is_admin,
                'is_creator' => $is_creator,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'last_activity' => time()
            ];

            writeLog('Login', "Utente {$user['Nickname']} ($email) ha effettuato l'accesso");
            return true;
        }

        writeLog('Login fallito', "Tentativo di login fallito con email: $email");
        return false;

    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

function logout() {
    $nickname = $_SESSION['nickname'] ?? 'Utente sconosciuto';
    $email = $_SESSION['id'] ?? 'email sconosciuta';

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
    writeLog('Logout', "Utente $nickname ($email) ha effettuato il logout");

    header("Location: index.php");
    exit;
}

function verifyAdminCode($code) {
    $email = $_SESSION['id'];

    try {
        $mysqlConn = getMySQLConnection();
        $stmt = $mysqlConn->prepare("SELECT Codice_Sicurezza FROM AMMINISTRATORE WHERE Email_Utente = :email");
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && md5($code) === $admin['Codice_Sicurezza']) {
            $_SESSION['is_admin'] = true;
            writeLog('Accesso Admin', "Utente {$_SESSION['nickname']} ($email) ha effettuato il login come admin");
            return true;
        }

        writeLog('Login Admin fallito', "Codice errato per l'utente $email");
        return false;

    } catch (PDOException $e) {
        error_log("Errore verifica codice admin: " . $e->getMessage());
        return false;
    }
}

function isLoggedIn() {
    return isset($_SESSION['id'], $_SESSION['user_agent'], $_SESSION['ip'], $_SESSION['last_activity']) &&
           $_SESSION['last_activity'] >= time() - 1800 &&
           $_SESSION['user_agent'] === ($_SERVER['HTTP_USER_AGENT'] ?? '') &&
           $_SESSION['ip'] === ($_SERVER['REMOTE_ADDR'] ?? '') &&
           ($_SESSION['last_activity'] = time());
}

function isAdmin() {
    return isLoggedIn() && ($_SESSION['is_admin'] ?? false);
}

function isCreator() {
    return isLoggedIn() && ($_SESSION['is_creator'] ?? false);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: loginAdmin.php");
        exit;
    }
}

function requireCreator() {
    requireLogin();
    if (!isCreator()) {
        header("Location: index.php?error=unauthorized");
        exit;
    }
}
?>
