<?php
App::uses('CakeEmail', 'Network/Email');

class FeedbackController extends AppController {
	
	/*
	Ajax function to save the feedback form
	 */
	public function savefeedback(){

		$this->layout='ajax';

		//Save screenshot:
		$image = $this->request->data['screenshot'];

		$image = str_replace('data:image/png;base64,', '', $image);

		$fullpath = APP.'tmp'.DS.'screenshot.png';

		file_put_contents($fullpath, $image);

		$Email = new CakeEmail();
		$Email->from(array('feedback@mywebsite.com' => 'My Site'));
		$Email->to('reciever');
		$Email->subject($this->request->data['subject']);
		/*$Email->attachments(array(
		    'screenshot.png' => array(
		        'file' => $fullpath,
		        'mimetype' => 'image/png',
		        'contentId' => 'screenshot-id'
		    )
		));*/

		if($Email->send($this->request->data['feedback'])){
			die('Success');
		}else{
			die('Fail');
		}


	}
	
}
?>