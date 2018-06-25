<!DOCTYPE html>
<html lang="en">
<head>
    <title>CMF - Auth Form</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <style>
        h2{
            text-align: center;
        }
    </style>
</head>
<body>

<?php
    $form = $this->form;
?>

<h2>
    <?= $this->pageTitle; ?>
</h2>
<div class="container" style="margin-top: 20px">

    <?= $this->form()->start($form, [
        'attr' => [
            'action' => $this->formAction."?".$this->queryUrlString,
            'class' => 'form-horizontal'
        ]
    ]); ?>

    <div class="form-group">
        <label class="col-sm-3 control-label" for="zip"><?= $this->translate("auth.username") ?>:</label>
        <div class="col-sm-6">

            <?= $this->form()->widget($form['username'], [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => "email"
                ]
            ]) ?>

        </div>

    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label" for="client_secret"><?= $this->translate("auth.password") ?>:</label>
        <div class="col-sm-6">
            <?= $this->form()->widget($form['password'], [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => "password"
                ]
            ]) ?>

        </div>

    </div>

    <div class="form-group">
        <?= $this->form()->row($form['_submit'], ['attr' => ['class' => 'btn btn-primary', 'style' => 'margin-left:160px']]) ?>
    </div>

    <?= $this->form()->end($form); ?>

</div>

</body>
</html>