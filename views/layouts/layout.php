<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Customer Management</title>

    <?php
    $this->headLink()->appendStylesheet('/plugins/CustomerManagementFramework/static/dist/css/style.css');
    ?>

    <?= $this->headLink(); ?>
</head>
<body>

<?= $this->layout()->content ?>
<?= $this->jsConfig() ?>

<?php
$this->headScript()->appendFile('/plugins/CustomerManagementFramework/static/dist/js/lib.js');
$this->headScript()->appendFile('/plugins/CustomerManagementFramework/static/dist/js/script.js');
?>

<?= $this->headScript() ?>

</body>
</html>
