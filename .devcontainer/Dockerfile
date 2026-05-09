FROM mcr.microsoft.com/devcontainers/universal:2

# Remove broken Yarn apt source inherited from the base image.
# It can fail apt update during devcontainer feature installation.
RUN rm -f /etc/apt/sources.list.d/yarn.list \
    && rm -f /usr/share/keyrings/yarnkey.gpg

# Create workspace directory to avoid the "no such file or directory" error
RUN mkdir -p /workspace
