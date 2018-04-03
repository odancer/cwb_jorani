<?php 
/**
 * This view allows to modify an employee record.
 * @copyright  Copyright (c) 2014-2017 Benjamin BALET
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/bbalet/jorani
 * @since         0.1.0
 */
?>

<div class="row-fluid">
    <div class="span12">
<h2><?php echo lang('users_edit_view');?><?php echo $users_item['id']; ?><?php echo $help;?></h2>

<?php echo validation_errors(); ?>
    </div>
</div>

<?php 
$attributes = array('class' => 'form-horizontal');
if (isset($_GET['source'])) {
    echo form_open('users/view/' . $users_item['id'] .'?source=' . $_GET['source'], $attributes);
} else {
    echo form_open('users/view/' . $users_item['id'], $attributes);
} ?>

    <input type="hidden" name="id" value="<?php echo $users_item['id']; ?>" />
    
<div class="row">
    <div class="span4">
        <div class="control-group">
            <label class="control-label" for="firstname"><?php echo lang('users_edit_field_firstname');?></label>
            <div class="controls">
                <input readonly="value" type="text" name="firstname" value="<?php echo $users_item['firstname']; ?>" required />
            </div>
        </div>
    </div>

    <div class="span4">
        <div class="control-group">
            <label class="control-label" for="lastname"><?php echo lang('users_edit_field_lastname');?></label>
            <div class="controls">
                <input readonly="value" type="text" name="lastname" value="<?php echo $users_item['lastname']; ?>" required />
            </div>
        </div>
    </div>
    
    <div class="span4">
        <div class="control-group">
            <label class="control-label" for="login"><?php echo lang('users_edit_field_login');?></label>
            <div class="controls">
                <input readonly="value" type="text" name="login" value="<?php echo $users_item['login']; ?>" required />
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="span4">
        <div class="control-group">
            <label class="control-label" for="email"><?php echo lang('users_edit_field_email');?></label>
            <div class="controls">
                <input readonly="value" type="email" id="email" name="email" value="<?php echo $users_item['email']; ?>" required />
            </div>
        </div>
    </div>

    <div class="span4">
        <div class="control-group">
            <label class="control-label" for="manager"><?php echo lang('users_edit_field_manager');?></label>
            <div class="controls">
                <input readonly="value" type="manager" id="manager" name="manager" value="<?php echo $manager_label; ?>" required />
            </div>
        </div>
    </div>

    <div class="span4">
        <div class="control-group">
            <label class="control-label" for="role"><?php echo lang('users_edit_field_role');?></label>
            <div class="controls">
                <input readonly="value" type="text" id="role" name="role" value="<?php echo $role?>" required />
            </div>
        </div>
    </div>

</div>

<div class="row">

     <div class="span4">
        <div class="control-group">
            <label class="control-label" for="contract"><?php echo lang('users_edit_field_contract');?></label>
            <div class="controls">
                <input readonly="value" type="text" id="contract" name="contract" value="<?php echo $contract?>" required />
            </div>
        </div>
    </div>

    <div class="span4">
        <div class="control-group">
            <label class="control-label" for="group"><?php echo lang('users_edit_field_entity');?></label>
            <div class="controls">
                <input readonly="value" type="text" id="group" name="group" value="<?php echo $organization_label; ?>" required />
            </div>
        </div>
    </div>

    <div class="span4">
        <div class="control-group">
            <label class="control-label" for="datehired"><?php echo lang('users_edit_field_hired');?></label>
            <div class="controls">
                <input readonly="value" type="text" id="datehired" name="datehired" value="<?php 
                    $date = new DateTime($users_item['datehired']);
                    echo $date->format(lang('global_date_format'));?>" required />
            </div>
        </div>
    </div>

    <div class="span4">
     <div class="control-group">
            <label class="control-label" for="identifier"><?php echo lang('users_edit_field_identifier');?></label>
            <div class="controls">
                <input readonly="value" type="text" id="identifier" name="identifier" value="<?php echo $users_item['identifier']; ?>" />
            </div>
     </div>
    </div>

    <div class="span4">
        <div class="control-group">
            <label class="control-label" for="language"><?php echo lang('users_edit_field_language');?></label>
            <div class="controls">
                <input readonly="value" type="text" id="language" name="language" value="<?php echo $users_item['language']; ?>" />
            </div>
        </div>
    </div>

    <div class="span4">
        <div class="control-group">
            <label class="control-label" for="timezone"><?php echo lang('users_edit_field_timezone');?></label>
            <div class="controls">
                <input readonly="value" type="text" id="timezone" name="timezone" value="<?php echo $users_item['timezone']; ?>" />
            </div>
        </div>
    </div>

    <div class="span4">
        &nbsp;
    </div>
</div>
    
<hr />

<table cellpadding="1" cellspacing="1" border="1" class="display" id="leaves" width="100%">
    <thead>
        <tr>
            <th><?php echo lang('users_edit_field_firstname');?></th>
            <th><?php echo lang('users_edit_field_lastname');?></th>
            <th><?php echo lang('users_edit_field_position');?></th>
            <th><?php echo lang('users_edit_field_raise');?></th>
        </tr>
    </thead>
    <tbody>
       <?php foreach ($users_history as $record): ?>
          <tr>
             <td align='center' valign='middle'><?php  echo $users_item['firstname']; ?></td>
             <td align='center' valign='middle'><?php echo $users_item['lastname']; ?></td>
             <td align='center' valign='middle'><?php echo $record['position_name'] ; ?></td>
             <td align='center' valign='middle'><?php echo $record['change_date'];?></td>
          </tr>
       <?php endforeach ?>
    </tbody>
</table>
</form>
<input action="action" onclick="window.history.go(-1); return false;" type="button" class="btn btn-primary" value=<?php echo lang('users_edit_previous');?> />