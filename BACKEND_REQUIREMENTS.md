# Backend Requirements for Frontend

This document tells you exactly what your PHP backend needs to provide.

## 📋 Required Endpoints

### 1. Chat Messages

#### Get Messages
- **URL:** `GET api/getMessages.php?server=ID&channel=ID`
- **Returns:**
```json
{
  "success": true,
  "messages": [
    {
      "id": 1,
      "username": "User1",
      "avatar": "👤",
      "content": "Hello!",
      "timestamp": "2024-01-15T10:30:00Z"
    }
  ]
}
```

#### Send Message
- **URL:** `POST api/sendMessage.php`
- **Body:**
```json
{
  "serverId": "general",
  "channelId": "general",
  "content": "Hello!"
}
```
- **Returns:**
```json
{
  "success": true,
  "message": {
    "id": 123,
    "username": "You",
    "avatar": "👤",
    "content": "Hello!",
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

### 2. Servers & Channels

#### Get Servers
- **URL:** `GET api/getServers.php`
- **Returns:**
```json
{
  "success": true,
  "servers": [
    {
      "id": "1",
      "name": "General",
      "icon": "💬",
      "color": "bg-green-500"
    }
  ]
}
```

#### Get Channels
- **URL:** `GET api/getChannels.php?server=ID`
- **Returns:**
```json
{
  "success": true,
  "channels": [
    {
      "id": "general",
      "name": "general",
      "type": "text"
    }
  ]
}
```

### 3. Live Streams

#### Get Streams
- **URL:** `GET api/getStreams.php`
- **Returns:**
```json
{
  "success": true,
  "streams": [
    {
      "id": 1,
      "title": "Epic Gaming Session",
      "streamer": "ProGamer123",
      "viewers": 1234,
      "isLive": true
    }
  ]
}
```

#### Get Stream Chat
- **URL:** `GET api/getStreamChat.php?streamId=1`
- **Returns:**
```json
{
  "success": true,
  "messages": [
    {
      "id": 1,
      "username": "User1",
      "message": "Great stream!",
      "timestamp": "2m ago"
    }
  ]
}
```

### 4. Twitter Feed

#### Get Feed
- **URL:** `GET api/getFeed.php?page=1`
- **Returns:**
```json
{
  "success": true,
  "posts": [
    {
      "id": 1,
      "username": "User1",
      "handle": "@user1",
      "avatar": "👤",
      "content": "This is a post!",
      "timestamp": "2024-01-15T10:30:00Z",
      "likes": 10,
      "retweets": 5,
      "replies": 2,
      "hasMedia": false,
      "mediaType": null
    }
  ],
  "hasMore": true
}
```

#### Create Post
- **URL:** `POST api/createPost.php`
- **Body:**
```json
{
  "content": "My tweet text"
}
```
- **Returns:**
```json
{
  "success": true,
  "post": {
    "id": 123,
    "content": "My tweet text",
    "timestamp": "2024-01-15T10:30:00Z"
  }
}
```

#### Like Post
- **URL:** `POST api/likePost.php`
- **Body:**
```json
{
  "postId": 123
}
```
- **Returns:**
```json
{
  "success": true,
  "likes": 456
}
```

## 🔧 Important Notes

1. **CORS:** Your PHP must allow requests from `http://localhost:3000`
   ```php
   header('Access-Control-Allow-Origin: http://localhost:3000');
   header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
   header('Access-Control-Allow-Headers: Content-Type');
   ```

2. **JSON Format:** All responses must be valid JSON with `Content-Type: application/json`

3. **Error Handling:** Return errors like this:
   ```json
   {
     "success": false,
     "error": "Error message here"
   }
   ```

4. **Timestamps:** Use ISO 8601 format: `"2024-01-15T10:30:00Z"`

## 📝 Where to See Examples

Open `src/services/api.js` in the frontend - it shows exactly what each endpoint should do!



