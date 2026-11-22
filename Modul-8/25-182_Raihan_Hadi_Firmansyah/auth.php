<?php
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] != true) {
    header("Location: index.php");
    exit;
}

function cek_owner() {
    if (!isset($_SESSION['level']) || $_SESSION['level'] != 1) {
        header("Location: index.php");
        exit;
    }
}
?>