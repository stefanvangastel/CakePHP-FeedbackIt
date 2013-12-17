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
		'FeedbackIt.html2canvas/html2canvas', //html2canvas.js for screenshot function
		'FeedbackIt.feedbackit-functions' //Specific FeedbackIt functions
		)
	);
?>


<div id="feedbackit-slideout">
  <?php echo $this->Html->image('FeedbackIt.feedback.png');?>
</div>
<div id="feedbackit-slideout_inner">
<form id="feedbackit-form" autocomplete="off">
    <input type="text" name="subject" id="feedbackit-subject" placeholder="<?php echo __('Subject'); ?>">
    <textarea name="feedback" id="feedbackit-feedback" placeholder="<?php echo __('Feedback or suggestion'); ?>" rows="3"></textarea>
    <p>
    	<button class="btn btn-warning" data-loading-text="<?php echo __('Click anywhere on website'); ?>" id="feedbackit-highlight" onclick="return false;"><i class="icon-screenshot icon-white"></i> <?php echo __('Highlight something'); ?></button>
    </p>
    <p>
    	<div class="btn-group">
    		<button class="btn btn-success" id="feedbackit-submit" onclick="return false;"><i class="icon-envelope icon-white"></i> <?php echo __('Send'); ?></button>
    		<button class="btn btn-danger" id="feedbackit-cancel" onclick="return false;"><i class="icon-remove icon-white"></i> <?php echo __('Cancel'); ?></button>
    	</div>
    </p>
</form>
</div>


<div id="feedbackit-highlight-holder"><?php echo $this->Html->image('FeedbackIt.circle.gif');?></div>

<script>
//Create URL using cake's url helper, this is used in feedbackit-functions.js 
window.formURL = '<?php echo $this->Html->url(array("plugin"=>"feedback_it","controller"=>"feedback","action"=>"savefeedback"),true); ?>';	   

</script>