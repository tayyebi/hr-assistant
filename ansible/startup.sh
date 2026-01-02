#!/bin/bash
set -e

# Wait for app service to be healthy
until curl -sSf http://app:8080/login > /dev/null; do
  echo "Waiting for app service..."
  sleep 2
done

echo "App service is up. Running Ansible E2E tests..."
ansible-playbook -i ansible/inventory ansible/test_e2e.yml
