# Installation Document for Ollama

## Overview
Ollama is a tool for running machine learning models with ease. This document provides step-by-step instructions for installing Ollama using Docker and demonstrates how to interact with it through the API.

## Prerequisites
- Docker installed on your machine.
- Basic knowledge of using the terminal/command line.
- An internet connection to download the Docker images.

## Installation Steps

1. **Open Terminal**: Launch your terminal application.

2. **Create a Docker Network (optional but recommended)**:  
   ```bash
   docker network create ollama-network

3. **Run the Ollama Container: Use the following command to run the Ollama container, making sure to expose the necessary ports.**:
docker run -d --network ollama-network -p 11434:11434 --name ollama ollama/ollama
Verify the Container is Running: Check if the Ollama container is up and running:

docker ps
Pull the Required Model: Before using the API, ensure that the model you want to use is available. Replace llama3 with the appropriate model name if necessary:

docker exec ollama ollama pull llama3
Test the API: You can test the API using curl to ensure it's working correctly:

curl -X POST http://localhost:11434/api/generate -H "Content-Type: application/json" -d '{"model": "llama3", "prompt": "Hello world!"}'
Or use this if the IP address is different:

curl -X POST http://172.17.0.1:11434/api/generate -H "Content-Type: application/json" -d '{"model": "llama3", "prompt": "Hello world!"}'
You should receive a response with the model output.

Troubleshooting
If you encounter errors about ports being in use, check running containers with docker ps and stop/remove conflicting containers.
Ensure that the required models are pulled before attempting to generate responses.
Ollama Installation Script
Create a file named install_ollama.sh with the following content:

#!/bin/bash
# Create Docker network
echo "Creating Docker network..."
docker network create ollama-network

# Run the Ollama container
echo "Running Ollama container..."
docker run -d --network ollama-network -p 11434:11434 --name ollama ollama/ollama

# Pull the required model
echo "Pulling model llama3..."
docker exec ollama ollama pull llama3

# Output container status
echo "Ollama container is up and running. You can access it at http://localhost:11434/api/generate"
echo "Test the API using the following command:"
echo "curl -X POST http://localhost:11434/api/generate -H 'Content-Type: application/json' -d '{\"model\": \"llama3\", \"prompt\": \"Hello world!\"}'"
Instructions for the Script
Save the script to your preferred directory.

Make it executable:

chmod +x install_ollama.sh
Run the script:

./install_ollama.sh

