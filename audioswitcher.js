var AudioSwitcher = function(containerElement) {
	this.audioElements = [];
	this.$el = $(containerElement);
	this.timeDriftThreshold = 0.5; // seconds
	this.currentIndex = null;
	this.init();
};

AudioSwitcher.prototype.init = function() {
	var self = this;
	// TODO: there is a bug here where toggling doesn't highlight active button
	this.audioElements = this.$el.find('audio');
	this.audioElements.bind('pause', function() {
		self.$el.find('.switch').removeClass('active');
	});
	this.audioElements.bind('play', function(event) {
		var index = self.audioElements.index(event.target);
		if (self.currentIndex !== null) {
			//index = self.currentIndes;
			if (index !== self.currentIndex) return;
		}
		self.$el.find('.switch').eq(index).addClass('active');
		// blunt hack: pause all audio elements outside this group
		$('audio').not(self.$el.find('audio')).each(function() {
			this.pause();
		});
	});
	this.$el.find('.switch.a').bind('click', function() {
		self.switchToAndPlay(0);
	});
	this.$el.find('.switch.b').bind('click', function() {
		self.switchToAndPlay(1);
	});
	this.$el.find('.toggle').bind('click', function() {
		self.cycle();
	});
	this.$el.find('.rewind').bind('click', function() {
		self.rewind(3);
	});
	this.$el.find('.toggleAndRewind').bind('click', function() {
		self.rewind(3);
		self.cycle();
	});
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
	// Switch and play
	this.switchTo(idx);
	this.play();
};

AudioSwitcher.prototype.switchTo = function(idx) {
	console.log("Switching to", idx);
	// Always sync before switching
	this.sync(this.currentIndex, idx);
	this.currentIndex = idx;
	for (var i = 0; i < this.audioElements.length; i++) {
		if (i === this.currentIndex) {
			this.audioElements[i].volume = 1;
			this.audioElements[i].style.display = 'inline-block';
			this.$el.find('.switch').eq(i).addClass('active');
		} else {
			this.audioElements[i].volume = 0;
			// this.fadeVolume(this.audioElements[i], 0);
			this.audioElements[i].style.display = 'none';
			this.$el.find('.switch').eq(i).removeClass('active');
		}
	}
};

// Currently unused
AudioSwitcher.prototype.fadeVolume = function(audioElement, fadeTo, callback) {
	var self = this;
	var step = 0.02;
	if (audioElement.volume > fadeTo) step = step * -1;
	var duration = 0.1;
	var interval = duration / step;
	if (Math.abs(audioElement.volume - fadeTo) <= Math.abs(step)) {
		console.log('finishing fade', audioElement);
		audioElement.volume = fadeTo;
	} else {
		console.log('setting volume to', audioElement.volume + step);
		audioElement.volume += step;
		setTimeout(function() {
			self.fadeVolume(audioElement, fadeTo, callback);
		}, interval);
	}
}

AudioSwitcher.prototype.getActiveElement = function() {
	return this.audioElements[this.currentIndex];
}

AudioSwitcher.prototype.rewind = function(seconds) {
	seconds = seconds || 3;
	var el = this.getActiveElement();
	el.currentTime = Math.max(el.currentTime - seconds, 0);
}

AudioSwitcher.prototype.cycle = function() {
	var nextIdx = (this.currentIndex + 1) % this.audioElements.length;
	this.switchToAndPlay(nextIdx);
};

AudioSwitcher.prototype.sync = function(from, to) {
	if (from !== null) {
		var currentTime = this.audioElements[from].currentTime;
		if (Math.abs(this.audioElements[to].currentTime - currentTime) > this.timeDriftThreshold) {
			console.log('syncing audio...');
			this.audioElements[to].currentTime = this.audioElements[from].currentTime;
		}
	}
};
