<?php

?>
<!doctype html>
<html>
<head>
	<title>Audio Test</title>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
	<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.3.1/underscore-min.js"></script>
	<script type="text/javascript" src="http://cdnjs.cloudflare.com/ajax/libs/backbone.js/0.9.1/backbone-min.js"></script>
	<script type="text/javascript" src="audioswitcher.js"></script>
	<link rel="stylesheet" href="audiotest.css">
</head>
<body>
	<h1>Audio Test</h1>

	<ol>
		<li>
            <div class="listitem">
                <div class="col1">
                    <button class="switch" id="a">A</button>
                    <div class="chooser">
                        <input type="radio" id="chooser_0_0" name="audio_0" value="0">
                        <label for="chooser_0_0">A is Watermarked</label>
                    </div>
                </div>
                <div class="col2">
                    <button class="toggle" id="toggle">Toggle &rarr;</button>
                    <button class="toggle" id="rewind">Rewind &#8634; 3 sec</button><br>
                    <button class="toggle" id="toggleAndRewind">Toggle and Rewind &#8634; 3 sec</button>
                    <audio id="audio_0_0" controls="true" src="audio/file_1.mp3"></audio>
                    <audio id="audio_0_1" controls="true" src="audio/file_2.mp3" style="display: none"></audio>
                </div>
                <div class="col3">
                    <button class="switch" id="b">B</button>
                    <div class="chooser">
                        <input type="radio" id="chooser_0_1" name="audio_0" value="1">
                        <label for="chooser_0_1">B is Watermarked</label>
                    </div>
                </div>
            </div>
		</li>
	</ol>
    <script>
        var audioSwitcher1 = new AudioSwitcher(document.getElementById('audio_0_0'), document.getElementById('audio_0_1'));
        $('#a').bind('click', function() {
            audioSwitcher1.switchToAndPlay(0);
        });
        $('#b').bind('click', function() {
            audioSwitcher1.switchToAndPlay(1);
        });

        $('#toggle').bind('click', function() {
            audioSwitcher1.cycle();
        });
        $('#rewind').bind('click', function() {
            audioSwitcher1.rewind(3);
        });
        $('#toggleAndRewind').bind('click', function() {
            audioSwitcher1.rewind(3);
            audioSwitcher1.cycle();
        });
    </script>
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