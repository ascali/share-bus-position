#!/bin/bash

# Renew SSL certificates
certbot renew --quiet --post-hook "apachectl graceful"

# Check if renewal was successful
if [ $? -eq 0 ]; then
    echo "SSL certificates renewed successfully."
else
    echo "SSL certificate renewal failed."
fi


# docker exec -it <container_id> /bin/bash
# certbot --apache -d your-domain.com --non-interactive --agree-tos -m your-email@example.com
# certbot --apache -d localhost --non-interactive --agree-tos -m localhost@yopmail.com

# docker exec -it <container_id> /bin/bash
# tail -f /var/log/cron.log