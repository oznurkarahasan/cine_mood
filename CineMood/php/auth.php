<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserName() {
    return $_SESSION['user_name'] ?? null;
}

function setUserSession($userId, $userName) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $userName;
}

function clearUserSession() {
    session_unset();
    session_destroy();
}
?> 