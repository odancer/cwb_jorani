   <?php
/**
 * This view displays the list of overtime request created by the connected user.
 * @copyright  Copyright (c) 2014-2017 Benjamin BALET
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/bbalet/jorani
 * @since         0.2.0
 */
?>

<div class="row-fluid">
    <div class="span12">
<h2><?php echo lang('attendance_title');?></h2>
<table cellpadding="0" cellspacing="0" class="display" id="attendance" width="100%">
        <tr>
             <th><?php echo lang('calendar/attendance');?></th>
        </tr>
<table/>

<table cellpadding="0" cellspacing="0" border="2" class="display" id="attendance2" width="100%">
    <thead>

        <tr>
            <th><?php echo lang('attendance_index_user_id');?></th>
            <th><?php echo lang('attendance_index_user_fullname');?></th>
            <th><?php echo lang('attendance_index_date');?></th>
            <th><?php echo lang('attendance_index_in');?></th>
            <th><?php echo lang('attendance_index_out');?></th>
        </tr>
    </thead>
    <tbody>

    <?php foreach ($records as $records_item): ?>
        <tr>
        <td align="center"><?php echo $records_item['userid']; ?></td>
        <td align="center"><?php echo $fullname; ?></td>
        <td align="center"><?php echo $records_item['tc_date']; ?></td>
        <td align="center"><?php echo $records_item['first']; ?></td> 
        <td align="center"><?php echo $records_item['final']; ?></td>   
    </tr>
<?php endforeach ?>

	</tbody>
</table>
    </div>
</div>