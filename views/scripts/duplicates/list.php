<div class="container">
    <table class="table">

        <?php foreach($this->duplicates as $duplicate) {?>

            <tr>
                <td colspan="99">
                    <?= $duplicate['dbData']['id'] ?>
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
                        <td><?=$customer->getBirthDate() instanceof \Pimcore\Date ? date('d.m.Y',$customer->getBirthDate()->getTimestamp()) : '-'?></td>
                        <td>shoe size <?=$customer->getShoeSize() ? : '-' ?></td>

                    </tr>
                <?php } ?>
                <tr>
                    <td colspan="99" style="background-color: silver;">

                    </td>
                </tr>
        <?php }?>

    </table>
</div>