<?php
if ($this->pageCount === 0) {
    return;
}

/** @var Zend_Controller_Request_Http $request */
$request    = Zend_Controller_Front::getInstance()->getRequest();
$linkParams = $this->addPerPageParam($request);

$buildLink = function ($page) use ($request, $linkParams) {
    $linkParams = array_merge($linkParams, [
        'page' => (int)$page
    ]);

    $url = $this->url($linkParams);
    $url = $this->formQueryString($request, $url);

    return $url;
};
?>

<ul class="pagination no-margin">
    <!-- first page -->
    <?php if ($this->first !== $this->current): ?>
        <li class="first">
            <a href="<?= $buildLink($this->first) ?>">&larr;</a>
        </li>
    <?php else: ?>
        <li class="disabled"><span>&larr;</span></li>
    <?php endif; ?>

    <!-- previous page -->
    <?php if (isset($this->previous)): ?>
        <li class="previous">
            <a href="<?= $buildLink($this->previous) ?>">&laquo;</a>
        </li>
    <?php else: ?>
        <li class="disabled"><span>&laquo;</span></li>
    <?php endif; ?>

    <!-- page selectors -->
    <?php foreach ($this->pagesInRange as $page): ?>
        <?php if ($page !== $this->current): ?>
            <li>
                <a href="<?= $buildLink($page) ?>"><?= $page ?></a>
            </li>
        <?php else: ?>
            <li class="active">
                <span><?= $page ?></span>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- next page -->
    <?php if (isset($this->next)): ?>
        <li class="next">
            <a href="<?= $buildLink($this->next) ?>">&raquo;</a>
        </li>
    <?php else: ?>
        <li class="disabled"><span>&raquo;</span></li>
    <?php endif; ?>

    <!-- last page -->
    <?php if ($this->last !== $this->current): ?>
        <li class="last">
            <a href="<?= $buildLink($this->last) ?>">&rarr;</a>
        </li>
    <?php else: ?>
        <li class="disabled"><span>&rarr;</span></li>
    <?php endif; ?>
</ul>
