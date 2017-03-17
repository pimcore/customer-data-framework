<div class="container">
    <table class="table">

        <?php foreach($this->duplicates as $duplicate) {?>

            <tr>
                <td colspan="99">
                    <br/>
                    <?= $duplicate['dbData']['id'] ?>
                    <br/>
                    <strong>matched field combinations:</strong><br/>
                    <?= str_replace(';', '<br/>', $duplicate['dbData']['fieldCombinations']) ?>
                </td>
            </tr>

                <?php
                /**
                 * @var \CustomerManagementFramework\Model\CustomerInterface $customer
                 */
                foreach($duplicate['customers'] as $customer) {?>
                    <tr>

                        <td><?=$customer->getId()?></td>
                        <td><?=$customer->getEmail()?></td>
                        <td><?=$customer->getFirstname()?> <?=$customer->getLastname()?></td>
                        <td><?=$customer->getStreet() ?></td>
                        <td><?=$customer->getZip() ?> <?=$customer->getCity() ?></td>
                        <td><?=$customer->getBirthDate() ? date('d.m.Y',$customer->getBirthDate()->getTimestamp()) : '-'?></td>
                        <td>shoe size <?=$customer->getShoeSize() ? : '-' ?></td>

                    </tr>
                <?php } ?>
                <tr>
                    <td colspan="99" style="background-color: silver;">

                    </td>
                </tr>
        <?php }?>

    </table>

    <?php if($this->paginator->getPages()->pageCount > 1): ?>
        <div class="text-center">
            <?= $this->paginationControl($paginator, 'Sliding', 'includes/pagination/default.php', ['params'=>$this->getAllParams()]); ?>
        </div>
    <?php endif; ?>
</div>