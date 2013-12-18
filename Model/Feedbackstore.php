<?php
class Feedbackstore extends AppModel {

	public $name = 'Feedbackstore';

	public $useTable = false;

	/*
	Store functions for different save methods
	 */
	public function filesystem($feedbackObject = null){

		if(empty($feedbackObject)){
			return false;
		}

		//Get save path from config
		$savepath = Configure::read('FeedbackIt.methods.filesystem.location');

		//Serialize and save the object to a store in the Cake's tmp dir.
		if( ! file_exists($savepath ) ){
			if( ! mkdir($savepath) ){
				//Throw error, directory is requird
				throw new NotFoundException( __('Could not create directory to save feedbacks in. Please provide write rights to webserver user on directory: ').$savepath  );
			}
		}

		//Save serialized with timestamp + randnumber as filename
		$filename = time() . '-' . rand(1000,9999).'.feedback';

		//Add filename to data
		$feedbackObject['filename'] = $filename;

		if(file_put_contents($savepath.$filename, serialize($feedbackObject))){
			return true;
		}

		return false;
	}

	/*
	Mantis store function
	 */
	public function mantis($feedbackObject = null){

		if(empty($feedbackObject)){
			return false;
		}

		$api_url	= Configure::read('FeedbackIt.methods.mantis.api_url');
		$username	= Configure::read('FeedbackIt.methods.mantis.username');
		$password	= Configure::read('FeedbackIt.methods.mantis.password');
		$project_id	= Configure::read('FeedbackIt.methods.mantis.project_id');
		$category	= Configure::read('FeedbackIt.methods.mantis.category');

		//Read mantis instance url from configfile
		$c = new SoapClient($api_url);
	   
	    //Create new issue
	    $issue = array ( 
	                    'summary' => $feedbackObject['subject'], 
	                    'description' => $feedbackObject['feedback'], 
	                    'project'=>array('id'=>$project_id	), 
	                    'category'=>$category
	                    );
	    if( $issueid = $c->mc_issue_add($username, $password, $issue) ){
	    	//Add screenshot to issue (Do not send as base64 despite what de WSDL says)
	   		return ($c->mc_issue_attachment_add( $username,  $password, $issueid, date('d-m-Y_H-i-s').'.png', 'image/png', base64_decode($feedbackObject['screenshot'])));
	    }

	    return false;
	}
}