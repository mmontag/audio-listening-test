<?php

require("audioswitcher.inc.php");
require("config.inc.php");

?>
<!doctype html>
<html>
<head>
	<title>Audio Watermark Listening Test</title>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
	<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.3.1/underscore-min.js"></script>
	<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/backbone.js/0.9.1/backbone-min.js"></script>
	<script type="text/javascript" src="audioswitcher.js"></script>
  <script>
      var audioSwitcher = [];
      var timer = {
        id: null,
        totalTime: 0,
        interval: 1000, // milliseconds
        start: function () {
          var self = this;
          if (this.id != null) clearInterval(this.id);
          this.id = setInterval(function () {
            self.totalTime = self.totalTime + self.interval/1000;
          }, this.interval);
        },
        stop: function () {
          clearInterval(this.id);
        }
      };

      timer.start();

	    window.addEventListener('focus', function() {
        timer.start();
        console.log('starting timer at:', timer.totalTime);
      }, false);

      window.addEventListener('blur', function() {
        timer.stop();
        console.log('stopping timer at:', timer.totalTime);
      }, false);
  </script>
	<link rel="stylesheet" href="audiotest.css">
</head>
<body>
<h1>Audio Watermark Listening Test</h1>
<?
if(isset($_POST['token'])) {

  $mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

  $token = intval($_POST['token']);
  $active_time = intval($_POST['active_time']);
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
      $correct[$audio_id] = $corr = $filemap[$audio_id][$response]['num'] == '2'; // when response value matches suffix for watermarked files
      $query = "INSERT INTO responses (audio_id, audio_name, response, correct, environment, device, ip_address, token, active_time)
                  VALUES ('$audio_id', '$audio_name', '$response', '$corr', '$environment', '$device', '$ip_address', '$token', '$active_time')";
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
    Digital audio watermarks hide extra information in an audio signal, usually for
    copyright enforcement purposes. The watermarks are designed to be inaudible, but necessarily add
    some distortion to the original audio.

  <h2>Training Phase</h2>
  <p>
    The information below is meant to familiarize you with the sound qualities of the watermark before taking the test.
    Take your time with this material before you begin answering questions.
  <p>
    The following sample demonstrates a greatly exaggerated version of the watermark applied to a noise signal.
    The watermark starts after 2 seconds.
  <figure>
    <audio controls="true">
      <source type="audio/mpeg" src="noise-watermarked.mp3">
      <source type="audio/ogg" src="noise-watermarked.ogg">
    </audio>
      <figcaption>Audio sample containing an exaggerated watermark.</figcaption>
        </figure>
  <p>
    This distortion is visualized in the figure below, showing discrete blocks of noise that fluctuate in intensity over time.
    <figure>
      <img src="watermark.png" width="400" alt="Spectrogram of digital audio watermark">
      <figcaption>A spectrogram of the watermark by itself. The distortion lies in the region from 1 to 3 kHz.</figcaption>
    </figure>


  <p>
    In the following example, sample <strong>A</strong> is the original, and sample <strong>B</strong> is watermarked.
    The distortion is most audible during the loudest portion of the watermarked sample (<strong>B</strong>).
    Focus your attention on the voices of the choir.
    Listen for the rapid fluttering distortion, similar in character to the exaggerated example above.
    Switch back to the original sample (<strong>A</strong>) and notice that the fluttering is absent.
  <div class="listitem" id="audioswitcher_reference">
    <div class="col1">
      <button type="button" class="switch a">A</button>
      <div class="chooser">
        Original
      </div>
    </div>
    <div class="col2">
      <button type="button" class="toggle">Toggle &harr;</button>
      <button type="button" class="rewind">Rewind &#8634; 3 sec</button><br>
      <button type="button" class="toggleAndRewind">Toggle and Rewind &#8634; 3 sec</button><br>
      <audio id="audio_reference_0" controls="true">
        <source type="audio/ogg" src="ravel-original.ogg">
        <source type="audio/mpeg" src="ravel-original.mp3">
        Your browser does not support the Audio element.
      </audio>
      <audio id="audio_reference_1" controls="true" style="display: none">
        <source type="audio/ogg" src="ravel-watermarked.ogg">
        <source type="audio/mpeg" src="ravel-watermarked.mp3">
        Your browser does not support the Audio element.
      </audio>
    </div>
    <div class="col3">
      <button type="button" class="switch b">B</button>
      <div class="chooser">
        Watermarked
      </div>
    </div>
  </div>
  <script>
    audioSwitcher['reference'] = new AudioSwitcher(document.getElementById('audioswitcher_reference'), true);
  </script>

  <h2>Testing</h2>
  <p>
    Below, you will find <?=count($filemap)?> groups of audio samples. In each group, you are presented with
    two versions of the same music sample. One
    contains a digital watermark, and the other does not. Switch back and forth between the two audio
    samples using the control buttons. Listen closely and try to determine which sample in each group
    contains a watermark. <strong>For best results, use headphones and take the test in a quiet environment.</strong>
  <p>
    Your score will be reported after you submit your answers.

  <form action="index.php" method="post">
  <input type="hidden" name="token" value="<?=$token?>"/>
  <input type="hidden" id="active_time" name="active_time" value="0"/>
	<ol class="quiz">
    <?php
    foreach($filemap as $index => $fileset) {
    ?>
        <li><h2 class="question"><?=htmlspecialchars($fileset[0]['name'])?></h2>
            <? if ($fileset[0]['note']) { ?>
            <div class="note">
                <?=$fileset[0]['note']?>
            </div>
            <? } ?>
            <div class="listitem" id="audioswitcher<?=$index?>">
                <div class="col1">
                    <button type="button" class="switch a">A</button>
                    <div class="chooser">
                        <input class="required" type="radio" id="chooser_<?=$index?>_0" name="audio_<?=$index?>" value="0">
                        <label for="chooser_<?=$index?>_0">A is Watermarked</label>
                    </div>
                </div>
                <div class="col2">
                    <button type="button" class="toggle">Toggle &harr;</button>
                    <button type="button" class="rewind">Rewind &#8634; 3 sec</button><br>
                    <button type="button" class="toggleAndRewind">Toggle and Rewind &#8634; 3 sec</button><br>
                    <audio id="audio_<?=$index?>_0" controls="true">
                        <source type="audio/ogg" src="getAudio.php?token=<?=$token?>&file=<?=$index?>,0&ext=ogg">
                        <source type="audio/mpeg" src="getAudio.php?token=<?=$token?>&file=<?=$index?>,0&ext=mp3">
                        Your browser does not support the Audio element.
                    </audio>
                    <audio id="audio_<?=$index?>_1" controls="true" style="display: none">
                        <source type="audio/ogg" src="getAudio.php?token=<?=$token?>&file=<?=$index?>,1&ext=ogg">
                        <source type="audio/mpeg" src="getAudio.php?token=<?=$token?>&file=<?=$index?>,1&ext=mp3">
                        Your browser does not support the Audio element.
                    </audio>
                </div>
                <div class="col3">
                    <button type="button" class="switch b">B</button>
                    <div class="chooser">
                        <input class="required" type="radio" id="chooser_<?=$index?>_1" name="audio_<?=$index?>" value="1">
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
          <p>I am listening with:
          <p>
              <input type="radio" id="device_1" name="device" value="headphones"><label for="device_1">Headphones</label><br>
              <input type="radio" id="device_2" name="device" value="speakers"><label for="device_2">Speakers</label><br>
              <input type="radio" id="device_3" name="device" value="laptop"><label for="device_3">Laptop Speakers</label><br>
      </div>
      <div class="split">
          <p>My listening environment is:
          <p>
              <input type="radio" id="environment_1" name="environment" value="quiet"><label for="environment_1">Quiet</label><br>
              <input type="radio" id="environment_2" name="environment" value="normal"><label for="environment_2">Normal</label><br>
              <input type="radio" id="environment_3" name="environment" value="loud"><label for="environment_3">Loud</label><br>
      </div>
  </div>
  <button class="submit" type="button">Submit My Answers</button>
  <p class="centered formMessage"></p>
  <script>
      var $button = $('button.submit');
      var $form = $('form');
      $button.bind('click', function() {
        $('#active_time').val(timer.totalTime);
        if (validateForm()) {
            $form.submit();
        }
      });
      $form.find('input').bind('change', resetValidation);

      function validateForm() {
        var isValid = true;
        $form.find('.required').each(function() {
            var name = $(this).attr('name');
            if (!$form.find('input[name=' + name + ']:checked').val()) {
                $(this).closest('.listitem').addClass('error');
                isValid = false;
            }
        });
        if (!isValid) {
          $('.formMessage').text('Please answer all the questions. The questions you forgot to answer are highlighted above.');
        }
        return isValid;
      }
      function resetValidation(event) {
        $container = $(event.target).closest('.listitem');
        $container.removeClass('error');
        $('.formMessage').text('');
      }
  </script>
  </form>
  <?
}
?>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-25089589-1']);
  _gaq.push(['_setCampNameKey', 'Listening Test']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</body>
</html>