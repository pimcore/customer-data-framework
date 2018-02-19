<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Customer Management</title>

    <?php
    $this->headLink()->appendStylesheet($this->minifiedAssetUrl()->minifiedAssetUrl('/bundles/pimcorecustomermanagementframework/admin/css/lib.css'));
    $this->headLink()->appendStylesheet($this->minifiedAssetUrl()->minifiedAssetUrl('/bundles/pimcorecustomermanagementframework/admin/css/cmf.css'));
    ?>

    <?= $this->headLink(); ?>
</head>
<body class="sidebar-collapse">

<div class="wrapper">
    <div class="content-wrapper">
        <?php $this->slots()->output('_content') ?>
    </div>
</div>

<?= $this->jsConfigHelper() ?>

<?php
$this->headScript()->prependFile($this->minifiedAssetUrl()->minifiedAssetUrl('/bundles/pimcorecustomermanagementframework/admin/js/cmf.js'));
$this->headScript()->prependFile($this->minifiedAssetUrl()->minifiedAssetUrl('/bundles/pimcorecustomermanagementframework/admin/js/lib.js'));
?>

<?= $this->headScript() ?>

</body>
</html>
