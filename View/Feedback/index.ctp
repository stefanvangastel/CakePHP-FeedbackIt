<?php 
/*
Quick example of tmp file storage index.
 */
foreach($feedbacks as $feedback){
  ?>

  <div class="media">
    <a class="pull-left" href="#">
      <img class="media-object feedbackit-small-img" src="data:image/png;base64,<?php echo $feedback['Feedback']['screenshot']; ?>">
    </a>
    <div class="media-body">
      <h4 class="media-heading"><?php echo $feedback['Feedback']['subject'] . ' <i>(' . date('d-m-Y H:i:s',$feedback['Feedback']['time']) . ')</i>';?></h4>
      <b><?php echo $feedback['Feedback']['feedback'];?></b>
      <br />
      <?php echo $feedback['Feedback']['url'];?>
      <br />
      <?php echo $feedback['Feedback']['browser'];?> <?php echo $feedback['Feedback']['browser_version'];?>
    </div>
  </div>

  <?php
}
?>