<?php
$this->Html->addCrumb (__('Home', true));
$this->Html->addCrumb ($this->Session->read('Zuluru.Person.full_name'));
?>

<div class="all splash">
<?php echo $this->Html->tag('h2', $this->Session->read('Zuluru.Person.full_name')); ?>

<?php
// We already have a lot of the information we need, stored from when we built the menu
$teams = $this->Session->read('Zuluru.Teams');
$divisions = $this->Session->read('Zuluru.Divisions');
$unpaid = $this->Session->read('Zuluru.Unpaid');
$past_teams = $this->requestAction(array('controller' => 'teams', 'action' => 'past_count'));
if (Configure::read('feature.affiliates')) {
	$affiliates = $this->requestAction(array('controller' => 'affiliates', 'action' => 'index'));
	AppModel::_reindexOuter($affiliates, 'Affiliate', 'id');
}
?>

<div id="kick_start">
<?php
if ($is_admin) {
	echo $this->element('version_check');

	if (Configure::read('feature.affiliates')) {
		if (empty($affiliates)) {
			echo $this->Html->para('warning-message', __('You have enabled the affiliate option, but have not yet created any affiliates. ', true) .
				$this->Html->link(__('Create one now!', true), array('controller' => 'affiliates', 'action' => 'add', 'return' => true)));
		} else {
			$unmanaged = $this->requestAction(array('controller' => 'affiliates', 'action' => 'unmanaged'));
			if (!empty($unmanaged)):
?>
<p class="warning-message">The following affiliates do not yet have managers assigned to them:</p>
<table class="list">
<tr>
	<th><?php __('Affiliate'); ?></th>
	<th><?php __('Actions'); ?></th>
</tr>
<?php
				$i = 0;
				foreach ($unmanaged as $affiliate):
					$class = null;
					if ($i++ % 2 == 0) {
						$class = ' class="altrow"';
					}
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php echo $affiliate['Affiliate']['name']; ?></td>
		<td class="actions">
			<?php
					echo $this->ZuluruHtml->iconLink('edit_24.png',
						array('controller' => 'affiliates', 'action' => 'edit', 'affiliate' => $affiliate['Affiliate']['id'], 'return' => true),
						array('alt' => __('Edit', true), 'title' => __('Edit', true)));
					echo $this->ZuluruHtml->iconLink('coordinator_add_24.png',
						array('controller' => 'affiliates', 'action' => 'add_manager', 'affiliate' => $affiliate['Affiliate']['id'], 'return' => true),
						array('alt' => __('Add Manager', true), 'title' => __('Add Manager', true)));
					echo $this->ZuluruHtml->iconLink('delete_24.png',
						array('controller' => 'affiliates', 'action' => 'delete', 'affiliate' => $affiliate['Affiliate']['id'], 'return' => true),
						array('alt' => __('Delete', true), 'title' => __('Delete', true)),
						array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $affiliate['Affiliate']['id'])));
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<?php
			endif;
		}
	}
}

if ($is_manager) {
	$my_affiliates = $this->Session->read('Zuluru.ManagedAffiliates');
	if (!empty($my_affiliates)) {
		$facilities = $this->requestAction(array('controller' => 'facilities', 'action' => 'index'));
		$facilities = Set::extract('/Facility[id>0]', $facilities);
		if (empty($facilities)) {
			echo $this->Html->para('warning-message', __('You have no open facilities. ', true) .
				$this->Html->link(__('Create one now!', true), array('controller' => 'facilities', 'action' => 'add', 'return' => true)));
		} else {
			// Eliminate any open facilities that have fields, and check if there's anything left that we need to warn about
			foreach ($facilities as $key => $facility) {
				if (!empty($facility['Facility']['Field'])) {
					unset($facilities[$key]);
				}
			}
			if (!empty($facilities)):
?>
<p class="warning-message">The following facilities do not yet have <?php echo Configure::read('ui.fields'); ?>:</p>
<table class="list">
<tr>
	<th><?php __('Facility'); ?></th>
	<th><?php __('Actions'); ?></th>
</tr>
<?php
				$i = 0;
				foreach ($facilities as $facility):
					$class = null;
					if ($i++ % 2 == 0) {
						$class = ' class="altrow"';
					}
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php echo $facility['Facility']['name']; ?></td>
		<td class="actions">
			<?php
					echo $this->ZuluruHtml->iconLink('edit_24.png',
						array('controller' => 'facilities', 'action' => 'edit', 'facility' => $facility['Facility']['id'], 'return' => true),
						array('alt' => __('Edit', true), 'title' => __('Edit Facility', true)));
					echo $this->ZuluruHtml->iconLink('add_24.png',
						array('controller' => 'fields', 'action' => 'add', 'facility' => $facility['Facility']['id'], 'return' => true),
						array('alt' => sprintf(__('Add %s', true), __(Configure::read('ui.field'), true)), 'title' => sprintf(__('Add %s', true), __(Configure::read('ui.field'), true))));
					echo $this->ZuluruHtml->iconLink('delete_24.png',
						array('controller' => 'facilities', 'action' => 'delete', 'facility' => $facility['Facility']['id'], 'return' => true),
						array('alt' => __('Delete', true), 'title' => __('Delete Facility', true)),
						array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $facility['Facility']['id'])));
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<?php
			endif;
		}

		$leagues = $this->requestAction(array('controller' => 'leagues', 'action' => 'index'));
		if (empty($leagues)) {
			echo $this->Html->para('warning-message', __('You have no current or upcoming leagues. ', true) .
				$this->Html->link(__('Create one now!', true), array('controller' => 'leagues', 'action' => 'add', 'return' => true)));
		} else {
			// Eliminate any open leagues that have divisions, and check if there's anything left that we need to warn about
			foreach ($leagues as $key => $league) {
				if (!empty($league['Division'])) {
					unset($leagues[$key]);
				}
			}
			if (!empty($leagues)):
?>
<p class="warning-message">The following leagues do not yet have divisions:</p>
<table class="list">
<tr>
	<th><?php __('League'); ?></th>
	<th><?php __('Actions'); ?></th>
</tr>
<?php
				$i = 0;
				foreach ($leagues as $league):
					$class = null;
					if ($i++ % 2 == 0) {
						$class = ' class="altrow"';
					}
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php echo $league['League']['full_name']; ?></td>
		<td class="actions">
			<?php
					echo $this->ZuluruHtml->iconLink('edit_24.png',
						array('controller' => 'leagues', 'action' => 'edit', 'league' => $league['League']['id'], 'return' => true),
						array('alt' => __('Edit', true), 'title' => __('Edit League', true)));
					echo $this->ZuluruHtml->iconLink('league_clone_24.png',
						array('controller' => 'leagues', 'action' => 'add', 'league' => $league['League']['id'], 'return' => true),
						array('alt' => __('Clone League', true), 'title' => __('Clone League', true)));
					echo $this->ZuluruHtml->iconLink('division_add_24.png',
						array('controller' => 'divisions', 'action' => 'add', 'league' => $league['League']['id'], 'return' => true),
						array('alt' => __('Add Division', true), 'title' => __('Add Division', true)));
					echo $this->ZuluruHtml->iconLink('delete_24.png',
						array('controller' => 'leagues', 'action' => 'delete', 'league' => $league['League']['id'], 'return' => true),
						array('alt' => __('Delete', true), 'title' => __('Delete League', true)),
						array('confirm' => sprintf(__('Are you sure you want to delete # %s?', true), $league['League']['id'])));
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<?php
			endif;
		}

		$events = $this->requestAction(array('controller' => 'events', 'action' => 'index'));
		if (empty($events)) {
			echo $this->Html->para('warning-message', __('You have no current or upcoming registration events. ', true) .
				$this->Html->link(__('Create one now!', true), array('controller' => 'events', 'action' => 'add', 'return' => true)));
		}
	}
} else {
	// If the user has nothing going on, pull some more details to allow us to help them get started
	if (empty($teams) && empty($divisions) && empty($unpaid)) {
		$membership_events = $this->requestAction(array('controller' => 'events', 'action' => 'count'), array('pass' => array(true)));
		$non_membership_events = $this->requestAction(array('controller' => 'events', 'action' => 'count'));
		$open_teams = $this->requestAction(array('controller' => 'teams', 'action' => 'open_count'));
		$leagues = $this->requestAction(array('controller' => 'leagues', 'action' => 'index'));

?>
<h3><?php __('You are not yet on any teams.'); ?></h3>
<?php
		$options = array();
		if ($membership_events) {
			$options[] = 'membership';
		}
		if ($non_membership_events) {
			$options[] = 'an event';
		}

		$actions = array();
		if (!empty($options)) {
			$actions[] = $this->Html->link ('Register for ' . implode(' or ', $options), array('controller' => 'events', 'action' => 'wizard'));
		}

		if ($open_teams) {
			$actions[] = $this->Html->link ('Join an existing team', array('controller' => 'teams', 'action' => 'join'));
		}

		if (!empty($leagues)) {
			$actions[] = $this->Html->link ('Check out the leagues we are currently offering', array('controller' => 'leagues'));
		}

		if (!empty($actions)) {
			echo $this->Html->tag('div', $this->Html->nestedList($actions), array('class' => 'actions'));
		}
	}
}
?>
</div>

<?php
if (!empty($unpaid)) {
	echo $this->Html->para (null, sprintf (__('You currently have %s unpaid %s. %s to complete these registrations.', true),
			count($unpaid),
			__(count($unpaid) > 1 ? 'registrations' : 'registration', true),
			$this->Html->link (__('Click here', true), array('controller' => 'registrations', 'action' => 'checkout'))
	));
}
?>

<?php if (!empty($teams) || $past_teams > 0): ?>
<table class="list">
<tr>
	<th colspan="2"><?php
	__('My Teams');
	echo $this->ZuluruHtml->help(array('action' => 'teams', 'my_teams'));
	?></th>
</tr>
<?php
$i = 0;
foreach ($teams as $team):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php
		echo $this->element('teams/block', array('team' => $team['Team'])) .
				' (' . $this->Html->link ($team['Division']['league_name'], array('controller' => 'divisions', 'action' => 'view', 'division' => $team['Division']['id'])) . ')' .
				' (' . $this->element('people/roster', array('roster' => $team['TeamsPerson'], 'division' => $team['Division'])) . ')';
		?></td>
		<td class="actions splash_action">
			<?php
			$is_captain = in_array($team['Team']['id'], $this->Session->read('Zuluru.OwnedTeamIDs'));
			echo $this->element('teams/actions', array('team' => $team['Team'], 'division' => $team['Division'], 'league' => $team['Division']['League'], 'is_captain' => $is_captain, 'format' => 'links'));
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<?php if ($past_teams > 0): ?>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('Show Team History', true), array('controller' => 'people', 'action' => 'teams')); ?> </li>
	</ul>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if (!empty ($divisions)) : ?>
<table class="list">
<tr>
	<th colspan="2"><?php __('Divisions Coordinated');?></th>
</tr>
<?php
$i = 0;
foreach ($divisions as $division):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php
			echo $this->ZuluruHtml->link($division['Division']['long_league_name'],
					array('controller' => 'divisions', 'action' => 'view', 'division' => $division['Division']['id']));
		?></td>
		<td class="actions splash_action">
			<?php
			if ($division['Division']['schedule_type'] != 'none') {
				echo $this->ZuluruHtml->iconLink('schedule_24.png',
					array('controller' => 'divisions', 'action' => 'schedule', 'division' => $division['Division']['id']),
					array('alt' => __('Schedule', true), 'title' => __('View Division Schedule', true)));
				echo $this->ZuluruHtml->iconLink('standings_24.png',
					array('controller' => 'divisions', 'action' => 'standings', 'division' => $division['Division']['id']),
					array('alt' => __('Standings', true), 'title' => __('View Division Standings', true)));
				echo $this->ZuluruHtml->iconLink('score_approve_24.png',
					array('controller' => 'divisions', 'action' => 'approve_scores', 'division' => $division['Division']['id']),
					array('alt' => __('Approve scores', true), 'title' => __('Approve scores', true)));
				echo $this->ZuluruHtml->iconLink('schedule_add_24.png',
					array('controller' => 'schedules', 'action' => 'add', 'division' => $division['Division']['id']),
					array('alt' => __('Add games', true), 'title' => __('Add games', true)));
			}
			echo $this->ZuluruHtml->iconLink('edit_24.png',
				array('controller' => 'divisions', 'action' => 'edit', 'division' => $division['Division']['id']),
				array('alt' => __('Edit', true), 'title' => __('Edit Division', true)));
			?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<?php
$games = array_merge ($this->requestAction(array('controller' => 'games', 'action' => 'past')), $this->requestAction(array('controller' => 'games', 'action' => 'future')));
if (!empty($games)):
?>
<table class="list">
<tr>
	<th colspan="3"><?php
	__('Recent and Upcoming Games');
	echo $this->ZuluruHtml->help(array('action' => 'games', 'recent_and_upcoming'));
	?></th>
</tr>
<?php
$i = 0;
foreach ($games as $game):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td class="splash_item"><?php
			$time = $this->ZuluruTime->day($game['GameSlot']['game_date']) . ', ' .
					$this->ZuluruTime->time($game['GameSlot']['game_start']) . '-' .
					$this->ZuluruTime->time($game['GameSlot']['display_game_end']);
			echo $this->Html->link($time, array('controller' => 'games', 'action' => 'view', 'game' => $game['Game']['id']));
		?></td>
		<td class="splash_item"><?php
			Game::_readDependencies($game['Game']);
			if ($game['Game']['home_team'] === null) {
				echo $game['Game']['home_dependency'];
			} else {
				echo $this->element('teams/block', array('team' => $game['HomeTeam'], 'options' => array('max_length' => 16))) .
					' (' . __('home', true) . ')';
			}
			__(' vs. ');
			if ($game['Game']['away_team'] === null) {
				echo $game['Game']['away_dependency'];
			} else {
				echo $this->element('teams/block', array('team' => $game['AwayTeam'], 'options' => array('max_length' => 16))) .
					' (' . __('away', true) . ')';
			}
			__(' at ');
			echo $this->element('fields/block', array('field' => $game['GameSlot']['Field']));
		?></td>
		<td class="actions splash_action"><?php
		if (in_array ($game['HomeTeam']['id'], $this->Session->read('Zuluru.TeamIDs'))) {
			$team = $game['HomeTeam'];
		} else {
			$team = $game['AwayTeam'];
		}
		if ($team['track_attendance']) {
			$position = Set::extract("/TeamsPerson[team_id={$team['id']}]/position", $teams);
			echo $this->element('games/attendance_change', array(
				'team' => $team,
				'game_id' => $game['Game']['id'],
				'game_date' => $game['GameSlot']['game_date'],
				'game_time' => $game['GameSlot']['game_start'],
				'position' => $position[0],
				'status' => (array_key_exists (0, $game['Attendance']) ? $game['Attendance'][0]['status'] : ATTENDANCE_UNKNOWN),
				'comment' => (array_key_exists (0, $game['Attendance']) ? $game['Attendance'][0]['comment'] : null),
				'future_only' => true,
			));
			if ($game['GameSlot']['game_date'] >= date('Y-m-d')) {
				echo $this->ZuluruHtml->iconLink('attendance_24.png',
					array('controller' => 'games', 'action' => 'attendance', 'team' => $team['id'], 'game' => $game['Game']['id']),
					array('alt' => __('Attendance', true), 'title' => __('View Game Attendance Report', true)));
			}
		}

		echo $this->ZuluruGame->displayScore ($game, $game['Division']['League']);

		if (Configure::read('feature.annotations')) {
			echo $this->Html->link(__('Add Note', true), array('controller' => 'games', 'action' => 'note', 'game' => $game['Game']['id']));
		}
		?></td>
	</tr>
<?php endforeach; ?>
</table>

<p><?php
if (Configure::read('personal.enable_ical')) {
	$id = $this->requestAction(array('controller' => 'users', 'action' => 'id'));
	__('Get your personal schedule in ');
	// TODOIMG: Better image locations, alt text
	echo $this->ZuluruHtml->iconLink ('ical.gif',
		array('controller' => 'people', 'action' => 'ical', $id, 'player.ics'),
		array('alt' => 'iCal'));
	__(' format or ');
	echo $this->ZuluruHtml->imageLink ('http://www.google.com/calendar/images/ext/gc_button6.gif',
		'http://www.google.com/calendar/render?cid=' . $this->Html->url(array('controller' => 'people', 'action' => 'ical', $id), true),
		array('alt' => 'add to Google Calendar'),
		array('target' => 'google'));
} else {
	echo $this->Html->link (__('Edit your preferences', true), array('controller' => 'people', 'action' => 'preferences'));
	__(' to enable your personal iCal feed');
}
?>. <?php echo $this->ZuluruHtml->help(array('action' => 'games', 'personal_feed')); ?></p>
<?php endif; ?>

<?php if (Configure::read('feature.affiliates') && count($affiliates) > 1): ?>
<div id="affiliate_select">
<?php
	if ($this->Session->check('Zuluru.CurrentAffiliate')) {
		echo $this->Html->para(null, sprintf(__('You are currently browsing the %s affiliate. You might want to %s or %s.', true),
			$affiliates[$this->Session->read('Zuluru.CurrentAffiliate')]['Affiliate']['name'],
			$this->Html->link(__('remove this restriction', true), array('controller' => 'affiliates', 'action' => 'view_all')),
			$this->Html->link(__('select a different affiliate to view', true), array('controller' => 'affiliates', 'action' => 'select'))));
	} else if (count($this->Session->read('Zuluru.AffiliateIDs')) != count($affiliates)) {
		if ($is_admin) {
			echo $this->Html->para(null, sprintf(__('This site has multiple affiliates. You might want to %s.', true),
				$this->Html->link(__('select a specific affiliate to view', true), array('controller' => 'affiliates', 'action' => 'select'))));
		} else if (Configure::read('feature.multiple_affiliates')) {
			echo $this->Html->para(null, sprintf(__('This site has affiliates that you are not a member of. You might want to %s or %s.', true),
				$this->Html->link(__('join other affiliates', true), array('controller' => 'people', 'action' => 'edit')),
				$this->Html->link(__('select a specific affiliate to view', true), array('controller' => 'affiliates', 'action' => 'select'))));
		} else {
			echo $this->Html->para(null, sprintf(__('This site has affiliates that you are not a member of. You might want to %s or %s.', true),
				$this->Html->link(__('change which affiliate you are a member of', true), array('controller' => 'people', 'action' => 'edit')),
				$this->Html->link(__('select a specific affiliate to view', true), array('controller' => 'affiliates', 'action' => 'select'))));
		}
	} else {
		echo $this->Html->para(null, sprintf(__('You are a member of all affiliates on this site. You might want to %s or %s.', true),
			$this->Html->link(__('reduce your affiliations', true), array('controller' => 'people', 'action' => 'edit')),
			$this->Html->link(__('select a specific affiliate to view', true), array('controller' => 'affiliates', 'action' => 'select'))));
	}
?>
</div>
<?php endif; ?>

</div>

<?php echo $this->element('games/attendance_div'); ?>
