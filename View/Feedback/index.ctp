<?php 
/*
Quick example of tmp file storage index.
 */

/**
 * @var $this view
 * @var $feedbacks mixed
 */
foreach($feedbacks as $feedback){
  ?>

  <div class="media">
    <a class="pull-left" href="<?php echo $this->Html->url(array("plugin"=>"feedback_it","controller"=>"feedback","action"=>"viewimage",$feedback['Feedback']['filename']),true); ?>" target="_blank">
      <img class="media-object feedbackit-small-img" src="data:image/png;base64,<?php echo $feedback['Feedback']['screenshot']; ?>">
    </a>
    <div class="media-body">
      <h4 class="media-heading"><?php echo $feedback['Feedback']['subject'] . ' <i>(' . date('d-m-Y H:i:s',$feedback['Feedback']['time']) . ')</i>';?></h4>
      <b><?php echo $feedback['Feedback']['feedback'];?></b>

      <?php
      //Unset the already displayed vars and loop throught the next. Saves us some coding when a new var is added to the feedback
      unset($feedback['Feedback']['subject']);
      unset($feedback['Feedback']['feedback']);
      unset($feedback['Feedback']['screenshot']);
      unset($feedback['Feedback']['time']);
      unset($feedback['Feedback']['filename']);
      unset($feedback['Feedback']['copyme']);

      foreach($feedback['Feedback'] as $fieldname => $fieldvalue){
          echo '<br />';
          echo "<b>".ucfirst($fieldname).":</b> $fieldvalue";
      }
      ?>
    </div>
  </div>

  <?php
}
?>