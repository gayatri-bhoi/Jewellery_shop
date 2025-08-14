<?php
session_start();

function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

function isUser() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'user';
}

function checkLogin() {
    if (!isset($_SESSION['user'])) {
        header("Location: auth/login.php");
        exit();
    }
}
