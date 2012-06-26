<?php
/**
 * Base class for ratings calculators.
 */

class RatingsComponent extends Object
{
	var $per_game_ratings = true;

	/**
	 * These settings are only used if per_game_ratings is false.
	 * These defaults are good for the majority of such calulators.
	 */
	var $iterative = true;
	var $iterations = 20;

	/**
	 * By default, we don't track ratings.
	 */
	function calculateRatingsChange($home_score, $away_score, $expected_win) {
		return 0;
	}

	/**
	 * Calculate the expected win ratio.  Answer
	 * is always 0 <= x <= 1
	 */
	function calculateExpectedWin ($rating1, $rating2) {
		$difference = $rating1 - $rating2;
		$power = pow(10, (0 - $difference) / 400);
		return ( 1 / ($power + 1) );
	}

	function recalculateRatings(&$division, $correct) {
		if (!isset($this->game_obj)) {
			$this->game_obj = ClassRegistry::init('Game');
			$this->team_obj = ClassRegistry::init('Team');
		}

		// Do the actual calculations
		$this->_initializeRatings($division);
		if ($this->per_game_ratings) {
			$game_update_count = $this->_recalculateRatings($division, $correct);
		} else {
			$game_update_count = 0;
			if ($this->iterative) {
				for ($it = 0; $it < $this->iterations; ++ $it) {
					$this->_recalculateRatings($division);
				}
			} else {
				$this->_recalculateRatings($division);
			}
		}
		$this->_finalizeRatings($division);

		// Perhaps save any changes
		$team_updates = array();
		foreach ($division['Team'] as $key => $team) {
			if ($team['rating'] != $team['current_rating']) {
				$team_updates[] = array(
					'id' => $team['id'],
					'rating' => $team['current_rating'],
				);
			}
		}
		if ($correct && !empty($team_updates)) {
			$this->team_obj->saveAll ($team_updates);
		}

		return array(count($team_updates), $game_update_count);
	}

	/**
	 * Initialize for ratings recalculation.
	 */
	function _initializeRatings(&$division) {
		AppModel::_reindexOuter($division['Team'], 'Team', 'id');
	}

	/**
	 * Finalize ratings recalculation. Might be used to normalize ratings, for example.
	 */
	function _finalizeRatings(&$division) {
	}

	/**
	 * Recalculate ratings for all teams in a division. This default
	 * implementation works for all calculators where rating points are
	 * transferred on a per-game basis (i.e. per_game_ratings = true).
	 */
	function _recalculateRatings(&$division, $correct) {
		$moved_teams = array();
		$game_updates = array();

		foreach ($division['Game'] as $key => $game) {
			// Handle teams that have moved
			if (!array_key_exists ($game['Game']['home_team'], $division['Team'])) {
				$moved_teams[] = $game['Game']['home_team'];
				$division['Team'][$game['Game']['home_team']] = $game['HomeTeam'];
			}
			if (!array_key_exists ($game['Game']['away_team'], $division['Team'])) {
				$moved_teams[] = $game['Game']['away_team'];
				$division['Team'][$game['Game']['away_team']] = $game['AwayTeam'];
			}

			if (!array_key_exists ('current_rating', $division['Team'][$game['Game']['home_team']])) {
				$division['Team'][$game['Game']['home_team']]['current_rating'] = $division['Team'][$game['Game']['home_team']]['initial_rating'];
			}
			if (!array_key_exists ('current_rating', $division['Team'][$game['Game']['away_team']])) {
				$division['Team'][$game['Game']['away_team']]['current_rating'] = $division['Team'][$game['Game']['away_team']]['initial_rating'];
			}

            $division['Game'][$key]['Game']['calc_rating_home'] = $division['Team'][$game['Game']['home_team']]['current_rating'];
			$division['Game'][$key]['Game']['calc_rating_away'] = $division['Team'][$game['Game']['away_team']]['current_rating'];

			if ($this->game_obj->_is_finalized ($game) && $game['Game']['status'] != 'rescheduled') {
				if ($game['Game']['tournament']) {
					// Playoff games don't adjust ratings
					$change = 0;
				} else if (strpos($game['Game']['status'], 'default') !== false && !Configure::read('default_transfer_ratings')) {
					// Defaulted games might not adjust ratings
					$change = 0;
				} else if ($game['Game']['home_score'] >= $game['Game']['away_score']) {
					$division['Game'][$key]['Game']['expected'] = $this->calculateExpectedWin($division['Team'][$game['Game']['home_team']]['current_rating'], $division['Team'][$game['Game']['away_team']]['current_rating']);
					$change = $this->calculateRatingsChange($game['Game']['home_score'], $game['Game']['away_score'], $division['Game'][$key]['Game']['expected']);
					$division['Team'][$game['Game']['home_team']]['current_rating'] += $change;
					$division['Team'][$game['Game']['away_team']]['current_rating'] -= $change;
				} else {
					$division['Game'][$key]['Game']['expected'] = $this->calculateExpectedWin($division['Team'][$game['Game']['away_team']]['current_rating'], $division['Team'][$game['Game']['home_team']]['current_rating']);
					$change = $this->calculateRatingsChange($game['Game']['home_score'], $game['Game']['away_score'], $division['Game'][$key]['Game']['expected']);
					$division['Team'][$game['Game']['home_team']]['current_rating'] -= $change;
					$division['Team'][$game['Game']['away_team']]['current_rating'] += $change;
				}
				$division['Game'][$key]['Game']['calc_rating_points'] = $change;
			} else {
				$division['Game'][$key]['Game']['calc_rating_points'] = $division['Game'][$key]['Game']['expected'] = null;
			}

			// Only save updates for games that actually changed
			$update = array('id' => $game['Game']['id']);
			if ($division['Game'][$key]['Game']['calc_rating_points'] != $game['Game']['rating_points']) {
				$update['rating_points'] = $division['Game'][$key]['Game']['calc_rating_points'];
			}
			if (count($update) > 1) {
				$game_updates[] = $update;
			}
		}

		foreach ($division['Team'] as $key => $team) {
			if (!array_key_exists('current_rating', $team)) {
				$division['Team'][$key]['current_rating'] = $division['Team'][$key]['rating'];
			}
		}

		if ($correct && !empty ($game_updates)) {
			$this->game_obj->saveAll ($game_updates);
		}

		// Remove moved teams
		foreach ($moved_teams as $team) {
			unset ($division['Team'][$team]);
		}

		return count($game_updates);
	}
}

?>
