<?php
App::uses('CakeEmail', 'Network/Email');

class Feedbackstore extends AppModel {

	public $name = 'Feedbackstore';

	public $useTable = false;

	public $returnobject = array();

	/*
	 * Store functions for different save methods
   	 */
	public function filesystem($feedbackObject = null){

		//Standard return value
		$returnobject['result'] = false;
		$returnobject['msg'] = '';

		if (empty($feedbackObject)){
			return $returnobject;
    	}

    	//Create filename based on timestamp and random number (to prevent collisions)
		$feedbackObject['filename'] = $this->generateFilename();

		if ( $this->saveFile($feedbackObject) ){
			
			$msg = __d('feedback_it','Thank you. Your feedback was saved.');

			if( Configure::read('FeedbackIt.returnlink') ){
				$msg .= ' ';
				$msg .= __d('feedback_it','View your feedback on: ');
				
				$url  = Router::url(array('plugin'=>'feedback_it','controller'=>'feedback','action'=>'index'),true);

				$msg .= '<a target="_blank" href="'.$url.'">'.$url.'</a>';
			}

			$returnobject['result'] = true;
			$returnobject['msg'] = $msg;		
    	}

    	return $returnobject;
	}

	/*
	Mantis store function
	 */
	public function mantis($feedbackObject = null){

		//Standard return value
		$returnobject['result'] = false;
		$returnobject['msg'] = '';

		if(empty($feedbackObject)){
			return $returnobject;
		}

		//Mandatory
		$api_url	= Configure::read('FeedbackIt.methods.mantis.api_url');
		$username	= Configure::read('FeedbackIt.methods.mantis.username');
		$password	= Configure::read('FeedbackIt.methods.mantis.password');
		$project_id	= Configure::read('FeedbackIt.methods.mantis.project_id');
		$category	= Configure::read('FeedbackIt.methods.mantis.category');
		$decodeimage= Configure::read('FeedbackIt.methods.mantis.decodeimage');

		//Optional HTTP credentials for bypassing Basic Auth or Kerberos
		$soap_options = array();

		if($http_username = Configure::read('FeedbackIt.methods.mantis.http_username') AND $http_password = Configure::read('FeedbackIt.methods.mantis.http_password') ){

			$soap_options = array(
				'login'          => $http_username,
				'password'       => $http_password,
				);
		} 

		//Uncomment to debug:
		$soap_options['cache_wsdl'] = WSDL_CACHE_NONE;

		//Create a SoapClient
		$c = new SoapClient($api_url,$soap_options);

		//Mantis specific: append browser, browser version and URL to feedback:
		$feedbackObject['feedback'] .= "\n\n";
		$feedbackObject['feedback'] .= sprintf("Browser: %s %s\n",$feedbackObject['browser'],$feedbackObject['browser_version']);
		$feedbackObject['feedback'] .= sprintf("Url: %s\n",$feedbackObject['url']);
        $feedbackObject['feedback'] .= sprintf("OS: %s\n",$feedbackObject['os']);
		$feedbackObject['feedback'] .= sprintf("By: %s",$feedbackObject['name']);

	    //Create new issue
		$issue = array ( 
			'summary' => $feedbackObject['subject'], 
			'description' => $feedbackObject['feedback'], 
			'project'=>array('id'=>$project_id), 
			'category'=>$category
			);

	    //Try to save the issue
		if( $issueid = $c->mc_issue_add($username, $password, $issue) ){

	    	//Decode image or not?
			if($decodeimage){
				$feedbackObject['screenshot'] = base64_decode($feedbackObject['screenshot']);
			}

	    	//Add screenshot to issue (Do not send as base64 despite what de WSDL says)
			if ( $c->mc_issue_attachment_add( $username,  $password, $issueid, date('d-m-Y_H-i-s').'.png', 'image/png', $feedbackObject['screenshot'] )){
				
				$msg = __d('feedback_it','Thank you. Your feedback was saved.');

				if( Configure::read('FeedbackIt.returnlink') ){
					$msg .= '<br />';
					$msg .= __d('feedback_it','View your feedback on: ');
					
					list($url,$tmp) = explode('api',$api_url );
					$url .= 'view.php?id=' . $issueid;

					$msg .= '<a target="_blank" href="'.$url.'">'.$url.'</a>';
				}

				$returnobject['result'] = true;
				$returnobject['msg'] = $msg;

			}
		}

		return $returnobject;
	}

	/*
	Mail function
	- Function has possibility to mail submitting user instead of target adress
	 */
	public function mail($feedbackObject = null,$copyreporter = false){

		//Standard return value
		$returnobject['result'] = false;
		$returnobject['msg'] = '';

		if(empty($feedbackObject)){
			return $returnobject;
		}

		//Read settings from config if not in copy mode
		$to	    = Configure::read('FeedbackIt.methods.mail.to');
		$from	= Configure::read('FeedbackIt.methods.mail.from');

        // Change recipient if sending a copy
        if($copyreporter){
            $to   = $feedbackObject['email'];
        }

        //Change the sender if any given
        if(!empty($feedbackObject['email']) AND !empty($feedbackObject['name'])){
            $from	= array($feedbackObject['email'] => $feedbackObject['name']);
        }

		//Tmp store the screenshot:
		$tmpfile = APP.'tmp'.DS.time().'_'.rand(1000,9999).'.png';
		if( ! file_put_contents($tmpfile, base64_decode($feedbackObject['screenshot'])) ){
			//Need to save tmp file
			throw new NotFoundException( __d('feedback_it','Could not save tmp file for attacment in mail') );
		}

		$email = new CakeEmail();
		$email->from($from);
		$email->to($to);
		$email->subject($feedbackObject['subject']);
		$email->emailFormat('html');
		$email->attachments(array(
		    'screenshot.png' => array(
		        'file' => $tmpfile,
		        'mimetype' => 'image/png',
		        'contentId' => 'id-screenshot'
		    )
		));

		//Mail specific: append browser, browser version, URL, etc to feedback :
        if($copyreporter){
            $feedbackObject['feedback'] = '<p>' . __d('feedback_it','A copy of your submitted feedback:') . '</p>' . $feedbackObject['feedback'];
        }
		$feedbackObject['feedback'] .= "<p>";
		$feedbackObject['feedback'] .= sprintf("Browser: %s %s<br />",$feedbackObject['browser'],$feedbackObject['browser_version']);
		$feedbackObject['feedback'] .= sprintf("Url: %s<br />",$feedbackObject['url']);
        $feedbackObject['feedback'] .= sprintf("OS: %s<br />",$feedbackObject['os']);
		$feedbackObject['feedback'] .= sprintf("By: %s<br />",$feedbackObject['name']);
        $feedbackObject['feedback'] .= "Screenshot: <br />";
		$feedbackObject['feedback'] .= "</p>";
		$feedbackObject['feedback'] .= '<img src="cid:id-screenshot">'; //Add inline screenshot

		if( $email->send($feedbackObject['feedback']) ){
			$returnobject['result'] = true;
			$returnobject['msg'] = __d('feedback_it','Thank you. Your feedback was saved.');

			return $returnobject;
		}

		unlink($tmpfile);

		return $returnobject;
	}

	/*
	Github API v3
	 */
	public function github($feedbackObject = null){

		//Standard return value
		$returnobject['result'] = false;
		$returnobject['msg'] = '';

		if(empty($feedbackObject)){
			return $returnobject;
		}

		//Read settings
		$api_url			= Configure::read('FeedbackIt.methods.github.api_url');
		$username			= Configure::read('FeedbackIt.methods.github.username');
		$password			= Configure::read('FeedbackIt.methods.github.password');
		$localimagestore 	= Configure::read('FeedbackIt.methods.github.localimagestore');

		//Mantis specific: append browser, browser version and URL to feedback:
		$feedbackObject['feedback'] .= "\n\n";
		$feedbackObject['feedback'] .= sprintf("**Browser**: %s %s\n\n",$feedbackObject['browser'],$feedbackObject['browser_version']);
		$feedbackObject['feedback'] .= sprintf("**Url**: %s\n\n",$feedbackObject['url']);
        $feedbackObject['feedback'] .= sprintf("**OS**: %s\n\n",$feedbackObject['os']);
		$feedbackObject['feedback'] .= sprintf("**By**: %s\n\n",$feedbackObject['name']);

		// WARNING: This may not work for sites with different domains (or dev environments)
    	//          If the given URL is not public, Github won't display the screenshot
		if ($localimagestore){
			//Create filename based on timestamp and random number (to prevent collisions)
			if( $imagename = $this->saveScreenshot($feedbackObject) ){
				$viewimageUrl  = Router::url("/feedback_it/img/screenshots/$imagename",true);

				$feedbackObject['feedback'] .= sprintf("**Screenshot**:\n![screenshot](%s)", $viewimageUrl);
			}
		}
		// Github still doesn't support this kind of image format in Markup Language
		// $content = '[screenshot]: data:image/png;base64,'. $feedbackObject['screenshot'] . " \n\n";

		//Prepare data 
		$data = array("title" => $feedbackObject['subject'], "body" => $feedbackObject['feedback']);
		$data_string = json_encode($data);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		curl_setopt($ch, CURLOPT_POST, true); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

		$result 		= curl_exec($ch);
		$curlstatuscode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if( ! $result){
			//Return curl error
			$returnobject['msg'] = curl_error($ch);

		}else if($curlstatuscode >= 400){
			//Return http error and message
			$message = json_decode($result);
			$returnobject['msg'] = trim($message->message); //Can contain linebreaks

		}else{
			//Set return value to true and return message
			$returnobject['result'] = true;
			$returnobject['msg'] = __d('feedback_it','Thank you. Your feedback was saved.');

		}

		return $returnobject;
	}

    /*
	Bitbucket API
	 */
	public function bitbucket($feedbackObject = null){

		//Standard return value
		$returnobject['result'] = false;
		$returnobject['msg'] = '';

		if (empty($feedbackObject)){
    		return false;
    	}

    	//Read settings
    	$api_url			= Configure::read('FeedbackIt.methods.bitbucket.api_url');
    	$username 			= Configure::read('FeedbackIt.methods.bitbucket.username');
    	$password 			= Configure::read('FeedbackIt.methods.bitbucket.password');
    	$localimagestore 	= Configure::read('FeedbackIt.methods.bitbucket.localimagestore');

		//Append browser, browser version and URL to feedback:
		$feedbackObject['feedback'] .= sprintf("**By**: %s\n\n", $feedbackObject['name']);
		$feedbackObject['feedback'] .= sprintf("**Browser**: %s %s\n\n", $feedbackObject['browser'], $feedbackObject['browser_version']);
        $feedbackObject['feedback'] .= sprintf("**OS**: %s\n\n",$feedbackObject['os']);
		$feedbackObject['feedback'] .= sprintf("**Url**: %s\n\n", $feedbackObject['url']);
    
		// WARNING: This may not work for sites with different domains (or dev environments)
    	//          If the given URL is not public, Bitbucket won't display the screenshot
		if ($localimagestore){
			//Create filename based on timestamp and random number (to prevent collisions)
			if( $imagename = $this->saveScreenshot($feedbackObject) ){
				$viewimageUrl  = Router::url("/feedback_it/img/screenshots/$imagename",true);

				$feedbackObject['feedback'] .= sprintf("**Screenshot**:\n![screenshot](%s)", $viewimageUrl);
			}
		}
		// Bitbucket still doesn't support this kind of image format in Markup Language
		// $content = '[screenshot]: data:image/png;base64,'. $feedbackObject['screenshot'] . " \n\n";

    	//Prepare data 
		$data = array("title" => $feedbackObject['subject'], "content" => $feedbackObject['feedback']);

		App::uses('HttpSocket', 'Network/Http');
		$HttpSocket = new HttpSocket(array('ssl_verify_peer' => false));
    	$HttpSocket->configAuth('Basic', $username, $password);
    	$result = $HttpSocket->post($api_url, $data);

    	// TODO: A better error management
    	if( ! $result){
			$returnobject['msg'] = $HttpSocket->lastError();
		}else{
			$returnobject['result'] = true;
			$returnobject['msg'] = __d('feedback_it','Thank you. Your feedback was saved.');
		}

		return $returnobject;
	}

	/*
   	 * Auxiliary function that saves the file 
     */
  	private function saveFile($feedbackObject = null){
    	//Get save path from config
    	$savepath = Configure::read('FeedbackIt.methods.filesystem.location');
    	//Serialize and save the object to a store in the Cake's tmp dir.
    	if (!file_exists($savepath)){
			if (!mkdir($savepath)){
				//Throw error, directory is requird
				throw new NotFoundException(__d('feedback_it','Could not create directory to save feedbacks in. Please provide write rights to webserver user on directory: ') . $savepath);
			}
		}

		if (file_put_contents($savepath . $feedbackObject['filename'], serialize($feedbackObject))){
			//Add filename to data
			return true;
		}
		return false;
	}

	/*
   	 * Auxiliary function that creates filename 
     */
	private function generateFilename(){
		return time() . '-' . rand(1000, 9999) . '.feedback';
	}

	/*
   	 * Auxiliary function that creates screenshotname 
     */
	private function generateScreenshotname(){
		return time() . '-' . rand(1000, 9999) . '.png';
	}


	/*
   	 * Auxiliary function that save screenshot as image in webroot 
     */
  	private function saveScreenshot($feedbackObject = null){
    	//Get save path from config
    	$savepath = APP.'Plugin'.DS.'FeedbackIt'.DS.'webroot'.DS.'img'.DS.'screenshots'.DS;
    	
    	//Serialize and save the object to a store in the Cake's tmp dir.
    	if (!file_exists($savepath)){
			if (!mkdir($savepath)){
				//Throw error, directory is requird
				throw new NotFoundException(__d('feedback_it','Could not create directory to save screenshots in. Please provide write rights to webserver user on directory: ') . $savepath);
			}
		}

		$screenshotname = $this->generateScreenshotname();

		if (file_put_contents($savepath . $screenshotname, base64_decode($feedbackObject['screenshot']))){
			//Return the screenshotname
			return $screenshotname;
		}
		return false;
	}

}
