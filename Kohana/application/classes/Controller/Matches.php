<?php  defined('SYSPATH') or die('No direct script access.');

class Controller_Matches extends Controller_DotaTrack {

	public function before()
	{
		// Get the match data
		parent::before();
	}

	public function action_index()
	{
        $view = View::Factory('matches/index');
		
		$session = Session::instance();
		
		$view->output = "UserID: " . $session->get('userId');
		
        $generated_view = $view->render();

		$this->add_header();
        $this->add_view_content($generated_view);
	}
}

?>
