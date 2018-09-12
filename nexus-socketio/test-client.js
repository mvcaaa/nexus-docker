process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';

console.debug("Initializing socketio");

var fs = require('fs');

const io = require("socket.io-client");
const ioClient = io.connect("https://mvc.local:5000/enkora", 
	{
		ca: [ fs.readFileSync('mvc.local.crt') ],
		rejectUnauthorized: false,
		transports: ['websocket']
	}
);

console.debug("Initialisation complete");

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
	console.debug("Disconnected");
	clearTimeout(tick_timer);
});

ioClient.on('error', function(error) {
	console.debug(error);
});