'use strict'
var app = require('express');
var server = require('http').Server(app);
var io = require('socket.io')(server);
require('dotenv').config();

var redisHost = process.env.REDIS_HOST;
var redisPort = process.env.REDIS_PORT;
var serverPort = process.env.BROADCAST_PORT;
var serverHost = process.env.BROADCAST_HOST;
var ioRedis = require('ioredis');
var redis = new ioRedis(redisPort, redisHost);

var listChannel = {
  complaintChannel: 'complaint-channel',
  assignedComplaintChannel: 'assign-complaint-channel',
  startWorkingComplaintChannel: 'start-work-complaint-channel',
  finishedWorkingComplaintChannel: 'finished-work-complaint-channel',
  notificationChannel: 'notification-channel',
  
  addOrderChannel: 'add-order-channel',
  agreeOrderChannel: 'agree-order-channel',
  disagreeOrderChannel: 'disagree-order-channel'
}


var sourceData = {
  users: [],
}

redis.subscribe(listChannel.complaintChannel, () => {
  console.log(`Now you subscribe channel ${listChannel.complaintChannel}`);
});

redis.subscribe(listChannel.assignedComplaintChannel, () => {
  console.log(`Now you subscribe channel ${listChannel.assignedComplaintChannel}`);
});

redis.subscribe(listChannel.startWorkingComplaintChannel, () => {
  console.log(`Now you subscribe channel ${listChannel.startWorkingComplaintChannel}`);
});

redis.subscribe(listChannel.finishedWorkingComplaintChannel, () => {
  console.log(`Now you subscribe channel ${listChannel.finishedWorkingComplaintChannel}`);
});

redis.subscribe(listChannel.readNotification, () => {
  console.log(`Now you subscribe channel ${listChannel.notificationChannel}`);
});

//Order Channel
redis.subscribe(listChannel.addOrderChannel, () => {
  console.log(`Now you subscribe channel ${listChannel.addOrderChannel}`);
});

//Agree Order Channel
redis.subscribe(listChannel.agreeOrderChannel, () => {
  console.log(`Now you subscribe channel ${listChannel.agreeOrderChannel}`);
});

//Disagree Order Channel
redis.subscribe(listChannel.disagreeOrderChannel, () => {
  console.log(`Now you subscribe channel ${listChannel.disagreeOrderChannel}`);
});


redis.on('message', (channel,message) => {
  message = JSON.parse(message);
  console.log("[Run On Channel] On", channel);
  io.emit(channel + ":" + message.event, message.data);
});

io.on("connection", function (socket) {
  socket.on("sendUserLogin", function (userId) {
    sourceData.users[userId] = socket.id;
    io.emit("sendDataUserActiveLogin", sourceData.users);
  });

  socket.on("disconnect", function () {
    var i = sourceData.users.indexOf(socket.id);
    sourceData.users.splice(i, 1, 0);
    io.emit('sendDataUserActiveLogin', sourceData.users);
  });
});


server.listen(serverPort, serverHost, () => {
  console.log(`Socket server is running on host ${serverHost} and port ${serverPort}`)
});
