<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Музыкальная коллекция</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION[CSRF_TOKEN_NAME] ?? ''; ?>">
    <link href="<?php echo URL; ?>css/style.css" rel="stylesheet">
</head>
<body>
    <div class="logo"></div>
    <div class="navigation">
        <a href="<?php echo URL; ?>" class="<?php echo (basename($_SERVER['SCRIPT_NAME']) == 'index.php') ? 'active' : ''; ?>">Главная</a>
        <a href="<?php echo URL; ?>songs">Все песни</a>
    </div>