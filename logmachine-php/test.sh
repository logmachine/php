curl -X POST "https://logmachine.bufferpunk.com/api/logs?room=php-logger" \
  -H "Content-Type: application/json" \
  -d '{
    "user": "rezzcode",
    "module": "logmachine-php",
    "level": "INFO",
    "timestamp": "2025-08-06T18:00:00+00:00",
    "message": "Priming php-logger room"
  }'
