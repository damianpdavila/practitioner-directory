<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2022 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

if ($this->config->enable_avatar)
{
	$avatarExists = false;

	if ($this->item->avatar && file_exists(JPATH_ROOT . '/media/com_osmembership/avatars/' . $this->item->avatar))
	{
		$avatarExists = true;
	?>
		<div id="field_existing_profile_avatar" class="<?php echo $controlGroupClass; ?>">
			<div class="<?php echo $controlLabelClass; ?>">
				<?php echo Text::_('OSM_AVATAR'); ?>
			</div>
			<div class="<?php echo $controlsClass; ?>">
				<img class="oms-avatar" src="<?php echo Uri::base(true) . '/media/com_osmembership/avatars/' . $this->item->avatar; ?>"/>
                <div id="osm-delete-avatar-container">
                    <label class="checkbox">
                        <input type="checkbox" name="delete_avatar" value="1" />
                        <?php echo Text::_('OSM_DELETE_AVATAR'); ?>
                    </label>
                </div>
			</div>
		</div>
	<?php
	}
	?>
	<div id="field_upload_profile_avatar" class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo $avatarExists ? Text::_('OSM_NEW_AVATAR') : Text::_('OSM_AVATAR'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<input type="file" name="profile_avatar" accept="image/*">
		</div>
	</div>
	<?php
}

if ($this->item->user_id)
{
	$params = ComponentHelper::getParams('com_users');
	$validationRules = array();
	$minimumLength = $params->get('minimum_length', 4);

	if ($minimumLength)
	{
		$validationRules[] = "minSize[$minimumLength]";
	}

	$validationRules[] = 'ajax[ajaxValidatePassword]';

	if (count($validationRules))
	{
		$class = ' class="validate[' . implode(',', $validationRules) . ']' . $this->bootstrapHelper->getFrameworkClass('uk-input', 1) . '"';
	}
	else
	{
		$class = $this->bootstrapHelper->getFrameworkClass('uk-input', 3);
	}
	?>
	<div  id="field_username" class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('OSM_USERNAME'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<?php
				if ($params->get('change_login_name'))
				{
				?>
					<input type="text" name="username" id="username1" class="validate[required,minSize[2],ajax[ajaxUserCall]]<?php echo $this->bootstrapHelper->getFrameworkClass('uk-input', 1); ?> form-control" value="<?php echo $this->escape($this->input->post->getUsername('username', $this->item->username)); ?>" size="15" autocomplete="off"/>
				<?php
				}
				else
				{
					echo $this->item->username;
				}
			?>
		</div>
	</div>
	<div id="field_password" class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('OSM_PASSWORD'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<?php // Disables autocomplete ?> <input type="password" style="display:none">
			<input type="password" class="form-control" id="password" name="password" autocomplete="new-password" size="20" value=""<?php echo $class; ?> />
		</div>
	</div>
	<div id="field_password2" class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
            <?php echo  Text::_('OSM_RETYPE_PASSWORD') ?>
            <span class="required">*</span>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<input value="" class="form-control validate[equals[password]]<?php echo $this->bootstrapHelper->getFrameworkClass('uk-input', 1); ?>" type="password" name="password2" id="password2" />
		</div>
	</div>
	<?php
}
if ($this->item->membership_id)
{
?>
	<div id="field_membership_id" class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo Text::_('OSM_MEMBERSHIP_ID'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<?php echo OSMembershipHelper::formatMembershipId($this->item, $this->config); ?>
		</div>
	</div>
<?php
}


$fields = $this->form->getFields();

foreach ($fields as $field)
{
    if (!$field->row->show_on_user_profile)
    {
        continue;
    }

	/* @var MPFFormField $field*/
	if ($field->fee_field || !$field->row->can_edit_on_profile)
	{
		echo $field->getOutput(true, $bootstrapHelper);
	}
	else
	{
		echo $field->getControlGroup($bootstrapHelper);
	}
	// Move the "show on members list" field down to the Practitioner Directory section
	if ($field->name == 'publicprofileheading')
	{

		if (isset($this->lists['show_on_members_list']))
		{
		?>
			<div id="field_show_on_members_list_control" class="<?php echo $controlGroupClass; ?>">
				<div class="<?php echo $controlLabelClass; ?>">
					<?php echo Text::_('OSM_SHOW_ON_MEMBERS_LIST'); ?>
				</div>
				<div class="<?php echo $controlsClass; ?>">
					<?php echo $this->lists['show_on_members_list']; ?>
				</div>
			</div>
		<?php
		}
		
	}


}
?>
<div class="form-actions">
	<input type="submit" class="<?php echo $bootstrapHelper->getClassMapping('btn btn-primary'); ?>" value="<?php echo Text::_('OSM_UPDATE'); ?>"/>
    <input type="button" class="<?php echo $bootstrapHelper->getClassMapping('btn btn-primary'); ?>" value="<?php echo Text::_('OSM_LOGOUT'); ?>" onclick="document.osm_logout_form.submit();" />
</div>