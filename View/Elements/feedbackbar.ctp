<?php
/**
 * @var $this view
 */

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

/*
 Read config settings
*/
//Config file location (if you use it)
$configfile = CakePlugin::path('FeedbackIt').'Config'.DS.'feedbackit-config.php';

//Defaults in case config file cannot be loaded for some reason
$forceauthusername	= false;
$forceemail	        = false;
$enablecopybyemail	= false;
$enableacceptterms	= false;
$username           = '';
$email              = '';
$termstext          = '';

//Check if a config file exists:
if( file_exists($configfile) AND is_readable($configfile) ){
    //Load config file into CakePHP config
    Configure::load('FeedbackIt.feedbackit-config');

    //Get config vars used in this view
    $forceauthusername	= Configure::read('FeedbackIt.forceauthusername');
    $forceemail	        = Configure::read('FeedbackIt.forceemail');

    $enablecopybyemail	= Configure::read('FeedbackIt.enablecopybyemail');

    $enableacceptterms	= Configure::read('FeedbackIt.enableacceptterms');
    $termstext	        = Configure::read('FeedbackIt.termstext');

    //Assemble optional vars if AuthComponent is loaded
    if( class_exists('AuthComponent','user') ){
        $username = AuthComponent::user('name') ?: AuthComponent::user('username') ?: AuthComponent::user('account') ?: '';
        $email = AuthComponent::user('mail') ?: AuthComponent::user('email') ?: '';
    }
}
?>

<script>
    //Create URL using cake's url helper, this is used in feedbackit-functions.js
    <?php $formposturl = $this->Html->url(array("plugin"=>"feedback_it","controller"=>"feedback","action"=>"savefeedback"),true); ?>
    window.formURL = '<?php echo $formposturl; ?>';
</script>

<div id="feedbackit-slideout">
    <?php echo $this->Html->image('FeedbackIt.feedback.png');?>
</div>
<div id="feedbackit-slideout_inner">
    <div class="feedbackit-form-elements">
        <p>
            <?php echo __('Send your feedback or bugreport!');?>
        </p>
        <form id="feedbackit-form" autocomplete="off">
            <div class="form-group">
                <input
                    type="text"
                    name="name"
                    id="feedbackit-name"
                    class="<?php if( !empty($username) ) echo 'feedbackit-input"'; ?> form-control"
                    value="<?php echo $username; ?>"
                    placeholder="<?php echo __('Your name '); if( !$forceauthusername ) echo '(optional)"'; ?>"
                    <?php if( $forceauthusername AND !empty($username) ) echo 'readonly="readonly"'; ?>
                    >
            </div>
            <div class="form-group">
                <input
                    type="email"
                    name="email"
                    id="feedbackit-email"
                    class="<?php if( !empty($email) ) echo 'feedbackit-input"'; ?> form-control"
                    value="<?php echo $email; ?>"
                    placeholder="<?php echo __('Your e-mail '); if( !$forceemail ) echo '(optional)"'; ?>"
                    <?php if( $forceemail AND !empty($email) ) echo 'readonly="readonly"'; ?>
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
                <p <?php if( ! $enableacceptterms) echo 'style="display:none;"'; ?>>
                    <label class="checkbox">
                        <input type="checkbox"
                               required id="feedbackit-okay"
                                <?php
                                if( ! $enableacceptterms){
                                   echo 'class="isinvisible"';
                                   echo 'checked="checked"';
                                }else{
                                   echo 'class="isvisible"';
                                }
                                ?>
                            >
                        I'm okay with <b><a id="feedbackit-okay-message" href="#" onclick="return false;" data-toggle="tooltip" title="<?php echo $termstext;?>">this</a></b>.
                    </label>
                </p>
                <?php
                if($enablecopybyemail){
                ?>
                <p>
                    <label class="checkbox">
                        <input type="checkbox" name="copyme" id="feedbackit-copyme" >
                        E-mail me a copy
                    </label>
                </p>
                <?php
                }
                ?>
                <p>
                <div class="btn-group">
                    <button class="btn btn-success" id="feedbackit-submit" disabled="disabled" type="submit"><i class="icon-envelope icon-white"></i><span class="glyphicon glyphicon-envelope"></span> <?php echo __d('feedback_it','Submit'); ?></button>
                    <button class="btn btn-danger" id="feedbackit-cancel" onclick="return false;"><i class="icon-remove icon-white"></i><span class="glyphicon glyphicon-remove"></span> <?php echo __d('feedback_it','Cancel'); ?></button>
                </div>
                </p>
            </div>
        </form>
    </div>
</div>

<div id="feedbackit-highlight-holder"><?php echo $this->Html->image('FeedbackIt.circle.gif');?></div>

<!-- Modal for confirmation -->
<div class="modal fade" id="feedbackit-modal" tabindex="-1" role="dialog" aria-labelledby="feedbackit-modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="feedbackit-modalLabel">Feedback submitted</h4>
            </div>
            <div class="modal-body">
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

