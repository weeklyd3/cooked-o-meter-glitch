<?php
$title = 'FEEL THE PAIN!!';
$calc_required = true;
require_once 'elements/header.php';
?>
<p>Your least favorite theory exercise is back! Play the rhythms, but you have two lines to worry about this time! Yippee!</p>
<p>Note: This requires JavaScript to work properly.</p>
<p>Note: 1 is the bottom line, 2 is the top line. You should use your keyboard to enter two notes at the same time, but if you aren't using keyboard, you need to <button onclick="single_channel = true">restrict it to one line</button>, in which case both buttons have the same function.</p>
<div id="pad" style="text-align: center;"></div>
<div style="text-align: center;">
	<br />
	<button class="music-button" style="font-size:90px; -webkit-appearance: none; appearance: none; padding: 20px; padding-left: 50px; padding-right: 50px;" id="button-1" disabled="disabled" onclick="click_button(1)">1</button>
	<button class="music-button" style="font-size:90px; -webkit-appearance: none; appearance: none; padding: 20px; padding-left: 50px; padding-right: 50px;" id="button-2" disabled="disabled" onclick="click_button(2)">2</button>
	<br /><br />
	Notes: <br />
	<label><input type="checkbox" id="note_4" checked="checked" /> Whole</label>
	<label><input type="checkbox" id="note_2" checked="checked" /> Half</label>
	<label><input type="checkbox" id="note_1" checked="checked" /> Quarter</label>
	<label><input type="checkbox" id="note_0.5" checked="checked" /> 8th</label>
	<label><input type="checkbox" id="note_0.25" /> 16th</label>
	<label><input type="checkbox" id="note_0.125" /> 32nd</label><br />
	Key signature <label>top: <input type="number" value="4" onchange="if (Number(this.value)) time_top_new = this.value;" /></label> <label>bottom: <input type="number" value="4" onchange="if (Number(this.value)) time_bottom_new = this.value;" /></label>
	<br />
	<label>requested bpm: <input type="number" value="60" onchange="if (Number(this.value)) bpm = this.value;" /></label>
	<br />
	<button onclick="start()">let's go</button>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.11.3/p5.min.js" integrity="sha512-I0Pwwz3PPNQkWes+rcSoQqikKFfRmTfGQrcNzZbm8ALaUyJuFdyRinl805shE8xT6iEWsWgvRxdXb3yhQNXKoA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
function update() {
	draw.clear();
	draw.strokeWeight(0);
	if (player_started) {
		update_timing();
		draw.push();
		if (player_measure >= 0) {
			draw.text('(measure)', 400 - 150, 50);
			draw.fill('lime');
			var player_measure_line = Math.floor(player_measure / 3);
			var player_measure_remainder = player_measure % 3;
			draw.rect(40 + 250 * player_measure_remainder, 100 * (player_measure_line + 1), 250, 100);
			draw.fill('black');
		} else draw.text('GET READY!!!', 400 - 150, 50);
		draw.text('(beat)', 400 + 150, 50);
		draw.textSize(20);
		if (player_measure >= 0) {
			draw.text(player_measure + 1, 400 - 75, 50);
		}
		draw.text(player_beat + 1, 400 + 75, 50);
		draw.pop();
		draw.rect(0, 60, 800, 20);
		draw.fill('white');
		draw.rect(2.5 + 780 * Math.abs(player_total_beats % 2 - 1), 62.5, 15, 15);
		draw.fill('black');
	}
	draw.strokeWeight(1);
	measure = 0;
	draw_line(37.5 * 4 / 3 + 100, true);
	draw_line(37.5 * 3 * 4 / 3 + 100, false);
	draw_line(37.5 * 5 * 4 / 3 + 100, false);
	draw_line(37.5 * 7 * 4 / 3 + 100, false, true);
	draw.push();
	draw.pop();
}
function start_timing() {
	player_started = true;
	start_time = performance.now();
	ms_per_beat = 60000 / bpm;
	ms_per_quarter = ms_per_beat * (time_bottom) / 4;
	ms_per_measure = ms_per_beat * time_top;
}
function update_timing() {
	var time_elapsed = performance.now() - start_time;
	player_measure = -2 + Math.floor(time_elapsed / ms_per_measure);
	player_total_beats = time_elapsed / ms_per_beat;
	player_beat = Math.floor(time_elapsed / ms_per_beat) % time_top;
	player_beat_raw = (time_elapsed / ms_per_beat) % time_top;
	if (player_measure > 12) player_started = false;
}
var time_top = 4;
var time_bottom = 4;
var time_top_new = 4;
var time_bottom_new = 4;
var notes_top = [];
var notes_bottom = [];
var beats_top = [];
for (var i = 0; i < 14; i++) beats_top.push([]);
var beats_bottom = [];
for (var i = 0; i < 14; i++) beats_bottom.push([]);
var player_measure = -2;
var player_beat = 0;
var player_beat_raw = 0;
var player_total_beats = 0;
var player_started = false;
var start_time = 0;
var single_channel = false;
var bpm = 60;
function draw_line(y, time, last) {
	draw.push();
	draw.translate(0, y);
	draw.fill('black');
	draw.line(10, 0, 790, 0);
	draw.strokeWeight(3);
	draw.line(15, -7, 15, 7);
	draw.line(20, -7, 20, 7);
	if (time) {
		draw.text(time_top, 30, -7);
		draw.text(time_bottom, 30, 7);
	}
	draw.strokeWeight(1);
	for (var i = 0; i < 4; i++) {
		var x = (790 - 40) / 3 * i + 40;
		if (i != 3) { 
			measure++;
			draw.text(measure, x, -25);
		}
		draw.line(x, -20, x, 20);
	}
	if (!notes_top[measure - 3]) return draw.pop();
	var top_notes = notes_top.slice(measure - 3, measure);
	var bottom_notes = notes_bottom.slice(measure - 3, measure);
	var top_beats = beats_top.slice(measure - 1, measure + 2);
	var bottom_beats = beats_bottom.slice(measure - 1, measure + 2);
	if (single_channel) {
		draw_notes(0, top_notes, top_beats, true);
	} else {
		draw_notes(-3, top_notes, top_beats, true);
		draw_notes(3, bottom_notes, bottom_beats, false);
	}
	draw.pop();
}
function draw_notes(y, notes, beats, up_stem) {
	draw.push();
	draw.translate(40, y);
	draw.strokeWeight(1);
	var stem_multiplier = up_stem ? -1 : 1;
	var measure_index = -1;
	for (const measure of notes) {
		measure_index += 1;
		draw.translate(7, 0);
		var index = 0;
		draw.stroke('black');
		draw.strokeWeight(1);
		for (const note of measure) {
			if (note == 2 || note == 4) draw.fill('white');
			else draw.fill('black');
			draw.strokeWeight(1);
			draw.ellipse(0, 0, 6, 5);
			if (note <= 2) {
				draw.line(-stem_multiplier * 3, 0, -stem_multiplier * 3, stem_multiplier * 20);
			}
			if (note < 1) {
				if (index > 0 && (measure[index - 1] < 1)) {
					var previous_note = measure[index - 1];
					draw.strokeWeight(2);
					draw.line(-stem_multiplier * 3, stem_multiplier * 20, -previous_note * 236 / (time_top * 4 / time_bottom) - stem_multiplier * 3, stem_multiplier * 20);
					if (previous_note < 1) {
						if (previous_note < 1 / 2) {
							draw.line(-stem_multiplier * 3, stem_multiplier * 17, -previous_note * 236 / (time_top * 4 / time_bottom) - stem_multiplier * 3, stem_multiplier * 17);
						}
						if (previous_note < 1 / 4) {
							draw.line(-stem_multiplier * 3, stem_multiplier * 14, -previous_note * 236 / (time_top * 4 / time_bottom) - stem_multiplier * 3, stem_multiplier * 14);
						}
					}
					if (index < (measure.length - 1)) var next_note = measure[index + 1];
					else next_note = 0;
					if (previous_note > note && note != next_note) {
						draw.line(-stem_multiplier * 3, stem_multiplier * 20, -stem_multiplier * 3 - 3, stem_multiplier * 20);
						if (note < 1 / 2) {
							draw.line(-stem_multiplier * 3, stem_multiplier * 17, -stem_multiplier * 3 - 3, stem_multiplier * 17);
						}
						if (note < 1 / 4) {
							draw.line(-stem_multiplier * 3, stem_multiplier * 14, -stem_multiplier * 3 - 3, stem_multiplier * 14);
						}
					}
				} else if (index < (measure.length - 1) && (measure[index + 1] < 1) && (measure[index + 1] > note) && (index == 0 || measure[index - 1] != note)) {
					draw.strokeWeight(2);
					draw.line(-stem_multiplier * 3, stem_multiplier * 20, -stem_multiplier * 3 + 3, stem_multiplier * 20);
					if (note < 1 / 2) {
						draw.line(-stem_multiplier * 3, stem_multiplier * 17, -stem_multiplier * 3 + 3, stem_multiplier * 17);
					}
					if (note < 1 / 4) {
						draw.line(-stem_multiplier * 3, stem_multiplier * 14, -stem_multiplier * 3 + 3, stem_multiplier * 14);
					}
				} else if (index < (measure.length - 1) && (measure[index + 1] < 1)) {
				} else {
					draw.strokeWeight(2);
					draw.line(-stem_multiplier * 3, stem_multiplier * 20, -stem_multiplier * 3 + 5, stem_multiplier * 20 - 7 * stem_multiplier);
					if (note < 1 / 2) {
						draw.line(-stem_multiplier * 3, stem_multiplier * 16, -stem_multiplier * 3 + 5, stem_multiplier * 16 - 7 * stem_multiplier);
					}
					if (note < 1 / 4) {
						draw.line(-stem_multiplier * 3, stem_multiplier * 12, -stem_multiplier * 3 + 5, stem_multiplier * 12 - 7 * stem_multiplier);
					}
				}
			}
			draw.translate(236 * note / (time_top * 4 / time_bottom), 0);
			index++;
		}

		draw.stroke('red');
		draw.strokeWeight(2);
		for (const beat of beats[measure_index]) {
			var x = 236 * beat / time_top - 236;
			draw.line(x - 3, -3, x + 3, 3);
			draw.line(x + 3, -3, x - 3, 3);
		}
		draw.translate(7, 0);
	}
	draw.pop();
}
function get_valid_notes() {
	var valid_notes = [];
	if (document.getElementById('note_4').checked) valid_notes.push(4);
	if (document.getElementById('note_2').checked) valid_notes.push(2);
	if (document.getElementById('note_1').checked) valid_notes.push(1);
	if (document.getElementById('note_0.5').checked) valid_notes.push(0.5);
	if (document.getElementById('note_0.25').checked) valid_notes.push(0.25);
	if (document.getElementById('note_0.125').checked) valid_notes.push(0.125);
	return valid_notes;
}
function generate_notes(notes) {
	while (notes.length) notes.pop();
	for (var i = 0; i < 12; i++) {
		var measure = [];
		var quarters = time_top * 4 / time_bottom;
		var quarters_taken = 0;
		var note_lengths = [4, 2, 1, 1 / 2, 1 / 4, ]; //1 / 8];
		note_lengths = get_valid_notes();
		var last_note = null;
		while (quarters_taken < quarters) {
			var quarters_left = quarters - quarters_taken;
			var filter = note_lengths.filter((b) => b <= quarters_left);
			if (last_note == 1 / 8) {
				filter.push(1 / 8);
				filter.push(1 / 8);
			}
			if (last_note == 1 / 4) {
				//filter.push(1 / 8);
				filter.push(1 / 4);
				filter.push(1 / 4);
			}
			if (last_note == 1 / 2) {
				//filter.push(1 / 8);
				filter.push(1 / 4);
				filter.push(1 / 2);
			}
			var note = filter[Math.floor(Math.random() * filter.length)];
			quarters_taken += note;
			measure.push(note);
		}
		notes.push(measure);
	}
}
function start() {
	time_top = time_top_new;
	time_bottom = time_bottom_new;
	generate_notes(notes_top);
	generate_notes(notes_bottom);
	document.getElementById('button-1').disabled = false;
	document.getElementById('button-2').disabled = false;
}
function click_button(button) {
	if (single_channel) button = 2;
	if (!player_started) return start_timing();
	update_timing();
	if (button == 1) {
		beats_bottom[player_measure + 2].push(player_beat_raw);
	} else {
		beats_top[player_measure + 2].push(player_beat_raw);
	}
}
var s = function(sketch) {
	sketch.setup = async function() {
		sketch.createCanvas(800, 500);
		draw.angleMode('degrees');
		draw.frameRate(60);
		draw.strokeCap('square');
		draw.textAlign('center', 'center');
	};
	sketch.draw = update;
}
onkeydown = (ev) => {
	if (ev.key == 1) click_button(1);
	if (ev.key == 2) click_button(2);
};
var draw = new p5(s, 'pad'); 
</script>
<style>
	button.music-button:active {
		background-color: blue;
		color: white;
	}
</style>
<?php
require_once 'elements/footer.php';