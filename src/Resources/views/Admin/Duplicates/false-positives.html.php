<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */
?>
<div class="container">
    <table class="table">

        <?php foreach ($this->paginator as $row) {
    ?>

                <tr>

                    <td><?=$row['row1']?></td>
                    <td><?=$row['row1Details']?></td>

                </tr>
                <tr>

                    <td><?=$row['row2']?></td>
                    <td><?=$row['row2Details']?></td>

                </tr>
                <tr>
                    <td colspan="99" style="background-color: silver;">

                    </td>
                </tr>
        <?php
}?>

    </table>

    <?php if ($this->paginator->getPages()->pageCount > 1): ?>
        <div class="text-center">
            <?= $this->paginationControl($paginator, 'Sliding', 'includes/pagination/default.php', ['params' => $this->getAllParams()]); ?>
        </div>
    <?php endif; ?>
</div>