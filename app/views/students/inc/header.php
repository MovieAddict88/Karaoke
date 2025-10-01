<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/css/style.css">
    <title><?php echo SITENAME; ?> - Student</title>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-info mb-3">
    <div class="container">
        <a class="navbar-brand" href="<?php echo URLROOT; ?>/students">Student Dashboard</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarsExampleDefault">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo URLROOT; ?>/students">Dashboard</a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link" href="<?php echo URLROOT; ?>/messages">Messages</a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                 <li class="nav-item">
                    <a class="nav-link">Welcome <?php echo $_SESSION['user_name']; ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo URLROOT; ?>/users/logout">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container">