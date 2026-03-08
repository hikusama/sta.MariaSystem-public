@echo off
echo Starting Stamaria Enrollment System app in Docker...
REM Run Docker Compose from your project folder in WSL
wsl -d Ubuntu-24.04 -- /bin/bash -c "cd /mnt/c/Users/Acer/Desktop/sta.MariaSys && docker compose up -d"