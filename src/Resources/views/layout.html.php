<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Customer Management</title>

    <?php
    $this->headLink()->appendStylesheet($this->minifiedAssetUrl('/plugins/CustomerManagementFramework/static/dist/css/lib.css'));
    $this->headLink()->appendStylesheet($this->minifiedAssetUrl('/plugins/CustomerManagementFramework/static/dist/css/style.css'));
    ?>

    <?= $this->headLink(); ?>
</head>
<body class="sidebar-collapse">

<div class="wrapper">
    <div class="content-wrapper">
        <?php $this->slots()->output('_content') ?>
    </div>
</div>

<?= $this->jsConfig() ?>

<?php
$this->headScript()->appendFile($this->minifiedAssetUrl('/plugins/CustomerManagementFramework/static/dist/js/lib.js'));
$this->headScript()->appendFile($this->minifiedAssetUrl('/plugins/CustomerManagementFramework/static/dist/js/script.js'));
?>

<?= $this->headScript() ?>

</body>
</html>
