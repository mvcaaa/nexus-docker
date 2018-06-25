app = require('express');

const fs = require('fs');

const
		httpsOptions = {
			key: fs.readFileSync('mvc.loc.key'),
			cert: fs.readFileSync('mvc.loc.crt')
		};

const
		secureServer = require('https').createServer(httpsOptions, app);

const
		io = require("socket.io"),
		server = io.listen(secureServer, {pingTimeout: 7000, pingInterval: 10000});

let
		sequenceNumberByClient = new Map();

let
		randomToken = function () {
			return 'mf_' + Math.random().toString(36).slice(-18)
		};

const PORT = 5000;

secureServer.listen(PORT);
console.log('Server is running');

var enkora = server.of('/enkora');

// event fired every time a new client connects:
enkora.on("connection", (socket) => {
	console.info(`Client connected [id=${socket.id}]`);
// initialize this client's sequence number
	sequenceNumberByClient.set(socket, 1);

// when socket disconnects, remove it from the list:
	socket.on("disconnect", () => {
		sequenceNumberByClient.delete(socket);
		console.info(`Client gone [id=${socket.id}]`);
	});

	socket.on('configure', () => {
		console.log("New client reported, reply with token: new-sst-image");
		socket.emit("configure", {'auth_token' : 'new-sst-image' });
	});

});

var readline = require('readline');

// var debug_tokens = {mf_1601250804: true, mf_36031906864489220: true, mf_36067626910973956: true};

setInterval(() => {
			for (const [client, sequenceNumber] of sequenceNumberByClient.entries()
					) {
				var rl = readline.createInterface({
					input: process.stdin,
					output: process.stdout
				});
				rl.question('Token: ', function (token) {
					if (token == 'exit') //we need some base case, for recursion
						return rl.close(); //closing RL and returning from function.
					client.emit("token", {'token': token});
					console.log('Token: '+ token + ' sent');
				});
				sequenceNumberByClient.set(client, sequenceNumber + 1);
			}
		},
		6000
);