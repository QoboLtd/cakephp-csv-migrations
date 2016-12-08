<?php
$title = (!empty($data['options']['title'])) ? $data['options']['title'] : '';

if (!empty($data['content']['records'])) :
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= $title ?></h3>
    </div>
    <div class="panel-body">
        <?php if (!empty($data['content']['records'])) : ?>
        <table class="table table-hover">
            <?php foreach ($data['content']['records'] as $row) :?>
                <tr>
                    <?php foreach ($row as $k => $cell) : ?>
                        <td><?= $cell ?> </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach;?>
        </table>
        <?php endif; ?>
    </div>
</div>
<hr/>
<?php endif; ?>
