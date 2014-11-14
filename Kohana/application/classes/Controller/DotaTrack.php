<?php  defined('SYSPATH') or die('No direct script access.');

class Controller_DotaTrack extends Controller {
	public $template;

	protected function add_header()
	{
		$header = View::Factory('mainpage_header');

		$header->playerName = Session::instance()->get('playerName', 'No Name');

		$header = $header->render();

		$this->add_view_content($header);
	}

	/**
	 * Adds the given content to the body of the template.
	 *
	 * @param $content A string containing the content to add to the template body.
	 */
	protected function add_view_content($content)
	{
		if(isset($this->template))
		{
			$this->template->body .= $content;
		}
		else
		{
			$this->template = View::factory('dotatrack_template');
			$this->template->body = $content;
		}
	}

	protected function render_template()
	{
		$rendered = $this->template->render();

		$this->response->body($rendered);
	}

	protected function add_javascript($key, $value)
	{
		$javascriptVars = Session::instance()->get('javascriptVariables', array());

		$javascriptVars[$key] = $value;

		Session::instance()->set('javascriptVariables', $javascriptVars);
	}

	protected function write_javascript()
	{
		$javascriptVars = Session::instance()->get('javascriptVariables', array());
		$this->javascript = "var server = ".json_encode($javascriptVars).";";
	}

	/**
	 * Takes the id's present in the match data and translates it into human
	 * readable junk.
	 *
	 * @param $matchData Contains a whole bunch of relatively confusing ids for
	 * the match data. The data structure is equivalent to what get_match_data
	 * will return for most models.
	 *
	 * @return A revised $matchData containing human readable strings.
	 */
	protected function nicify_match_data($matchData)
	{
		$db = Model::Factory('DotaTrackDatabase');
		
		if ($matchData['result'] === 0) {
			$matchData['result'] = "Radiant Victory";
		}
		else {
			$matchData['result'] = "Dire Victory";
		}
		
		// Nicify Game Mode
		$matchData['gameMode'] = $db->get_mode_data($matchData['gameMode'])['name'];
		
		// Nicify Match Type
		$matchData['matchType'] = $db->get_lobby_data($matchData['matchType'])['name'];
		
		// Nicify Date
		$matchData['date'] = date("j M Y G:i:s", $matchData['date']);
		
		// Nicify Duration
		if ($matchData['duration'] >= 3600) {
			$matchData['duration'] = gmdate("H:i:s", $matchData['duration']);
		}
		else {
			$matchData['duration'] = gmdate("i:s", $matchData['duration']);
		}
		
		// Nicify Performance
		foreach ($matchData['playerPerformance'] as $key => $value) {
			// Nicify Items
			$matchData['playerPerformance'][$key]['item0'] = $db->get_item_data($value['item0'])['name'];
			$matchData['playerPerformance'][$key]['item1'] = $db->get_item_data($value['item1'])['name'];
			$matchData['playerPerformance'][$key]['item2'] = $db->get_item_data($value['item2'])['name'];
			$matchData['playerPerformance'][$key]['item3'] = $db->get_item_data($value['item3'])['name'];
			$matchData['playerPerformance'][$key]['item4'] = $db->get_item_data($value['item4'])['name'];
			$matchData['playerPerformance'][$key]['item5'] = $db->get_item_data($value['item5'])['name'];
			
			// $key['item1'] = $db->get_item_data($key['item1'])['name'];
			// $key['item2'] = $db->get_item_data($key['item2'])['name'];
			// $key['item3'] = $db->get_item_data($key['item3'])['name'];
			// $key['item4'] = $db->get_item_data($key['item4'])['name'];
			// $key['item5'] = $db->get_item_data($key['item5'])['name'];
			
			// Nicify Heroes
			$matchData['playerPerformance'][$key]['hero'] = $db->get_hero_data($value['hero'])['heroName'];
		}
		
		return $matchData;
	}
}

?>
