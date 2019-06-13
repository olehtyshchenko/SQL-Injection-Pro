<?php
?>
<!doctype html>
<html lang="en">
<head>
    <link rel="stylesheet" href="styles/index.css">
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<div class="preloader">
    <div class="preloader__circle">
        <div class="preloader__in-circle"></div>
    </div>
</div>
    <div class="cont">
        <form action="" class="inp-form">
            <p class="text">Додати адресу, для сканування</p>
            <input type="text" class="inp" id="inp" pattern="((https?|ftp)://)?">
            <div class="btn-cont">
                <div class="bth-add" onclick="addAddress()">Додати</div>
                <div class="bth-scan" onclick="startScan()">Виконати перевірку</div>
            </div>
        </form>
    </div>
<script src="jquery-3.3.1.min.js"></script>
<script src="code.js"></script>
</body>
</html>
