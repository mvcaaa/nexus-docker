console.debug("Initializing socketio");

const io = require("socket.io-client");
const	ioClient = io.connect("http://localhost:5000/enkora");

var tick_timer = null;

ioClient.on('connect', function() {
	console.debug("Connected to SocketIO");
	socket.emit("configure", {
		type: 'sst',
	});

	clearTimeout(tick_timer);
	tick_timer = setInterval(function() { socket.emit("tick"); }, 5000);
});

ioClient.on('disconnect', function() {
	clearTimeout(tick_timer);
});
