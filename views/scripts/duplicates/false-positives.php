<div class="container">
    <table class="table">

        <?php foreach($this->rows as $row) {?>

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
        <?php }?>

    </table>
</div>