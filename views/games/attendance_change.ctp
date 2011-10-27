<?php
$this->Html->addCrumb (__('Games', true));
$this->Html->addCrumb (__('Attendance Change', true));
$this->Html->addCrumb ($team['name']);
?>

<div class="games form">
<h2><?php  __('Attendance Change'); ?></h2>
	<dl>
		<dt><?php __('Team'); ?></dt>
		<dd><?php echo $this->element('team/block', array('team' => $team)); ?></dd>
		<dt><?php __('Game Date'); ?></dt>
		<dd><?php
		if (isset ($game)) {
			echo $this->ZuluruTime->date($game['GameSlot']['game_date']);
		} else {
			echo $this->ZuluruTime->date($date);
		}
		?></dd>
		<dt><?php __('Game Time'); ?></dt>
		<dd><?php
		if (isset ($game)) {
			echo $this->ZuluruTime->time($game['GameSlot']['game_start']);
		} else {
			__('TBD');
		}
		?></dd>
		<dt><?php __('Opponent'); ?></dt>
		<dd><?php
		if (isset ($opponent)) {
			echo $this->element('team/block', array('team' => $opponent));
		} else {
			__('TBD');
		}
		?></dd>
	</dl>

<?php
$status_descriptions = Configure::read('attendance');
$roster_descriptions = Configure::read('options.roster_position');
echo $this->Html->para(null, __('You are attempting to change attendance for', true) . ' ' .
	$this->Html->link($person['full_name'], array('controller' => 'people', 'action' => 'view', 'person' => $person['id'])) .
	' (' . $roster_descriptions[$person['Team'][0]['TeamsPerson']['position']] . ').');
echo $this->Html->para(null, __('Current status:', true) . ' ' .
	$this->Html->tag('strong', __($status_descriptions[$attendance['status']], true)));

echo $this->Html->para(null, __('Possible attendance options are:', true));
echo $this->Form->create('Person', array('url' => $this->here));
echo $this->Form->input('status', array(
		'legend' => false,
		'type' => 'radio',
		'name' => 'status',
		'options' => $attendance_options,
		'default' => $attendance['status'],
));
echo $this->Form->input('comment', array(
		'label' => __('You may optionally add a comment', true),
		'size' => 80,
		'default' => $attendance['comment'],
));
if ($is_captain && array_key_exists(ATTENDANCE_INVITED, $attendance_options)) {
	echo $this->Form->input('note', array(
			'label' => __('You may optionally add a personal note which will be included in the invitation email to the player', true),
			'size' => 80,
	));
}
echo $this->Form->end(__('Submit', true));

$invited = ATTENDANCE_INVITED;
echo $this->Html->scriptBlock("
function statusChanged() {
	if ($('input:radio[name=status]:checked').val() == $invited) {
		$('#PersonNote').closest('div').show();
	} else {
		$('#PersonNote').closest('div').hide();
	}
}
");
$this->Js->get('input:radio[name=status]')->event('click', 'statusChanged()', array('stop' => false));
$this->Js->buffer('statusChanged();');

?>
