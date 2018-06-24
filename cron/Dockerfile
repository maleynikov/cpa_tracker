FROM ubuntu:latest

# Add crontab file in the cron directory
ADD crontab /etc/cron.d/cpa-cron

# Give execution rights on the cron job
RUN chmod 0644 /etc/cron.d/cpa-cron

# Create the log file to be able to run tail
RUN touch /var/log/cron.log

#Install Cron
RUN apt-get update && apt-get -y install \
     cron \
     curl

# Run the command on container startup
CMD ["cron", "-f"]