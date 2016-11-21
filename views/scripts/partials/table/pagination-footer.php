<?php
/** @var Zend_Paginator $paginator */
$paginator = $this->paginator;

$showPageSelector = (isset($this->showPageSelector)) ? (bool)$this->showPageSelector : true;
?>

<div class="box-footer">
    <div class="pagination-footer row">
        <div class="pagination-footer__count-selector col-md-2">
            <?php if ($showPageSelector): ?>
                <form class="pagination-footer__count-selector-form form-inline">
                    <div class="form-group">

                        <label>Per page</label>
                        <select class="form-control">
                            <?php foreach ([10, 25, 50, 100, 250, 500] as $itemCount): ?>

                                <?php
                                $countParams = [
                                    'page'    => null,
                                    'perPage' => null
                                ];

                                // only add perPage to URL if it's not the default size
                                if ($itemCount !== $this->defaultPageSize()) {
                                    $countParams['perPage'] = $itemCount;
                                }

                                $countUrl = $this->formQueryString($this->request, $this->url($countParams));
                                ?>

                                <option data-url="<?= $countUrl ?>" value="<?= $itemCount ?>" <?= ($paginator->getItemCountPerPage() === $itemCount) ? 'selected' : '' ?>>
                                    <?= $itemCount ?>
                                </option>

                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <div class="pagination-footer__pagination col-md-8">
            <?php if ($paginator->getPages()->pageCount > 1): ?>
                <?= $this->paginationControl($paginator, 'Sliding', 'partials/pagination/default.php', $this->getAllParams()); ?>
            <?php endif; ?>
        </div>

        <div class="pagination-footer__summary col-md-2">
            <?php printf(
                'Showing items %d to %d of total %d',
                $paginator->getPages()->firstItemNumber,
                $paginator->getPages()->lastItemNumber,
                $paginator->getTotalItemCount()
            ); ?>
        </div>
    </div>
</div><!-- box-footer -->
