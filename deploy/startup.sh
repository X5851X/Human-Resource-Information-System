#!/bin/bash
apt-get update
apt-get install -y docker.io docker-compose git unzip
systemctl enable docker
cd /home
git clone https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git app
cd app
docker-compose up -d --build
