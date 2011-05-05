<?php
if ($team['track_attendance']) {
	$long = Configure::read("attendance.$status");
	$low = Inflector::slug(low($long));

	$short = $this->ZuluruHtml->icon("attendance_{$low}_24.png", array(
			'title' => sprintf (__('Current attendance: %s', true), __($long, true)),
			'alt' => Configure::read("attendance_alt.$status"),
	));

	if (!isset($future_only)) {
		$future_only = false;
	}
	if (!isset($game_time)) {
		$game_time = '00:00:00';
	}

	$recent = ($game_date >= date('Y-m-d', time() - 14 * 24 * 60 * 60));
	$future = ("$game_date $game_time" >= date('Y-m-d H:i:s'));
	$is_me = (!isset($person_id) || $person_id == $my_id);
	if (($future || (!$future_only && $recent)) && ($is_me || $is_captain)) {
		$url = array('controller' => 'games', 'action' => 'attendance_change', 'team' => $team['id']);
		if (isset ($game_id) && $game_id) {
			$url['game'] = $game_id;
		} else {
			$url['date'] = $game_date;
		}

		if (!$is_me) {
			$url['person'] = $person_id;
		}

		$options = Game::_attendanceOptions($team['id'], $position, $status, !$future);
		$option_strings = array();
		foreach ($options as $key => $value) {
			$option_strings[] = "$key: '$value'";
		}
		$option_string = '{' . implode(', ', $option_strings) . '}';
		$url_string = Router::url($url);
		echo $this->Html->link($short, $url, array(
			'escape' => false,
			'onClick' => "return attendance_status('$url_string', $status, $option_string, $(this));",
		));
	} else if (!$future_only) {
		echo $short;
	}
}

?>