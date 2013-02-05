// see https://github.com/hungrymedia/hm-audio-sync/blob/master/hm-audio-sync.js

var AudioSwitcher = function(masterAudioElement, slaveAudioElement) {
	this.audioElements = [].splice.call(arguments, 0);
	this.currentIndex = null;

	this.masterAudio = masterAudioElement;
	this.masterAudio.addEventListener('play', this.onMasterPlay.bind(this));
	this.masterAudio.addEventListener('pause', this.onMasterPause.bind(this));
};

AudioSwitcher.prototype.init = function() {
	
};

AudioSwitcher.prototype.onMasterPlay = function() {
	//this.syncTimer = setInterval(this.sync.bind(this), 250);
};

AudioSwitcher.prototype.onMasterPause = function() {
	//clearInterval(this.syncTimer);
};

AudioSwitcher.prototype.play = function() {
	for (var i = 0; i < this.audioElements.length; i++) {
		this.audioElements[i].play();
	}
}

AudioSwitcher.prototype.pause = function() {
	for (var i = 0; i < this.audioElements.length; i++) {
		this.audioElements[i].pause();
	}
}

AudioSwitcher.prototype.switchToAndPlay = function(idx) {
	// Guard
	idx = Math.max(idx, 0);
	idx = Math.min(idx, this.audioElements.length - 1);
	if (idx === this.currentIndex) {
		return;
	}
	// Sync, switch, and play
	if (this.currentIndex !== null) {
		this.sync(this.currentIndex, idx);
	}
	this.switchTo(idx);
	this.play();
};

AudioSwitcher.prototype.switchTo = function(idx) {
	console.log("Switching to", idx);
	this.currentIndex = idx;
	for (var i = 0; i < this.audioElements.length; i++) {
		if (i === this.currentIndex) {
			this.audioElements[i].volume = 1;
			this.audioElements[i].style.display = 'inline-block';
		} else {
			this.audioElements[i].volume = 0;
			this.audioElements[i].style.display = 'none';
		}
	}
};

AudioSwitcher.prototype.cycle = function() {
	this.switchTo((this.currentIndex + 1) % this.audioElements.length);
};

AudioSwitcher.prototype.sync = function(from, to) {
	this.audioElements[to].currentTime = this.audioElements[from].currentTime;
//	for (var i = 1; i < this.audioElements.length; i++) {
//		this.audioElements[i].currentTime = this.masterAudio.currentTime;
//	}
};