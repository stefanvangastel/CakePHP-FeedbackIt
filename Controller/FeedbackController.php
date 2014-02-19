<?php
/**
 * Class FeedbackController
 */
class FeedbackController extends AppController {
	
	public $uses = array('FeedbackIt.Feedbackstore');

	public function beforeFilter(){

		//Check security component loaded and disable it for this plugin:
		if(isset($this->Security)){
			$this->Security->csrfCheck = false;
			$this->Security->validatePost = false;	
		}

		//Config file location (if you use it)
		$configfile = CakePlugin::path('FeedbackIt').'Config'.DS.'feedbackit-config.php';

		//Check if a config file exists:
		if( file_exists($configfile) AND is_readable($configfile) ){
			//Load config file into CakePHP config 
			Configure::load('FeedbackIt.feedbackit-config');
			return true;
		}

		//Throw error, config file required
		throw new NotFoundException( __d('feedback_it','No config file found. Please create one: ').' ('.$configfile.')' );
	}

	/*
	Ajax function to save the feedback form. Lots of TODO's on this side.
	 */
	public function savefeedback(){

		//Is ajax action
		$this->layout='ajax';

		//Save screenshot:
		$this->request->data['screenshot'] = str_replace('data:image/png;base64,', '', $this->request->data['screenshot']);

		//Add current time to data
		$this->request->data['time'] = time();

		//Check name
		if( empty($this->request->data['name']) ){
			$this->request->data['name'] = "Anonymous";
		}
		
		//Create feedbackObject
		$feedbackObject = $this->request->data;
		
		//Determine method of saving
		if( $method = Configure::read('FeedbackIt.method') ){

			//Check method exists in Model
			if( ! (method_exists($this->Feedbackstore, $method)) ){
				throw new NotImplementedException( __d('feedback_it','Method not found in Feedbackstore model:').' '.$method );
			}

			//Use method to save:
			$result = $this->Feedbackstore->$method($feedbackObject);

			if( ! $result['result'] ){			
				$this->response->statusCode(500);
				
				if( empty($result['msg']) ){
					$result['msg'] = 'Error saving feedback.';
				}	
			}else{
				if( empty($result['msg']) ){
					$result['msg'] = 'Your feedback was saved succesfully.';
				}
			}

			$this->set('msg',$result['msg']);

            //Send a copy to the reciever:
            if(!empty($feedbackObject['copyme'])){
               	$this->Feedbackstore->mail($feedbackObject,true);
            }

            //Use secondarymethod to save:
            $secondarymethod = Configure::read('FeedbackIt.secondarymethod');
            if(method_exists($this->Feedbackstore, $secondarymethod)){
            	$secondaryresult = $this->Feedbackstore->$secondarymethod($feedbackObject);
	        }
          
		}else{
			//Throw error, method required
			throw new NotFoundException( __d('feedback_it','No save method found in config file') );
		}	
	}

	/*
	Example index function for current save in tmp dir solution
	 */
	public function index(){

		if(Configure::read('FeedbackIt.method') != 'filesystem'){
			$this->Session->setFlash(__d('feedback_it','This function is only available with filesystem save method'));
			$this->redirect($this->referer());
		}

		//Find all files in feedbackit dir
		$savepath = Configure::read('FeedbackIt.methods.filesystem.location');

		//Check dir
		if( ! file_exists($savepath) ){
			throw new NotFoundException( __d('feedback_it','Feedback location not found: ').$savepath );
		}

		//Creat feedback array in a cake-like way
		$feedbacks = array();

		//Loop through files
		foreach(glob($savepath.'*.feedback') as $feedbackfile){

			$feedbackObject = unserialize(file_get_contents($feedbackfile));
			$feedbacks[$feedbackObject['time']]['Feedback'] = $feedbackObject;

		}

		//Sort by time
		krsort($feedbacks);

		$this->set('feedbacks',$feedbacks);
	}

	/*
	Temp function to view captured image from index page
	 */
	public function viewimage($feedbackfile){

		$savepath = Configure::read('FeedbackIt.methods.filesystem.location');

		if( ! file_exists($savepath.$feedbackfile) ){
			 throw new NotFoundException( __d('feedback_it','Could not find that file') );
		}

		$feedbackobject = unserialize(file_get_contents($savepath.$feedbackfile));

		if( ! isset($feedbackobject['screenshot']) ){
			throw new NotFoundException( __d('feedback_it','No screenshot found') );
		}

		$this->set('screenshot',$feedbackobject['screenshot']);

		$this->layout = 'ajax';
	}
}
