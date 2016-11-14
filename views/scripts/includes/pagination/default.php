<?php

$params = $this->params ?: [];

if ($this->pageCount): ?>
    <ul class="pagination">
        <?php if (isset($this->previous)): ?>
            <li>
                <a href="<?= $this->url(array_merge($params, ['page' => $this->previous])); ?>">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                </a>
            </li>
        <?php endif; ?>

        <?php foreach ($this->pagesInRange as $page): ?>
            <li class="<?= $page == $this->current ? 'active' : '' ?>">
                <a href="<?= $this->url(array_merge($params, ['page' => $page])); ?>">
                    <?= $page; ?>
                </a>
            </li>
        <?php endforeach; ?>

        <?php if (isset($this->next)): ?>
            <li>
                <a href="<?= $this->url(array_merge($params, ['page' => $this->next])); ?>">
                    <span class="glyphicon glyphicon-chevron-right"></span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
<?php endif; ?>
