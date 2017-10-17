<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */
if ($this->pageCount): ?>
    <ul class="pagination">
        <?php if (isset($this->previous)): ?>
            <li>
                <a href="<?= $this->pimcoreUrl(['page' => $this->previous]); ?>">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                </a>
            </li>
        <?php endif; ?>

        <?php foreach ($this->pagesInRange as $page): ?>
            <li class="<?= $page == $current ? 'active' : '' ?>">
                <a href="<?= $this->pimcoreUrl(['page' => $page]); ?>">
                    <?= $page; ?>
                </a>
            </li>
        <?php endforeach; ?>

        <?php if (isset($this->next)): ?>
            <li>
                <a href="<?= $this->pimcoreUrl(['page' => $this->next]); ?>">
                    <span class="glyphicon glyphicon-chevron-right"></span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
<?php endif; ?>
