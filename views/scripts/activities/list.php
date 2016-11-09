<div class="container-fluid">
    <h2>Activities</h2>

    <?if(sizeof($this->activities)) {?>
        <table class="table table-striped">

            <?foreach($this->activities as $activity) {?>
                <tr>
                    <td><?=$activity->cmfGetType()?></td>
                    <td><?=$activity->cmfGetActivityDate()?></td>
                </tr>
            <?}?>
        </table>
    <?} else {?>
        <div class="alert alert-warning">
            no activities found
        </div>

    <?}?>

</div>