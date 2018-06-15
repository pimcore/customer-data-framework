<!DOCTYPE html>
<html lang="en">
<head>
    <title>CMF - Auth Form</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>

<?php
    $form = $this->form;
?>

<div class="container" style="margin-top: 20px">

    <?= $this->form()->start($form, [
        'attr' => [
            'action' => $this->path("form_auth_code_path")."?".$this->queryUrlString,
            'class' => 'form-horizontal'
        ]
    ]); ?>

    <div class="form-group">
        <label class="col-sm-3 control-label" for="zip"><?= $this->translate("auth.client_id") ?> *</label>
        <div class="col-sm-6">

            <?= $this->form()->widget($form['client_id'], [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => "email"
                ]
            ]) ?>

        </div>

    </div>

    <div class="form-group">
        <label class="col-sm-3 control-label" for="client_secret"><?= $this->translate("auth.client_secret") ?> *</label>
        <div class="col-sm-6">
            <?= $this->form()->widget($form['client_secret'], [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => "password"
                ]
            ]) ?>

        </div>

    </div>

    <div class="form-group">
        <?= $this->form()->row($form['_submit'], ['attr' => ['class' => 'btn btn-primary', 'style' => 'margin-left:150px']]) ?>
    </div>

    <?= $this->form()->end($form); ?>

</div>

</body>
</html>