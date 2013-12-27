<?php
App::uses('CakeEmail', 'Network/Email');

class Feedbackstore extends AppModel {

	public $name = 'Feedbackstore';

	public $useTable = false;

	/*
    Store functions for different save methods
   	 */
	public function filesystem($feedbackObject = null){
		if (empty($feedbackObject)){
			return false;
    	}
		$feedbackObject = $this->saveFile($feedbackObject);
		if (isset($feedbackObject['filename'])){
			return true;
    	}
    	return false;
	}

	/*
   	 * Auxiliary function that saves the file 
     */
  	private function saveFile($feedbackObject = null)
	{
    	//Get save path from config
    	$savepath = Configure::read('FeedbackIt.methods.filesystem.location');
    	//Serialize and save the object to a store in the Cake's tmp dir.
    	if (!file_exists($savepath)){
			if (!mkdir($savepath)){
			//Throw error, directory is requird
			throw new NotFoundException(__('Could not create directory to save feedbacks in. Please provide write rights to webserver user on directory: ') . $savepath);
			}
		}
		//Save serialized with timestamp + randnumber as filename
		$filename = time() . '-' . rand(1000, 9999) . '.feedback';

		if (file_put_contents($savepath . $filename, serialize($feedbackObject))){
			//Add filename to data
			$feedbackObject['filename'] = $filename;
		}
		return $feedbackObject;
	}

	/*
	Mantis store function
	 */
	public function mantis($feedbackObject = null){

		if(empty($feedbackObject)){
			return false;
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
			return ($c->mc_issue_attachment_add( $username,  $password, $issueid, date('d-m-Y_H-i-s').'.png', 'image/png', $feedbackObject['screenshot'] ));
		}

		return false;
	}

	/*
	Mail function
	 */
	public function mail($feedbackObject = null){

		if(empty($feedbackObject)){
			return false;
		}

		//Read settings from config
		$to	= Configure::read('FeedbackIt.methods.mail.to');
		$from	= Configure::read('FeedbackIt.methods.mail.from');

		//Tmp store the screenshot:
		$tmpfile = APP.'tmp'.DS.time().'_'.rand(1000,9999).'.png';
		if( ! file_put_contents($tmpfile, base64_decode($feedbackObject['screenshot'])) ){
			//Need to save tmp file
			throw new NotFoundException( __('Could not save tmp file for attacment in mail') );
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

		//Mail specific: append browser, browser version and URL to feedback:
		$feedbackObject['feedback'] .= "<p>";
		$feedbackObject['feedback'] .= sprintf("Browser: %s %s<br />",$feedbackObject['browser'],$feedbackObject['browser_version']);
		$feedbackObject['feedback'] .= sprintf("Url: %s<br />",$feedbackObject['url']);
		$feedbackObject['feedback'] .= sprintf("By: %s<br />",$feedbackObject['name']);
		$feedbackObject['feedback'] .= "</p>";
		$feedbackObject['feedback'] .= '<img src="cid:id-screenshot">'; //Add inline screenshot

		if( $email->send($feedbackObject['feedback']) ){
			unlink($tmpfile);
			return true;
		}
		unlink($tmpfile);
		return false;
	}

	/*
	Github API v3 NO IMAGE SUPPORT YET
	 */
	public function github($feedbackObject = null){

		if(empty($feedbackObject)){
			return false;
		}

		//Read settings
		$api_url	= Configure::read('FeedbackIt.methods.github.api_url');
		$username	= Configure::read('FeedbackIt.methods.github.username');
		$password	= Configure::read('FeedbackIt.methods.github.password');

		//Mantis specific: append browser, browser version and URL to feedback:
		$feedbackObject['feedback'] .= "\n\n";
		$feedbackObject['feedback'] .= sprintf("Browser: %s %s\n",$feedbackObject['browser'],$feedbackObject['browser_version']);
		$feedbackObject['feedback'] .= sprintf("Url: %s\n",$feedbackObject['url']);
		$feedbackObject['feedback'] .= sprintf("By: %s",$feedbackObject['name']);

		//TODO: Optional, append image (link) to this websites ../feedback_it/feedback/viewimage/xxx url or something

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

		$result = curl_exec($ch); 

		if( ! $result){
			throw new InternalErrorException( curl_error($ch) );
		}

		return true;
	}

	public function github($feedbackObject = null){

		if (empty($feedbackObject)){
    		return false;
    	}

    	//Read settings
    	$api_url = Configure::read('FeedbackIt.methods.bitbucket.api_url');
    	$username = Configure::read('FeedbackIt.methods.bitbucket.username');
    	$password = Configure::read('FeedbackIt.methods.bitbucket.password');
    	$localimagestore = Configure::read('FeedbackIt.methods.bitbucket.localimagestore');

		//Append browser, browser version and URL to feedback:
		$feedbackObject['feedback'] .= sprintf("**By**: %s\n\n", $feedbackObject['name']);
		$feedbackObject['feedback'] .= sprintf("**Browser**: %s %s\n\n", $feedbackObject['browser'], $feedbackObject['browser_version']);
		$feedbackObject['feedback'] .= sprintf("**Url**: %s\n\n", $feedbackObject['url']);    
    
		// WARNING: This may not work for sites with different domains (or dev environments)
    	//          If the given URL is not public, Bitbucket won't display the screenshot
		if ($localimagestore){
			$newFeedbackObject = $this->saveFile($feedbackObject);
			$viewimageUrl = Router::fullBaseUrl();
			$viewimageUrl .= Router::url('/feedback_it/feedback/viewimage/');
			$viewimageUrl .= $newFeedbackObject['filename'];
			$feedbackObject['feedback'] .= sprintf("**Screenshot**: ![screenshot](%s)\n\n", $viewimageUrl);
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
    	if (!$result)
    	{
    	  throw new InternalErrorException($HttpSocket->lastError());
    	}

		return true;
	}

}
