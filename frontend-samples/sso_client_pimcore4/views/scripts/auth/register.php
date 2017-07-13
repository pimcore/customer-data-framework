<?php
/** @var \CustomerManagementFrameworkBundle\Model\CustomerInterface $customer */
$customer = $this->customer;

/** @var Zend_Form $form */
$form = $this->form;
?>

<?php $this->headTitle('Register') ?>

<div class="row">
    <div class="col-md-4 col-md-push-4">

        <form class="form-signin" action="<?= $this->url(['action' => 'register'], 'auth', true) ?>" method="post">
            <?php if ($this->getParam('provider')): ?>
                <input type="hidden" name="provider" value="<?= $this->escape($this->getParam('provider')) ?>">
            <?php endif; ?>

            <h2 class="form-signin-heading">Register</h2>
            <?= $this->template('auth/partials/form-errors.php') ?>

            <div class="row">
                <div class="col-md-12">

                    <?php
                    /** @var Zend_Form_Element $element */
                    foreach ($form->getElements() as $field => $element): ?>

                        <div class="form-group">
                            <label for="input-<?= $element->getName() ?>"><?= $element->getLabel() ?></label>
                            <input type="<?= $element->getAttrib('type') ?: 'text' ?>" name="<?= $element->getName() ?>" id="input-<?= $element->getName() ?>" class="form-control" value="<?= $element->getValue() ?>">
                        </div>

                    <?php endforeach; ?>

                    <button class="btn btn-lg btn-primary btn-block" type="submit">Register</button>
                </div>
            </div>
        </form>

    </div>
</div>
