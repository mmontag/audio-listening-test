<?php

require("audioswitcher.inc.php");

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
  <form action="submit.php" method="post">
  <input type="hidden" name="token" value="<?=$token?>"/>
	<ol>
    <?php
    foreach($filemap as $index => $fileset) {
    ?>
        <li>
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
                    <button type="button" class="toggleAndRewind">Toggle and Rewind &#8634; 3 sec</button>
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
  </form>
<!-- Templates -->
<script type="text/template" id="tpl-switcher">
  <li>
    <a href="{{ url }}" data-autoplayurl="{{ autoPlayUrl }}" target="m">{% if (artist) { %}{{ artist }} - {% } %}{{ track }}</a>
    {% if (popularity !== undefined) { %}
    <span class="album withPopularity">{{ album }}</span>
    <span class="popularity"><span class="popularityValue" style="width: {{ popularity }}%"></span></span>
    {% } else { %}
    <span class="album">{{ album }}</span>
    {% } %}
  </li>
</script>
</body>
</html>