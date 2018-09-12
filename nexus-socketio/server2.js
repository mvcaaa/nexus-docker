const
    io = require("socket.io"),
    server = io.listen(5000);

let
    sequenceNumberByClient = new Map();

let
    randomToken = function () {
        return 'mf_' + Math.random().toString(36).slice(-18)
    };


var enkora = server.of('/enkora');


    console.info("Server is up on port 5000")
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

// sends each client its current sequence number
setInterval(() => {
    for (const [client, sequenceNumber] of sequenceNumberByClient.entries()) {
        client.emit("seq-num", sequenceNumber);
        sequenceNumberByClient.set(client, sequenceNumber + 1);
    }
}, 1000);

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