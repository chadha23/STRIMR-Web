module.exports = {
  rtmp: { port: 1935, chunk_size: 60000, gop_cache: true, ping: 30, ping_timeout: 60 },
  http: { port: 8000, allow_origin: '*', mediaroot: './public/stream' },
  hls: { 
    path: './public/stream',
    fragment: 3,
    playlist_length: 60,
    cleanup: true
  },
  logType: 3
};