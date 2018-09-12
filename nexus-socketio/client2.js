const
    io = require("socket.io-client"),
    ioClient = io.connect("http://localhost:5000/enkora");

ioClient.on("seq-num", (msg) => console.info(msg));

ioClient.on('connect', function() {
	console.debug("Connected to SocketIO");
	ioClient.emit("configure", {
		type: 'sst',
	});
});