const
		io = require("socket.io-client"),
		ioClient = io.connect("https://mvc.local:5000/enkora");

ioClient.on("seq-num", (msg) => console.info(msg));