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

<?php
$attributes = array('id' => 'searchMonthDataForm');
echo form_open('calendar/attendance', $attributes) ?>

<select name="selectmonth" id="selectmonth" value="" size="1" style="width: 150px;">
  <option selected>=== 選擇月份 ===</option> 
<?php foreach($monthArr as $month_Item):?>
  <option value ="<?php echo $month_Item?>"><?php echo $month_Item?></option>
<?php endforeach;?>
</select>
<?php if ($is_hr == TRUE || $is_admin == TRUE) { ?>
 &nbsp;
 <select class="input-large" name="selectuser" id="selectuser" value="" size="1" style="width: 150px;">
      <option selected>=== 選擇人員 ===</option> 
    <?php foreach ($userName as $agentId => $AgentName): ?>
        <option value="<?php echo $AgentName; ?>"><?php echo $AgentName; ?></option>
    <?php endforeach ?>
 </select>
<?php } ?>
 &nbsp; <button value="7" type="submit" style="width:60px;height:22px;font-size:8px;"><?php echo lang('attendance_search');?></button>
</form>
&nbsp;<a href="<?php echo base_url();?>calendar/export/<?php echo $login_id; ?>/<?php echo $date;?>" class="btn btn-primary"><i class="fa fa-file-excel-o"></i>&nbsp; <?php echo lang('attendance_export');?></a>

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