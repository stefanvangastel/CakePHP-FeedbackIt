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
    <div class="feedbackit-form-elements">
        <p>
            <?php echo __('Send us your feedback or report a bug!');?>
        </p>
        <form id="feedbackit-form" autocomplete="off" role="form">
         <div class="form-group">
            <input
                type="text"
                name="name"
                id="feedbackit-name"
                class="feedbackit-input form-control"
                placeholder="<?php echo __('Your name (optional)'); ?>"
            >
         </div>
         <div class="form-group">
            <input
                type="text"
                name="subject"
                id="feedbackit-subject"
                class="feedbackit-input form-control"
                required="required"
                placeholder="<?php echo __('Subject'); ?>"
            >

         </div>
         <div class="form-group">
            <textarea name="feedback" id="feedbackit-feedback" class="feedbackit-input form-control" required="required" placeholder="<?php echo __('Feedback or suggestion'); ?>" rows="3"></textarea>
         </div>
         <div class="form-group">
            <p>
            	<button
                    class="btn btn-info"
                    data-loading-text="<?php echo __('Click anywhere on website'); ?>"
                    id="feedbackit-highlight"
                    onclick="return false;">
                    <i class="icon-screenshot icon-white"></i><span class="glyphicon glyphicon-screenshot"></span> <?php echo __('Highlight something'); ?>
                </button>
            </p>
            <p>
                <label class="checkbox">
                  <input type="checkbox" required id="feedbackit-okay">
                    I'm okay with <b><a id="feedbackit-okay-message" href="#" onclick="return false;" data-toggle="tooltip" title="<?php echo __('When you submit, a screenshot (of only this website) will be taken to aid us in processing your feedback or bugreport.');?>">this</a></b>.
                </label>
            </p>
            <p>
            	<div class="btn-group">
            		<button class="btn btn-success" id="feedbackit-submit" disabled="disabled" type="submit"><i class="icon-envelope icon-white"></i><span class="glyphicon glyphicon-envelope"></span> <?php echo __('Submit'); ?></button>
            		<button class="btn btn-danger" id="feedbackit-cancel" onclick="return false;"><i class="icon-remove icon-white"></i><span class="glyphicon glyphicon-remove"></span> <?php echo __('Cancel'); ?></button>
            	</div>
            </p>
         </div>
        </form>
    </div>
</div>

<div id="feedbackit-highlight-holder"><?php echo $this->Html->image('FeedbackIt.circle.gif');?></div>

<script>
//Create URL using cake's url helper, this is used in feedbackit-functions.js
window.formURL = '<?php echo $this->Html->url(array("plugin"=>"feedback_it","controller"=>"feedback","action"=>"savefeedback"),true); ?>';
</script>
