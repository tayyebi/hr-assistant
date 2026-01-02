# Ansible E2E Test Container

This directory contains the Dockerfile and configuration for running Ansible-based end-to-end tests in an isolated container.

## Usage

1. **Build the container:**
   ```bash
   docker build -t hr-assistant-ansible ./ansible
   ```

2. **Run Ansible playbooks:**
   ```bash
   docker run --rm -v "$PWD:/workspace" -w /workspace hr-assistant-ansible ansible-playbook -i ansible/inventory ansible/test_e2e.yml
   ```

## Notes
- The container includes Ansible, Python 3, and curl.
- You can add custom Python modules to the Dockerfile if needed.
- Place your playbooks, roles, and inventory files in the `ansible/` directory.
- The container can interact with other services via Docker Compose if configured.
