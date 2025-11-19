const NodeMediaServer = require('node-media-server');
const config = require('./nms-config.js');
const nms = new NodeMediaServer(config);

nms.run();
console.log('Node Media Server v4.0.17 STARTED');
console.log('RTMP: rtmp://localhost:1935/live');
console.log('HLS: http://localhost:8000/live/STREAM_KEY/index.m3u8');
console.log('Leave this window open!');