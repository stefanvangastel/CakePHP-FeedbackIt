<?php
/*
Load CSS
*/
echo $this->Html->css(array('FeedbackIt.feedbackbar'));

/*
Load JavaScript
*/
echo $this->Html->script(
	array(
		'FeedbackIt.html2canvas/html2canvas',
		'FeedbackIt.base64'
		//'FeedbackIt.canvas2image'
		)
	);
?>


<div id="feedbackit-slideout">
  <?php echo $this->Html->image('FeedbackIt.feedback.png');?>
  <div id="feedbackit-slideout_inner">
    <form id="feedbackit-form">
	    <input type="text" name="subject" id="feedbackit-subject" placeholder="Subject...">
	    <textarea name="feedback" id="feedbackit-feedback" placeholder="Feedback or suggestion" rows="3"></textarea>
	    <p>
	    	<button class="btn btn-warning" data-loading-text="Click location on website..." id="feedbackit-highlight" onclick="return false;"><i class="icon-screenshot icon-white"></i> <?php echo __('Highlight (optional)'); ?></button>
	    </p>
	    <p>
	    	<div class="btn-group">
	    		<button class="btn btn-success" id="feedbackit-submit" onclick="return false;"><i class="icon-envelope icon-white"></i> <?php echo __('Submit'); ?></button>
	    		<button class="btn btn-danger" id="feedbackit-cancel" onclick="return false;"><i class="icon-remove icon-white"></i> <?php echo __('Cancel'); ?></button>
	    	</div>
	    </p>
	</form>
  </div>
</div>

<div id="feedbackit-highlight-holder">hier</div>

<script>

/*
Submit button click
 */
$('#feedbackit-submit').click(function(){
	
	//Hide feedback slider
	$('#feedbackit-slideout').hide();

	html2canvas(document.body, {
	  onrendered: function(canvas) {
	    
	    //encode the image data as a base64 encoded PNG file and return it
	    var strDataURI = canvas.toDataURL(); 

	    var postData = $('#feedbackit-form').serializeArray();

	    postData.push({name:'screenshot',value:strDataURI});

	    console.log(postData);

	    var formURL = '<?php echo $this->Html->url(array("plugin"=>"feedback_it","controller"=>"feedback","action"=>"savefeedback"),true); ?>';
	    $.ajax(
	    {
	        url : formURL,
	        type: "POST",
	        data : postData,
	        success:function(data, textStatus, jqXHR) 
	        {
	        	$('#feedbackit-subject').val(''); //Reset
	        	$('#feedbackit-feedback').val(''); //Reset
	            alert('Thank you. Your feedback was submitted.');
	        },
	        error: function(jqXHR, textStatus, errorThrown) 
	        {
	            alert('There was an error submitting your feedback. Please try again later.');    
	        }
	    });
	    
	    //Show it again
	    $('#feedbackit-slideout').show();
	  }
	});

});

/*
Hightlight button click
 */
$('#feedbackit-highlight').mouseup(function(){

	//Disable button and show loading text
	$('#feedbackit-highlight').button('loading');

	/*
	Highlight function
	 */
	$('body').click(function(e){
		// capture the mouse position
	    var posx = 0;
	    var posy = 0;
	    if (!e) var e = window.event;
	    if (e.pageX || e.pageY)
	    {
	        posx = e.pageX;
	        posy = e.pageY;
	    }
	    else if (e.clientX || e.clientY)
	    {
	        posx = e.clientX;
	        posy = e.clientY;
	    }
	 
	 	//Set position, to exactly center substract half the width and height from the x and y position
		$( "#feedbackit-highlight-holder" ).css( "left", posx);
	    $( "#feedbackit-highlight-holder" ).css( "top", posy);
		$( "#feedbackit-highlight-holder" ).css( "display", 'block');

		//Reset highlight button
		$('#feedbackit-highlight').button('reset');

		//Unbind click function
		$('body').off('click');
	});

});


</script>