<?php
/**
 * @var \CustomerManagementFrameworkBundle\CustomerDuplicatesView\CustomerDuplicatesViewInterface $duplicatesView
 * @var array $searchBarFields
 */

$exampleQueries = [
    'foo',
    'foo AND bar',
    'foo OR bar',
    'foo OR !bar',
    'foo AND "bar"',
    'foo OR (bar AND baz)',
];

$modifiers = [
    'Exact search (field must match exactly)' => [
        '"<term>"',
    ],
    'Negation' => [
        '!<term>',
        '!(<query>)'
    ]
];

$codify = function (array $values) {
    return array_map(function ($value) {
        return '<code>' . $this->escape($value) . '</code>';
    }, $values);
};

$codeList = function (array $values) use ($codify) {
    return implode(' ', $codify($values));
};
?>

<p class="help-block help-block-toggle">
    <a role="button" data-toggle="collapse" href="#searchBarHelp" aria-expanded="false" aria-controls="searchBarHelp">
        <i class="fa fa-question-circle"></i>
        <?= $duplicatesView->translate('cmf_filters_search_show_help') ?>
    </a>.

    <?= $duplicatesView->translate('cmf_filters_search_info'); ?>
</p>

<div class="help-block collapse" id="searchBarHelp">
    <div id="searchBarFields">
        <h4><?= $duplicatesView->translate('cmf_filters_fields_include') ?></h4>
        <?= $codeList($this->searchBarFields); ?>
    </div>

    <div id="searchBarExamples">
        <h4><?= $duplicatesView->translate('cmf_filters_example_queries') ?></h4>
        <?= $codeList($exampleQueries) ?>
    </div>

    <div id="searchBarModifiers">
        <h4><?= $duplicatesView->translate('cmf_filters_modifiers') ?></h4>
        <dl>
            <?php foreach ($modifiers as $modifierTitle => $modifierExamples): ?>

                <dt><?= $duplicatesView->translate($modifierTitle) ?></dt>
                <dd>
                    <?= $codeList($modifierExamples) ?>
                </dd>

            <?php endforeach; ?>
        </dl>
    </div>
</div>