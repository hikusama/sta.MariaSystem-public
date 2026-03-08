<div align="center">
    <h1>Stamaria Enrollment System Containerized</h1>
    <img src="assets\image\mdlogo.gif" alt="Dockerized Picture" height="200">
</div>

Setup for Stamaria Enrollment System WSL
1. Install WSL2

Open PowerShell (Admin) and run:
```bash
wsl --install -d Ubuntu-24.04
wsl --set-default-version 2
```

Launch Ubuntu and create your Linux user.

2. Install Docker Engine

In Ubuntu:
```bash
sudo apt update
sudo apt install -y docker.io
sudo systemctl enable --now docker
sudo usermod -aG docker $USER
exit
wsl
```

3. Install Docker Compose plugin
```bash
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg \
  | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

echo \
"deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" \
| sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt update
sudo apt install docker-compose-plugin
docker compose version
```

4. Go to project folder

Assuming your project is at:

C:\Users\Acer\Desktop\sta.MariaSys

In Ubuntu:

```bash
cd /mnt/c/Users/Acer/Desktop/sta.MariaSys
```

5. Start containers
```bash
docker compose up -d
docker ps
```

6. To make it run at startup: Press Win + R, type:
```bash
shell:startup
```

Put the stamariasystem.bat file in that folder. It will run automatically when Windows starts.