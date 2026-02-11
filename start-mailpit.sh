#!/bin/bash
# Check if Mailpit is already running
if ! pgrep -x "mailpit" > /dev/null
then
    echo "Starting Mailpit..."
    mailpit -l :8085 -s :1025 &
else
    echo "Mailpit already running"
fi
