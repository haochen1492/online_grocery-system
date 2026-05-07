<?php
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin Panel' ?> – FreshCart</title>
    <link href="[cdn.jsdelivr.net](https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css)" rel="stylesheet">
    <link rel="stylesheet" href="[cdn.jsdelivr.net](https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css)">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="d-flex" id="wrapper">

