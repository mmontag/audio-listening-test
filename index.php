<?php

require("audioswitcher.inc.php");
require("dbconfig.inc.php");

?>
<!doctype html>
<html>
<head>
	<title>Audio Test</title>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
	<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.3.1/underscore-min.js"></script>
	<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/backbone.js/0.9.1/backbone-min.js"></script>
	<script type="text/javascript" src="audioswitcher.js"></script>
  <script>
      var audioSwitcher = [];
  </script>
	<link rel="stylesheet" href="audiotest.css">
</head>
<body>
<h1>Audio Test</h1>
<?
if(isset($_POST['token'])) {

  $mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

  $token = intval($_POST['token']);
  $ip_address = $mysqli->escape_string($_SERVER['REMOTE_HOST']);
  $environment = $mysqli->escape_string($_POST['environment']);
  $device = $mysqli->escape_string($_POST['device']);

  $num_correct = 0;
  $correct = array();

  foreach($_POST as $key => $value) {
    $matches = array();
    $match = preg_match('/^audio_([0-9]+)$/', $key, $matches);
    if ($match === 1) {
      // This item is a question response
      $audio_id = intval($matches[1]);
      $response = intval($value);
      $audio_name = $filemap[$audio_id][0]['name'];
      $correct[$audio_id] = $corr = $filemap[$audio_id][$response]['num'] == '1'; // when response value matches suffix for original files
      $query = "INSERT INTO responses (audio_id, audio_name, response, correct, environment, device, ip_address, token)
                  VALUES ('$audio_id', '$audio_name', '$response', '$corr', '$environment', '$device', '$ip_address', '$token')";
      $result = $mysqli->query($query);
      // echo("running $query.<br>");
      // echo($mysqli->error."<br>");
      if ($corr) $num_correct++;
    }
  }
  ?>
  <h2>Results</h2>
  <h3 class="score">You scored <?=$num_correct?> out of <?=count($filemap)?> correct.</h3>
  <ol><?
  foreach($correct as $key => $value) {
    print ("<li>[".$filemap[$key][0]['name']."] ");
    if ($value == true)
      print ("<span class='correct'>correct</span>");
    else
      print ("<span class='incorrect'>incorrect</span>");
  }
  ?>
  </ol>
  <p>Thanks for taking the audio watermark listening test!</p>
  <?
} else {
  ?>
  <p>
    This survey will test your ability to detect a digital audio watermark embedded in music files.
  <p>
    Digital audio watermarks are designed to hide extra information in an audio signal, usually for
    copyright enforcement purposes. The watermarks are designed to be inaudible, but necessarily add
    some distortion to the original audio.
  <p>
    Below, you will find <?=count($filemap)?> groups of audio samples. In each group, you are presented with
    two versions of the same music sample. One
    contains a digital watermark, and the other does not. You can switch back and forth between the two audio
    samples using the control buttons. Listen closely and try to determine which sample contains a watermark
    in each group. For best results, use headphones and take the test in a quiet environment.
  <p>
    The watermarking technology is the same in all audio samples.
    Your score will be reported after you submit your answers.
  <form action="index.php" method="post">
  <input type="hidden" name="token" value="<?=$token?>"/>
	<ol class="quiz">
    <?php
    foreach($filemap as $index => $fileset) {
    ?>
        <li><h2 class="question"><?=htmlspecialchars($fileset[0]['name'])?></h2>
            <div class="listitem" id="audioswitcher<?=$index?>">
                <div class="col1">
                    <button type="button" class="switch a">A</button>
                    <div class="chooser">
                        <input type="radio" id="chooser_<?=$index?>_0" name="audio_<?=$index?>" value="0">
                        <label for="chooser_<?=$index?>_0">A is Watermarked</label>
                    </div>
                </div>
                <div class="col2">
                    <button type="button" class="toggle">Toggle &harr;</button>
                    <button type="button" class="rewind">Rewind &#8634; 3 sec</button><br>
                    <button type="button" class="toggleAndRewind">Toggle and Rewind &#8634; 3 sec</button><br>
                    <audio id="audio_<?=$index?>_0" controls="true" src="getAudio.php?token=<?=$token?>&file=<?=$index?>,0"></audio>
                    <audio id="audio_<?=$index?>_1" controls="true" src="getAudio.php?token=<?=$token?>&file=<?=$index?>,1" style="display: none"></audio>
                </div>
                <div class="col3">
                    <button type="button" class="switch b">B</button>
                    <div class="chooser">
                        <input type="radio" id="chooser_<?=$index?>_1" name="audio_<?=$index?>" value="1">
                        <label for="chooser_<?=$index?>_1">B is Watermarked</label>
                    </div>
                </div>
            </div>
        </li>
        <script>
          audioSwitcher[<?=$index?>] = new AudioSwitcher(document.getElementById('audioswitcher<?=$index?>'));
        </script>
    <?php
    }
    ?>
	</ol>
  <div class="centered">
      <div class="split">
          <p>My listening environment is:
          <p>
              <input type="radio" id="environment_1" name="environment" value="quiet"><label for="environment_1">Quiet</label><br>
              <input type="radio" id="environment_2" name="environment" value="normal"><label for="environment_2">Normal</label><br>
              <input type="radio" id="environment_3" name="environment" value="loud"><label for="environment_3">Loud</label><br>
      </div>
      <div class="split">
          <p>I am listening with:
          <p>
              <input type="radio" id="device_1" name="device" value="headphones"><label for="device_1">Headphones</label><br>
              <input type="radio" id="device_2" name="device" value="speakers"><label for="device_2">Speakers</label><br>
              <input type="radio" id="device_3" name="device" value="laptop"><label for="device_3">Laptop Speakers</label><br>
      </div>
      <!--
      <div class="split">
          <p>Did the watermarks bother you?
          <p>
              <input type="radio" id="device_1" name="device" value="headphones"><label for="device_1">Headphones</label><br>
              <input type="radio" id="device_2" name="device" value="speakers"><label for="device_2">Speakers</label><br>
              <input type="radio" id="device_3" name="device" value="laptop"><label for="device_3">Laptop Speakers</label><br>
      </div>
      -->
  </div>
  <button class="submit">Submit My Answers</button>
  </form>
  <?
}
?>
</body>
</html>