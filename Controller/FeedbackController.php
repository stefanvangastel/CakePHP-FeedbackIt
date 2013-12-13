<?php
class FeedbackController extends AppController {
	

	/*
	Example index function for current save in tmp dir solution
	 */
	public function index(){

		//Find all files in feedbackit dir
		$savepath = APP.'tmp'.DS.'feedbackit'.DS;

		//Creat feedback array in a cake-like way
		$feedbacks = array();

		//Loop through files
		foreach(glob($savepath.'*.feedback') as $feedbackfile){

			$feedbackobject = unserialize(file_get_contents($feedbackfile));

			$feedbacks[$feedbackobject['time']]['Feedback'] = $feedbackobject;

		}

		//Sort by time
		krsort($feedbacks);

		$this->set('feedbacks',$feedbacks);
	}


	/*
	Ajax function to save the feedback form
	 */
	public function savefeedback(){

		//TMP FUNCTION BODY, CREATE YOUR OWN OR WAIT FOR THE OPTIONS (Mail, fss)

		//Is ajax action
		$this->layout='ajax';

		//Do not autorender
		$this->autoRender = false;

		//Set path
		$savepath = APP.'tmp'.DS.'feedbackit'.DS;

		//Save screenshot:
		$this->request->data['screenshot'] = str_replace('data:image/png;base64,', '', $this->request->data['screenshot']);

		//Add current time to data
		$this->request->data['time'] = time();
		
		//Serialize and save the object to a store in the Cake's tmp dir.
		if( ! file_exists($savepath ) ){
			mkdir($savepath);
		}

		//Save serialized with timestamp + randnumber as filename
		$filename = time() . '-' . rand(1000,9999).'.feedback';

		if(file_put_contents($savepath.$filename, serialize($this->request->data))){
			echo "Feedback saved";
		}else{
			$this->response->statusCode(500);
			echo "Error saving feedback";
		}
	}
	
}
?> 